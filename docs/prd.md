# Product Requirements Document (PRD)

**Nama Produk:** Mini Manufacturing System (MMS)
**Versi Dokumen:** 1.0
**Tanggal:** 23 Juli 2026
**Penulis:** Roni Setiawan
**Status Dokumen:** Draft

---

# 1. Latar Belakang (Background)

Mini Manufacturing System (MMS) merupakan aplikasi manufaktur sederhana yang dikembangkan sebagai media pembelajaran implementasi PostgreSQL pada proses bisnis manufaktur.

Fokus utama sistem bukan pada kompleksitas fitur, melainkan pada penerapan konsep database tingkat lanjut seperti **Stored Procedure**, **Transaction**, **Trigger**, dan **Database Indexing** sehingga sebagian besar business logic dijalankan langsung di dalam PostgreSQL.

Sistem hanya digunakan oleh **satu pengguna (Administrator)** di lingkungan lokal sehingga tidak memerlukan autentikasi multi-user maupun pengaturan hak akses.

### Masalah yang dipecahkan

* Sulit memahami implementasi Stored Procedure pada studi kasus nyata.
* Belum adanya proyek latihan yang menggabungkan Inventory dan Production Process.
* Membutuhkan contoh implementasi indexing PostgreSQL untuk meningkatkan performa query.

### Konteks

Project ini ditujukan sebagai portofolio sekaligus sarana belajar Database Engineering menggunakan PostgreSQL.

---

# 2. Tujuan (Objectives & Goals)

### Tujuan Bisnis

* Membuat aplikasi manufaktur sederhana yang dapat digunakan sebagai project pembelajaran.
* Menunjukkan kemampuan implementasi PostgreSQL pada proses bisnis.

### Tujuan Pengguna

* Mengelola data bahan baku.
* Mengelola produk.
* Menjalankan proses produksi.
* Mengelola stok secara otomatis menggunakan Stored Procedure.

---

# 3. Pengguna Sasaran (Target Audience)

### Persona 1

Developer yang ingin mempelajari implementasi PostgreSQL Advanced Features.

### Persona 2

Mahasiswa atau Software Engineer yang membutuhkan project portofolio bertema Manufacturing.

---

# 4. Ruang Lingkup (Scope)

## In-Scope

* Dashboard
* Master Raw Material
* Master Product
* Supplier
* Bill of Material (BOM)
* Receive Raw Material
* Work Order
* Start Production
* Finish Production
* Stock Movement
* Inventory Report
* Production Report
* Stored Procedure
* Trigger
* Database Indexing

## Out-of-Scope

* Multi User
* Authentication & Authorization
* Purchase Order
* Sales Order
* Accounting
* Multi Warehouse
* Barcode / QR Code
* REST API
* Mobile Application

---

# 5. Kebutuhan Fungsional (Functional Requirements)

| ID  | Fitur               | Deskripsi                                                                                 | Prioritas |
| --- | ------------------- | ----------------------------------------------------------------------------------------- | --------- |
| F01 | Dashboard           | Menampilkan ringkasan stok, produk, dan Work Order                                        | Tinggi    |
| F02 | Master Raw Material | CRUD bahan baku                                                                           | Tinggi    |
| F03 | Master Product      | CRUD produk jadi                                                                          | Tinggi    |
| F04 | Supplier            | CRUD supplier                                                                             | Sedang    |
| F05 | Bill of Material    | Menentukan komposisi bahan setiap produk                                                  | Tinggi    |
| F06 | Receive Material    | Menambah stok bahan baku melalui Stored Procedure                                         | Tinggi    |
| F07 | Work Order          | Membuat Work Order produksi                                                               | Tinggi    |
| F08 | Start Production    | Validasi stok, mengurangi bahan baku, dan mengubah status WO menggunakan Stored Procedure | Tinggi    |
| F09 | Finish Production   | Menambah stok produk jadi menggunakan Stored Procedure                                    | Tinggi    |
| F10 | Stock Movement      | Menampilkan seluruh histori mutasi stok                                                   | Tinggi    |
| F11 | Inventory Report    | Laporan stok bahan baku dan produk jadi                                                   | Sedang    |
| F12 | Production Report   | Riwayat proses produksi                                                                   | Sedang    |

---

# 6. Kebutuhan Non-Fungsional (Non-Functional Requirements)

### Kinerja (Performance)

* Query master data maksimal **100 ms**.
* Query histori stok maksimal **300 ms** dengan bantuan index.
* Dashboard mampu menampilkan data kurang dari **1 detik** pada dataset simulasi.

### Keamanan (Security)

* Menggunakan Foreign Key Constraint.
* Validasi data dilakukan melalui Stored Procedure.
* Seluruh transaksi menggunakan Transaction (BEGIN, COMMIT, ROLLBACK).

### Ketersediaan (Availability)

* Sistem berjalan secara lokal menggunakan PostgreSQL.
* Tidak memerlukan koneksi internet.

### Maintainability

* Business logic utama berada pada Stored Procedure.
* Backend hanya bertugas memanggil Stored Procedure dan menampilkan hasil.

---

# 7. User Stories (Skenario Pengguna)

* Sebagai Administrator, saya ingin mengelola data bahan baku sehingga stok selalu tersedia.
* Sebagai Administrator, saya ingin membuat Bill of Material agar sistem mengetahui kebutuhan bahan setiap produk.
* Sebagai Administrator, saya ingin menerima bahan baku sehingga stok bertambah secara otomatis.
* Sebagai Administrator, saya ingin membuat Work Order agar proses produksi dapat dimulai.
* Sebagai Administrator, saya ingin memulai produksi sehingga sistem memvalidasi stok dan mengurangi bahan baku secara otomatis.
* Sebagai Administrator, saya ingin menyelesaikan produksi sehingga stok produk jadi bertambah.
* Sebagai Administrator, saya ingin melihat histori mutasi stok sehingga seluruh perubahan stok dapat ditelusuri.

---

# 8. Pertimbangan Desain (UX/UI Requirements)

### User Flow

Dashboard

↓

Master Data

↓

Receive Material

↓

Create Work Order

↓

Start Production

↓

Finish Production

↓

Stock Movement

↓

Reporting

### Desain

* Menggunakan Admin Dashboard sederhana.
* Sidebar Navigation.
* Tabel dengan pagination.
* Form CRUD standar.
* Dashboard menggunakan Card Summary.

---

# 9. Pertimbangan Teknis (Technical Considerations)

### Teknologi

* Backend : Laravel 12
* Database : PostgreSQL
* Frontend : React + Inertia.js
* Styling : Tailwind CSS

### Stored Procedure

* `sp_receive_material()`
* `sp_start_production()`
* `sp_finish_production()`
* `sp_adjust_stock()`

### Trigger

* Audit Stock Movement
* Update Timestamp

### Indexing

**Unique Index**

* products(code)
* materials(code)
* suppliers(code)
* work_orders(wo_number)

**B-Tree Index**

* movement_date
* product_id
* material_id

**Composite Index**

* (product_id, status)
* (material_id, movement_date)

**Partial Index**

* work_orders(status) WHERE status = 'In Progress'

---

# 10. Garis Waktu & Milestones (Timeline)

| Tahapan                       | Estimasi |
| ----------------------------- | -------- |
| Analisis Requirement          | 2 Hari   |
| Desain Database               | 2 Hari   |
| Implementasi Stored Procedure | 3 Hari   |
| Pengembangan Backend          | 4 Hari   |
| Pengembangan Frontend         | 4 Hari   |
| Testing                       | 2 Hari   |
| Dokumentasi                   | 1 Hari   |

Estimasi total pengerjaan: **18 Hari**

---

# 11. Metrik Keberhasilan (Success Metrics)

* Seluruh CRUD berjalan tanpa error.
* Stored Procedure berhasil menjalankan proses produksi secara atomik.
* Trigger otomatis mencatat seluruh mutasi stok.
* Index PostgreSQL meningkatkan performa query pada dashboard dan laporan.
* Tidak terdapat inkonsistensi data setelah simulasi produksi berulang.
* Seluruh proses dapat dijalankan oleh satu pengguna pada lingkungan lokal.

---

# 12. Risiko dan Mitigasi (Risks & Mitigation)

| Risiko                    | Dampak | Peluang Terjadi | Langkah Mitigasi                                                  |
| ------------------------- | ------ | --------------- | ----------------------------------------------------------------- |
| Stored Procedure gagal    | Tinggi | Sedang          | Menggunakan Transaction dan Exception Handling                    |
| Query lambat              | Sedang | Sedang          | Menambahkan Index dan melakukan EXPLAIN ANALYZE                   |
| Data stok tidak konsisten | Tinggi | Rendah          | Seluruh update stok dilakukan melalui Stored Procedure            |
| Kesalahan relasi tabel    | Sedang | Rendah          | Menggunakan Foreign Key Constraint                                |
| Deadlock saat transaksi   | Rendah | Rendah          | Menggunakan row locking (`SELECT ... FOR UPDATE`) pada Work Order |
