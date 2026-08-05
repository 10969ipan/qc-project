# Cetak Biru & Dokumentasi Kode Terperinci Modul Durability Plating (Standard Performance Test)

Dokumen ini merupakan **panduan arsitektur teknis dan cetak biru (*blueprint*) komprehensif** untuk modul **Durability Plating (Standard Performance Test)** pada sistem Quality Control (QC). Dokumen ini dirancang sedemikian rupa agar pengembang atau tim teknis dapat memahami seluruh alur logika, skema database, perlakuan data, struktur frontend, hingga **dapat mereplikasi / membangun modul serupa dari nol** untuk departemen atau pengujian fisik lainnya.

---

## 1. Konsep Arsitektur & Pola Desain Sistem

Modul Durability Plating memiliki tantangan arsitektural khusus di mana **satu produk/lot part yang sama memiliki 5 jenis pengujian fisik yang berbeda** (Thickness, Corrodkote, CASS, Salt Spray, Porecount) dan dilakukan dalam **dua tahap pengujian (Data 1/Aktual dan Data 2/Trial)** serta memerlukan **4 tingkatan Approval hierarkis**.

### Pola Utama yang Diterapkan:
1. **Master-Transaction Pattern**:
   - Master Specs (`standard_performance_tests`): Menyimpan standar teknis permanen per Part (Ketebalan Cr/Ni/Cu, Waktu Jam Corrodkote/CASS/Salt Spray, Min Porous Porecount).
   - Transaction (`durability_thickness_reports`): Menyimpan data inspeksi aktual yang dilakukan oleh analis QC di lapangan.
2. **Single-Row Multi-Test Consolidation**:
   - Seluruh hasil 5 pengujian (Thickness, Corrodkote, CASS, Salt Spray, Porecount) untuk lot yang sama disimpan dalam **satu baris data (*single record*)** di tabel `durability_thickness_reports`.
   - Setiap pengujian memiliki kolom hasil aktual, kolom waktu chamber, dan kolom deskripsi terisolasi masing-masing (`description`, `description_corrodkote`, `description_cass`, `description_salt_spray`, `description_porecount`).
3. **Multi-Level Approval Flow (4 Roles)**:
   - Mendukung 4 jenjang verifikasi bertahap:
     1. **Supervisor Quality** (`supervisor_qc` / `supervisor_approved_at`)
     2. **Supervisor Plating** (`supervisor_plating` / `supervisor_plating_approved_at`)
     3. **Asst Manager Quality** (`asst_manager_qc` / `asst_manager_approved_at`)
     4. **Asst Manager Plating** (`asst_manager_plating` / `asst_manager_plating_approved_at`)
   - Dilengkapi status **APPROVED**, **PENDING**, atau **REJECTED** (dengan catatan alasan penolakan `rejection_remarks`), serta dukungan fitur **Bulk Approve** untuk menyetujui seluruh data laporan sesuai filter tanggal.
   - Status approval otomatis disinkronisasikan antara pasangan Data 1 dan Data 2 Trial.
4. **Dual-Report Tracking & Explicit Pairing (`data1_id`)**:
   - Pasangan data dibedakan oleh flag boolean `is_trial` dan terhubung oleh foreign key eksplisit `data1_id`:
     - `is_trial = false` $\rightarrow$ Data 1 (Pengujian Utama / Produksi Massal)
     - `is_trial = true` $\rightarrow$ Data 2 (Pengujian Trial / Pembanding)
   - Pada pencatatan baru (`store`), sistem secara otomatis menciptakan 2 record sekaligus (`is_trial = false` dan `is_trial = true`) di mana record Data 2 menyimpan `data1_id = $report1->id`.
5. **3-Slot Evidence Photo Architecture**:
   - Menyediakan 3 slot upload foto evidence terpisah:
     1. **BEFORE TEST** (`evidence_before`): Foto kondisi sampel sebelum diuji (sinkron antara Data 1 dan Data 2).
     2. **AFTER TEST (DATA 1)** (`evidence_after`): Foto hasil pengujian khusus Data 1.
     3. **AFTER TEST (DATA 2)** (`evidence_after_trial`): Foto hasil pengujian khusus Data 2 (Trial).
   - Pada tampilan halaman tunggal Data 2 Trial (`$isTrial == true`), layout secara dinamis menguncup menjadi 2 kartu rapi: **`BEFORE TEST`** dan **`AFTER TEST`**.
6. **Conditional Thickness Link Protection (`$validVal`)**:
   - Pada tabel laporan pengujian non-thickness (Corrodkote, CASS, Salt Spray, Porecount), kolom **Thickness** secara dinamis mengevaluasi nilai ketebalan (`actual_cu`, `actual_ni`, `actual_cr`).
   - Jika record tersebut tidak memiliki hasil ukur ketebalan (atau bernilai `null`, `""`, `"-"`, `"0"`, `"0.0"`), kolom otomatis menampilkan keterangan **`tidak ada data`**. Tautan `<i class="fas fa-external-link-alt"></i> Data` hanya muncul jika data ketebalan fisik memang tersedia.
7. **Report-Filtered Part Search & Multi-Field Matching**:
   - Opsi pada dropdown filter **Part:** di tabel laporan (`$items`) secara dinamis disaring hanya memuat master part yang **benar-benar memiliki rekaman transaksi simpanan di jenis laporan tersebut**.
   - Sistem pencarian mendukung *multi-field autocomplete matching* berdasarkan `Nama Part` (`part_name`), `Part Number` (`part_number`), `Nama Customer` (`customer_name`), dan `Standard Customer` (`customer_standard`).
8. **Partial Deletion Mechanism (`clearTestData`)**:
   - Penghapusan data tes tertentu (misal: hanya tes Corrodkote) tidak akan menghapus baris record jika tes lain (misal: Thickness) pada lot tersebut masih memiliki data. Baris baru akan benar-benar dihapus dari DB jika seluruh 5 jenis pengujian bernilai kosong (`allEmpty`).

---

## 2. Skema Database Lengkap & Blueprint Migrasi

### A. Tabel `standard_performance_tests` (Master Spesifikasi)

```sql
CREATE TABLE `standard_performance_tests` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `part_name` varchar(255) NOT NULL,
  `part_number` varchar(255) DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `customer_standard` varchar(255) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `thickness_cr` varchar(255) DEFAULT NULL,
  `thickness_ni` varchar(255) DEFAULT NULL,
  `thickness_cu` varchar(255) DEFAULT NULL,
  `thickness_freq` varchar(255) DEFAULT NULL,
  `corrodkote_time` varchar(255) DEFAULT NULL,
  `corrodkote_std_max_corrosion` varchar(255) DEFAULT NULL,
  `corrodkote_freq` varchar(255) DEFAULT NULL,
  `cass_time` varchar(255) DEFAULT NULL,
  `cass_std_min_rn` varchar(255) DEFAULT NULL,
  `cass_freq` varchar(255) DEFAULT NULL,
  `salt_spray_time` varchar(255) DEFAULT NULL,
  `salt_spray_std_rusting` varchar(255) DEFAULT NULL,
  `salt_spray_freq` varchar(255) DEFAULT NULL,
  `porecount_std_min` varchar(255) DEFAULT NULL,
  `porecount_freq` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### B. Tabel `durability_thickness_reports` (Transaksi Inspeksi QC & Approval)

```sql
CREATE TABLE `durability_thickness_reports` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `standard_performance_test_id` bigint(20) UNSIGNED NOT NULL,
  `data1_id` bigint(20) UNSIGNED DEFAULT NULL,
  `production_date` date DEFAULT NULL,
  `shift` varchar(255) DEFAULT NULL,
  `lot_no` varchar(255) DEFAULT NULL,
  `tgl_masuk` date DEFAULT NULL,
  `jam_masuk` time DEFAULT NULL,
  `tgl_keluar` date DEFAULT NULL,
  `jam_keluar` time DEFAULT NULL,
  `tanggal_cek` date DEFAULT NULL,
  `actual_cu` varchar(255) DEFAULT NULL,
  `actual_ni` varchar(255) DEFAULT NULL,
  `actual_cr` varchar(255) DEFAULT NULL,
  `actual_corrodkote_waktu` varchar(255) DEFAULT NULL,
  `standar_jam_corrodkote` varchar(255) DEFAULT NULL,
  `aktual_corrosion` varchar(255) DEFAULT NULL,
  `actual_cass_waktu` varchar(255) DEFAULT NULL,
  `standar_jam_cass` varchar(255) DEFAULT NULL,
  `actual_salt_spray_waktu` varchar(255) DEFAULT NULL,
  `standar_jam_salt_spray` varchar(255) DEFAULT NULL,
  `actual_porecount` varchar(255) DEFAULT NULL,
  `result_judgment` varchar(255) DEFAULT NULL,
  `result_judgment_corrodkote` varchar(255) DEFAULT NULL,
  `result_judgment_cass` varchar(255) DEFAULT NULL,
  `result_judgment_salt_spray` varchar(255) DEFAULT NULL,
  `result_judgment_porecount` varchar(255) DEFAULT NULL,
  `evidence_before` varchar(255) DEFAULT NULL,
  `evidence_before_uploaded_at` timestamp NULL DEFAULT NULL,
  `evidence_after` varchar(255) DEFAULT NULL,
  `evidence_after_uploaded_at` timestamp NULL DEFAULT NULL,
  `evidence_after_trial` varchar(255) DEFAULT NULL,
  `evidence_after_trial_uploaded_at` timestamp NULL DEFAULT NULL,
  `analis_id` bigint(20) UNSIGNED DEFAULT NULL,
  `analis_corrodkote_id` bigint(20) UNSIGNED DEFAULT NULL,
  `analis_cass_id` bigint(20) UNSIGNED DEFAULT NULL,
  `analis_salt_spray_id` bigint(20) UNSIGNED DEFAULT NULL,
  `analis_porecount_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_trial` tinyint(1) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `description_corrodkote` text DEFAULT NULL,
  `description_cass` text DEFAULT NULL,
  `description_salt_spray` text DEFAULT NULL,
  `description_porecount` text DEFAULT NULL,
  `supervisor_qc` varchar(255) DEFAULT NULL,
  `supervisor_approved_at` timestamp NULL DEFAULT NULL,
  `supervisor_plating` varchar(255) DEFAULT NULL,
  `supervisor_plating_approved_at` timestamp NULL DEFAULT NULL,
  `asst_manager_qc` varchar(255) DEFAULT NULL,
  `asst_manager_approved_at` timestamp NULL DEFAULT NULL,
  `asst_manager_plating` varchar(255) DEFAULT NULL,
  `asst_manager_plating_approved_at` timestamp NULL DEFAULT NULL,
  `rejection_remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `durability_reports_std_id_foreign` (`standard_performance_test_id`),
  KEY `durability_reports_data1_id_foreign` (`data1_id`),
  KEY `idx_durability_lot_trial` (`lot_no`, `is_trial`),
  KEY `idx_durability_prod_date` (`production_date`),
  KEY `idx_durability_tgl_cek` (`tanggal_cek`),
  CONSTRAINT `durability_reports_std_id_foreign` FOREIGN KEY (`standard_performance_test_id`) REFERENCES `standard_performance_tests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 3. Logika Backend & Spesifikasi Controller (`StandardPerformanceTestController`)

Controller [StandardPerformanceTestController.php](file:///d:/laragon/www/qc-project/app/Http/Controllers/StandardPerformanceTestController.php) mengendalikan tanggung jawab utama berikut:

### A. Metode `renderReport(Request $request, $testType, $isTrial)`
Mengelola query filter data laporan berdasarkan jenis pengujian (`thickness`, `corrodkote`, `cass`, `salt_spray`, `porecount`) dan mode (`Data 1` vs `Data 2 Trial`):
1. Query diawali dengan memfilter `is_trial = $isTrial`.
2. Dilakukan klausa `where` untuk hanya menampilkan data yang memiliki nilai aktual sesuai jenis tes aktif (`$testType`).
3. **Merging Data 2 Trial ke Data 1 Object**:
   Jika `$isTrial == false`, controller mengambil record Data 2 (`is_trial == true`) berdasarkan `data1_id` (dengan fallback ke `standard_performance_test_id` + `lot_no`) dan menggabungkan nilai-nilainya ke objek `$report` (seperti `$report->actual_cr_trial`, `$report->description_corrodkote_trial`, `$report->evidence_after_trial`, dll.) sehingga views Blade dan JS dapat menampilkan Data 1 & Data 2 secara berdampingan dalam satu modal.
4. **Penyaringan Dropdown Filter Part (`$items`)**:
   Sistem membentuk query `$testReportIds` khusus untuk jenis tes aktif (`$testType`) dan menyaring `$items` hanya untuk master item yang terhubung dengan record transaksi tersebut.

### B. Metode Approval: `approve`, `reject`, dan `bulkApprove`
- **`approve(Request $request, $id, $type)`**:
  - Memeriksa hak akses peran pengesah (`supervisor`, `supervisor_plating`, `asst_manager`, `asst_manager_plating`, atau `admin`).
  - Mengisikan nama penandatangan dan timestamp pengesahan pada kolom yang sesuai.
  - Secara otomatis menyingkronkan status persetujuan ke record pasangan (Data 1 / Data 2 Trial).
  - Mencatat jejak audit pada `ActivityLogger`.
- **`reject(Request $request, $id, $type)`**:
  - Menerima masukan masukan alasan penolakan (`rejection_remarks`).
  - Mengubah status kolom persetujuan menjadi `'REJECTED'` dan menyimpan format riwayat penolakan `[tgl user]: alasan`.
- **`bulkApprove(Request $request)`**:
  - Memproses persetujuan massal untuk seluruh data laporan dalam rentang tanggal yang difilter.

### C. Metode `storeThickness(Request $request)`
1. Mengunggah berkas foto evidence jika disertakan (`evidence_before`, `evidence_after`, dan `evidence_after_trial`) ke direktori `public/uploads/durability_plating/`.
2. Membuat record Data 1 (`is_trial = false`) dengan menyimpan data metadata, hasil pengujian, dan deskripsi spesifik (`description_corrodkote`, `description_cass`, dll.).
3. Membuat record Data 2 Trial (`is_trial = true`) secara otomatis dengan nomor lot yang sama dan menyimpan `data1_id = $report1->id`.

### D. Metode `updateThickness(Request $request, $id)`
1. Meng-update record Data 1 berdasarkan field yang dikirimkan.
2. Mencari atau membuat record Data 2 Trial yang berpasangan berdasarkan `data1_id` (atau `standard_performance_test_id` + `lot_no`).
3. Memperbarui nilai Data 2 Trial (`actual_*_trial`, `description_*_trial`, `result_judgment_trial`).
4. Memproses pencatatan PIC spesifik pengujian (`analis_corrodkote_id`, `analis_cass_id`, `analis_salt_spray_id`, `analis_porecount_id`).
5. Memproses penghapusan foto evidence per-card (jika tombol X diklik) atau replacement foto evidence baru.

---

## 4. Frontend Architecture & JavaScript Logic (`report.js`)

File JavaScript [report.js](file:///d:/laragon/www/qc-project/public/js/durability_plating/report.js) menerapkan modul terpisah (*Strict Separation of Concerns*):

### A. Fitur Auto Judgment Real-Time & Salt Spray Options
Fungsi `calculateTestAutoJudgment($form)` mengevaluasi input pengguna secara *real-time*:
- **Thickness**: Membandingkan `actual_cr`, `actual_ni`, `actual_cu` terhadap standar master.
- **Salt Spray Test**: Mengakomodasi opsi judgment spesifik `OK`, `NG - White Rust`, dan `NG - Red Rust`.

### B. Approval UI & Rejection Modals
- Menyediakan kolom status badge terpisah untuk 4 peran:
  - **SPV Quality** (`supervisor_qc`)
  - **SPV Plating** (`supervisor_plating`)
  - **Asst Manager Quality** (`asst_manager_qc`)
  - **Asst Manager Plating** (`asst_manager_plating`)
- Tombol aksi **Approve** dan **Reject** ditampilkan secara bertahap (*sequential approval*).
- Setiap penolakan memicu modal konfirmasi `#rejectModal{id}{role}` dengan textarea alasan penolakan yang wajib diisi.
- Mendukung fitur **Approve Semua** (`#btnBulkApprove`) yang di-include dari `partials/bulk_approve_button` dan `partials/bulk_approve_script`.

### C. Solusi Anti-Clipping & Auto-Flip Dropdown Menu Aksi (`Body-Appended positioning`)
Untuk mengatasi masalah menu dropdown pada tabel responsif yang terpotong (*clipped*) oleh container `.table-responsive` atau tertutup oleh header tabel sticky (`thead` dengan `z-index: 105`):

1. **Body-Appended Transfer (`z-index: 1095`)**:
   Saat event `show.bs.dropdown` terpicu, menu `.dropdown-menu` dialihkan secara dinamis sebagai anak langsung dari `<body>` dengan `z-index: 1095`.
2. **Fixed Compact Width (200px)**:
   Menu diberi style `width: 200px !important`, `min-width: 200px !important`, dan `max-width: 200px !important`.
3. **Smart Auto-Flip Viewport Positioning**:
   Sistem mengukur posisi tombol 3-dots terhadap viewport layar:
   - Jika sisa ruang di bawah tombol tidak cukup (`top + menuHeight > windowBottom`), menu secara otomatis terbuka ke **ATAS (Flip Up)**.
   - Jika sisa ruang di bawah cukup, menu terbuka ke **BAWAH** secara normal.
4. **Clean Restoration**:
   Saat event `hide.bs.dropdown` terpicu, gaya CSS dikembalikan dan `.dropdown-menu` dipindahkan kembali ke elemen parent semula.

### D. Sistem Audio Validasi & Notifikasi Scan (`window.playAppAudio`)
Seluruh pemicuan auditif pada pemindaian QR Code dikonsolidasikan melalui helper global `window.playAppAudio(type)` pada `layouts/admin.blade.php`:
- `success` $\rightarrow$ `QR CODE BERHASIL DI SCAN.mp3` (disertai `Swal.fire` modal feedback).
- `format_error` $\rightarrow$ `FORMAT QR CODE SALAH, SCAN QR INTERNAL.mp3`.
- `duplicate_saved` $\rightarrow$ `QR CODE DUPLICATE, SUDAH DI SIMPAN SEBELUM NYA.mp3`.
- `duplicate_list` $\rightarrow$ `QR CODE SUDAH ADA DI LIST.mp3`.
- `item_not_found` $\rightarrow$ `ITEM PART INI TIDAK ADA DI CHECKSHEET INI.mp3`.

---

## 5. Langkah demi Langkah Replikasi untuk Membuat Modul Baru

Jika Anda ingin membuat modul pengujian baru (misalnya: **Painting Durability Test** atau **Heat Treatment Test**), ikuti 5 langkah terstruktur berikut:

### Langkah 1: Buat Migration & Model Eloquent
1. Buat Model & Migration Master Specs:
   `php artisan make:model PaintingStandard -m`
2. Buat Model & Migration Transaksi Report:
   `php artisan make:model PaintingReport -m`
3. Tambahkan kolom standar pada master dan kolom hasil pengujian + `is_trial` + `data1_id` + `supervisor_qc` + `supervisor_plating` + `asst_manager_qc` + `asst_manager_plating` + `rejection_remarks` pada transaksi.

### Langkah 2: Buat Controller
1. Buat Controller: `php artisan make:controller PaintingPerformanceTestController`
2. Salin struktur method `renderReport`, `storeThickness`, `updateThickness`, `approve`, `reject`, `bulkApprove`, dan `clearTestData` dari `StandardPerformanceTestController.php`.
3. Sesuaikan pemetaan nama kolom untuk pengujian baru Anda.

### Langkah 3: Registrasi Routes di `routes/checksheets.php`
Daftarkan URI route untuk master index, report Data 1, report Data 2 Trial, store, update, destroy, approve, reject, dan bulk-approve.

### Langkah 4: Buat Views Blade (`resources/views/painting/`)
1. `index.blade.php`: Tabel CRUD Master Specs dengan header 2 baris (`Standard<br>Customer`).
2. `report.blade.php`: Tabel laporan utama dengan `max-height: calc(100vh - 220px)`, modal edit 2 kolom (DATA 1 & DATA 2 berdampingan), kolom approval 4 peran, modal rejection, serta kartu modal bertema putih minimalis ber-border.
3. Tempatkan Floating Action Bar (`#bulkActionMenu`) berposisi tengah bawah dengan warna tema Putih-Biru minimalis.

---

## 6. Verifikasi & QA Checklist

- [x] Sintaks PHP & Blade valid tanpa error (`php artisan view:cache`).
- [x] JavaScript syntactically clean (`node -c public/js/durability_plating/report.js`).
- [x] Migrasi kolom approval (`supervisor_qc`, `supervisor_plating`, `asst_manager_qc`, `asst_manager_plating`, `rejection_remarks`) berhasil dijalankan.
- [x] Route `approve`, `reject`, dan `bulk-approve` terdaftar di `routes/checksheets.php`.
- [x] Kolom badge status approval 4 peran dan tombol Approve/Reject serta modal penolakan berfungsi.
- [x] Status approval tersinkronisasi otomatis antara Data 1 & Data 2.
- [x] File dokumentasi `.md` berada dalam `.gitignore` (`*.md`).

---
*Dokumen ini merupakan cetak biru teknis resmi modul Durability Plating dan siap dijadikan acuan utama dalam pembuatan modul serupa di masa mendatang.*
