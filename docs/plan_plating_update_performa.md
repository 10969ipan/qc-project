# 📝 Rencana Optimasi Performa Modul Checksheet Plating (Sub-Assy & QR Code Scan Pattern)

**Lokasi File Dokumen**: `docs/plan_plating_update_performa.md`  
**Tanggal**: 22 Agustus 2026  
**Target Modul**: Checksheet Plating (`plating`) & `resources/views/plating/create.blade.php`

---

## 1. Pendahuluan & Analisis Fitur

Dokumen ini berisi detail rencana pengodean (*Detailed Code Implementation Plan*) untuk mengoptimasi kinerja backend, kueri database, serta transaksi **Simpan Data via Scan QR Code** pada modul **Checksheet Plating**.

### 🔍 Temuan Analisis:
1. **Simpan Data via Scan QR Code (`plating/create.blade.php`)**:
   * Pengecekan duplikasi QR Code / Unique Code ID saat operator menembakkan scanner QR dilindungi oleh B-Tree Composite Index `plating_checksheets_unique_sap_idx` (`unique_code_id`, `sap_code`), sehingga eksekusi validasi berjalan **< 1 ms**.
2. **Kueri Subquery Scan pada Dropdown Filter (`PlatingChecksheetController.php`)**:
   * Halaman index masih mengeksekusi 3 subquery scan berulang ke tabel `plating_checksheets` pada setiap load/paginasi.
3. **Kueri Pencarian `qr_raw` di Service Layer**:
   * Kueri pencarian `qr_raw` menggunakan `LIKE '%...%'` yang dapat ditingkatkan kecepatannya dengan mendahulukan pencarian *Exact Match*.

---

## 2. Detail Perubahan Kode (*Proposed Code Changes*)

### 2.1. Controller Layer: `app/Http/Controllers/PlatingChecksheetController.php`

#### ❌ Kode Lama (Sebelum Optimasi):
```php
// Subquery scan pada setiap index page load / pagination
$items = Item::whereIn('id', function($query) use ($plantId) {
    $query->select('item_id')->from('plating_checksheets')->where('plant_id', $plantId);
})->orderBy('name')->get();

$customers = Item::whereIn('id', function($query) use ($plantId) {
    $query->select('item_id')->from('plating_checksheets')->where('plant_id', $plantId);
})->whereNotNull('customer')->distinct()->pluck('customer')->sort();

$initials = PlatingChecksheet::where('plant_id', $plantId)
    ->whereNotNull('operator_initials')
    ->distinct()
    ->pluck('operator_initials')
    ->sort();
```

#### ✅ Kode Baru (Disamakan dengan Pola Sub-Assy - Isolated & Cached per Plant):
```php
// Data for filters (Cached per plant to avoid subquery scans on every page load)
$plantId = \App\Models\Plant::resolveId('karawang');

$items = \Illuminate\Support\Facades\Cache::remember("plating_filter_items_{$plantId}", 1800, function () use ($plantId) {
    return Item::where('plant_id', $plantId)->orderBy('name')->get();
});

$customers = \Illuminate\Support\Facades\Cache::remember("plating_filter_cust_{$plantId}", 1800, function () use ($plantId) {
    return Item::where('plant_id', $plantId)
        ->whereNotNull('customer')
        ->where('customer', '!=', '')
        ->distinct()
        ->pluck('customer')
        ->sort();
});

$initials = \Illuminate\Support\Facades\Cache::remember("plating_filter_init_{$plantId}", 1800, function () use ($plantId) {
    return PlatingChecksheet::where('plant_id', $plantId)
        ->where('date', '>=', now()->subDays(90))
        ->whereNotNull('operator_initials')
        ->where('operator_initials', '!=', '')
        ->distinct()
        ->pluck('operator_initials')
        ->sort();
});
```

---

### 2.2. Service Layer: `app/Services/PlatingChecksheetService.php`

#### A. Kueri Pencarian Scan QR Code (`qr_raw`)

```php
// QR Raw filter (Prioritize B-Tree index lookup for exact QR/Unique/SAP code scans)
if (!empty($filters['qr_raw'])) {
    $qr = trim($filters['qr_raw']);
    $query->where(function ($q) use ($qr) {
        $q->where('plating_checksheets.qrcode', $qr)
          ->orWhere('plating_checksheets.unique_code_id', $qr)
          ->orWhere('plating_checksheets.sap_code', $qr)
          ->orWhere('plating_checksheets.qrcode', 'like', "%{$qr}%");
    });
}
```

#### B. Invalidation Cache Otomatis saat Simpan Data QR / Checksheet Baru (`createChecksheet`)

```php
DB::commit();

// Clear filter cache so dropdown options refresh immediately
\Illuminate\Support\Facades\Cache::forget("plating_filter_init_{$checksheet->plant_id}");
```

---

## 3. Rencana Verifikasi & Pengujian (*Verification Plan*)

1. **Uji Kecepatan Simpan Data Scan QR**:
   Memastikan simpan data via scan QR Code di `plating/create.blade.php` berjalan instan **~20 ms – 35 ms**.
2. **Uji Kecepatan Respon Index Page**:
   Mengukur waktu eksekusi `PlatingChecksheetController::index()` via CLI/DevTools (target **< 40 ms**).
3. **Uji Akurasi Validasi & Filter**:
   Memastikan seluruh filter dan validasi simpan/edit/hapus berjalan 100% akurat.
4. **Uji Auto-Reset Cache**:
   Memastikan saat checksheet Plating baru diinput, pilihan di dropdown filter langsung ter-update secara otomatis.
