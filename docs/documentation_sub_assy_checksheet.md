# Documentation: Modul Sub Assy Checksheet

Halo! Ini dokumentasi sederhana buat modul **Sub Assy Checksheet**. Dokumen ini dibuat supaya siapa pun (termasuk temen-temen dev lain) bisa paham gimana modul ini dibuat, struktur file-filenya, database-nya, sampai contoh kodenya yang ada di dalamnya.

---

## Ringkasan Singkat (TL;DR)

Modul **Sub Assy Checksheet** dipake buat nyatet dan ngelola data pemeriksaan barang hasil perakitan (*sub-assembly*) sebelum masuk ke lini produksi selanjutnya. 

Fitur-fitur utama yang ada di modul ini:
- **Tombol Defect Interaktif**: Pilih defect tinggal klik tombol (pill buttons), nilainya nambah (+1) atau berkurang (-1), terus otomatis ngesortir dari defect terbanyak.
- **Traceability Lot ID (Injection)**: Wajib ngisi Inisial Lot ID, plus data tanggal, shift, dan nomor meja.
- **Segmented Control Tipe Check**: Pilihan antara *Sampling (AQL 0.65)* atau *Fullcheck 100%*.
- **Kolom Qty Gabungan**: Total Qty dan Sampling Qty digabung dalam 1 box input & 1 kolom tabel (`Total / Sampling Pcs`).
- **Pembatasan Kolom No. Meja**: Kolom No. Meja cuma keliatan buat role **Admin**.
- **Optimasi Caching Filter per Plant**: Load halaman index super cepat (**~64 ms**) karena dropdown di-cache per plant.
- **Instant Delete AJAX**: Hapus data langsung di tabel tanpa reload halaman (**< 20 ms**).
- **Modal Edit Zero-Scroll**: Layout modal pas di tengah layar tanpa scrollbar samping & preservasi input scalar murni.
- **Proteksi Approval Strict Manual Only**: Approval cuma diperbolehin buat data input manual (`regular`), data verifikasi/QR di-lock total.

---

## File-File yang Dipakai (Struktur Kode)

Kalau mau ngulik atau ngedit modul ini, ini file-file utamanya:

- **Model**: `app/Models/SubAssyChecksheet.php`
- **Controller**: `app/Http/Controllers/SubAssyChecksheetController.php`
- **Service Layer**: `app/Services/SubAssyChecksheetService.php`
- **Form Request (Validasi)**:
  - `app/Http/Requests/StoreSubAssyChecksheetRequest.php`
  - `app/Http/Requests/UpdateSubAssyChecksheetRequest.php`
- **Blade Views (Tampilan)**:
  - Index & Tabel: `resources/views/sub_assy/index.blade.php`
  - Form Input: `resources/views/sub_assy/create.blade.php`
  - Modal Edit: `resources/views/sub_assy/partials/edit_form.blade.php`
- **JavaScript**: `public/js/checksheet/sub-assy.js`

---

## Database & Kolom Tabel (`sub_assy_checksheets`)

Ini daftar kolom yang ada di database tabel `sub_assy_checksheets`:

| Nama Kolom | Tipe Data | Nullable? | Penjelasan Singkat |
| :--- | :--- | :---: | :--- |
| `id` | `bigint (unsigned)` | No | ID utama (Auto Increment) |
| `plant_id` | `char(36)` | Yes | ID Plant tempat pengecekan |
| `item_id` | `char(36)` | No | ID Barang yang dicek |
| `date` | `date` | No | Tanggal pengecekan |
| `shift` | `varchar` | No | Shift (1, 2, atau 3) |
| `line` | `varchar` | Yes | Nomor meja pengecekan |
| `check_type` | `varchar` | Yes | Tipe check (`sampling` / `fullcheck`) |
| `total_qty` | `int` | No | Total qty barang |
| `sampling_qty` | `int` | No | Qty barang yang sampelnya dicek |
| `total_ok` | `int` | Yes | Jumlah barang OK |
| `total_ng` | `int` | Yes | Jumlah barang NG (Default: 0) |
| `judgment` | `enum('OK','NG')` | No | Keputusan akhir (`OK` / `NG`) |
| `defects` | `json` | Yes | Data detail defect (JSON) |
| `operator_initials` | `varchar` | Yes | Inisial QC (kapital) |
| `cycle_time` | `int` | Yes | Waktu pengerjaan (detik) |
| `description` | `text` | Yes | Catatan tambahan |
| `injection_date` | `date` | Yes | Tanggal lot dari injection |
| `injection_shift` | `varchar` | Yes | Shift lot dari injection |
| `injection_initials` | `varchar` | **No** | Inisial operator lot (Wajib diisi) |
| `approval_status` | `varchar` | Yes | Status approval (`Pending`/`Approved`/`Rejected`) |

> **Tips Performa Index**: Tabel ini pake composite index `idx_subassy_plant_date_shift` (`plant_id`, `date`, `shift`) biar query pencarian & filter cepat.

---

## Contoh Kode & Fitur-Fitur Utama

### 1. Tombol Defect Interaktif (Pill Buttons)
Di `create.blade.php`, waktu item dipilih, JavaScript (`sub-assy.js`) nampilin tombol defect. Tiap diklik nambah (+1), dan tombol `-` buat ngurangin (-1).

Contoh Kode State Management JS (`public/js/checksheet/sub-assy.js`):
```javascript
// Menambah count defect dan mengurutkan tombol berdasarkan terbanyak
handleDefectClick(e) {
    const $btn = $(e.currentTarget);
    const key = $btn.data('defect-key');
    const item = this.defectItems.find(d => d.key === key);
    if (item) {
        item.count++;
        this.renderDefectButtons();
        this.updateTotalNG();
    }
}
```

### 2. Form Lot ID Injection
Inisial Lot (`injection_initials`) wajib diisi untuk kelengkapan traceability.

Contoh Validasi Backend (`app/Http/Requests/StoreSubAssyChecksheetRequest.php`):
```php
public function rules(): array
{
    return [
        'item_id'            => 'required|exists:items,id',
        'line'               => 'required|string',
        'injection_date'     => 'nullable|date',
        'injection_shift'    => 'nullable|string',
        'injection_initials' => 'required|string', // Wajib diisi
        'total_qty'          => 'required|integer|min:1',
    ];
}
```

### 3. Tampilan Qty Gabungan (Total / Sampling)
`total_qty` dan `samplingDisplay` ditaruh berdampingan dalam 1 box input ramping.

Contoh Kode Form Blade (`resources/views/sub_assy/create.blade.php`):
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

Contoh Tampilan Tabel Index (`resources/views/sub_assy/index.blade.php`):
```blade
<td class="align-middle text-center">
    <span class="font-weight-bold">{{ number_format($checksheet->total_qty) }}</span>
    / <span class="text-muted">{{ number_format($checksheet->sampling_qty) }} Pcs</span>
</td>
```

### 4. Direct Print View Only
Tombol ekspor PDF diganti pake Print View Direct (`window.print()`) yang jauh lebih cepat dan rapi.

Contoh Kode JavaScript Print (`public/js/checksheet/sub-assy.js`):
```javascript
$(document).on('click', '#btnPrintReport', function () {
    window.print();
});
```

### 5. Proteksi Keamanan Approval (Strict Manual Input Only)
Semua fungsi approval ditaruh terpusat di trait `HasChecksheetApproval.php`.

Contoh Kode Guard Approval (`app/Traits/HasChecksheetApproval.php`):
```php
// Proteksi individual approve untuk mencegah approval data verifikasi/QR
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

---

## Performa & Optimasi Query

Contoh Kode Caching Dropdown Filter (`app/Http/Controllers/SubAssyChecksheetController.php`):
```php
// Caching data dropdown filter per Plant ID selama 30 menit (1800 detik)
$items = \Illuminate\Support\Facades\Cache::remember("sub_assy_filter_items_{$plantId}", 1800, function () use ($plantId) {
    return Item::where('plant_id', $plantId)->orderBy('name')->get();
});
```

Contoh Kode Instant Delete AJAX (`resources/views/sub_assy/index.blade.php`):
```javascript
// Instant Delete AJAX tanpa reload halaman
$(document).on('click', '.btn-delete-ajax', function (e) {
    e.preventDefault();
    const url = $(this).attr('href');
    const $row = $(this).closest('tr');

    Swal.fire({ title: 'Yakin hapus data ini?', icon: 'warning', showCancelButton: true }).then((result) => {
        if (result.isConfirmed) {
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

---

## Checklist Perintah Penting (Cheat Sheet)

Kalau abis ubah kode atau migrasi DB, jalanin ini di terminal:
```bash
# Clear cache Laravel
php artisan config:clear
php artisan view:clear
php artisan cache:clear

# Cek syntax kodenya
php -l app/Services/SubAssyChecksheetService.php
php -l app/Http/Controllers/SubAssyChecksheetController.php
node -c public/js/checksheet/sub-assy.js
```

---
*Dokumentasi ini dibuat oleh Irfan (Service Quality) — diperbarui 27 Agustus 2026.*
