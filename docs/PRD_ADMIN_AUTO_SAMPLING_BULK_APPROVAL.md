# PRODUCT REQUIREMENT DOCUMENT (PRD)
## Fitur: Automatic Quota Sampling & Multi-User Approver pada Bulk Approval Admin

| Metadata Dokumen | Deskripsi |
| :--- | :--- |
| **Nama Dokumen** | PRD - Admin Bulk Approval with Auto-Sampling & Multi-User Assignment |
| **Versi** | 1.1.0 (Production Release) |
| **Status** | Completed / Implemented (Production Ready) |
| **Target User** | Super Admin / Administrator QC |
| **Modul Terdampak** | In-Process Checksheet, Sub Assy, First Piece Approval (FPA), Incoming (Parts, Material, Chemical, Sub Part), Double Tape, Cross Cut, & Performance Test |
| **Tanggal Pembuatan** | 15 Oktober 2026 |

---

## 1. Latar Belakang & Masalah (Problem Statement)

### 1.1 Kondisi Saat Ini (Current State)
1. **Bulk Approval Selalu Menandai Metode `bulk`**:
   Saat Administrator menggunakan tombol **"Approve Semua"**, sistem meng-approve seluruh checksheet yang sesuai filter dengan tanda metode `bulk` (`approval_methods = {"role": "bulk"}`).
2. **Ketiadaan Data Sampling Otomatis**:
   SOP QC mensyaratkan adanya **Sampling Checksheet** dengan rasio/kuota tertentu per role (misalnya Karu/Kashift wajib 2 sampling per shift, SPV 1 sampling per shift, Manager 1 sampling per hari). Saat ini, jika Admin melakukan bulk approve, status data sampling tidak terbentuk kecuali diapprove satu per satu secara manual.
3. **Approver Selalu Tertulis "Administrator"**:
   Saat Admin melakukan bulk approve, kolom approver (`karu_qc`, `supervisor_qc`, dll) selalu terisi nama `"Administrator"`. Padahal di lapangan, setiap role (khususnya Karu QC dan Kepala Regu) memiliki beberapa user yang bertugas (misal: *Budi - Shift 1*, *Agus - Shift 2*, *Iwan - Shift 3*, atau beberapa SPV/Manager). Admin tidak dapat memilih atas nama user siapa approval tersebut dicatat.
4. **Kebutuhan Fleksibilitas Filter**:
   Admin seringkali perlu melakukan approval lintas tanggal (misal 1 minggu ke belakang, 1 bulan, atau tanggal tertentu) dengan filter aktif (mesin, inisial, shift, status) dan ingin kuota sampling otomatis terpenuhi secara acak (*random distribution*) di dalam rentang data yang difilter tersebut.

---

## 2. Tujuan & Sasaran Fitur (Objectives)

1. **Auto-Quota Compliance**:
   Memastikan bulk approval oleh Admin secara otomatis mengalokasikan data berstatus **`sampling`** sesuai kuota resmi per role, dan sisanya otomatis berstatus **`bulk`**.
2. **Fair & Randomized Selection**:
   Sistem secara acak (*pseudo-random secure*) memilih baris data checksheet untuk kuota sampling dari data yang memenuhi filter aktif, mereplikasi proses sampling audit yang objektif.
3. **Multi-User Dynamic Assignment**:
   Jika suatu role memiliki lebih dari 1 user terdaftar di sistem, Admin disajikan opsi untuk memilih user mana yang akan dicatat sebagai penandatangan/approver (atau memilih opsi delegasi per shift).
4. **Efisiensi Kerja Admin**:
   Admin tidak perlu lagi mencari dan mengklik manual 2 data per shift untuk dijadikan sampling sebelum melakukan bulk approve. Cukup 1 kali klik filter aktif & konfirmasi modal, seluruh kuota sampling dan bulk langsung tuntas.

---

## 3. Matriks Aturan Kuota Sampling (Sampling Quota Rules)

Berdasarkan aturan baku pada `App\Helpers\ApprovalHelper::getSamplingQuotaRequirement()`:

| Level Approval (Role) | Target Kolom DB | Cakupan / Scope | Kuota Wajib | Logika Alokasi Otomatis |
| :--- | :--- | :--- | :---: | :--- |
| **Karu QC / Kashift QC** | `karu_qc` / `kashift_qc` | **Per Shift & Per Tanggal** | **2 data / shift** | Pilih 2 data acak per shift per hari sebagai `sampling`, sisanya `bulk`. |
| **Kashift Plating** | `kashift_plating` | **Per Shift & Per Tanggal** | **2 data / shift** | Pilih 2 data acak per shift per hari sebagai `sampling`, sisanya `bulk`. |
| **Supervisor QC** | `supervisor_qc` / `supervisor` | **Per Shift & Per Tanggal** | **1 data / shift** | Pilih 1 data acak per shift per hari sebagai `sampling`, sisanya `bulk`. |
| **Supervisor Plating** | `supervisor_plating` | **Per Shift & Per Tanggal** | **1 data / shift** | Pilih 1 data acak per shift per hari sebagai `sampling`, sisanya `bulk`. |
| **Asst Manager QC** | `asst_manager_qc` / `asst_manager` | **Per Tanggal (Daily)** | **1 data / hari** | Pilih 1 data acak per hari (lintas shift) sebagai `sampling`, sisanya `bulk`. |
| **Asst Manager Plating** | `asst_manager_plating` | **Per Tanggal (Daily)** | **1 data / hari** | Pilih 1 data acak per hari sebagai `sampling`, sisanya `bulk`. |
| **Manager QC** | `manager_qc` / `manager` | **Per Tanggal (Daily)** | **1 data / hari** | Pilih 1 data acak per hari sebagai `sampling`, sisanya `bulk`. |
| **Manager Plating** | `manager_plating` | **Per Tanggal (Daily)** | **1 data / hari** | Pilih 1 data acak per hari sebagai `sampling`, sisanya `bulk`. |

> **Penanganan Jumlah Data Kurang Dari Kuota:**
> - Jika dalam suatu kelompok (misal Shift 3 pada tanggal tertentu) jumlah checksheet yang tersedia **kurang dari kuota** (misal hanya ada 1 data), maka **seluruh data tersebut (1 data) dijadikan sampling**, dan tidak ada data bulk. Sistem tidak melempar error dan proses tetap berjalan mulus.
> - Jika sudah ada data yang sebelumnya diapprove sebagai `sampling` oleh user reguler, sistem memperhitungkan data tersebut sehingga total sampling tetap pas sesuai kuota (tidak berlebih).

---

## 4. Alur Kerja Pengguna (User Flow & Modal UX)

1. **Step 1: Pilihan Level Approval (Existing Modal)**:
   - Admin membuka tabel dengan filter aktif (bisa tanggal apa saja, shift apa saja).
   - Klik tombol **"Approve Semua"**.
   - Admin memilih level role yang akan diapprove (Karu QC, SPV QC, Asst Mgr QC, Mgr QC, dst).

2. **Step 2: Pemilihan User Approver (Multi-User Selection)**:
   - Saat level approval dipilih, modal secara dinamis memuat daftar user aktif dengan role terkait (melalui AJAX endpoint cepat):
     - **Opsi 1: Satu User untuk Seluruh Batch Filter**
       Contoh: Dropdown memilih satu approver untuk semua data (misal: `Hendro (SPV QC)` atau `Desti Kurniasari (Manager QC)`).
     - **Opsi 2: Berdasarkan Shift (Smart Shift Assignment - Khusus Role Karu/Kashift)**
       Jika role memiliki user per shift, Admin dapat memilih pemetaan approver:
       - Shift 1: `[ Dropdown: Budi (Karu Shift 1) ]`
       - Shift 2: `[ Dropdown: Agus (Karu Shift 2) ]`
       - Shift 3: `[ Dropdown: Iwan (Karu Shift 3) ]`
     - **Opsi 3: Akun Administrator Saya Sendiri**
       Pilihan fallback jika Admin ingin tetap mencatatkan namanya sebagai `"Administrator"`.

3. **Step 3: Ringkasan Simulasi & Konfirmasi (Simulation Preview)**:
   - Sebelum eksekusi dimulai, modal menyajikan ringkasan transparan:
     - **Total Data Terpilih**: `N Data`
     - **Rentang Filter**: `Tanggal (Start - End), Shift, Mesin, dll`
     - **Alokasi Sampling Otomatis**: `X Data (Random Acak Berdasarkan Kuota)`
     - **Alokasi Bulk Otomatis**: `Y Data (Sisa Checks)`
     - **Approver Terpilih**: `Nama User yang Dipilih`
   - Admin klik tombol hijau **"Ya, Jalankan Approval"**.
   - Sistem memproses secara chunking batch dengan Progress Bar SweetAlert2 hingga selesai (100%).

---

## 5. Spesifikasi Teknis & Logika Backend

### 5.1 Endpoint Baru / Modifikasi Endpoint
1. **Endpoint Lookup User (`GET /api/approvals/users-by-role`)**:
   - Menerima parameter `role` dan `plant_id`.
   - Mengambil data `id`, `name`, `initials` dari tabel `users` dengan kondisi `is_active = 1`.
   - Menggunakan cache memori singkat agar respon `< 20ms`.
2. **Payload Tambahan pada Endpoint `POST {bulkApproveRoute}`**:
   ```json
   {
     "approval_type": "karu_qc",
     "approver_mode": "single",
     "selected_user_id": 100,
     "approver_by_shift": {
       "1": 100,
       "2": 101,
       "3": 102
     },
     "start_date": "2026-10-01",
     "end_date": "2026-10-15",
     "shift": "",
     "plant": "karawang"
   }
   ```

### 5.2 Algoritma Pengacakan Sampling (Backend Random Allocation)
```php
// Logika Backend di HasChecksheetApproval Trait:
1. Query semua checksheet ID yang memenuhi filter aktif dan status approval-nya masih pending.
2. Group checksheet berdasarkan unit scope:
   - Jika scope == 'shift': Group per (target_date + '_' + shift).
   - Jika scope == 'day': Group per (target_date).
3. Inisialisasi $samplingIds = [] dan $bulkIds = [].
4. Untuk setiap grup data:
   a. Cek apakah di grup tersebut sudah ada baris yang berstatus 'sampling' untuk role ini.
   b. Hitung sisa kuota yang dibutuhkan: $needed = max(0, $quota - $existingSamplingCount).
   c. Jika $needed > 0:
      - Ambil kandidat baris yang pending pada grup ini.
      - Lakukan pengacakan: $picked = collect($candidateIds)->shuffle()->take($needed)->all();
      - Tambahkan $picked ke $samplingIds.
      - Sisa kandidat yang tidak terpilih dimasukkan ke $bulkIds.
   d. Jika $needed == 0:
      - Seluruh kandidat pending pada grup ini dimasukkan ke $bulkIds.
5. Return daftar ID terklasifikasi: ['sampling' => $samplingIds, 'bulk' => $bulkIds].
```

### 5.3 Batch Update & Stamping Nama User
- Eksekusi update dilakukan dalam chunk batch (misal 25 baris per request AJAX seperti arsitektur saat ini) untuk mencegah locking database:
  - Baris dalam `$samplingIds` di-update:
    - Kolom approver = Nama User terpilih (atau User sesuai shift baris tersebut).
    - Kolom tanggal approval = Timestamp waktu saat ini.
    - Kolom `approval_methods` di-update via JSON_SET: `$."{role}" = 'sampling'`.
  - Baris dalam `$bulkIds` di-update:
    - Kolom approver = Nama User terpilih.
    - Kolom tanggal approval = Timestamp waktu saat ini.
    - Kolom `approval_methods` di-update via JSON_SET: `$."{role}" = 'bulk'`.

---

## 6. Kepatuhan Standar Sistem (7 Pilar Blueprint & Aturan Proyek)

1. **Separation of Concerns (Pilar 1)**:
   - Tidak ada query Eloquent berat di dalam Blade.
   - Seluruh logika alokasi ID ditempatkan di Service / Trait Layer (`HasChecksheetApproval`).
2. **Pilar 7 & Aturan Frontend**:
   - **DILARANG KERAS membuat `<script>` inline di blade**. Seluruh fungsi pemanggilan modal, cascading dropdown user, dan animasi progress bar ditempatkan pada file eksternal `public/js/checksheet/bulk-approve.js`.
   - Data rute dan CSRF diinjeksikan via window configuration (`window.bulkApproveConfig`).
3. **Database Performance (Pilar 5)**:
   - Pengacakan dilakukan pada level kumpulan ID di memori PHP (array manipulation), bukan menggunakan `ORDER BY RAND()` di MySQL yang sangat lambat pada tabel puluhan ribu baris.
4. **UI Standardization**:
   - Komponen modal menggunakan Bootstrap 4 kustom dengan header rounded, badge warna yang seragam (Sampling: Kuning/Emas, Bulk: Hijau Mint), dan tombol konfirmasi standar SweetAlert2.

---

## 7. Rencana Kerja & Tahapan Implementasi

| Fase | Uraian Pengerjaan | Target Output |
| :---: | :--- | :--- |
| **Fase 1** | **Backend Lookup & Service Logic**<br>- Tambah method pencarian user berdasarkan role di `UserController` / `ApprovalHelper`.<br>- Refactor `HasChecksheetApproval::bulkApprove` untuk mendukung pemisahan random sampling vs bulk dan penentuan nama approver. | Endpoint user lookup & unit test logika pembagian kuota sampling. |
| **Fase 2** | **Frontend Modal & Ekstraksi JS**<br>- Modifikasi modal SweetAlert2 di `public/js/...` agar menampilkan step pemilihan user jika role memiliki > 1 user.<br>- Tambahkan simulasi ringkasan kuota sebelum kirim AJAX. | UI Modal pemilihan user yang responsif dan rapi. |
| **Fase 3** | **Integrasi & QA Testing**<br>- Verifikasi pada In-Process Checksheet & modul checksheet lainnya.<br>- Verifikasi badge warna di tabel DataTables (Sampling vs Bulk).<br>- Verifikasi nama approver di log aktivitas & database. | Fitur siap rilis 100% tanpa bug dan tanpa pelanggaran arsitektur. |
