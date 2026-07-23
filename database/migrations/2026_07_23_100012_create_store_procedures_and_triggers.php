<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {

        // 1. SP: sp_receive_material
        DB::unprepared(<<<SQL
            CREATE OR REPLACE PROCEDURE sp_receive_material(p_supplier_id INT, p_receipt_number VARCHAR, p_items JSON)
            LANGUAGE plpgsql AS $$
            DECLARE
                v_receipt_id INT;
                v_item RECORD;
                v_current_stock DECIMAL;
            BEGIN
                /* Create Receipt Record */
                INSERT INTO material_receipts (supplier_id, receipt_number, receipt_date, created_at, updated_at)
                VALUES (p_supplier_id, p_receipt_number, CURRENT_DATE, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
                RETURNING id INTO v_receipt_id;

                /* Loop through JSON items */
                FOR v_item IN SELECT * FROM json_to_recordset(p_items) AS x(material_id INT, quantity DECIMAL)
                LOOP
                    /* Insert receipt item */
                    INSERT INTO material_receipt_items (receipt_id, material_id, quantity)
                    VALUES (v_receipt_id, v_item.material_id, v_item.quantity);

                    /* Lock and update material stock */
                    SELECT stock INTO v_current_stock FROM materials WHERE id = v_item.material_id FOR UPDATE;
                    UPDATE materials SET stock = stock + v_item.quantity WHERE id = v_item.material_id;

                    /* Record Movement */
                    INSERT INTO stock_movements (reference_type, reference_id, item_type, item_id, movement_type, quantity, balance, movement_date)
                    VALUES ('Receipt', v_receipt_id, 'Material', v_item.material_id, 'In', v_item.quantity, v_current_stock + v_item.quantity, CURRENT_TIMESTAMP);
                END LOOP;
            END;
            $$;
SQL
        );

        // 2. SP: sp_start_production
        DB::unprepared(<<<SQL
            CREATE OR REPLACE PROCEDURE sp_start_production(p_wo_id INT)
            LANGUAGE plpgsql AS $$
            DECLARE
                v_wo RECORD;
                v_bom_id INT;
                v_bom_item RECORD;
                v_current_stock DECIMAL;
                v_req_qty DECIMAL;
            BEGIN
                /* Lock Work Order */
                SELECT * INTO v_wo FROM work_orders WHERE id = p_wo_id FOR UPDATE;

                IF v_wo.status != 'Pending' THEN
                    RAISE EXCEPTION 'Work order is not Pending.';
                END IF;

                /* Get BOM for Product */
                SELECT id INTO v_bom_id FROM bill_of_materials WHERE product_id = v_wo.product_id LIMIT 1;
                IF NOT FOUND THEN
                    RAISE EXCEPTION 'BOM not found for product ID %', v_wo.product_id;
                END IF;

                /* Check and Update Material Stocks */
                FOR v_bom_item IN SELECT material_id, quantity FROM bom_items WHERE bom_id = v_bom_id
                LOOP
                    v_req_qty := v_bom_item.quantity * v_wo.quantity;

                    SELECT stock INTO v_current_stock FROM materials WHERE id = v_bom_item.material_id FOR UPDATE;
                    IF v_current_stock < v_req_qty THEN
                        RAISE EXCEPTION 'Insufficient stock for material ID %. Required: %, Available: %', v_bom_item.material_id, v_req_qty, v_current_stock;
                    END IF;

                    /* Deduct stock */
                    UPDATE materials SET stock = stock - v_req_qty WHERE id = v_bom_item.material_id;

                    /* Movement */
                    INSERT INTO stock_movements (reference_type, reference_id, item_type, item_id, movement_type, quantity, balance, movement_date)
                    VALUES ('WorkOrder', p_wo_id, 'Material', v_bom_item.material_id, 'Out', v_req_qty, v_current_stock - v_req_qty, CURRENT_TIMESTAMP);
                END LOOP;

                /* Update WO Status */
                UPDATE work_orders SET status = 'In Progress', start_date = CURRENT_DATE, updated_at = CURRENT_TIMESTAMP WHERE id = p_wo_id;
            END;
            $$;
SQL
        );

        // 3. SP: sp_finish_production
        DB::unprepared(<<<SQL
            CREATE OR REPLACE PROCEDURE sp_finish_production(p_wo_id INT)
            LANGUAGE plpgsql AS $$
            DECLARE
                v_wo RECORD;
                v_current_stock DECIMAL;
            BEGIN
                SELECT * INTO v_wo FROM work_orders WHERE id = p_wo_id FOR UPDATE;

                IF v_wo.status != 'In Progress' THEN
                    RAISE EXCEPTION 'Work order is not In Progress.';
                END IF;

                /* Add Product Stock */
                SELECT stock INTO v_current_stock FROM products WHERE id = v_wo.product_id FOR UPDATE;
                UPDATE products SET stock = stock + v_wo.quantity WHERE id = v_wo.product_id;

                /* Movement */
                INSERT INTO stock_movements (reference_type, reference_id, item_type, item_id, movement_type, quantity, balance, movement_date)
                VALUES ('WorkOrder', p_wo_id, 'Product', v_wo.product_id, 'In', v_wo.quantity, v_current_stock + v_wo.quantity, CURRENT_TIMESTAMP);

                /* Finish WO */
                UPDATE work_orders SET status = 'Completed', end_date = CURRENT_DATE, updated_at = CURRENT_TIMESTAMP WHERE id = p_wo_id;
            END;
            $$;
SQL
        );

    }

    public function down(): void {
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_finish_production;");
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_start_production;");
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_receive_material;");
    }
};
