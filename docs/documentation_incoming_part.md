# Documentation: Modul Incoming Part Checksheet

Halo! Ini catatan panduan simpel buat modul **Incoming Part Checksheet**. Dokumentasi ini dibuat biar temen-temen developer bisa paham alur kerja dan struktur kode modul inspeksi kedatangan komponen/part suku cadang ini.

---

## Ringkasan Singkat (TL;DR)

Modul **Incoming Part** dipake buat pencatatan dan verifikasi inspeksi kedatangan part/komponen dari supplier luar (*sub-contractor/vendor*) sebelum masuk ke gudang atau lini produksi.

Fitur utama di modul ini:
- **Pemeriksaan Kedatangan Part**: Pencatatan Surat Jalan, Lot No, Qty Kedatangan, Qty Sample, OK/NG, dan Detail Defect Cacat.
- **Dual Input Mode**: Input manual atau scan otomatis QR Code / Barcode label part.
- **Approval 4 Tingkat & Strict Manual Protection**: Approval terintegrasi via `HasChecksheetApproval` trait khusus buat data input manual (`regular`). Data verifikasi scan QR di-lock total dari approval.
- **Ekspor Data & Google Sheets Sync**: Terhubung otomatis dengan Google Sheets via `GoogleSheetService` dan ekspor CSV via `HasChecksheetExport`.
- **Instant Delete AJAX**: Hapus baris data di tabel secara instant tanpa refresh halaman (**< 20 ms**).

---

## File-File yang Dipakai (Struktur Kode)

- **Model**: `app/Models/IncomingPart.php`
- **Controller**: `app/Http/Controllers/IncomingPartController.php`
- **Service Layer**: `app/Services/IncomingPartService.php`
- **Form Request (Validasi)**:
  - `app/Http/Requests/StoreIncomingPartRequest.php`
  - `app/Http/Requests/UpdateIncomingPartRequest.php`
- **Traits Reusable**:
  - `app/Traits/HasChecksheetApproval.php` (Approval 4 jenjang)
  - `app/Traits/HasChecksheetExport.php` (Ekspor CSV & Sync Google Sheets)
  - `app/Traits/HasPlantFilter.php` (Auto-filter per Plant)
- **Blade Views (Tampilan)**: `resources/views/incoming/parts/*`

---

## Database & Kolom Utama (`incoming_parts`)

| Nama Kolom | Tipe Data | Nullable? | Penjelasan Singkat |
| :--- | :--- | :---: | :--- |
| `id` | `bigint` | No | Primary Key |
| `plant_id` | `char(36)` | Yes | ID Plant |
| `item_id` | `char(36)` | No | ID Part Barang |
| `date` | `date` | No | Tanggal Kedatangan |
| `shift` | `varchar` | No | Shift (1, 2, 3) |
| `total_qty` | `int` | No | Total Qty Part |
| `sampling_qty` | `int` | No | Qty Sampel Pengecekan |
| `total_ok` / `total_ng` | `int` | Yes | Jumlah OK / NG |
| `judgment` | `enum('OK','NG')` | No | Hasil Keputusan (`OK` / `NG`) |
| `defects` | `json` | Yes | Detail defect part (JSON) |
| `approval_status` | `varchar` | Yes | Status approval (`Pending`/`Approved`/`Rejected`) |

---

## Contoh Kode & Alur Utama

### 1. Instant AJAX Delete tanpa Page Reload
Tombol hapus memicu AJAX DELETE dan menghapus baris tabel secara langsung.

Contoh Kode Handler Delete JS (`resources/views/incoming/parts/index.blade.php`):
```javascript
$(document).on('click', '.btn-delete-ajax', function (e) {
    e.preventDefault();
    const url = $(this).attr('href');
    const $row = $(this).closest('tr');

    Swal.fire({ title: 'Hapus data ini?', icon: 'warning', showCancelButton: true }).then((res) => {
        if (res.isConfirmed) {
            $.ajax({
                url: url,
                type: 'DELETE',
                data: { _token: $('meta[name="csrf-token"]').attr('content') },
                success: function () {
                    $row.fadeOut(300, function () { $(this).remove(); });
                }
            });
        }
    });
});
```

### 2. Guard Query Bulk Approve untuk Data Regular Only
Fungsi bulk approve memfilter query agar data scan verifikasi tidak ikut ter-approve.

Contoh Kode Trait Bulk Approve (`app/Traits/HasChecksheetApproval.php`):
```php
public function bulkApprove(Request $request)
{
    $query = $this->getModelClass()::where(function ($q) {
        $q->where('entry_method', 'regular')
          ->orWhereNull('entry_method');
    });

    // Melakukan update approval_status menjadi Approved...
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
php -l app/Services/IncomingPartService.php
php -l app/Http/Controllers/IncomingPartController.php
```

---
*Dokumentasi ini dibuat oleh Irfan (Service Quality) — diperbarui 27 Agustus 2026.*
