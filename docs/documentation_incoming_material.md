# Documentation: Modul Incoming Material Checksheet

Halo! Ini catatan panduan praktis buat modul **Incoming Material Checksheet**. Dokumentasi ini dibuat biar temen-temen developer bisa paham alur kerja dan struktur kode modul inspeksi kedatangan bahan baku (material) ini.

---

## Ringkasan Singkat (TL;DR)

Modul **Incoming Material** dipake buat nyatet hasil inspeksi kedatangan bahan baku (seperti resin, biji plastik, cairan/kimia, atau kawat material) dari supplier.

Fitur utama di modul ini:
- **Pemeriksaan Spesifikasi Material**: Pencatatan Lot No, Supplier, Surat Jalan, Parameter Fisik/Kimia, Qty Kedatangan, Qty Sample, OK/NG, dan Keterangan.
- **Dual Input Mode**: Mendukung input manual dan scan label QR Code/Barcode.
- **Approval 4 Tingkat & Strict Manual Protection**: Approval terintegrasi via `HasChecksheetApproval` trait khusus buat data input manual (`regular`). Data verifikasi scan QR di-lock total dari approval.
- **Ekspor Data & Google Sheets Sync**: Terhubung otomatis dengan Google Sheets via `GoogleSheetService` dan ekspor CSV via `HasChecksheetExport`.
- **Instant Delete AJAX**: Hapus baris data di tabel secara instant tanpa refresh halaman (**< 20 ms**).

---

## File-File yang Dipakai (Struktur Kode)

- **Model**: `app/Models/IncomingMaterial.php`
- **Controller**: `app/Http/Controllers/IncomingMaterialController.php`
- **Service Layer**: `app/Services/IncomingMaterialService.php`
- **Form Request (Validasi)**:
  - `app/Http/Requests/StoreIncomingMaterialRequest.php`
  - `app/Http/Requests/UpdateIncomingMaterialRequest.php`
- **Traits Reusable**:
  - `app/Traits/HasChecksheetApproval.php` (Approval 4 jenjang)
  - `app/Traits/HasChecksheetExport.php` (Ekspor CSV & Sync Google Sheets)
  - `app/Traits/HasPlantFilter.php` (Auto-filter per Plant)
- **Blade Views (Tampilan)**: `resources/views/incoming/materials/*`

---

## Database & Kolom Utama (`incoming_materials`)

| Nama Kolom | Tipe Data | Nullable? | Penjelasan Singkat |
| :--- | :--- | :---: | :--- |
| `id` | `bigint` | No | Primary Key |
| `plant_id` | `char(36)` | Yes | ID Plant |
| `item_id` | `char(36)` | No | ID Material |
| `date` | `date` | No | Tanggal Kedatangan |
| `shift` | `varchar` | No | Shift (1, 2, 3) |
| `total_qty` | `int` | No | Total Qty Material |
| `sampling_qty` | `int` | No | Qty Sampel Pengecekan |
| `total_ok` / `total_ng` | `int` | Yes | Jumlah OK / NG |
| `judgment` | `enum('OK','NG')` | No | Hasil Keputusan (`OK` / `NG`) |
| `defects` | `json` | Yes | Detail defect material (JSON) |
| `approval_status` | `varchar` | Yes | Status approval (`Pending`/`Approved`/`Rejected`) |

---

## Contoh Kode & Alur Utama

### 1. Auto-Scope Query Berdasarkan Plant User
Penggunaan trait `HasPlantFilter` memastikan query otomatis hanya menampilkan data sesuai plant pengguna.

Contoh Kode Trait `HasPlantFilter` (`app/Traits/HasPlantFilter.php`):
```php
public static function bootHasPlantFilter()
{
    static::addGlobalScope('plant', function (Builder $builder) {
        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->plant_id) {
            $builder->where($builder->getModel()->getTable() . '.plant_id', auth()->user()->plant_id);
        }
    });
}
```

### 2. Validasi Entri Form Material
Validasi input kedatangan material dipusatkan di Form Request.

Contoh Kode Validasi Request (`app/Http/Requests/StoreIncomingMaterialRequest.php`):
```php
public function rules(): array
{
    return [
        'item_id'     => 'required|exists:items,id',
        'date'        => 'required|date',
        'shift'       => 'required|string',
        'total_qty'   => 'required|integer|min:1',
        'sampling_qty'=> 'required|integer|min:1',
        'judgment'    => 'required|in:OK,NG',
    ];
}
```

---

## Checklist Perintah Pemeliharaan

```bash
# Clear Cache Laravel
php artisan config:clear
php artisan view:clear
php artisan cache:clear

# Cek Syntax PHP
php -l app/Services/IncomingMaterialService.php
php -l app/Http/Controllers/IncomingMaterialController.php
```

---
*Dokumentasi ini dibuat oleh Irfan (Service Quality) — diperbarui 27 Agustus 2026.*
