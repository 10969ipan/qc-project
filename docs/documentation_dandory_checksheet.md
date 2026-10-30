# Documentation: Modul Checksheet Dandory Quality (QC-KRW-F-0094 / QC-JKT-F-0094)

Halo! Ini panduan catatan arsitektur dan teknis komprehensif untuk modul **Checksheet Dandory Quality**. Dokumentasi ini dirancang agar rekan-rekan developer dapat memahami alur kerja, struktur kode, skema database, aturan bisnis (*strict approval rules*), integrasi cetak langsung (*direct print*), dan optimasi performa yang diterapkan pada modul ini.

---

## Ringkasan Singkat (TL;DR)

Modul **Checksheet Dandory Quality** mengacu pada standar dokumen ISO **`QC-KRW-F-0094`** (Plant Karawang) dan **`QC-JKT-F-0094`** (Plant Jakarta). Modul ini digunakan oleh Inspector/Operator QC untuk memverifikasi kesiapan meja dan area kerja saat terjadi pergantian part, mold, dies, atau material inspeksi.

Berbeda dengan checksheet kepatuhan operator yang sifatnya periodik bulanan, transaksi Dandory berlangsung dinamis per sesi pergantian part:
- **Dual-Phase Cycle**:
  - **Awal Dandory (9 Parameter)**: Diisi saat inspector **mulai** menyiapkan meja dan dokumen sebelum memeriksa part baru (IK, PCCP, Packaging, Label, Jig Hitung, Subpart, Alat Kerja, Limit Sample, Alat Ukur).
  - **Akhir Dandory (10 Parameter)**: Diisi saat inspector **selesai** memeriksa part tersebut sebelum beralih ke part berikutnya atau saat pergantian shift.
- **3-State Scoring**: Penilaian menggunakan simbol `✓` (`OK` - Hijau), `✕` (`NG` - Merah), dan `-` (`NA` - Abu-abu).
- **Interactive Inline Matrix**: Sel tabel dapat diklik langsung untuk mengubah nilai secara instan melalui AJAX (`OK` -> `NG` -> `NA` -> `Kosong`) tanpa reload halaman.
- **Strict Approval Gatekeeper**: Leader / Kashift / Karu **hanya dapat melakukan Approve atau Reject jika data Dandory sudah 100% lengkap** (9 item awal dan 10 item akhir terisi semua). Jika belum lengkap, tombol aksi disembunyikan dan status tetap `PENDING`.
- **Traceability Waktu Input Inspector**: Kolom `TANGGAL` menampilkan tanggal pemeriksaan (`d/m/y`) dan jam menit aktual saat inspector membuat data (`created_at->format('H:i')`) tepat di bawah tanggal.
- **Direct Silent Print**: Pencetakan dokumen ISO langsung memunculkan print dialog browser melalui *hidden iframe* tanpa membuka jendela/tab baru.

---

## File-File yang Dipakai (Struktur Kode)

Berikut adalah daftar berkas utama tempat logika Checksheet Dandory berada:

- **Model**:
  - `app/Models/DandoryChecksheet.php` (Model transaksi, JSON casting, relasi operator & item, method kelengkapan `isComplete()`, `isAwalComplete()`, `isAkhirComplete()`)
  - `app/Models/DandoryItem.php` (Master item parameter standar Dandory Awal & Akhir)
  - `app/Models/DandoryOperatorConfig.php` (Konfigurasi bagian operator & relasi kategori)
- **Controller**: `app/Http/Controllers/DandoryChecksheetController.php` (Menangani filter tanggal/shift, AJAX toggle cell, input awal/akhir, approval/reject, dan direct print)
- **Service Layer**: `app/Services/DandoryChecksheetService.php` (Pengolahan database, query filter, smart preset part, sinkronisasi bagian operator dari compliance)
- **Blade Views (Tampilan)**:
  - `resources/views/dandory/index.blade.php` (Tabel matriks interaktif, Action Bar, filter dinamis, modal Mulai Dandory, modal Tutup Akhir, modal Master Item, modal Kelola Operator & Meja)
  - `resources/views/dandory/print.blade.php` (Template cetak standar ISO landscape, header formal tanda tangan, tabel split referensi standar & matriks transaksi, footer timestamp)
- **JavaScript**:
  - `public/js/checksheet/dandory-quality.js` (Logika interaktif matriks, AJAX modal submission, validasi SweetAlert auto-focus, direct print silent iframe, kelola master item & meja)
- **Database Migrations & Seeders**:
  - `database/migrations/2026_10_29_120000_create_dandory_quality_tables.php` (Tabel `dandory_items`, `dandory_checksheets`, `dandory_operator_configs`)
  - `database/migrations/2026_10_29_140431_add_rejection_columns_to_dandory_checksheets_table.php` (Kolom rejection audit trail)
  - `database/seeders/DandoryQualitySeeder.php` (19 parameter standar default ISO)
- **Routes**: `routes/checksheets.php` (Prefix grup `checksheet.dandory.*`)

---

## Database & Skema Tabel

### 1. Tabel Transaksi: `dandory_checksheets`

Menyimpan setiap transaksi pergantian part di meja inspeksi:

| Nama Kolom | Tipe Data | Nullable? | Penjelasan Singkat |
| :--- | :--- | :---: | :--- |
| `id` | `bigint (unsigned)` | No | Auto Increment Primary Key |
| `plant_code` | `varchar(20)` | No | Kode Plant (`karawang` / `jakarta`) |
| `check_date` | `date` | No | Tanggal produksi / pemeriksaan |
| `shift` | `varchar(20)` | No | Shift kerja (`Shift 1`, `Shift 2`, `Shift 3`, `Non Shift`) |
| `table_no` | `varchar(50)` | No | Nomor Meja aktual (misal: `Meja 01`, `Meja 02`) |
| `operator_id` | `bigint (unsigned)` | No | FK ke tabel `users` (Inspector yang bertugas) |
| `jabatan` | `varchar(100)` | Yes | Posisi/Bagian operator (misal: `Quality Control`) |
| `item_id` | `char(36)` | Yes | FK ke tabel `items` (UUID master part) |
| `part_name` | `varchar(200)` | No | Nama dan nomor part yang diperiksa |
| `awal_checks` | `json` | Yes | Map JSON status 9 parameter Awal Dandory |
| `akhir_checks` | `json` | Yes | Map JSON status 10 parameter Akhir Dandory |
| `awal_completed_at` | `timestamp` | Yes | Waktu selesai pengisian Awal Dandory |
| `akhir_completed_at`| `timestamp` | Yes | Waktu selesai pengisian Akhir Dandory |
| `notes` | `varchar(255)` | Yes | Catatan temuan khusus |
| `leader_checked` | `boolean` | No | Status approval leader (`true` = Disetujui) |
| `leader_user_id` | `bigint (unsigned)` | Yes | ID user leader yang menyetujui |
| `leader_name` | `varchar(100)` | Yes | Nama user leader pemeriksa |
| `leader_checked_at`| `timestamp` | Yes | Waktu approval disetujui |
| `is_rejected` | `boolean` | No | Status penolakan checksheet (`true` = Ditolak) |
| `rejection_remarks`| `varchar(255)` | Yes | Alasan penolakan dari leader/supervisor |
| `rejected_by` | `bigint (unsigned)` | Yes | ID user yang melakukan penolakan |
| `rejector_name` | `varchar(100)` | Yes | Nama user yang menolak |
| `rejected_at` | `timestamp` | Yes | Waktu checksheet ditolak |
| `created_by` | `bigint (unsigned)` | Yes | ID user pembuat record |
| `created_at` / `updated_at` | `timestamp` | Yes | Timestamp sistem |

#### Struktur JSON Kolom `awal_checks` & `akhir_checks`
Sesuai prinsip *Ponytail*, checks disimpan sebagai lightweight JSON object tanpa relasi tabel anak yang membebani query:
```json
{
  "1": "OK",
  "2": "OK",
  "3": "NA",
  "4": "OK",
  "5": "OK",
  "6": "OK",
  "7": "OK",
  "8": "OK",
  "9": "OK"
}
```

### 2. Tabel Master Parameter: `dandory_items`

Menyimpan daftar parameter standar ISO:

| Nama Kolom | Tipe Data | Nullable? | Penjelasan Singkat |
| :--- | :--- | :---: | :--- |
| `id` | `bigint (unsigned)` | No | Primary Key |
| `phase` | `enum('awal', 'akhir')` | No | Fase parameter (`awal` / `akhir`) |
| `order_no` | `tinyint (unsigned)` | No | Urutan item (`1..9` untuk awal, `1..10` untuk akhir) |
| `item_check` | `varchar(150)` | No | Nama item pemeriksaan (contoh: `IK`, `PCCP & Flow Check`) |
| `standard` | `varchar(255)` | No | Standar yang harus dipenuhi (contoh: `Terpasang & update`) |
| `is_active` | `boolean` | No | Status aktif (`1` = Aktif) |

### 3. Tabel Konfigurasi Operator: `dandory_operator_configs`

| Nama Kolom | Tipe Data | Nullable? | Penjelasan Singkat |
| :--- | :--- | :---: | :--- |
| `id` | `bigint (unsigned)` | No | Primary Key |
| `plant_code` | `varchar(20)` | No | Kode Plant |
| `operator_id` | `bigint (unsigned)` | No | FK ke tabel `users` |
| `bagian` | `varchar(100)` | Yes | Bagian kerja operator |
| `category_id` | `bigint (unsigned)` | Yes | Filter kategori part default |

---

## Logika Kunci & Arsitektur Kode

### 1. Strict Approval Gatekeeper (`isComplete()`)
Leader / Supervisor dilarang menyetujui atau menolak data yang belum selesai diinspeksi. Aturan kelengkapan divalidasi pada tingkatan Model & Controller:

```php
// app/Models/DandoryChecksheet.php

public function isAwalComplete(int $totalAwal = 9): bool
{
    if (empty($this->awal_checks) || !is_array($this->awal_checks)) return false;
    $validCount = 0;
    for ($i = 1; $i <= $totalAwal; $i++) {
        if (!empty($this->awal_checks[(string)$i])) $validCount++;
    }
    return $validCount >= $totalAwal;
}

public function isAkhirComplete(int $totalAkhir = 10): bool
{
    if (empty($this->akhir_checks) || !is_array($this->akhir_checks)) return false;
    $validCount = 0;
    for ($i = 1; $i <= $totalAkhir; $i++) {
        if (!empty($this->akhir_checks[(string)$i])) $validCount++;
    }
    return $validCount >= $totalAkhir;
}

public function isComplete(int $totalAwal = 9, int $totalAkhir = 10): bool
{
    return $this->isAwalComplete($totalAwal) && $this->isAkhirComplete($totalAkhir);
}
```

Pada tingkatan Blade View ([`index.blade.php`](file:///d:/laragon/www/qc-project/resources/views/dandory/index.blade.php)):
- Jika `$isComplete === false`, tombol **Approve** dan **Reject** disembunyikan (*hide*).
- Status badge tetap ditampilkan sebagai **`PENDING`** (abu-abu) tanpa memunculkan label peringatan yang mengganggu kerapian layout.
- Tombol aksi Approve/Reject hanya muncul saat data sudah terisi penuh (19 parameter).

### 2. Traceability Waktu Input Inspector pada Kolom Tanggal
Untuk melihat jam berapa inspector menginput data checksheet, waktu aktual ditampilkan tepat di bawah tanggal:

```blade
<!-- resources/views/dandory/index.blade.php -->
<td class="font-weight-bold text-dark">
    <div>{{ \Carbon\Carbon::parse($cs->check_date)->format('d/m/y') }}</div>
    <div class="text-muted font-weight-normal" style="font-size: 0.62rem; line-height: 1.1; margin-top: 1px;">
        {{ $cs->created_at ? \Carbon\Carbon::parse($cs->created_at)->format('H:i') : '-' }}
    </div>
</td>
```

### 3. Direct Print via Hidden Iframe (Tanpa Membuka Tab Baru)
Pencetakan dokumen checksheet menggunakan mekanisme silent iframe standar proyek:

```javascript
// public/js/checksheet/dandory-quality.js
$(document).on('click', '.btn-print-direct, #btnCetakDandory', function (e) {
    e.preventDefault();
    var printUrl = $(this).attr('href') || $(this).data('print-url');
    if (!printUrl || printUrl === '#' || printUrl === '') return;

    var oldIframe = document.getElementById('silentPrintIframe');
    if (oldIframe) {
        oldIframe.parentNode.removeChild(oldIframe);
    }

    var iframe = document.createElement('iframe');
    iframe.id = 'silentPrintIframe';
    iframe.style.position = 'fixed';
    iframe.style.right = '0';
    iframe.style.bottom = '0';
    iframe.style.width = '0';
    iframe.style.height = '0';
    iframe.style.border = '0';
    iframe.style.opacity = '0';
    iframe.src = printUrl;

    document.body.appendChild(iframe);
});
```

### 4. Interactive 3-State Matrix Toggle via AJAX
Pengguna dapat mengubah status sel tabel secara langsung:

```javascript
// public/js/checksheet/dandory-quality.js
$(document).on('click', '.dandory-cell', function () {
    var cell = $(this);
    var id = cell.data('id');
    var phase = cell.data('phase');
    var order = cell.data('order');
    var currentStatus = cell.data('status');

    // Siklus status: OK -> NG -> NA -> Kosong
    var nextStatus = '';
    if (!currentStatus) nextStatus = 'OK';
    else if (currentStatus === 'OK') nextStatus = 'NG';
    else if (currentStatus === 'NG') nextStatus = 'NA';
    else if (currentStatus === 'NA') nextStatus = '';

    $.ajax({
        url: config.akhirBaseUrl + '/' + id + '/toggle-cell',
        type: 'POST',
        data: { phase: phase, order_no: order, status: nextStatus },
        success: function (res) {
            if (res.success) {
                // Update tampilan sel seketika tanpa reload
                updateCellDisplay(cell, nextStatus);
            }
        }
    });
});
```

### 5. Validasi Form Mulai Dandory (SweetAlert Auto-Focus)
Untuk memastikan data tidak kosong saat disubmit tanpa menggunakan tooltip browser native:

```javascript
// public/js/checksheet/dandory-quality.js
$('#formAwalDandory').on('submit', function (e) {
    var tableVal = $('#awalTable').val();
    var partVal = $('#awalItemId').val() || $('#awalPartName').val();

    if (!tableVal) {
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'No. Meja Belum Dipilih',
            text: 'Silakan pilih No. Meja aktual terlebih dahulu sebelum menyimpan data Dandory.',
            confirmButtonColor: '#4e73df'
        }).then(function () {
            setTimeout(function () { $('#awalTable').focus(); }, 200);
        });
        return false;
    }

    if (!partVal) {
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Part Belum Dipilih',
            text: 'Silakan pilih Part yang akan diperiksa terlebih dahulu.',
            confirmButtonColor: '#4e73df'
        }).then(function () {
            setTimeout(function () { $('#awalPartSearch').focus(); }, 200);
        });
        return false;
    }
});
```

---

## Alur Hak Akses & Role Pengguna (RBAC)

1. **Inspector / Operator**:
   - Hanya dapat melihat data pemeriksaannya sendiri (`operator_id = Auth::id()`).
   - Dapat menambah data Awal Dandory (`+ Mulai Dandory`) dan menyelesaikan data Akhir Dandory (`Tutup Akhir`).
   - Dapat melakukan toggle sel matriks miliknya.
   - Tidak memiliki hak untuk Approve atau Reject data.
2. **Leader (Kashift / Karu)**:
   - Dapat melihat seluruh operator di plant terkait.
   - Dapat memfilter berdasarkan Operator, Tanggal, dan Shift.
   - Memiliki akses tombol **Approve (✓)** dan **Reject (✕)** jika baris checksheet sudah terisi lengkap 100%.
   - Dapat melakukan **Bulk Approve** pada baris yang dipilih.
3. **Supervisor / Manager**:
   - Memiliki semua hak akses Leader.
   - Dapat mengakses menu konfigurasi posisi/bagian operator & daftar meja.
4. **Admin**:
   - Memiliki semua hak akses sistem, termasuk tombol khusus **Master Item** untuk memodifikasi teks standar ISO pada tabel `dandory_items`.

---

## Standar Output Dokumen Cetak (ISO Layout)

Tampilan cetak pada [`print.blade.php`](file:///d:/laragon/www/qc-project/resources/views/dandory/print.blade.php) dirancang presisi sesuai formulir asli Excel:
- **Ukuran Kertas**: A4 Landscape (`margin: 8mm 8mm 6mm 8mm`).
- **Header Standar ISO**:
  - Logo Perusahaan & Judul: `CHECK SHEET DANDORY QUALITY`.
  - No. Dokumen: `QC-KRW-F-0094` (Karawang) / `QC-JKT-F-0094` (Jakarta).
  - Kolom Tanda Tangan: `Dibuat (Inspector)`, `Diperiksa (Leader)`, `Disetujui (SPV)`.
- **Tabel Transaksi Utama**: Memuat Nomor, Tanggal & Waktu Input, Shift, Meja, Nama Part, 9 kolom Awal Dandory, 10 kolom Akhir Dandory, dan Tanda Tangan / Nama Leader.
- **Tabel Referensi Standar Bawah**: Menampilkan rincian standar parameter 1–9 Awal dan 1–10 Akhir di bagian bawah halaman cetak sebagai acuan keterbacaan dokumen.
- **Footer**: Timestamp cetak otomatis (`Dicetak: dd/mm/yyyy hh:ii`).

---

## Checklist Developer & Perawatan Kode

```bash
# 1. Bersihkan compiled view dan cache aplikasi
php artisan view:clear
php artisan config:clear
php artisan cache:clear

# 2. Validasi sintaks PHP file terkait Dandory
php -l app/Http/Controllers/DandoryChecksheetController.php
php -l app/Services/DandoryChecksheetService.php
php -l app/Models/DandoryChecksheet.php
php -l app/Models/DandoryItem.php
php -l resources/views/dandory/index.blade.php
php -l resources/views/dandory/print.blade.php

# 3. Validasi sintaks JavaScript
node -c public/js/checksheet/dandory-quality.js

# 4. Jalankan seeder parameter standar ISO jika database baru
php artisan db:seed --class=DandoryQualitySeeder
```

---
*Dokumentasi ini dibuat untuk standardisasi arsitektur QC System — Modul Checksheet Dandory Quality, Oktober 2026.*

