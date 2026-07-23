<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->string('reference_type'); // Receipt, WorkOrder, Adjustment
            $table->unsignedBigInteger('reference_id');
            $table->string('item_type'); // Material, Product
            $table->unsignedBigInteger('item_id');
            $table->enum('movement_type', ['In', 'Out']);
            $table->decimal('quantity', 15, 2);
            $table->decimal('balance', 15, 2);
            $table->timestamp('movement_date')->useCurrent();

            $table->index('movement_date', 'idx_movement_date');
            $table->index(['item_type', 'item_id', 'movement_date'], 'idx_item_movement');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
