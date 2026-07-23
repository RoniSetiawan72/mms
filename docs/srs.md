# Software Requirements Specification (SRS)
**Nama Produk:** Mini Manufacturing System (MMS)  
**Versi Dokumen:** 1.0  
**Tanggal:** 23 Juli 2026  
**Status Dokumen:** Draft  

---

## 1. Pendahuluan (Introduction)

### 1.1 Tujuan
Dokumen ini mendefinisikan spesifikasi kebutuhan perangkat lunak untuk **Mini Manufacturing System (MMS)**. Sistem ini dibangun sebagai aplikasi manufaktur fungsional yang berfokus pada implementasi arsitektur database tingkat lanjut.

### 1.2 Ruang Lingkup
MMS difokuskan pada manajemen bahan baku, pengelolaan produk, dan siklus *Work Order* produksi. Sistem beroperasi dalam mode *single-user* (Administrator) pada lingkungan lokal, tanpa mengelola modul kompleksitas seperti *multi-warehouse*, akuntansi, atau penjualan.

---

## 2. Deskripsi Keseluruhan (Overall Description)

### 2.1 Lingkungan Operasi & Arsitektur
Sistem ini menggunakan ekosistem berbasis **Laravel 12** sebagai *backend*, **PostgreSQL** sebagai sistem manajemen basis data utama, dan **React** (dengan **Inertia.js** dan **Tailwind CSS**) untuk lapisan antarmuka (*frontend*). 

Pendekatan arsitektur menerapkan *Domain-Driven Flow*, di mana logika domain utama manufaktur (seperti validasi ketersediaan bahan, pengurangan stok, dan status transisi produksi) diserahkan ke lapisan basis data. 

### 2.2 Pengguna Sistem
Sistem ini hanya digunakan oleh satu peran, yaitu **Administrator**, yang memiliki kontrol penuh atas seluruh fungsionalitas CRUD dan inisiasi proses produksi.

---

## 3. Fitur Sistem (System Features)

### 3.1 Manajemen Master Data
- **F01 - Master Raw Material:** Sistem harus mengizinkan Admin untuk melakukan penambahan, pengubahan, dan penghapusan data bahan baku (kode, nama, satuan).
- **F02 - Master Product:** Sistem harus mengizinkan Admin untuk mengelola data produk jadi.
- **F03 - Supplier:** Sistem harus menyediakan antarmuka CRUD untuk entitas penyuplai bahan baku.
- **F04 - Bill of Material (BOM):** Sistem harus memungkinkan definisi resep/komposisi bahan baku spesifik untuk setiap produk jadi.

### 3.2 Siklus Produksi & Inventaris
- **F05 - Receive Material:** Admin dapat mencatat penerimaan bahan dari *Supplier*. Sistem mengeksekusi `sp_receive_material()` untuk menambah stok.
- **F06 - Work Order (WO):** Sistem harus mengizinkan pembuatan WO untuk produk tertentu beserta target kuantitasnya.
- **F07 - Start Production:** Sistem memvalidasi ketersediaan bahan baku sesuai BOM. Jika tervalidasi, sistem mengubah status WO menjadi *In Progress* dan mengurangi stok bahan secara otomatis melalui `sp_start_production()`.
- **F08 - Finish Production:** Sistem menandai WO sebagai *Completed* dan menambah stok produk jadi melalui `sp_finish_production()`.
- **F09 - Stock Movement & Reporting:** Sistem harus dapat menyajikan riwayat pergerakan stok (*in/out*) secara *real-time* yang dihasilkan oleh eksekusi *Trigger* dari tabel terkait.

---

## 4. Spesifikasi Teknis & Logika Basis Data (Database Logic)

Penerapan *ACID principles* menjadi standar utama sistem ini, di mana seluruh perubahan status produksi dan stok dijalankan secara atomik dalam satu blok transaksi database.

### 4.1 Stored Procedures
Sebagian besar proses *business logic* diletakkan pada prosedur PostgreSQL:
1. `sp_receive_material(p_supplier_id, p_items)`: Memproses *insert* ke tabel penerimaan dan memperbarui kolom stok di tabel bahan baku.
2. `sp_start_production(p_wo_id)`: Mengunci baris data (`SELECT ... FOR UPDATE`), mengecek ketersediaan bahan (BOM × qty), mencatat mutasi keluar untuk bahan, dan mengubah status WO. Jika stok kurang, proses di-*rollback*.
3. `sp_finish_production(p_wo_id)`: Mengubah status WO menjadi selesai, menambah stok produk, dan mencatat mutasi masuk produk.
4. `sp_adjust_stock(p_item_id, p_type, p_qty)`: Menyesuaikan stok secara manual jika terdapat diskrepansi.

### 4.2 Database Triggers
- **Audit Stock Movement:** *Trigger function* yang aktif secara otomatis `AFTER INSERT OR UPDATE` pada tabel-tabel penerimaan dan produksi untuk memastikan tabel `stock_movements` selalu tersinkronisasi tanpa intervensi *backend*.
- **Update Timestamp:** *Trigger* standar untuk mengelola kolom `updated_at`.

### 4.3 Optimasi Indexing
Untuk memenuhi target kinerja (kueri < 300 ms):
- **Unique Index:** `products(code)`, `materials(code)`, `suppliers(code)`, `work_orders(wo_number)`.
- **B-Tree Index:** Mempercepat pencarian berdasarkan `movement_date`, `product_id`, dan `material_id`.
- **Composite Index:** `(product_id, status)` dan `(material_id, movement_date)`.
- **Partial Index:** `CREATE INDEX idx_wo_in_progress ON work_orders(status) WHERE status = 'In Progress';`

---

## 5. Kebutuhan Non-Fungsional

- **Keamanan (Security):** Integritas data dijaga secara ketat melalui *Foreign Key Constraints*. Tidak ada proses *insert/update* stok yang diizinkan melalui *query builder backend* secara langsung, melainkan harus melewati *Stored Procedure*.
- **Kinerja (Performance):** Beban komputasi dipindahkan ke *database engine*. Dasbor harus memuat agregasi stok dan WO di bawah 1 detik.
