# 📝 Rencana Optimasi Performa Modul Laporan Durability Plating (Sub-Assy Pattern)

**Lokasi File Dokumen**: `docs/plan_durability_plating_update_performa.md`  
**Tanggal**: 22 Agustus 2026  
**Target File/Menu**: `resources/views/durability_plating/report.blade.php` (`StandardPerformanceTestController@report`)

---

## 1. Pendahuluan & Analisis Masalah

Saat pengguna membuka, menyaring (*filter*), atau mengeklik tombol navigasi halaman (*pagination*) di menu **Laporan Durability Plating** (`/durability_plating/report`), controller `StandardPerformanceTestController@report` mengeksekusi kueri pemuatan master data dan dropdown filter pada setiap hit request:

### ❌ Titik Kemacetan (Bottleneck):
1. **Full Scan Master Items**:
   `StandardPerformanceTest::orderBy('part_name', 'asc')->get()` memuat seluruh data master item berulang kali dari database tanpa cache.
2. **Kueri Customer & Category Dropdown Un-cached**:
   `StandardPerformanceTest::whereNotNull('customer_name')->distinct()->pluck('customer_name')` dan `select('category')` memindai database pada setiap hit halaman/paginasi.

---

## 2. Detail Perubahan Kode (*Proposed Code Changes*)

### 2.1. Controller Layer: `app/Http/Controllers/StandardPerformanceTestController.php`

#### ❌ Kode Lama (Sebelum Optimasi):
```php
$masterItems = \App\Models\StandardPerformanceTest::orderBy('part_name', 'asc')->get();

$customers = \App\Models\StandardPerformanceTest::whereNotNull('customer_name')
    ->select('customer_name')
    ->distinct()
    ->orderBy('customer_name')
    ->pluck('customer_name');

$categories = \App\Models\StandardPerformanceTest::whereNotNull('category')
    ->where('category', '!=', '')
    ->select('category')
    ->distinct()
    ->orderBy('category')
    ->pluck('category');
```

#### ✅ Kode Baru (Disamakan dengan Pola Sub-Assy - Cached per Plant):
```php
$masterItems = \Illuminate\Support\Facades\Cache::remember("durability_report_master_items", 3600, function () {
    return \App\Models\StandardPerformanceTest::orderBy('part_name', 'asc')->get();
});

$customers = \Illuminate\Support\Facades\Cache::remember("durability_report_customers", 3600, function () {
    return \App\Models\StandardPerformanceTest::whereNotNull('customer_name')
        ->select('customer_name')
        ->distinct()
        ->orderBy('customer_name')
        ->pluck('customer_name');
});

$categories = \Illuminate\Support\Facades\Cache::remember("durability_report_categories", 3600, function () {
    return \App\Models\StandardPerformanceTest::whereNotNull('category')
        ->where('category', '!=', '')
        ->select('category')
        ->distinct()
        ->orderBy('category')
        ->pluck('category');
});
```

---

## 3. Rencana Verifikasi & Pengujian (*Verification Plan*)

1. **Uji Kecepatan Respon Report Page**:
   Mengukur waktu eksekusi `StandardPerformanceTestController@report` via CLI/DevTools (target **< 40 ms**).
2. **Uji Akurasi Rekap & Filter**:
   Memastikan kalkulasi rata-rata (RN1/RN2) dan hasil filter (Thickness, Corrodkote, CASS, Salt Spray, Porecount) tetap 100% akurat.
3. **Uji Auto-Reset Cache**:
   Memastikan saat ada master item baru disubmit atau diedit, pilihan di dropdown filter langsung ter-update secara otomatis.
