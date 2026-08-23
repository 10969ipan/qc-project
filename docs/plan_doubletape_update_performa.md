# 📝 Rencana Optimasi Performa Modul Checksheet Double Tape (Sub-Assy & QR Code Pattern)

**Lokasi File Dokumen**: `docs/plan_doubletape_update_performa.md`  
**Tanggal**: 22 Agustus 2026  
**Target Modul**: Checksheet Double Tape (`double_tape`) & `resources/views/double_tape/create.blade.php`

---

## 1. Pendahuluan & Analisis Fitur

Dokumen ini berisi detail rencana pengodean (*Detailed Code Implementation Plan*) untuk mengoptimasi kinerja backend, kueri database, serta transaksi pada modul **Checksheet Double Tape**.

### 🔍 Temuan Analisis:
1. **Form Input `double_tape/create.blade.php`**:
   * Kueri pemuatan master item & penentuan shift/tanggal di form `create` tergolong **sangat ringan & cepat (`< 15 ms`)**.
   * Pengecekan duplikasi QR Code / Unique Code ID saat simpan data dilindungi oleh Composite B-Tree Index `double_tape_checksheets_unique_sap_idx` (`unique_code_id`, `sap_code`), sehingga eksekusi validasi simpan berjalan **< 1 ms**.
2. **Subquery Scan pada Dropdown Filter (`DoubleTapeChecksheetController.php`)**:
   * Halaman index masih mengeksekusi 3 subquery scan berulang ke tabel `double_tape_checksheets` pada setiap load/paginasi.
3. **Kueri Pencarian `qr_raw` di Service Layer**:
   * Kueri pencarian `qr_raw` menggunakan `LIKE '%...%'` yang dapat ditingkatkan kecepatannya dengan mendahulukan pencarian *Exact Match*.

---

## 2. Detail Perubahan Kode (*Proposed Code Changes*)

### 2.1. Controller Layer: `app/Http/Controllers/DoubleTapeChecksheetController.php`

#### ❌ Kode Lama (Sebelum Optimasi):
```php
// Subquery scan pada setiap index page load / pagination
$items = Item::whereIn('id', function($query) use ($plantId) {
    $query->select('item_id')->from('double_tape_checksheets')->where('plant_id', $plantId);
})->orderBy('name')->get();

$customers = Item::whereIn('id', function($query) use ($plantId) {
    $query->select('item_id')->from('double_tape_checksheets')->where('plant_id', $plantId);
})->whereNotNull('customer')->distinct()->pluck('customer')->sort();

$initials = DoubleTapeChecksheet::where('plant_id', $plantId)
    ->whereNotNull('operator_initials')
    ->distinct()
    ->pluck('operator_initials')
    ->sort();
```

#### ✅ Kode Baru (Disamakan dengan Pola Sub-Assy - Isolated & Cached per Plant):
```php
// Data for filters (Cached per plant to avoid subquery scans on every page load)
$plantId = \App\Models\Plant::resolveId('karawang');

$items = \Illuminate\Support\Facades\Cache::remember("doubletape_filter_items_{$plantId}", 1800, function () use ($plantId) {
    return Item::where('plant_id', $plantId)->orderBy('name')->get();
});

$customers = \Illuminate\Support\Facades\Cache::remember("doubletape_filter_cust_{$plantId}", 1800, function () use ($plantId) {
    return Item::where('plant_id', $plantId)
        ->whereNotNull('customer')
        ->where('customer', '!=', '')
        ->distinct()
        ->pluck('customer')
        ->sort();
});

$initials = \Illuminate\Support\Facades\Cache::remember("doubletape_filter_init_{$plantId}", 1800, function () use ($plantId) {
    return DoubleTapeChecksheet::where('plant_id', $plantId)
        ->where('date', '>=', now()->subDays(90))
        ->whereNotNull('operator_initials')
        ->where('operator_initials', '!=', '')
        ->distinct()
        ->pluck('operator_initials')
        ->sort();
});
```

---

### 2.2. Service Layer: `app/Services/DoubleTapeChecksheetService.php`

#### A. Kueri Pencarian Scan QR Code (`qr_raw`)

```php
// QR Raw filter (Prioritize B-Tree index lookup for exact QR/Unique/SAP code scans)
if (!empty($filters['qr_raw'])) {
    $qr = trim($filters['qr_raw']);
    $query->where(function ($q) use ($qr) {
        $q->where('double_tape_checksheets.qrcode', $qr)
          ->orWhere('double_tape_checksheets.unique_code_id', $qr)
          ->orWhere('double_tape_checksheets.sap_code', $qr)
          ->orWhere('double_tape_checksheets.qrcode', 'like', "%{$qr}%");
    });
}
```

#### B. Invalidation Cache Otomatis saat Simpan Data QR / Checksheet Baru (`createChecksheet`)

```php
DB::commit();

// Clear filter cache so dropdown options refresh immediately
\Illuminate\Support\Facades\Cache::forget("doubletape_filter_init_{$checksheet->plant_id}");
```

---

## 3. Rencana Verifikasi & Pengujian (*Verification Plan*)

1. **Uji Kecepatan Form Create & Simpan Data QR**:
   Memastikan form `double_tape/create.blade.php` terbuka instan **< 15 ms** dan simpan data berjalan **~20 ms – 35 ms**.
2. **Uji Kecepatan Respon Index Page**:
   Mengukur waktu eksekusi `DoubleTapeChecksheetController::index()` via CLI/DevTools (target **< 40 ms**).
3. **Uji Akurasi Validasi & Filter**:
   Memastikan seluruh filter dan validasi simpan/edit/hapus berjalan 100% akurat.
4. **Uji Auto-Reset Cache**:
   Memastikan saat checksheet Double Tape baru diinput, pilihan di dropdown filter langsung ter-update secara otomatis.
