# True Stock - Enterprise Warehouse Management System (WMS)

## 1. Pendahuluan
True Stock adalah sistem manajemen inventaris dan gudang skala enterprise yang dibangun dengan fokus pada akurasi data, skalabilitas, dan transparansi. Sistem ini dirancang untuk menangani volume transaksi tinggi dengan integritas data yang dijamin melalui arsitektur modern.

## 2. Tech Stack
- **Backend:** Laravel 12.x, PHP 8.2+
- **Frontend:** Vue.js 3, Inertia.js, TailwindCSS
- **Database:** MySQL/PostgreSQL (Support SQLite for testing)
- **Cache & Queue:** Redis
- **Architecture:** Domain Driven Design (DDD), Clean Architecture, Event-Driven.

## 3. Prinsip Arsitektur
Sistem ini menggunakan pendekatan modular untuk memastikan maintainability jangka panjang:
- **DTO (Data Transfer Objects):** Memastikan validitas data antar layer.
- **Service Layer Pattern:** Memisahkan logika bisnis dari controller.
- **Event-Driven:** Pergerakan stok memicu event untuk decoupling proses audit dan notifikasi.
- **Repository Pattern:** Abstraksi akses data (Infrastructure layer).

## 4. Modul Utama & Fitur

### A. Inventory Core Engine
- **Multi-Warehouse & Multi-Location:** Mendukung struktur gudang hierarkis (Gudang -> Lokasi -> Rak).
- **Stock Batch & Serial Tracking:** Pelacakan barang berdasarkan batch produksi dan nomor seri.
- **FIFO/LIFO Support:** Strategi pengambilan barang yang fleksibel.

### B. Warehouse Transfer (In-Transit Management)
- Mengelola pemindahan barang antar gudang dengan status real-time.
- Alur: `PENDING` -> `IN_TRANSIT` (Stok dipotong dari asal) -> `COMPLETED` (Stok masuk ke tujuan).

### C. Stock Reservation System
- **Physical vs Available Stock:** Memungkinkan pemesanan stok untuk Sales atau Produksi tanpa memotong stok fisik segera.
- Mencegah *overselling* dengan validasi stok yang tersedia (Fisik - Reservasi).

### D. Advanced Audit Trail
- Pencatatan otomatis setiap perubahan data (Created, Updated, Deleted).
- Menyimpan nilai **Before vs After** untuk setiap field yang berubah.
- Melacak User, IP Address, dan User Agent.

### E. Reporting & Analytics
- **Inventory Valuation:** Valuasi aset berdasarkan `standard_cost`.
- **Dashboard KPIs:** Ringkasan total item, nilai inventaris, dan peringatan stok rendah secara real-time.
- **Valuation by Warehouse:** Distribusi nilai aset di berbagai lokasi.

## 5. Struktur Folder (DDD)
```text
app/
â”œâ”€â”€ Application/          # DTOs, Application Services
â”œâ”€â”€ Domain/               # Business Logic, Entities, Events, Listeners
â”‚   â””â”€â”€ Inventory/
â”œâ”€â”€ Infrastructure/       # Persistence (Eloquent), Traits, Services
â”œâ”€â”€ Interfaces/           # Controllers (API & Web)
â””â”€â”€ Models/               # Eloquent Models
```

## 6. Alur Data (Flowchart Logic)

### 1. Pergerakan Stok (Stock Movement)
`Action (StockIn/Out) -> StockUpdate (DB) -> Dispatch Event -> Listener (Audit Log & Movement Record)`

### 2. Transfer Barang (In-Transit)
1. **Initiate:** Create Transfer -> Status `PENDING`.
2. **Ship:** `initiateTransfer()` -> Stock Out Gudang A -> Status `IN_TRANSIT`.
3. **Receive:** `completeTransfer()` -> Stock In Gudang B -> Status `COMPLETED`.

## 7. API & Integration
Sistem ini siap diintegrasikan dengan perangkat eksternal melalui:
- **REST API:** Untuk Mobile Scanner atau ERP eksternal.
- **Webhooks/Events:** Untuk integrasi sistem pihak ketiga.

## 8. Panduan Pengembangan (Coding Guidelines)
- Selalu gunakan **DTO** saat melewatkan data ke Service.
- Jangan letakkan logika bisnis di Controller.
- Gunakan **Database Transactions** untuk setiap operasi yang melibatkan lebih dari satu tabel.
- Pastikan setiap fitur baru memiliki **Unit Test** yang sesuai.

---
**True Stock v1.0** - *Built for Scalability and Integrity.*
