# 📝 Rencana Optimasi Performa & Memori Modul Checksheet Cross-Cut (Sub-Assy Pattern & Image Compressor)

**Lokasi File Dokumen**: `docs/plan_crosscut_update_performa.md`  
**Tanggal**: 22 Agustus 2026  
**Target Modul**: Checksheet Cross-Cut (`cross_cut`)

---

## 1. Pendahuluan & Tujuan Optimasi

Dokumen ini berisi detail rencana pengodean (*Detailed Code Implementation Plan*) untuk mengoptimasi kinerja backend, kueri database, serta **konsumsi RAM & disk memori server pada transaksi upload foto** modul **Checksheet Cross-Cut**, dengan mengadopsi pola pengodean Sub-Assy dan modul `ImageCompressor`.

Target optimasi:
* Menurunkan waktu respon halaman index & paginasi dari **> 100 ms / beberapa detik** menjadi **< 40 ms**.
* Mengisolasikan data filter per Plant secara presisi dan ter-cache di RAM server.
* **Menghemat konsumsi RAM & Disk server pada foto hingga 80% – 95%** (mereduksi foto 5MB-15MB HP camera menjadi ~100KB-150KB tanpa mengurangi kejernihan gambar audit).
* Memastikan validasi CRUD dan akurasi filter data tetap terjaga 100%.

---

## 2. Detail Perubahan Kode (*Detailed Code Changes*)

### 2.1. Controller Layer: `app/Http/Controllers/CrossCutChecksheetController.php`

#### ❌ Kode Lama (Sebelum Optimasi):
```php
// Executed on every index page load / pagination (Un-cached & Cross-Plant scan)
$existingItemIds = CrossCutChecksheet::withoutGlobalScope('plant')->distinct()->pluck('item_id');

$items = Item::whereIn('id', $existingItemIds)->orderBy('name')->get();

$customers = Item::whereIn('id', $existingItemIds)
    ->whereNotNull('customer')
    ->where('customer', '!=', '')
    ->distinct()
    ->orderBy('customer')
    ->pluck('customer');
    
$initials = CrossCutChecksheet::withoutGlobalScope('plant')
    ->whereNotNull('operator_initials')
    ->where('operator_initials', '!=', '')
    ->distinct()
    ->orderBy('operator_initials')
    ->pluck('operator_initials');
```

#### ✅ Kode Baru (Disamakan dengan Pola Sub-Assy - Isolated & Cached per Plant):
```php
// Data for filters (Cached per plant to avoid subquery scans on every page load)
$plantId = \App\Models\Plant::resolveId($filters['plant']);

$items = \Illuminate\Support\Facades\Cache::remember("crosscut_filter_items_{$plantId}", 1800, function () use ($plantId) {
    return Item::where('plant_id', $plantId)->orderBy('name')->get();
});

$customers = \Illuminate\Support\Facades\Cache::remember("crosscut_filter_cust_{$plantId}", 1800, function () use ($plantId) {
    return Item::where('plant_id', $plantId)
        ->whereNotNull('customer')
        ->where('customer', '!=', '')
        ->distinct()
        ->pluck('customer')
        ->sort();
});

$initials = \Illuminate\Support\Facades\Cache::remember("crosscut_filter_init_{$plantId}", 1800, function () use ($plantId) {
    return CrossCutChecksheet::where('plant_id', $plantId)
        ->where('qc_datetime', '>=', now()->subDays(90))
        ->whereNotNull('operator_initials')
        ->where('operator_initials', '!=', '')
        ->distinct()
        ->pluck('operator_initials')
        ->sort();
});
```

---

### 2.2. Service Layer: `app/Services/CrossCutChecksheetService.php`

#### A. Invalidation Cache Otomatis saat Input Baru (`createChecksheet`)

##### ✅ Penambahan Kode di `createChecksheet()`:
```php
DB::commit();

// Clear filter cache for this plant so dropdowns refresh immediately
\Illuminate\Support\Facades\Cache::forget("crosscut_filter_init_{$checksheet->plant_id}");
```

---

### 2.3. Modul Kompresi Foto & Penghematan Memori Disk (`ImageCompressor.php`)

#### ❌ Kode Lama (Penyebab Memori Server Jebol):
```php
// Foto HP kamera 5MB - 15MB disimpan mentah-mentah tanpa kompresi
$imagePath = $data['image']->store('cross_cut_images', 'public');
```

#### ✅ Kode Baru (Otomatis Resize & Compression):
```php
// Foto di-resize max 1200px & dikompresi 80% (Ukuran file turun dari 10MB ke ~100KB)
$imagePath = \App\Helpers\ImageCompressor::compressAndStore($data['image'], 'cross_cut_images');
```

---

## 3. Rencana Verifikasi & Pengujian (*Verification Plan*)

1. **Uji Kecepatan Respon Index Page**:
   Mengukur waktu eksekusi `CrossCutChecksheetController::index()` via script CLI & browser DevTools.
2. **Uji Kompresi Foto**:
   Menguji upload foto resolusi tinggi (HP Camera), memastikan file terkompresi hingga **80% - 95% lebih hemat disk** dan gambar tetap jernih.
3. **Uji Akurasi Validasi & Filter**:
   Memastikan seluruh filter (Tanggal, Item, Customer, Shift, Status Approval, Search) dan validasi simpan/edit/hapus berjalan 100% akurat.
4. **Uji Auto-Reset Cache**:
   Memastikan saat checksheet Cross-Cut baru diinput, pilihan di dropdown filter langsung ter-update secara otomatis.
