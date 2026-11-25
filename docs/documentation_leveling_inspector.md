# Documentation: Modul Checksheet Leveling Inspector (PI-KRW-F-052 / QC-JKT-F-052)

Dokumen ini berisi panduan arsitektur dan referensi teknis modul **Checksheet Leveling Inspector**. Panduan ini disusun untuk memudahkan developer memahami struktur kode, skema database, rumus penilaian level per prinsip, alur perbaikan temuan NG, approval berjenjang rekap tahunan, hingga optimasi performa yang diterapkan.

---

## Ringkasan Singkat (TL;DR)

Modul **Checksheet Leveling Inspector** mengacu pada standar dokumen **`PI-KRW-F-052`** (Plant Karawang) dan **`QC-JKT-F-052`** (Plant Jakarta). Modul ini digunakan oleh Auditor (SPV ke atas / auditor pembuat audit) untuk mengevaluasi kompetensi dan kepatuhan Inspector QC di lapangan berdasarkan **5 Prinsip** dan **3 Level** per prinsip:

- **5 Prinsip Baku (`DEFAULT_PRINSIP_ORDER`)**: `MSK`, `KT`, `PNG`, `PSSP`, `KAO`.
- **3 Level per Prinsip (Level 1, 2, 3)**: Setiap prinsip memiliki daftar *point check* pada Level 1, 2, dan 3 beserta panduan penilaian (`guidance`).
- **Rumus Penilaian Independen (0–3)**:
  - Jika pada satu prinsip masih ada *point check* yang belum diisi (`null`), status level prinsip bernilai **`-`**.
  - Jika semua *point check* pada prinsip tersebut sudah terisi (`OK`/`NG`), setiap level (1, 2, 3) dinilai secara independen: **semua `OK` = 1 poin**, **ada minimal 1 `NG` = 0 poin**.
  - Total skor prinsip adalah penjumlahan poin Level 1 + Level 2 + Level 3 (`0` s/d `3`).
  - Skor keseluruhan audit (`score`) adalah rata-rata dari seluruh prinsip yang sudah bernilai numerik `0–3` (dibulatkan 2 desimal).
- **Manajemen Temuan NG & 5-Why Analysis**:
  - Setiap hasil `NG` otomatis berstatus `open` dan masuk ke daftar temuan NG.
  - Dilengkapi input analisis akar masalah (`why_1`, `why_2`, `why_3`, `root_cause`), `corrective_action`, `corrective_date`, serta multi-foto *evidence* temuan & *evidence* perbaikan (dengan kompresi GD otomatis & perbaikan orientasi EXIF).
  - Alur status NG: `open` → `waiting_verify` → `closed` (atau di-*reject* kembali ke `open`, maupun di-*unverify* kembali ke `waiting_verify`).
- **Rekap Tahunan & Approval Berjenjang 4 Tahap**:
  - Menampilkan ringkasan progres leveling per inspector dalam satu tahun (dengan perbandingan antara audit awal dan audit terbaru), plus approval berjenjang: **Auditor → Leader/Karu → SPV → Asst. Mgr**.
- **Direct Silent Print**: Pencetakan form audit (`print.blade.php`), daftar temuan NG (`ng_print.blade.php`), dan rekap tahunan (`rekap_print.blade.php`) menggunakan *hidden iframe* tanpa membuka tab baru.

---

## File-File yang Dipakai (Struktur Kode)

Berikut daftar berkas utama modul Leveling Inspector:

- **Models**:
  - [`LevelingAudit.php`](file:///d:/laragon/www/qc-project/app/Models/LevelingAudit.php) — Header transaksi audit per tanggal, inspector, bagian/meja/mesin, dan part. Memiliki accessor `proses_line_label`.
  - [`LevelingAuditResult.php`](file:///d:/laragon/www/qc-project/app/Models/LevelingAuditResult.php) — Detail hasil per *point check*, foto *evidence* (JSON array `evidence_paths` + fallback `evidence_path`), analisis 5-Why, tindakan perbaikan, dan status verifikasi NG.
  - [`LevelingCheckItem.php`](file:///d:/laragon/www/qc-project/app/Models/LevelingCheckItem.php) — Master *point check* per prinsip & level, konstanta `DEFAULT_PRINSIP_ORDER`, dan `scopeActive()`.
  - [`LevelingRecapApproval.php`](file:///d:/laragon/www/qc-project/app/Models/LevelingRecapApproval.php) — Data approval tahunan per inspector, daftar `STAGES`, `STAGE_ROLES`, dan `REKAP_ACCESS_ROLES`.
- **Controller**:
  - [`LevelingInspectorController.php`](file:///d:/laragon/www/qc-project/app/Http/Controllers/LevelingInspectorController.php) — Menangani otorisasi per level role/plant, tampilan index/rekap/print, AJAX hasil audit, lookup data, manajemen NG, dan CRUD master item.
- **Form Requests** (`app/Http/Requests/Leveling/`):
  - `StoreLevelingAuditRequest.php`
  - `SaveLevelingResultRequest.php`
  - `BulkSaveLevelingResultRequest.php`
  - `UploadLevelingEvidenceRequest.php`
  - `CorrectLevelingNgRequest.php`
  - `UpdateLevelingNgTextRequest.php`
  - `UpdateLevelingNgPhotosRequest.php`
  - `VerifyLevelingNgRequest.php`
  - `StoreLevelingCheckItemRequest.php`
  - `ApproveLevelingRecapRequest.php`
- **Service Layer**:
  - [`LevelingInspectorService.php`](file:///d:/laragon/www/qc-project/app/Services/LevelingInspectorService.php) — Seluruh logika bisnis, *caching* master item & plant map, *pre-computation* tabel audit & rekap tahunan, kompresi gambar GD, *bulk upsert*, dan *activity log*.
- **Blade Views** (`resources/views/leveling_inspector/`):
  - `index.blade.php` — Halaman utama checksheet (header ISO, filter audit, tabel matriks prinsip/level/point check, modal Audit Baru, modal Daftar Temuan NG, modal Master Point Check).
  - `print.blade.php` — Template cetak A4 audit individual.
  - `ng_print.blade.php` — Template cetak laporan daftar temuan NG.
  - `rekap.blade.php` — Halaman Rekap & Monitoring Leveling Inspector Tahunan + tabel approval 4 tahap.
  - `rekap_print.blade.php` — Template cetak Rekap Tahunan.
- **JavaScript Eksternal** (`public/js/checksheet/`):
  - [`leveling-inspector.js`](file:///d:/laragon/www/qc-project/public/js/checksheet/leveling-inspector.js) — Interaksi halaman utama (smart camera capture mobile/desktop, toggle OK/NG, bulk check, upload/hapus evidence, modal NG & 5-Why, modal master item, silent print).
  - [`leveling-rekap.js`](file:///d:/laragon/www/qc-project/public/js/checksheet/leveling-rekap.js) — Filter rekap, pencarian cepat inspector, silent print rekap, dan AJAX approval berjenjang 4 tahap.
  - [`leveling-print.js`](file:///d:/laragon/www/qc-project/public/js/checksheet/leveling-print.js) — Script cetak otomatis di dalam *hidden iframe* (mengisi timestamp `#footerDateTime` dan memanggil `window.print()`).
- **Database Migrations** (`database/migrations/`):
  - `2026_11_19_140000_create_leveling_inspector_tables.php`
  - `2026_11_19_140001_add_leveling_inspector_to_app_menus.php`
  - `2026_11_20_154000_add_why_analysis_and_multi_evidence_to_leveling_results.php`
  - `2026_11_23_143700_add_evidence_note_to_leveling_audit_results.php`
  - `2026_11_24_100700_create_leveling_recap_approvals_table.php`
  - `2026_11_24_140700_add_reject_fields_to_leveling_recap_approvals.php`
  - `2026_11_24_143400_grant_leveling_inspector_menu_to_karu_kashift.php`
  - `2026_11_25_081900_drop_reject_fields_from_leveling_recap_approvals.php`
  - `2026_11_25_092000_seed_default_leveling_check_items.php`
  - `2026_11_25_104600_add_meja_mesin_to_leveling_audits.php`
- **Routes**: [`routes/checksheets.php`](file:///d:/laragon/www/qc-project/routes/checksheets.php) (grup prefix `/checksheet/leveling-inspector`, nama route `checksheet.leveling_inspector.*`).

---

## Database & Skema Tabel

### 1. `leveling_check_items` (Master Point Check)

| Nama Kolom | Tipe Data | Nullable? | Penjelasan Singkat |
| :--- | :--- | :---: | :--- |
| `id` | `bigint (unsigned)` | No | Primary Key |
| `prinsip` | `varchar(30)` | No | Kode prinsip (`MSK`, `KT`, `PNG`, `PSSP`, `KAO`, dll.) |
| `level` | `tinyint (unsigned)` | No | Level item (`1`, `2`, atau `3`) |
| `order_no` | `int (unsigned)` | No | Urutan tampil dalam level (default `1`) |
| `point_check` | `text` | No | Deskripsi poin pemeriksaan |
| `guidance` | `text` | Yes | Panduan penilaian / kriteria (mendukung HTML tersanitasi) |
| `is_active` | `boolean` | No | Status aktif (`true` = tampil pada audit baru) |
| `created_at` / `updated_at` | `timestamp` | Yes | Timestamp sistem |

**Index**: `idx_lvl_items_active_group` pada `['is_active', 'prinsip', 'level', 'order_no']`.

### 2. `leveling_audits` (Header Audit)

| Nama Kolom | Tipe Data | Nullable? | Penjelasan Singkat |
| :--- | :--- | :---: | :--- |
| `id` | `bigint (unsigned)` | No | Primary Key |
| `plant_code` | `varchar(30)` | No | Kode plant (`karawang` / `jakarta`) |
| `audit_date` | `date` | No | Tanggal pelaksanaan audit |
| `inspector_id` | `bigint (unsigned)` | No | FK ke `users.id` (cascade on delete) |
| `bagian` | `varchar(120)` | Yes | Bagian/area kerja inspector |
| `meja_mesin` | `varchar(80)` | Yes | Nomor meja atau mesin (contoh: `Meja 9`, `Mesin 4`) |
| `item_id` | `char(36)` | Yes | FK ke `items.id` (null on delete) |
| `part_name` | `varchar(255)` | No | Nama part yang diaudit |
| `part_number` | `varchar(100)` | Yes | Nomor part |
| `auditor_id` | `bigint (unsigned)` | No | FK ke `users.id` (user pembuat audit) |
| `auditor_name` | `varchar(150)` | No | Snapshot nama auditor |
| `total_ok` | `smallint (unsigned)` | No | Jumlah point check bernilai `OK` |
| `total_ng` | `smallint (unsigned)` | No | Jumlah point check bernilai `NG` |
| `score` | `decimal(5,2)` | Yes | Rata-rata level seluruh prinsip yang sudah lengkap (`0.00–3.00`) |
| `created_at` / `updated_at` | `timestamp` | Yes | Timestamp sistem |

**Index & Unique**:
- `idx_lvl_audits_plant_date` pada `['plant_code', 'audit_date']`
- `idx_lvl_audits_plant_insp_date` pada `['plant_code', 'inspector_id', 'audit_date']`
- `uq_lvl_audit_plant_date_insp_item` (unique) pada `['plant_code', 'audit_date', 'inspector_id', 'item_id']`

### 3. `leveling_audit_results` (Hasil Check, Evidence & Perbaikan NG)

| Nama Kolom | Tipe Data | Nullable? | Penjelasan Singkat |
| :--- | :--- | :---: | :--- |
| `id` | `bigint (unsigned)` | No | Primary Key |
| `audit_id` | `bigint (unsigned)` | No | FK ke `leveling_audits.id` (cascade on delete) |
| `check_item_id` | `bigint (unsigned)` | Yes | FK ke `leveling_check_items.id` (null on delete) |
| `prinsip` | `varchar(30)` | No | Snapshot kode prinsip saat penilaian disimpan |
| `level` | `tinyint (unsigned)` | No | Snapshot level item |
| `point_check` | `text` | No | Snapshot teks point check |
| `result` | `enum('OK', 'NG')` | Yes | Hasil penilaian (`null` jika belum diisi) |
| `evidence_path` | `varchar(255)` | Yes | Path foto evidence tunggal (legacy/utama) |
| `evidence_paths` | `json` | Yes | Array path multi-foto evidence temuan |
| `evidence_note` | `text` | Yes | Keterangan foto evidence temuan |
| `ng_status` | `enum('open','waiting_verify','closed')` | Yes | Status tindak lanjut temuan NG (`null` jika bukan `NG`) |
| `why_1` / `why_2` / `why_3` | `text` | Yes | Analisis 5-Why tahap 1–3 |
| `root_cause` | `text` | Yes | Akar masalah utama |
| `corrective_action` | `text` | Yes | Tindakan perbaikan yang dilakukan |
| `corrective_evidence_path` | `varchar(255)` | Yes | Path foto bukti perbaikan tunggal (legacy/utama) |
| `corrective_evidence_paths` | `json` | Yes | Array path multi-foto bukti perbaikan |
| `corrective_date` | `date` | Yes | Tanggal selesai perbaikan |
| `corrected_by` | `bigint (unsigned)` | Yes | FK ke `users.id` (penginput perbaikan) |
| `verified_by` | `bigint (unsigned)` | Yes | FK ke `users.id` (verifikator) |
| `verified_at` | `timestamp` | Yes | Waktu verifikasi disetujui |
| `reject_reason` | `text` | Yes | Alasan penolakan perbaikan oleh verifikator |
| `created_at` / `updated_at` | `timestamp` | Yes | Timestamp sistem |

**Index & Unique**:
- `uq_lvl_res_audit_item` (unique) pada `['audit_id', 'check_item_id']`
- `idx_lvl_res_ng_status_audit` pada `['ng_status', 'audit_id']`

### 4. `leveling_recap_approvals` (Approval Rekap Tahunan)

| Nama Kolom | Tipe Data | Nullable? | Penjelasan Singkat |
| :--- | :--- | :---: | :--- |
| `id` | `bigint (unsigned)` | No | Primary Key |
| `plant_code` | `varchar(32)` | No | Kode plant |
| `year` | `smallint (unsigned)` | No | Tahun rekap |
| `inspector_id` | `bigint (unsigned)` | No | FK ke `users.id` (cascade on delete) |
| `auditor_approved_by` / `at` | `bigint` / `timestamp` | Yes | Tahap 1: Approval Auditor |
| `leader_approved_by` / `at` | `bigint` / `timestamp` | Yes | Tahap 2: Approval Leader/Karu/Kashift |
| `spv_approved_by` / `at` | `bigint` / `timestamp` | Yes | Tahap 3: Approval SPV |
| `asst_mgr_approved_by` / `at` | `bigint` / `timestamp` | Yes | Tahap 4: Approval Asst. Manager / Manager |
| `created_at` / `updated_at` | `timestamp` | Yes | Timestamp sistem |

**Index & Unique**:
- `lvl_recap_appr_unique` (unique) pada `['plant_code', 'year', 'inspector_id']`
- `lvl_recap_appr_plant_year_idx` pada `['plant_code', 'year']`

---

## Logika Kunci & Arsitektur Kode

### 1. Rumus Penilaian Level per Prinsip (`calculatePrinsipLevel`)

Logika perhitungan berada di [`LevelingInspectorService.php`](file:///d:/laragon/www/qc-project/app/Services/LevelingInspectorService.php) agar tidak ada kalkulasi di Blade:

```php
public function calculatePrinsipLevel(Collection $prinsipItems, Collection $resultsByItemId): string
{
    if ($prinsipItems->isEmpty()) {
        return '-';
    }

    $levelHasNg = [];

    foreach ($prinsipItems as $item) {
        $res = $resultsByItemId->get($item->id);
        $val = $res ? $res->result : null;
        if ($val === null) {
            return '-'; // Belum lengkap
        }

        $lvl = (int) $item->level;
        if (!array_key_exists($lvl, $levelHasNg)) {
            $levelHasNg[$lvl] = false;
        }
        if ($val === 'NG') {
            $levelHasNg[$lvl] = true;
        }
    }

    $total = 0;
    foreach ($levelHasNg as $hasNg) {
        if (!$hasNg) {
            $total++;
        }
    }

    return (string) $total;
}
```

Nilai rata-rata keseluruhan audit (`calculateAverageLevelScore()`) hanya menghitung prinsip yang sudah menghasilkan angka (`0–3`), mengabaikan prinsip yang masih `-`.

### 2. Preservasi Item Historis (`getCheckItemsForAudit`)

Jika suatu *master point check* dinonaktifkan (`is_active = false`) di kemudian hari, audit lama yang sudah memiliki record pada item tersebut tetap menampilkan baris historisnya agar skor audit lama tidak berubah. Method `getCheckItemsForAudit()` menggabungkan item aktif (dari cache) dengan item non-aktif yang ID-nya tercatat pada `leveling_audit_results` milik audit tersebut.

### 3. Simpan Hasil Satuan & Massal (`saveResult` & `bulkSaveResults`)

- **`saveResult()`**: Jika nilai diubah dari `NG` menjadi `OK` atau dikosongkan (`null`), seluruh data analisis NG (`why_1..3`, `root_cause`, `corrective_action`, `ng_status`, `reject_reason`, dan file foto perbaikan di storage) otomatis dibersihkan.
- **`bulkSaveResults()`**: Menggunakan 1 query `LevelingAuditResult::upsert(...)` untuk memperbarui banyak *point check* sekaligus (menghindari N+1 query), lalu memanggil `recalculateAuditSummary()` untuk memperbarui `total_ok`, `total_ng`, dan `score` di `leveling_audits`.

### 4. Optimasi Foto & Auto-Rotate EXIF (`storeOptimizedImage`)

Saat mengunggah foto *evidence* temuan maupun foto perbaikan:
- Jika ekstensi PHP `gd` tersedia, gambar dibaca orientasi EXIF-nya (untuk JPEG kamera HP agar tidak miring), di-resize proporsional maksimal `1600px`, diberi background putih (untuk PNG transparan), lalu dikompresi menjadi JPEG kualitas `80%`.
- Jika `gd` tidak tersedia atau terjadi error parsing, otomatis *fallback* ke `$file->store($dir, 'public')`.
- Frontend [`leveling-inspector.js`](file:///d:/laragon/www/qc-project/public/js/checksheet/leveling-inspector.js) juga menyesuaikan atribut `capture="environment"` pada perangkat mobile/tablet dan `multiple` pada desktop (`syncSmartCaptureInputs()`).

### 5. Alur Status Temuan NG

1. Point check diset `NG` → `ng_status = 'open'`.
2. SPV/Manager menginput 5-Why + tindakan perbaikan + foto (`correctNg`) → `ng_status = 'waiting_verify'`.
   - Teks perbaikan dapat diedit ulang melalui `updateNgCorrectionText()` dan daftar fotonya dapat ditambah/dikurangi via `updateNgCorrectionPhotos()`.
3. Verifikasi (`verifyNg`):
   - `action = 'approve'` → `ng_status = 'closed'`, mencatat `verified_by` & `verified_at`.
   - `action = 'reject'` → `ng_status = 'open'`, menyimpan `reject_reason`.
   - `action = 'unverify'` → `ng_status = 'waiting_verify'` (hanya boleh dilakukan oleh `admin`/`superadmin`, auditor pembuat audit, atau user yang melakukan verifikasi sebelumnya).

### 6. Approval Berjenjang 4 Tahap pada Rekap Tahunan

Diatur pada `formatRecapApprovalStages()` dan `processRecapApproval()`:
- Urutan tahap wajib berurutan: `auditor` → `leader` → `spv` → `asst_mgr`.
- Tahap berikutnya **tidak dapat** di-approve jika tahap sebelumnya belum disetujui.
- Pembatalan approval (`action = 'cancel'`) **hanya dapat** dilakukan oleh `admin` atau user yang melakukan approval pada tahap tersebut, dan **tidak boleh** jika tahap setelahnya sudah berstatus approved.

### 7. Kepatuhan 7 Pilar Blueprint Optimalisasi

- **Cache Master & Plant Map**: `getActiveCheckItems()` di-cache 1 jam (`leveling_active_check_items_v1`) dan di-invalidate via `clearMasterItemsCache()` setiap ada perubahan master item. Mapping `plant_code` → `plant_id` di-cache 1 jam (`leveling_plant_code_id_map_v1`).
- **Composite Index Friendly**: Filter tanggal di `getAuditsForFilter()` dan `searchAudits()` membandingkan kolom `audit_date` langsung (`where('audit_date', $date)` / `whereBetween`) tanpa `whereDate()` agar composite index `(plant_code, audit_date)` aktif.
- **Pre-computation**: `buildAuditTableData()` dan `buildAnnualRecapData()` menghitung seluruh rowspan, level prinsip, ringkasan skor, dan hak aksi tombol di Service Layer sebelum dikirim ke Blade.
- **External JS Config**: Route dan state awal dikirim dari Blade ke JS melalui atribut `data-*` pada elemen `#leveling-config` (untuk `index.blade.php`) dan `#rekapConfig` (untuk `rekap.blade.php`), tanpa inline `<script>`.

---

## Daftar Routes (`routes/checksheets.php`)

Seluruh route berada di bawah prefix `/checksheet/leveling-inspector` dengan nama `checksheet.leveling_inspector.*`:

| Method | URI | Controller Method | Fungsi |
| :--- | :--- | :--- | :--- |
| `GET` | `/` | `index` | Halaman utama checksheet Leveling Inspector |
| `GET` | `/rekap` | `rekap` | Halaman Rekap & Monitoring Tahunan |
| `GET` | `/rekap/print` | `rekapPrint` | Halaman cetak Rekap Tahunan |
| `POST` | `/rekap/approve` | `approveRekap` | Proses approve / cancel approval rekap |
| `GET` | `/print` | `print` | Halaman cetak form audit individual |
| `POST` | `/audit` | `storeAudit` | Buat sesi audit baru |
| `DELETE` | `/audit/{id}` | `destroyAudit` | Hapus audit (beserta seluruh file evidence terkait) |
| `POST` | `/result` | `saveResult` | Simpan nilai OK / NG / kosong pada 1 point check |
| `POST` | `/result/bulk` | `bulkSaveResult` | Simpan nilai OK / NG secara massal |
| `POST` | `/result/evidence` | `uploadEvidence` | Upload satu atau lebih foto evidence temuan |
| `PATCH` | `/result/{id}/evidence-note` | `updateEvidenceNote` | Perbarui keterangan evidence temuan |
| `DELETE` | `/result/{id}/evidence` | `destroyEvidence` | Hapus foto evidence (per index atau seluruhnya) |
| `GET` | `/lookup/inspectors` | `lookupInspectors` | Pencarian daftar inspector aktif per plant |
| `GET` | `/lookup/audits` | `lookupAudits` | Pencarian cepat riwayat audit |
| `GET` | `/lookup/bagian` | `lookupBagian` | Ambil bagian terbaru inspector dari jadwal compliance |
| `GET` | `/lookup/parts` | `lookupParts` | Cari part sesuai `DandoryOperatorConfig` inspector |
| `GET` | `/ng` | `listNg` | Ambil daftar temuan NG (filter status/tanggal/inspector) |
| `GET` | `/ng/print` | `printNg` | Halaman cetak laporan temuan NG |
| `POST` | `/ng/{id}/correct` | `correctNg` | Submit analisis 5-Why, tindakan perbaikan & foto |
| `PATCH` | `/ng/{id}/correct-text` | `updateNgCorrectionText` | Edit teks 5-Why & tindakan perbaikan |
| `POST` | `/ng/{id}/correct-photos` | `updateNgCorrectionPhotos` | Tambah/hapus foto evidence perbaikan |
| `POST` | `/ng/{id}/verify` | `verifyNg` | Approve / Reject / Unverify temuan NG |
| `GET` | `/master-item` | `listMasterItems` | Daftar seluruh Master Point Check |
| `POST` | `/master-item` | `storeMasterItem` | Tambah Master Point Check baru |
| `PUT` | `/master-item/{id}` | `updateMasterItem` | Ubah Master Point Check |
| `POST` | `/master-item/{id}/toggle` | `toggleMasterItem` | Aktifkan / nonaktifkan Master Point Check |

---

## Alur Hak Akses & Role Pengguna (RBAC)

1. **Akses Menu & Rekap (`authorizeAccess` / `authorizeRekapAccess`)**:
   - Hanya dapat diakses oleh role yang terdaftar di `LevelingRecapApproval::REKAP_ACCESS_ROLES` (Leader, Karu, Kashift, SPV, Asst. Manager, Manager, dan Admin) serta memiliki izin `view` pada `AppMenu` dengan route `checksheet.leveling_inspector.index` untuk plant terkait.
2. **Edit Data Audit (`canEditAudit`)**:
   - Hanya **auditor pembuat audit** (`audit->auditor_id === user->id`) atau user dengan role `OperatorComplianceItem::MASTER_MANAGER_ROLES` (SPV ke atas / Admin) yang dapat mengubah nilai OK/NG dan mengunggah evidence.
3. **Fitur Manajemen (`authorizeSpvUp`)**:
   - Membuat Audit Baru, melihat/mengelola Daftar Temuan NG, serta CRUD Master Point Check dibatasi hanya untuk `MASTER_MANAGER_ROLES` (SPV ke atas).
4. **Approval Rekap Tahunan (`LevelingRecapApproval::STAGE_ROLES`)**:
   - `auditor`: Role SPV ke atas / Admin **atau** user yang `id`-nya tercatat sebagai `auditor_id` pada audit inspector di tahun tersebut.
   - `leader`: `admin`, `leader`, `karu_qc`, `karu_prod`, `kashift`, `kashift_plating`.
   - `spv`: `admin`, `supervisor`, `supervisor_qc`, `supervisor_plating`.
   - `asst_mgr`: `admin`, `asst_manager`, `asst_manager_qc`, `asst_manager_plating`, `manager`, `manager_qc`, `manager_plating`.

---

## Standar Output Dokumen Cetak (ISO Layout)

- **Form Audit Individual** ([`print.blade.php`](file:///d:/laragon/www/qc-project/resources/views/leveling_inspector/print.blade.php)):
  - Nomor Dokumen dari `GeneralSetting::getDocHeader('leveling_inspector', ...)`, default `PI-KRW-F-052` (Karawang) / `QC-JKT-F-052` (Jakarta).
  - Header tanda tangan resmi per plant: Karawang (`Arief H` — Spv. QC, `Iwan S` — Asst. Mgr Quality, `Desti K` — Mgr. Quality), Jakarta (`Masuli` — Spv. QC, `Iwan S` — Asst. Mgr Quality, `Desti K` — Mgr. Quality).
  - Menampilkan metadata audit (Inspector, Bagian/Meja/Mesin, Part, Tanggal, Auditor), skor level per prinsip, dan rincian tabel point check.
- **Laporan Temuan NG** ([`ng_print.blade.php`](file:///d:/laragon/www/qc-project/resources/views/leveling_inspector/ng_print.blade.php)):
  - Mencetak ringkasan jumlah temuan (`total`, `open`, `waiting_verify`, `closed`) beserta detail analisis 5-Why, tindakan perbaikan, dan foto evidence.
- **Rekap Tahunan** ([`rekap_print.blade.php`](file:///d:/laragon/www/qc-project/resources/views/leveling_inspector/rekap_print.blade.php)):
  - Mencetak matriks nilai per prinsip (Audit Awal vs Audit Terbaru) seluruh inspector pada tahun & bagian terpilih beserta status 4 tahap approval.

---

## Checklist Developer & Perawatan Kode

```bash
# 1. Bersihkan compiled view dan cache aplikasi
php artisan view:clear
php artisan config:clear
php artisan cache:clear

# 2. Validasi sintaks PHP file terkait Leveling Inspector
php -l app/Http/Controllers/LevelingInspectorController.php
php -l app/Services/LevelingInspectorService.php
php -l app/Models/LevelingAudit.php
php -l app/Models/LevelingAuditResult.php
php -l app/Models/LevelingCheckItem.php
php -l app/Models/LevelingRecapApproval.php

# 3. Validasi sintaks JavaScript eksternal
node -c public/js/checksheet/leveling-inspector.js
node -c public/js/checksheet/leveling-rekap.js
node -c public/js/checksheet/leveling-print.js
```
