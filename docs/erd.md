# Entity Relationship Diagram (ERD) - Mini Manufacturing System

Dokumen ini memuat arsitektur struktur tabel untuk Mini Manufacturing System (MMS) menggunakan representasi *Mermaid.js*.

## Mermaid Diagram

```mermaid
erDiagram
    materials {
        int id PK
        string code UK
        string name
        string unit
        decimal stock
        timestamp created_at
        timestamp updated_at
    }

    products {
        int id PK
        string code UK
        string name
        decimal stock
        timestamp created_at
        timestamp updated_at
    }

    suppliers {
        int id PK
        string code UK
        string name
        string contact
        string address
        timestamp created_at
        timestamp updated_at
    }

    bill_of_materials {
        int id PK
        int product_id FK
        timestamp created_at
        timestamp updated_at
    }

    bom_items {
        int id PK
        int bom_id FK
        int material_id FK
        decimal quantity
    }

    material_receipts {
        int id PK
        int supplier_id FK
        string receipt_number UK
        date receipt_date
        timestamp created_at
    }

    material_receipt_items {
        int id PK
        int receipt_id FK
        int material_id FK
        decimal quantity
    }

    work_orders {
        int id PK
        string wo_number UK
        int product_id FK
        decimal quantity
        string status "Pending, In Progress, Completed"
        date start_date
        date end_date
        timestamp created_at
        timestamp updated_at
    }

    stock_movements {
        int id PK
        string reference_type "Receipt, WorkOrder, Adjustment"
        int reference_id
        string item_type "Material, Product"
        int item_id
        string movement_type "In, Out"
        decimal quantity
        decimal balance
        timestamp movement_date
    }

    %% Relationships
    products ||--o{ bill_of_materials : "has"
    bill_of_materials ||--|{ bom_items : "contains"
    materials ||--o{ bom_items : "used_in"
    
    suppliers ||--o{ material_receipts : "supplies"
    material_receipts ||--|{ material_receipt_items : "contains"
    materials ||--o{ material_receipt_items : "received_as"
    
    products ||--o{ work_orders : "manufactured_in"
```

## Penjelasan Relasi (Relationships)

1. **Product & Bill of Materials (BOM):** Satu `Product` memiliki satu atau banyak definisi resep di dalam `bill_of_materials`.
2. **BOM & BOM Items:** `bill_of_materials` memuat rincian bahan melalui tabel `bom_items`, yang terhubung langsung ke tabel `materials`.
3. **Penerimaan (Receive Materials):** Entitas `suppliers` berelasi dengan histori penerimaan di tabel `material_receipts`. Detail dari barang yang diterima dicatat pada `material_receipt_items` yang merujuk pada `materials`.
4. **Produksi (Work Order):** Proses produksi (`work_orders`) mengacu pada entitas `products`.
5. **Stock Movement:** Tabel `stock_movements` menerapkan konsep *Polymorphic Relations* sederhana di mana kolom `item_type` dan `item_id` dapat merepresentasikan pergerakan pada `materials` atau `products`. Hal ini memudahkan pembuatan *Inventory Report* dalam satu tabel audit yang ditrigger secara otomatis.
