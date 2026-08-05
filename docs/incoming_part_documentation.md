# Dokumentasi Kode Lengkap Modul Incoming Part

Dokumen teknis terperinci ini menyajikan panduan arsitektur, skema database, alur logika backend, struktur frontend, serta spesifikasi API/Routing untuk modul **Incoming Part** pada sistem Quality Control (QC). Dokumen ini telah diperbarui secara menyeluruh mencakup seluruh perbaikan terbaru: pemindaian kamera berkelanjutan (*Continuous High-Speed Camera Scanning*), integrasi data Traceability QR Code, fitur view mode Hasil Verifikasi, Laporan Cetak Resmi (*Print View*), penyelarasan UI Laporan Index & Form Input, serta standarisasi tabel antrean scan sementara.

---

## 1. Pendahuluan & Arsitektur Modul

Modul **Incoming Part** bertugas mencatat, mengelola, memverifikasi, serta menyetujui (*approval*) hasil pemeriksaan part/komponen kedatangan dari supplier (*Incoming Part Checksheet*). 

Modul ini dibangun menggunakan pola arsitektur **Laravel MVC + Service Layer Pattern**, serta memanfaatkan **Traits** untuk memisahkan fungsi umum (*reusable mixins*) seperti approval berjenjang, penanganan multi-plant, notifikasi penghapusan, dan ekspor data.

### Komponen Utama Sistem
- **Model Eloquent:**
  - `App\Models\IncomingPart` (Transaksi Inspeksi QC & Traceability QR)
  - `App\Models\IncomingPartArrival` (Master/Batch Lot Kedatangan Supplier)
- **Controller Layer:** `App\Http\Controllers\IncomingPartController`
- **Service Layer:** `App\Services\IncomingPartService`
- **Form Request Validation:** 
  - `App\Http\Requests\StoreIncomingPartRequest`
  - `App\Http\Requests\UpdateIncomingPartRequest`
- **Traits Terintegrasi:**
  - `App\Traits\HasChecksheetApproval` (Fungsi approval 4 tingkat & rejection)
  - `App\Traits\HasChecksheetExport` (Fungsi ekspor CSV & Google Sheets sync)
  - `App\Traits\HasPlantFilter` (Auto-scope data berdasarkan Plant pengguna)
  - `App\Traits\HasDeleteNotification` (Notifikasi penghapusan data)
- **Database Migration Terbaru:**
  - `database/migrations/2026_07_24_150000_add_qr_columns_to_incoming_parts_table.php` (Penambahan atribut QR Traceability & Cycle Time)
- **Views (Blade):**
  - `resources/views/incoming/parts/index.blade.php` (Laporan Index & Modal Traceability QR)
  - `resources/views/incoming/parts/create.blade.php` (Form Input Data & Antrian Scan)
  - `resources/views/incoming/parts/edit.blade.php` / `edit_modal.blade.php` (Modal Edit Data)
  - `resources/views/incoming/parts/print.blade.php` (Halaman Cetak Resmi ISO Landscape)
- **JavaScript External:** 
  - `public/js/checksheet/incoming-create.js` (Continuous Camera Scanner, QR parsing & queue management)
  - `public/js/checksheet/incoming-edit.js` (Handling baris defect dinamis pada modal edit)
  - `public/js/vendor/item-search.js` (Smart Autocomplete Part Search Dropdown)
- **Global Styling:**
  - `public/css/custom-responsive.css` (Selektor terpadu `#checksheetTable` & `#tempQueueTable`)

---

## 2. Skema Database & Model Data

### A. Tabel `incoming_part_arrivals` (Lot Kedatangan Supplier)

| Nama Kolom | Tipe Data | Nullable | Deskripsi / Catatan |
| :--- | :--- | :---: | :--- |
| `id` | `bigint (unsigned)` | NO | Primary Key (Auto Increment) |
| `plant_id` | `char(36)` | YES | Foreign Key ke tabel `plants` |
| `item_id` | `char(36)` | NO | Foreign Key ke tabel `items` |
| `tanggal_datang` | `date` | NO | Tanggal kedatangan part dari supplier |
| `shift_datang` | `varchar(255)` | NO | Shift kedatangan (1, 2, 3) |
| `qty_datang` | `int` | NO | Total Kuantitas awal dari supplier |
| `qty_sisa` | `int` | NO | Sisa Kuantitas (Balance) yang belum diinspeksi QC |
| `status` | `enum('OPEN','COMPLETED')` | NO | Default: `OPEN`, berubah `COMPLETED` jika `qty_sisa == 0` |
| `created_at` / `updated_at` | `timestamp` | YES | Standard Eloquent Timestamps |

### B. Tabel `incoming_parts` (Transaksi Inspeksi QC & QR Traceability)

| Nama Kolom | Tipe Data | Nullable | Deskripsi / Catatan |
| :--- | :--- | :---: | :--- |
| `id` | `bigint (unsigned)` | NO | Primary Key (Auto Increment) |
| `plant_id` | `char(36)` | NO | Foreign Key ke tabel `plants` |
| `item_id` | `char(36)` | NO | Foreign Key ke tabel `items` |
| `arrival_id` | `bigint (unsigned)` | YES | Foreign Key ke `incoming_part_arrivals` |
| `date` | `date` | NO | Tanggal pemeriksaan QC |
| `shift` | `varchar(255)` | NO | Shift pemeriksaan QC (1, 2, 3) |
| `lot_qty` | `int` | YES | Kuantitas total lot kedatangan |
| `total_check` | `int` | NO | Jumlah sampel part yang diinspeksi QC |
| `tanggal_datang` | `date` | YES | Tanggal kedatangan |
| `judgment` | `enum('OK','NG')` | NO | Hasil keputusan inspeksi |
| `defects` | `json` | YES | Data cacat/defect dalam JSON |
| `total_ng` | `int` | YES (Default: 0) | Total unit part NG |
| `operator_initials` | `varchar(255)` | YES | Inisial pemeriksa QC |
| `remarks` | `text` | YES | Catatan (Otomatis ditambahkan tag selisih `[Selisih: +/- X pcs]`) |
| `part_code` | `varchar(255)` | YES | Kode Part dari QR Code |
| `supplier_id` | `varchar(255)` | YES | ID Supplier dari QR Code |
| `quantity` | `int` | YES | Qty kemasan dari QR Code |
| `unique_code_id` | `varchar(255)` | YES | Unique ID Lot dari QR Code |
| `sap_code` | `varchar(255)` | YES | Kode SAP internal dari QR Code |
| `scan_method` | `varchar(255)` | YES | Metode entri (`manual`, `hardware`, `camera`) |
| `qrcode` | `text` | YES | String QR Raw lengkap (pipa `|`) |
| `cycle_time` | `int` | YES | Durasi pemeriksaan (detik) |
| `approval_status` | `varchar(255)` | YES | Status approval (`Pending`, `Approved`, `Rejected`) |

---

## 3. Fitur Utama & Standarisasi UI Modul Incoming Part

### A. Continuous High-Speed Camera Scanning & Audio Feedback
- Kamera scanner (`#qrScannerModal`) **tetap terbuka secara terus menerus** tanpa menutup/buka ulang modal untuk setiap kali scan.
- **Sistem Audio Validasi & Notifikasi Multi-Status (`window.playAppAudio`)**:
  - `success` $\rightarrow$ `QR CODE BERHASIL DI SCAN.mp3` (disertai notifikasi `Swal.fire` sukses singkat 1.5 detik).
  - `format_error` $\rightarrow$ `FORMAT QR CODE SALAH, SCAN QR INTERNAL.mp3`.
  - `duplicate_saved` $\rightarrow$ `QR CODE DUPLICATE, SUDAH DI SIMPAN SEBELUM NYA.mp3`.
  - `duplicate_list` $\rightarrow$ `QR CODE SUDAH ADA DI LIST.mp3`.
  - `item_not_found` $\rightarrow$ `ITEM PART INI TIDAK ADA DI CHECKSHEET INI.mp3`.
- Cooldown guard 1.2 detik mencegah double-trigger dan memfasilitasi pemindaian QR Code masif secara berturut-turut.

### B. Modal Traceability QR Code & Mode View "Hasil Verifikasi"
- **Action Bar Button**: Tombol **`Hasil Verifikasi`** berwarna ungu (`#6f42c1`) pada Action Bar laporan `index.blade.php` memungkinkan switching ke mode verifikasi scan QR (`view_mode=verifikasi`).
- **Conditional QR-Code Column**: Kolom `QR-Code` disembunyikan pada mode tampilan reguler/input manual, dan otomatis tampil pada mode Hasil Verifikasi.
- **Modal Traceability (`#qrModal`)**: Menampilkan detail elemen QR Code (*QR Raw, Part Code, Supplier ID, Qty, Unique ID, SAP Code*) saat tombol **`Traceability`** diklik.

### C. Laporan Cetak Resmi (*Print View*)
- Endpoint route `/report/incoming-part/print` (`incoming.parts.print`) memanggil `printView` pada `IncomingPartController.php`.
- Menggunakan template cetak ISO `resources/views/incoming/parts/print.blade.php` (Kop Dokumen Resmi `QC-KRW-F-0210`, Logo IPP, Filter Periode/Plant, Tabel Data, dan pemicu otomatis `window.print()`).
- Tombol **Print** berwarna hijau toska (`#17a589`) tersedia pada Action Bar laporan.

### D. Standarisasi Tabel Antrean Scan Sementara (`#tempQueueTable`)
- Tabel `#tempQueueTable` pada `create.blade.php` diselaraskan 100% dengan tabel `#checksheetTable`:
  - 8 Kolom Ringkas: **`No` | `Item Part` | `QR Raw` | `Tanggal & Shift Check` | `Total Check` | `Judgment` | `Inisial QC` | `Aksi`** (Kolom `Detail NG` & `Remarks` dihapus untuk kerapian).
  - Header `TH` menggunakan gaya *Industrial Minimalist* global (`background-color: #f1f5f9 !important`, teks `#475569`, font size `0.62rem`, font weight `700`, sticky top).
  - Selektor CSS dihubungkan secara terpadu melalui `public/css/custom-responsive.css`.

### E. Sinkronisasi Otomatis Qty Balance Kedatangan Supplier & Logika Auto FIFO (`IncomingPartArrival`)
- **Pencarian & Seleksi Auto FIFO (Silent Automatic Selection)**:
  - Kotak pemilihan manual (*Pilih Lot Kedatangan Belum Selesai*) telah dihapus sepenuhnya dari UI agar penginputan lebih bersih dan cepat.
  - Lot kedatangan supplier secara otomatis diproses oleh sistem berdasarkan `tanggal_datang` & `shift_datang` (`ASC`) di latar belakang (*under the hood*).
  - Saat inspector memilih Item Part, sistem **secara otomatis mengisi data Lot Terlama (Paling Awal)** yang masih memiliki sisa stok (`status == 'OPEN'` & `qty_sisa > 0`) ke dalam form.
  - Jika stok lot tersebut habis (`qty_sisa == 0`), status lot berubah menjadi **`COMPLETED`** dan pada entri berikutnya sistem otomatis berpindah ke lot berikutnya (misal Shift 2).
  - Jika inspector mengubah `Tgl & Shift Kedatangan Supplier` secara manual ke tanggal/shift lain, sistem secara dinamis mencocokkan lot aktif yang sesuai atau menyiapkan pembuatan lot baru jika belum pernah tercatat.
- **Penghapusan Checksheet (`deleteChecksheet`)**:
  - Saat checksheet dihapus, jumlah `total_check` otomatis dikembalikan ke `qty_sisa` (`qty_sisa = min(qty_datang, qty_sisa + total_check)`).
  - Jika `qty_sisa > 0`, status lot kedatangan secara otomatis kembali ke **`OPEN`**.
- **Pembaruan Checksheet (`updateChecksheet`)**:
  - Selisih perubahan kuantitas sampel dihitung dan `$arrival->qty_sisa` serta status (`OPEN`/`COMPLETED`) diperbarui secara presisi.

---

## 4. Verifikasi & Pemeliharaan

- Perintah pengujian Blade cache: `php artisan view:cache` (Status: **0 error, 100% Selesai**).
- Perintah pengujian PHP syntax: `php -l app/Http/Controllers/IncomingPartController.php` (Status: **No syntax errors detected**).
- Perintah pengujian JS syntax: `node -c public/js/checksheet/incoming-create.js` (Status: **No syntax errors detected**).

---
*Dokumen teknis ini diperbarui secara menyeluruh mencakup pembaruan Continuous Camera Scanning, QR Traceability, Print View ISO, Mode Hasil Verifikasi, serta standarisasi CSS tabel antrean scan sementara.*
