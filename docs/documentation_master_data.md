# Documentation: Modul Master Data (Items & Part Management)

Halo! Ini panduan catatan komprehensif untuk modul **Master Data (Items / Parts)**. Dokumentasi ini dibuat agar rekan-rekan developer dapat memahami arsitektur, struktur kode, alur integrasi ke modul checksheet produksi, skema database, dan optimasi performa yang diterapkan pada master part ini.

---

## Ringkasan Singkat (TL;DR)

Modul **Master Data Item** adalah jantung dari seluruh sistem Quality Control. Semua modul pemeriksaan produksi—seperti **In-Process Checksheet**, **First Piece Approval (FPA)**, **Durability Test**, **Incoming Inspection**, hingga **Sortir**—bergantung langsung pada konfigurasi data part yang ada di modul ini.

Fitur-fitur utama di modul ini:
- **Spesifikasi Dimensi Part & Toleransi**: Konfigurasi toleransi ukuran per point (`size`, `min`, `max`, `tolerance`).
- **Integrasi Master Alat Ukur Dinamis**: Pilihan alat ukur terstandarisasi (*Caliper, Thickness Gauge, Feeler Gauge, Push-Pull, dll.*) per point dimensi.
- **Smart Cascading Downwards Dropdown**: Saat alat ukur diubah pada suatu point (misal P3 diubah ke Timbangan), seluruh point di bawahnya (P3–P5) otomatis mengikuti pilihan tersebut, dan baris baru otomatis mewarisi alat ukur terakhir.
- **Standar Berat Part (AHM Requirement)**: Konfigurasi berat standar (*weight standard*), toleransi min/max per part/cavity.
- **Katalog Cacat (Defect List Mapping)**: Pemetaan jenis-jenis cacat (*defects*) khusus yang relevan untuk part bersangkutan agar mempermudah operator saat inspeksi.
- **Drawing Teknis & Similar Part**: Penyimpanan dokumen gambar teknik (PDF multi-page) dan referensi *similar part* untuk mitigasi kesalahan produksi.
- **Multi-Plant & Category Support**: Pengelompokan part berdasarkan Plant (Karawang, Jakarta, Sunter) dan Kategori Modul (`INPROSES`, `INCOMING`, `PLATING`, `PAINTING`, `SUB_ASSY`).
- **Import & Export XLSX Massal**: Upload data part secara massal dengan validasi baris otomatis dan template standar.

---

## File-File yang Dipakai (Struktur Kode)

Berikut adalah daftar berkas utama tempat logika Master Data berada:

- **Model**: 
  - `app/Models/Item.php` (Model utama, JSON casting, traits UUID & Plant Filter)
  - `app/Models/Category.php` (Kategori part / checksheet)
  - `app/Models/GeneralSetting.php` (Sumber daftar master alat ukur `getMeasuringTools()`)
- **Controller**: `app/Http/Controllers/ItemController.php`
- **Service Layer**: `app/Services/ItemService.php` (Pengolahan array dimensi, berat, upload file PDF/gambar, import/export)
- **Form Request (Validasi)**:
  - `app/Http/Requests/StoreItemRequest.php` (Validasi pembuatan part baru)
  - `app/Http/Requests/UpdateItemRequest.php` (Validasi pembaruan part)
- **Blade Views (Tampilan)**:
  - `resources/views/items/index.blade.php` (Tabel DataTables, Action Bar, Modal Tambah & Edit Item, Modal Import)
- **JavaScript**:
  - `public/js/items/items-form-logic.js` (Logika baris dimensi dinamis, cascading alat ukur, preview drawing PDF via PDF.js, AJAX form submission)
  - `public/js/vendor/item-search.js` (Komponen reusable pencarian part dropdown di form checksheet)
- **CSS**: `public/css/custom-responsive.css`

---

## Database & Kolom Tabel (`items`)

Skema tabel `items` di database:

| Nama Kolom | Tipe Data | Nullable? | Penjelasan Singkat |
| :--- | :--- | :---: | :--- |
| `id` | `char(36)` | No | UUID Primary Key |
| `plant_id` | `char(36)` | Yes | ID Plant kepemilikan part (FK ke `plants`) |
| `name` | `varchar(255)` | No | Nama Part / Barang |
| `part_number` | `varchar(255)` | No | Nomor Part unik (misal: `53205-K0V-N000`) |
| `sap_code` | `varchar(255)` | Yes | Kode SAP / Material ID ERP (diizinkan multi-item jika kosong/`-`) |
| `customer` | `varchar(255)` | Yes | Nama Customer (AHM, ADM, YIMM, dll.) |
| `category_id` | `bigint (unsigned)` | No | ID Kategori (FK ke `categories`) |
| `cavity` | `int` | Yes | Jumlah cavity default cetakan cetak |
| `standard_cycle_time` | `int` | Yes | Standar siklus waktu produksi (detik) |
| `dimension_standards` | `json` / `text` | Yes | Array JSON standar dimensi, batas toleransi, & alat ukur |
| `weight_standard` | `varchar(255)` | Yes | Nilai standar berat part (gram) |
| `defects` | `json` / `text` | Yes | Array JSON jenis defect yang berlaku untuk part ini |
| `file_path` | `varchar(255)` | Yes | Path file drawing PDF utama di storage |
| `file_paths` | `json` / `text` | Yes | Array JSON multi-file attachment / drawing tambahan |
| `similar_part_file_path` | `varchar(255)` | Yes | Path file gambar perbandingan part serupa |
| `created_at` / `updated_at` | `timestamp` | Yes | Audit trail pembuatan & perubahan |

### Struktur JSON Kolom `dimension_standards`
Kolom ini menyimpan array objek spesifikasi tiap point pengukuran:
```json
[
  {
    "point": "1",
    "size": "12.50",
    "tolerance": "0.10",
    "min": "12.40",
    "max": "12.60",
    "alat_ukur": "CALIPER"
  },
  {
    "point": "2",
    "size": "5.00",
    "tolerance": "+0.05/-0.02",
    "min": "4.98",
    "max": "5.05",
    "alat_ukur": "THICKNESS GAUGE"
  }
]
```

---

## Contoh Kode & Logika Kunci

### 1. Cascading Downwards Dropdown Alat Ukur (`items-form-logic.js`)
Ketika user memilih alat ukur pada baris tertentu, seluruh baris di bawahnya otomatis berubah mengikuti alat ukur tersebut. Baris baru yang ditambahkan juga otomatis mewarisi alat ukur dari baris terakhir:

```javascript
// public/js/items/items-form-logic.js

// 1. Cascading downwards saat dropdown diubah
$(document).on('change', 'select[name="dimension_alat_ukur[]"]', function () {
    const chosenTool = $(this).val();
    const $row = $(this).closest('tr');
    // Cari seluruh baris di bawahnya dan samakan nilainya
    $row.nextAll('tr').find('select[name="dimension_alat_ukur[]"]').val(chosenTool);
});

// 2. Pewarisan alat ukur pada penambahan baris baru
$('.add-dimension-row').on('click', function () {
    const lastTool = $('#modal-dimension-table tbody tr:last-child select[name="dimension_alat_ukur[]"]').val() || '';
    $('#modal-dimension-table tbody').append(dimensionRowTemplate(lastTool));
    renumberDimensionRows('#modal-dimension-table tbody');
});
```

### 2. Pemrosesan Data Dimensi di Backend (`ItemService.php`)
Data dimensi dari form dikonversi menjadi JSON array terstruktur yang bersih:

```php
// app/Services/ItemService.php
private function processDimensionStandards(array $data): ?array
{
    if (empty($data['dimension_points'])) {
        return null;
    }

    $dimensionStandards = [];
    foreach ($data['dimension_points'] as $key => $point) {
        if (!empty($point)) {
            $alatUkur = !empty($data['dimension_alat_ukur'][$key]) 
                ? trim((string)$data['dimension_alat_ukur'][$key]) 
                : null;

            $dimensionStandards[] = [
                'point' => $point,
                'size' => $data['dimension_sizes'][$key] ?? null,
                'tolerance' => $data['dimension_tolerances'][$key] ?? null,
                'min' => $data['dimension_mins'][$key] ?? null,
                'max' => $data['dimension_maxs'][$key] ?? null,
                'alat_ukur' => $alatUkur,
            ];
        }
    }

    return !empty($dimensionStandards) ? $dimensionStandards : null;
}
```

### 3. Integrasi Dropdown Alat Ukur Dinamis dari General Setting
Pilihan opsi dropdown alat ukur tidak di-*hardcode*, melainkan disuplai secara dinamis dari tabel `general_settings` dengan fallback aman:

```blade
<!-- resources/views/items/index.blade.php -->
<select name="dimension_alat_ukur[]" class="form-control form-control-sm border-0 shadow-sm">
    <option value="">-- Pilih --</option>
    @foreach(($measuringTools ?? \App\Models\GeneralSetting::getMeasuringTools()) as $tool)
        <option value="{{ $tool }}">{{ $tool }}</option>
    @endforeach
</select>
```

---

## ⚡ Integrasi Otomatis ke Modul Checksheet (In-Process & FPA)

Master Data Item terhubung langsung dengan modul transaksi produksi melalui mekanisme berikut:

1. **Konsolidasi Standar Dimensi (`getConsolidatedStandards`)**:
   `InProcessChecksheetService` dan `FirstPieceApprovalService` mengambil standar dimensi dari master item dan menyediakannya ke frontend form dalam format JSON siap pakai.
2. **Auto-Fill Saat Part Dipilih**:
   Ketika operator memilih Part Number di form create checksheet, JavaScript secara otomatis:
   - Menghitung jumlah Point dan Cavity.
   - Mengisi nama alat ukur pada header tabel dimensi (`POINT 1 [CALIPER]`, `POINT 2 [THICKNESS GAUGE]`).
   - Menyimpan mapping alat ukur ke hidden input `alat_ukur[1]`, `alat_ukur[2]`, dst.
3. **Dynamic Fallback untuk Data Lama (Zero Database Query)**:
   Data checksheet lama yang dibuat sebelum fitur alat ukur ada akan secara otomatis menampilkan alat ukur di halaman index berkat accessor model:
   ```php
   // app/Models/InProcessChecksheet.php & FirstPieceApproval.php
   public function getAlatUkurArrayAttribute()
   {
       if (!empty($this->alat_ukur)) {
           return is_array($this->alat_ukur) ? $this->alat_ukur : json_decode($this->alat_ukur, true);
       }

       // Fallback otomatis mengambil dari master item tanpa query tambahan
       if ($this->relationLoaded('item') && $this->item && !empty($this->item->dimension_standards)) {
           // Ekstrak alat ukur dari master item...
       }
       return [];
   }
   ```

---

## 🛡️ Aturan Validasi & Integritas Data

1. **SAP Code Unik Kondisional**:
   SAP Code boleh kosong atau berisi `-` untuk banyak part, tetapi jika memiliki kode SAP riil, sistem memastikan kode tersebut tidak duplikat di plant yang sama.
2. **Drawing File Sanitization**:
   File gambar teknik wajib berupa PDF (`mimes:pdf|max:10240`) dan disimpan di direktori privat/storage publik yang aman (`storage/app/public/drawings`).
3. **Pemberian Hak Akses (RBAC)**:
   Menu Master Item hanya dapat diubah (Create, Edit, Delete, Import) oleh role yang memiliki permission `manage_items` atau role `admin` / `supervisor_qc`.

---

## Checklist Perintah Perawatan (Developer Cheat Sheet)

```bash
# 1. Bersihkan cache view dan config setelah update Blade/JS
php artisan view:clear
php artisan config:clear

# 2. Cek sintaks file PHP terkait Master Item
php -l app/Models/Item.php
php -l app/Services/ItemService.php
php -l app/Http/Controllers/ItemController.php

# 3. Cek sintaks JavaScript
node -c public/js/items/items-form-logic.js
```

---
*Dokumentasi ini dibuat untuk standardisasi arsitektur QC System — diperbarui 16 Oktober 2026.*
