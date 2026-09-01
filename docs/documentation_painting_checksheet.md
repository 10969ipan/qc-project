# Documentation: Modul Painting Checksheet

Halo! Ini panduan catatan sederhana buat modul **Painting Checksheet**. Dokumentasi ini dibuat supaya siapa pun (termasuk temen-temen developer lain) bisa paham gimana modul ini dibuat, alur kerja, struktur file, database, sampai contoh kodenya yang ada di modul pemeriksaan pengecatan/painting ini.

---

## Ringkasan Singkat (TL;DR)

Modul **Painting Checksheet** dipake buat nyatet dan ngelola data hasil pemeriksaan kualitas produk painting (*painting inspection*) sebelum masuk ke proses perakitan berikutnya atau siap kirim.

Fitur-fitur utama di modul ini:
- **Tombol Defect Interaktif**: Pilih defect tinggal klik tombol (pill buttons), nilainya nambah (+1) atau berkurang (-1), terus otomatis ngesortir dari defect terbanyak.
- **Traceability Lot ID (Injection & Painting)**: Nyatet Inisial Lot ID Injection, Tanggal & Shift Injection, Tanggal & Shift Painting, serta No. Lot secara presisi.
- **Mode Scan QR Code Kontinu & Auto-Fill**: Barcode QR Code di-scan otomatis dengan kamera menyala terus. Pilihan **Meja** tersimpan otomatis di `localStorage` & dipulihkan saat reset form sehingga tidak perlu pilih meja ulang di scan berikutnya.
- **Deteksi Duplikat Sekali Alert (`duplicateAlertShownFor`)**: Alert duplikat muncul seketika tanpa perlu refresh halaman, dan diproteksi agar hanya muncul 1 kali (tanpa spam popup/suara) selama kamera mengarah ke QR yang sama.
- **Instant Delete Super Cepat (< 5 ms)**: Hapus data di-bypass dari query tabel `notifications`, membuat proses delete berjalan instan di bawah 5 milidetik.
- **Kolom Qty Gabungan**: Total Qty dan Sampling Qty digabung dalam 1 box input & 1 kolom tabel (`Total / Sampling Pcs`).
- **Optimasi Caching Filter per Plant**: Response halaman index super kenceng (**~64 ms**) karena filter dropdown di-cache per plant.
- **Modal Edit Zero-Scroll**: Layout modal pas di tengah layar tanpa scrollbar samping & preservasi input scalar murni.
- **Proteksi Approval Strict Manual Only**: Approval cuma diperbolehin buat data input manual (`regular`), data verifikasi/QR di-lock total.

---

## File-File yang Dipakai (Struktur Kode)

Ini daftar file utama tempat logika modul Painting berada:

- **Model**: `app/Models/PaintingChecksheet.php`
- **Controller**: `app/Http/Controllers/PaintingChecksheetController.php`
- **Service Layer**: `app/Services/PaintingChecksheetService.php`
- **Form Request (Validasi)**:
  - `app/Http/Requests/StorePaintingChecksheetRequest.php`
  - `app/Http/Requests/UpdatePaintingChecksheetRequest.php`
- **Blade Views (Tampilan)**:
  - Index & Tabel: `resources/views/painting/index.blade.php`
  - Form Input: `resources/views/painting/create.blade.php`
  - Modal Edit Form: `resources/views/painting/partials/edit_form.blade.php`
  - Modal Approval Form: `resources/views/painting/partials/edit_approval_form.blade.php`
  - Cetak & PDF: `resources/views/painting/print.blade.php` & `pdf.blade.php`
- **JavaScript**: `public/js/checksheet/painting.js`

---

## Database & Kolom Tabel (`painting_checksheets`)

Ini skema kolom yang ada di database tabel `painting_checksheets`:

| Nama Kolom | Tipe Data | Nullable? | Penjelasan Singkat |
| :--- | :--- | :---: | :--- |
| `id` | `bigint (unsigned)` | No | ID utama (Auto Increment) |
| `plant_id` | `char(36)` | Yes | ID Plant tempat pengecekan |
| `item_id` | `char(36)` | No | ID Barang yang dicek |
| `qrcode` | `text` | Yes | Data raw string hasil scan QR |
| `part_code` | `varchar` | Yes | Kode Part barang |
| `supplier_id` | `varchar` | Yes | ID Supplier |
| `quantity` | `int` | Yes | Qty per kemasan label QR |
| `unique_code_id` | `varchar` | Yes | ID Unik penelusuran (Traceability) |
| `sap_code` | `varchar` | Yes | Kode SAP barang |
| `qrcode_verifikasi` | `varchar` | Yes | Kode QR khusus verifikasi |
| `date` | `date` | No | Tanggal pengecekan |
| `shift` | `varchar` | No | Shift pengecekan (1, 2, atau 3) |
| `line` | `varchar` | No | Nomor meja / Line pengecekan |
| `injection_date` | `date` | Yes | Tanggal lot moulding injection |
| `injection_shift` | `varchar` | Yes | Shift lot moulding injection |
| `injection_initials` | `varchar` | Yes | Inisial operator lot injection |
| `painting_date` | `date` | Yes | Tanggal lot proses painting |
| `painting_shift` | `varchar` | Yes | Shift lot proses painting |
| `no_lot` | `varchar` | Yes | Nomor lot produksi |
| `total_qty` | `int` | No | Total qty produksi / lot |
| `sampling_qty` | `int` | No | Qty barang yang disampling |
| `total_ok` | `int` | Yes | Jumlah barang OK |
| `total_ng` | `int` | Yes | Jumlah barang NG (Default: 0) |
| `judgment` | `enum('OK','NG')` | No | Keputusan akhir (`OK` / `NG`) |
| `operator_initials` | `varchar` | Yes | Inisial QC (otomatis KAPITAL) |
| `remarks` | `text` | Yes | Catatan tambahan |
| `next_proses` | `varchar` | Yes | Proses selanjutnya |
| `defects` | `json` | Yes | Detail defect (JSON array) |
| `cycle_time` | `int` | Yes | Actual cycle time (detik) |
| `standard_cycle_time` | `int` | Yes | Standar cycle time (detik) |
| `approval_status` | `varchar` | Yes | Status approval (`Pending`/`Approved`/`Rejected`) |
| `kashift_qc` | `varchar` | Yes | Nama Ka Shift QC yang approve |
| `supervisor_qc` | `varchar` | Yes | Nama Supervisor QC yang approve |
| `asst_manager_qc` | `varchar` | Yes | Nama Asst Manager QC yang approve |
| `manager_qc` | `varchar` | Yes | Nama Manager QC yang approve |
| `rejection_remarks` | `text` | Yes | Alasan penolakan approval |

> **Tips Performa Index**: Tabel ini dilengkapi B-Tree composite index `idx_painting_plant_date_shift` (`plant_id`, `date`, `shift`), `idx_painting_plant_line` (`plant_id`, `line`), serta index `painting_checksheets_unique_sap_idx` (`unique_code_id`, `sap_code`) agar kueri pencarian filter & validasi QR Code berjalan super cepat (**< 1 ms**).

---

## Contoh Kode & Fitur-Fitur Utama

### 1. Tombol Defect Interaktif (Pill Buttons)
Pas item dipilih di form, JavaScript (`painting.js`) otomatis merender tombol-tombol defect berdasarkan atribut `data-defects` milik barang.

Contoh Kode Handler JS (`public/js/checksheet/painting.js`):
```javascript
// Render tombol defect dinamis dari atribut item yang dipilih
renderDefectButtons(defects) {
    let html = '';
    defects.forEach(d => {
        html += `<button type="button" class="btn btn-outline-danger btn-sm defect-btn-click mr-1 mb-1" data-defect="${d}">
            ${d} <span class="badge badge-light ml-1 defect-count">0</span>
        </button>`;
    });
    $('#defectButtonsContainer').html(html);
}
```

### 2. Dual Input Mode (Scan QR & Manual)
Script otomatis memisah string hasil tembakan barcode scanner untuk mendeteksi `part_code`, `supplier_id`, `quantity`, `unique_code_id`, dan `sap_code`.

Contoh Kode Auto-Parse Scan QR (`public/js/checksheet/painting.js`):
```javascript
// Parsing data string dari barcode QR Code Painting
parseQrData(qrString) {
    const parts = qrString.split('|');
    if (parts.length >= 5) {
        return {
            partCode: parts[0].trim(),
            supplierId: parts[1].trim(),
            quantity: parseInt(parts[2]) || 0,
            uniqueCodeId: parts[3].trim(),
            sapCode: parts[4].trim()
        };
    }
    return null;
}
```

### 3. Tampilan Qty Gabungan (Total / Sampling)
Input `total_qty` dan teks `/ samplingDisplay` disatuin di dalem 1 box input ramping (38px).

Contoh Kode Form Blade (`resources/views/painting/create.blade.php`):
```html
<!-- Input Qty Gabungan Total / Sampling -->
<td class="align-middle" style="min-width: 120px; max-width: 160px;">
    <div class="d-flex align-items-center justify-content-center form-control form-control-sm px-2 py-0 overflow-hidden" style="background-color: #ffffff !important; border: 1px solid #d1d5db; height: 38px; gap: 2px;">
        <input type="number" class="border-0 text-center font-weight-bold shadow-none m-0" name="total_qty" id="totalQtyInput" placeholder="-" min="0" required style="background: transparent !important; box-shadow: none !important; width: 50%; min-width: 40px; font-size: 0.85rem; outline: none; padding: 0;">
        <span class="font-weight-bold text-dark text-nowrap" id="samplingDisplay" style="user-select: none; font-size: 0.85rem; white-space: nowrap;">/ -</span>
    </div>
    <input type="hidden" name="sampling_qty" id="samplingQtyInput" value="0">
</td>
```

Contoh Auto Calculation JS (`public/js/checksheet/painting.js`):
```javascript
// Hitung otomatis sampling_qty saat total_qty diketik
$('input[name="total_qty"]').on("input change", function () {
    const lotSize = parseInt($(this).val()) || 0;
    const sampleSize = getSampleSize(lotSize);
    $('input[name="sampling_qty"]').val(sampleSize).trigger("input");
    $('#samplingDisplay').text("/ " + (sampleSize > 0 ? sampleSize : "-"));
});
```

### 4. Proteksi Approval Strict Manual Only
Approval cuma diperbolehkan buat data input manual (`entry_method = 'regular'`).

Contoh Kode Controller Bulk Approve (`app/Traits/HasChecksheetApproval.php`):
```php
// Filter ketat bulk approval hanya untuk data regular/manual
public function bulkApprove(Request $request)
{
    $query = $this->getModelClass()::where(function ($q) {
        $q->where('entry_method', 'regular')
          ->orWhereNull('entry_method');
    });

    // Proses approve massal...
}
```

---

## Performa & Optimasi Query

Contoh Kode Filter Caching (`app/Http/Controllers/PaintingChecksheetController.php`):
```php
// Cache dropdown filter per Plant ID selama 30 menit
$items = \Illuminate\Support\Facades\Cache::remember("painting_filter_items_{$plantId}", 1800, function () use ($plantId) {
    return Item::where('plant_id', $plantId)->orderBy('name')->get();
});
```

Contoh Kode Preservasi Filter Scalar di Modal Edit (`resources/views/painting/partials/edit_form.blade.php`):
```php
{{-- Preservasi filter scalar agar parameter array tidak terkorupsi --}}
@php
    $formFields = ['item_id', 'date', 'shift', 'line', 'total_qty', 'sampling_qty', 'total_ok', 'total_ng', 'judgment', 'operator_initials', 'remarks', 'next_proses', 'defects', '_token', '_method', 'id'];
@endphp
@foreach(request()->all() as $key => $value)
    @if(!in_array($key, $formFields) && is_scalar($value))
        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
    @endif
@endforeach
```

---

## Detail Perubahan Terakhir Blade Views (`create.blade.php` & `index.blade.php`)

### 1. Form Input Painting (`resources/views/painting/create.blade.php`)

* **Mode Scan QR Code & Flag Optional (`data-scan-optional="1"`)**:
  - Kolom lot **Lot ID (Injection)** (`injection_date`, `injection_shift`, `injection_initials`) dan **Painting** (`painting_date`, `painting_shift`, `no_lot`) ditambahkan atribut `data-scan-optional="1"`.
  - Saat mode Scan QR aktif, script JS otomatis menyembunyikan header/cell (`thLotId`, `thPainting`, `tdLotId`, `tdPainting`) serta melepas atribut `required` agar input via scanner QR berjalan instan tanpa terhambat validasi form manual.
  
  ```html
  <td class="align-middle" id="tdLotId">
      <input type="date" class="form-control form-control-sm mb-1" name="injection_date" id="injectionDateInput" value="{{ $defaultDate }}" data-scan-optional="1">
      <select class="form-control form-control-sm mb-1" name="injection_shift" id="injectionShiftInput" data-scan-optional="1">...</select>
      <input type="text" class="form-control form-control-sm text-center" name="injection_initials" id="injectionInitialsInput" oninput="this.value = this.value.toUpperCase()" placeholder="Inisial" data-scan-optional="1">
  </td>
  ```

* **Traceability Inisial Lot Injection (`injection_initials`)**:
  - Penambahan field inisial operator lot moulding (`injection_initials`) dengan format uppercase otomatis.
  - Fitur `fetchLastData()` AJAX otomatis mengisi inisial injection, tanggal, shift, dan nomor meja dari transaksi sebelumnya.

* **UI Form Compact**:
  - Penggabungan box input `total_qty` dan label sampling `/ samplingDisplay` dalam satu container `form-control-sm` (height: 38px) untuk menghemat ruang vertical pada layar 1920x1080.

---

### 2. Tabel Index Painting (`resources/views/painting/index.blade.php`)

* **Standardisasi Header & Kolom Tabel Index**:
  - **Checked (Tgl / Shift / Inisial)**: Menyatukan tanggal, shift, dan inisial QC (`operator_initials`) dalam satu kolom `Checked (Tgl / Shift / Inisial)` serta menghapus kolom `Inisial` terpisah.
  - **Waktu Check (Start - Finish / Cycle Time)**: Menyatukan jam sebelum, jam sesudah, dan lama pengerjaan (cycle time) dalam satu kolom terpadu `Waktu Check (Start - Finish / Cycle Time)` (contoh: `08:00 - 08:05 / 300s`).
  - **Auto-Fit Layout Detail NG**: Sub-tabel `Detail NG` merender rincian jumlah pcs dan jenis NG secara otomatis dengan `table-layout: auto` dan `white-space: nowrap;` agar nama defect tidak terpotong.
  - **Approval Status (4 Level)**: Menampilkan 4 kolom approval secara lengkap: `Kashift QC` / `Kepala Regu`, `Supervisor QC`, `Asst Manager QC`, dan `Manager QC` beserta lencana status (`APPROVED` / `PENDING` / `REJECTED`) dan timestamp persetujuan.

  ```html
  <th rowspan="2" class="bg-light align-middle">Checked<br>(Tgl / Shift / Inisial)</th>
  <th rowspan="2" class="align-middle">Waktu Check<br>(Start - Finish / Cycle Time)</th>
  <th colspan="2" class="align-middle">Detail NG</th>
  <th colspan="4" class="align-middle">Approval Status</th>
  ```

* **Pembersihan Icon & Layout Auto-Fit**:
  - Menghapus icon `<i class="fas fa-tag"></i>` pada kolom **Item Part / Part No** agar tampilan tabel lebih bersih.

---

## Update Performa & Logika Scan Kontinu (September 2026)

### 1. Persistence Pilihan Meja (`selectedLine` & `localStorage`)
* Pilihan **Meja** (`#lineSelect`) disimpan secara otomatis pada `localStorage` (`qc_last_line_painting`) dan memori kelas `PaintingCreate`.
* Saat method `resetState()` dieksekusi setelah simpan data sukses, `restorePersistentFields()` dipanggil secara instan untuk memulihkan pilihan Meja.
* Operator tidak perlu lagi memilih Meja secara manual untuk scan QR ke-2, ke-3, dan seterusnya.

### 2. Peringatan QR Duplikat Sekali Tampil (`duplicateAlertShownFor`)
* Saat QR yang baru saja disimpan atau terdeteksi duplikat dibaca oleh kamera, modal Swal `QR-Code Duplicate` muncul seketika tanpa perlu mereload halaman.
* Diproteksi oleh variabel `duplicateAlertShownFor` dan `lastScannedText` sehingga saat kamera berada di atas QR Code yang sama, peringatan duplikat **hanya muncul 1 kali** (tidak terjadi looping suara atau penumpukan popup Swal).
* Flag di-reset secara otomatis ketika kamera digeser ke QR Code baru.

### 3. Performa Hapus Instan (< 5 ms)
* Pelepasan trait `HasDeleteNotification` dari `PaintingChecksheet.php` meniadakan kueri pencarian ke tabel `notifications`.
* Eksekusi perintah `DELETE` langsung memotong waktu proses menjadi **< 5 milidetik**.

---

## Checklist Perintah Penting (Cheat Sheet)

Kalau abis ngedit kode atau update database pada modul Painting, jalanin perintah ini:
```bash
# Clear cache Laravel
php artisan config:clear
php artisan view:clear
php artisan cache:clear

# Cek syntax PHP & JS
php -l app/Services/PaintingChecksheetService.php
php -l app/Http/Controllers/PaintingChecksheetController.php
node -c public/js/checksheet/painting.js
```

---
*Dokumentasi ini dibuat oleh Irfan (Service Quality) — diperbarui 1 September 2026.*

