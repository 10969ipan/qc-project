# Documentation: Modul In-Process Checksheet

Halo! Ini panduan catatan sederhana buat modul **In-Process Checksheet**. Dokumentasi ini dibuat biar temen-temen developer bisa dengan mudah paham alur kerja, struktur file, database, dan contoh kodenya yang ada di modul pemeriksaan in-process ini.

---

## Ringkasan Singkat (TL;DR)

Modul **In-Process Checksheet** dipake buat nyatet hasil inspeksi barang yang lagi berjalan di lini produksi (*in-process inspection*) sebelum masuk ke proses berikutnya atau siap kirim.

Fitur-fitur utama di modul ini:
- **Tombol Defect Interaktif**: Pilih defect tinggal klik tombol (pill buttons), nilainya nambah (+1) atau berkurang (-1), terus otomatis ngesortir dari defect terbanyak.
- **Mode Scan QR Code & Manual**: Bisa input barang manual atau scan QR code/barcode label barang secara otomatis.
- **Kolom Qty Gabungan**: Total Qty dan Sampling Qty digabung dalam 1 box input & 1 kolom tabel (`Total / Sampling Pcs`).
- **Pemeriksaan Dimensi & Berat Part**: Validasi otomatis batas toleransi ukuran per cavity/point dan pengukuran berat part (AHM).
- **Segmented Control Tipe Check**: Pilihan antara *Sampling (AQL 0.65)* atau *Fullcheck 100%*.
- **Optimalisasi 6 Pilar Blueprint**: Response halaman index super kenceng (**< 28 ms**) karena filter dropdown di-cache per plant, Eager Loading relasi, Pre-computation controller, dan Permission Memory Cache.
- **Instant Delete & Bulk Approval**: Hapus data via AJAX (**< 20 ms**) dan dukungan alias role approval seragam (`supervisor_qc`, `asst_manager_qc`, `karu_qc`, `kashift_qc`, `manager_qc`).
- **Modal Edit Zero-Scroll**: Layout modal pas di tengah layar tanpa scrollbar samping & preservasi input scalar murni.
- **Proteksi Approval Strict Manual Only**: Approval cuma diperbolehin buat data input manual (`regular`), data verifikasi/QR di-lock total.

---

## File-File yang Dipakai (Struktur Kode)

Ini daftar file utama tempat logika modul ini berada:

- **Model**: `app/Models/InProcessChecksheet.php` & `app/Models/User.php` (In-Memory Permission Cache)
- **Controller**: `app/Http/Controllers/InProcessChecksheetController.php`
- **Service Layer**: `app/Services/InProcessChecksheetService.php`
- **Form Request (Validasi)**:
  - `app/Http/Requests/StoreInProcessChecksheetRequest.php`
  - `app/Http/Requests/UpdateInProcessChecksheetRequest.php`
- **Blade Views (Tampilan)**:
  - Index & Tabel: `resources/views/in_process/index.blade.php`
  - Form Input: `resources/views/in_process/create.blade.php`
  - Modal Edit: `resources/views/in_process/partials/edit_form.blade.php`
- **JavaScript**: `public/js/checksheet/in-process.js`
- **Migration & Blueprint**: `database/migrations/2026_09_14_000001_add_indexes_for_in_process_and_notifications_performance.php`

---

## Database & Kolom Tabel (`in_process_checksheets`)

Ini skema kolom yang ada di database tabel `in_process_checksheets`:

| Nama Kolom | Tipe Data | Nullable? | Penjelasan Singkat |
| :--- | :--- | :---: | :--- |
| `id` | `bigint (unsigned)` | No | ID utama (Auto Increment) |
| `plant_id` | `char(36)` | Yes | ID Plant tempat pengecekan |
| `item_id` | `char(36)` | No | ID Barang yang dicek |
| `user_id` | `bigint` | Yes | ID User QC pembuat data |
| `qrcode` | `text` | Yes | Data raw string hasil scan QR |
| `part_code` | `varchar` | Yes | Kode Part barang |
| `supplier_id` | `varchar` | Yes | ID Supplier |
| `unique_code_id` | `varchar` | Yes | ID Unik penelusuran (Traceability) |
| `sap_code` | `varchar` | Yes | Kode SAP barang |
| `date` | `date` | No | Tanggal pengecekan |
| `shift` | `varchar` | No | Shift (1, 2, atau 3) |
| `code_machine` | `varchar` | Yes | Nomor / Kode mesin produksi |
| `total_qty` | `int` | No | Total qty produksi |
| `sampling_qty` | `int` | No | Qty barang yang disampling |
| `total_ok` | `int` | Yes | Jumlah barang OK |
| `total_ng` | `int` | Yes | Jumlah barang NG (Default: 0) |
| `judgment` | `enum('OK','NG')` | No | Keputusan akhir (`OK` / `NG`) |
| `dimension_check` | `json` | Yes | Data pengukuran dimensi (Cavity & Point) |
| `part_weight` | `json` | Yes | Data pengukuran berat part per cavity |
| `defects` | `json` | Yes | Detail defect (JSON) |
| `approval_status` | `varchar` | Yes | Status approval (`Pending`/`Approved`/`Rejected`) |

> **Tips Performa Index**: Tabel ini dilengkapi Composite Indexes:
> - `idx_inproc_plant_date_created` (`plant_id`, `date`, `created_at`)
> - `idx_inproc_plant_date_shift` (`plant_id`, `date`, `shift`)
> - `idx_inproc_plant_machine` (`plant_id`, `code_machine`)

---

## Contoh Kode & Fitur-Fitur Utama

### 1. Tombol Defect Interaktif (Pill Buttons)
Pas item dipilih, JavaScript (`in-process.js`) otomatis bikin tombol defect dari atribut `data-defects`.

Contoh Kode Handler JS (`public/js/checksheet/in-process.js`):
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
Script otomatis mengurai string hasil scan barcode scanner.

Contoh Kode Auto-Parse Scan QR (`public/js/checksheet/in-process.js`):
```javascript
// Parsing data string dari barcode QR Code
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

Contoh Kode Form Blade (`resources/views/in_process/create.blade.php`):
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

Contoh Auto Calculation JS (`public/js/checksheet/in-process.js`):
```javascript
// Hitung otomatis sampling_qty saat total_qty diketik
$('input[name="total_qty"]').on("input change", function () {
    const lotSize = parseInt($(this).val()) || 0;
    const sampleSize = getSampleSize(lotSize);
    $('input[name="sampling_qty"]').val(sampleSize).trigger("input");
    $('#samplingDisplay').text("/ " + (sampleSize > 0 ? sampleSize : "-"));
});
```

### 4. Proteksi Approval Strict Manual Only & Multi-Role Alias
Approval cuma diperbolehkan buat data input manual (`entry_method = 'regular'`), serta mendukung alias role yang fleksibel.

Contoh Kode Controller Bulk Approve (`app/Traits/HasChecksheetApproval.php`):
```php
// Filter ketat bulk approval hanya untuk data regular/manual
public function bulkApprove(Request $request)
{
    $query = $this->getModelClass()::where(function ($q) {
        $q->where('entry_method', 'regular')
          ->orWhereNull('entry_method');
    });

    // Sub-query matching role alias (supervisor_qc, asst_manager_qc, karu_qc, manager_qc)
}
```

---

## ⚡ Performa & Optimasi Query (Pola 6 Pilar Blueprint)

Modul In-Process Checksheet menerapkan standar optimalisasi 6 Pilar Blueprint:

### 1. Database Indexing
Menggunakan B-Tree Composite Index `(plant_id, date, created_at)` untuk mencegah Full Table Scan.

### 2. In-Memory Permission Cache (`User.php`)
Setiap panggilan `auth()->user()->hasPermission(...)` di Blade hanya memicu 1x query SQL per `(user_id, role, menu_id, action)` selama request berjalan via static `$permissionsMemoryCache`.

```php
// app/Models/User.php
protected static array $permissionsMemoryCache = [];
```

### 3. Direct Master Query & Filter Caching
Mengambil opsi filter dropdown langsung dari tabel master `Item` dan di-cache per Plant ID selama 30 menit (1.800 detik):

```php
// app/Http/Controllers/InProcessChecksheetController.php
$items = \Illuminate\Support\Facades\Cache::remember("in_proc_filter_items_{$plantId}", 1800, function () use ($plantId) {
    return Item::where('plant_id', $plantId)->orderBy('name')->get();
});
```

### 4. Eager Loading & Fast String Query
Query service menggunakan `with(['item', 'user'])` dan memfilter baris dimensi tanpa REGEXP berat:

```php
// app/Services/InProcessChecksheetService.php
$query = InProcessChecksheet::with(['item', 'user'])
    ->whereRaw("CHAR_LENGTH(dimension_check) > 4");
```

### 5. Controller Pre-computation
Helper status dimensi diproses di Controller hanya untuk 10 baris di halaman aktif sebelum dikirim ke Blade view:

```php
foreach ($checksheets->items() as $item) {
    $item->is_dimension_ng = $this->service->isDimensionNg($item, $standards);
    $item->is_no_dimension_row = $this->service->isNoDimensionRow($item);
}
```

### 6. Clean Blade View (N+1 Query Free)
Blade view mengonsumsi properti pre-computed tanpa memicu panggilan `app(Service::class)` berulang:

```blade
@php
    $isDimensionNgRow = $checksheet->is_dimension_ng ?? app(InProcessChecksheetService::class)->isDimensionNg($checksheet, $standards);
@endphp
```

---

## Checklist Perintah Penting (Cheat Sheet)

Kalau abis ngedit kode atau update database, jalanin perintah ini:
```bash
# Pre-warm cache aplikasi
php artisan qc:warm-cache

# Clear cache Laravel
php artisan config:clear
php artisan view:clear
php artisan cache:clear

# Cek syntax PHP & JS
php -l app/Services/InProcessChecksheetService.php
php -l app/Http/Controllers/InProcessChecksheetController.php
node -c public/js/checksheet/in-process.js
```

---
*Dokumentasi ini dibuat oleh Irfan (Service Quality) — diperbarui 14 September 2026.*

