# Documentation: Modul Incoming Export Checksheet

Halo! Ini catatan panduan simpel buat modul **Incoming Export Checksheet**. Dokumen ini dibuat biar temen-temen developer bisa cepet paham gimana modul inspeksi barang kedatangan ekspor ini dibuat beserta contoh kodenya.

---

## Ringkasan Singkat (TL;DR)

Modul **Incoming Export** dipake buat pencatatan dan persetujuan (*approval*) hasil inspeksi barang kedatangan ekspor.

Fitur utama di modul ini:
- **Entri Form Checksheet Kompleks**: Pencatatan data identitas lot, supplier, invoice, customer, qty, sampling, OK/NG, dan detail defect.
- **Dual Input Mode**: Bisa entri manual atau otomatis via Scan Barcode / QR Label.
- **Approval 4 Tingkat & Strict Manual Protection**: Approval terintegrasi via `HasChecksheetApproval` trait khusus buat data input manual (`regular`). Data verifikasi scan QR di-lock total.
- **Ekspor Data & Google Sheets Sync**: Terhubung otomatis dengan Google Sheets via `GoogleSheetService` dan ekspor CSV via `HasChecksheetExport`.
- **Instant AJAX Delete**: Hapus baris data di tabel secara instant tanpa refresh halaman (**< 20 ms**).

---

## File-File yang Dipakai (Struktur Kode)

- **Model**: `app/Models/IncomingExport.php`
- **Controller**: `app/Http/Controllers/IncomingExportController.php`
- **Service Layer**: `app/Services/IncomingExportService.php`
- **Form Request (Validasi)**:
  - `app/Http/Requests/StoreIncomingExportRequest.php`
  - `app/Http/Requests/UpdateIncomingExportRequest.php`
- **Traits Reusable**:
  - `app/Traits/HasChecksheetApproval.php` (Approval 4 jenjang)
  - `app/Traits/HasChecksheetExport.php` (Ekspor CSV & Sync Google Sheets)
  - `app/Traits/HasPlantFilter.php` (Auto-filter per Plant)
- **Blade Views (Tampilan)**: `resources/views/incoming/exports/*`

---

## Database & Kolom Utama (`incoming_exports`)

| Nama Kolom | Tipe Data | Nullable? | Penjelasan Singkat |
| :--- | :--- | :---: | :--- |
| `id` | `bigint` | No | Primary Key |
| `plant_id` | `char(36)` | Yes | ID Plant |
| `item_id` | `char(36)` | No | ID Item Barang |
| `date` | `date` | No | Tanggal Inspeksi |
| `shift` | `varchar` | No | Shift (1, 2, 3) |
| `total_qty` | `int` | No | Total Qty Barang |
| `sampling_qty` | `int` | No | Qty Sampel Pengecekan |
| `total_ok` / `total_ng` | `int` | Yes | Jumlah OK / NG |
| `judgment` | `enum('OK','NG')` | No | Hasil Keputusan (`OK` / `NG`) |
| `defects` | `json` | Yes | Detail cacat/defect dalam format JSON |
| `approval_status` | `varchar` | Yes | Status approval (`Pending`/`Approved`/`Rejected`) |

---

## Contoh Kode & Alur Utama

### 1. Proteksi Approval Strict Manual Only
Cuma data ber-`entry_method = 'regular'` atau `NULL` yang boleh di-approve.

Contoh Kode Guard Approval (`app/Traits/HasChecksheetApproval.php`):
```php
public function approve(Request $request, $id)
{
    $checksheet = $this->getModelClass()::findOrFail($id);

    // Blokir jika data merupakan hasil scan verifikasi / QR Code
    if (($checksheet->entry_method && $checksheet->entry_method === 'verifikasi') || !empty($checksheet->qrcode)) {
        return response()->json(['message' => 'Data verifikasi tidak dapat di-approve.'], 403);
    }

    // Proses approval manual...
}
```

### 2. Auto Sync Google Sheets saat Simpan Data
Setiap data simpan disinkronisasi ke Google Sheets secara background.

Contoh Kode Service Sync (`app/Services/IncomingExportService.php`):
```php
if ($checksheet) {
    try {
        $this->googleSheetService->appendRow('IncomingExport', $this->mapToSheetRow($checksheet));
    } catch (\Exception $e) {
        Log::warning('Google Sheet sync failed: ' . $e->getMessage());
    }
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
php -l app/Services/IncomingExportService.php
php -l app/Http/Controllers/IncomingExportController.php
```

---
*Dokumentasi ini dibuat oleh Irfan (Service Quality) — diperbarui 27 Agustus 2026.*
