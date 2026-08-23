# 📝 Rencana Optimasi Performa Modul Checksheet In-Process (Sub-Assy Pattern)

**Lokasi File Dokumen**: `docs/plan_inproses_update_performa.md`  
**Tanggal**: 22 Agustus 2026  
**Target Modul**: Checksheet In-Process (`in_process`)

---

## 1. Pendahuluan & Tujuan Optimasi

Dokumen ini berisi detail rencana pengodean (*Detailed Code Implementation Plan*) untuk mengoptimasi kinerja backend dan kueri database pada modul **Checksheet In-Process**, dengan mengadopsi pola pengodean yang telah terbukti pada modul **Sub-Assy**.

Target optimasi:
* Menurunkan waktu respon halaman index & paginasi dari **> 100 ms / beberapa detik** menjadi **< 40 ms**.
* Mengeliminasi **4x Subquery Full Table Scan** pada 28.000+ baris data `in_process_checksheets`.
* Mengaktifkan **B-Tree Composite Index** untuk pencarian scan QR Code agar berjalan **< 1 ms**.
* Memastikan validasi CRUD dan akurasi filter data tetap terjaga 100%.

---

## 2. Detail Perubahan Kode (*Detailed Code Changes*)

### 2.1. Controller Layer: `app/Http/Controllers/InProcessChecksheetController.php`

#### ❌ Kode Lama (Sebelum Optimasi):
```php
// Executed on every index page load / pagination
$items = Item::whereIn('id', function($query) use ($plantId) {
    $query->select('item_id')->from('in_process_checksheets')->where('plant_id', $plantId);
})->orderBy('name')->get();

$customers = Item::whereIn('id', function($query) use ($plantId) {
    $query->select('item_id')->from('in_process_checksheets')->where('plant_id', $plantId);
})->whereNotNull('customer')->distinct()->pluck('customer')->sort();

$initialsQuery = InProcessChecksheet::where('plant_id', $plantId)
    ->whereNotNull('operator_initials');

if (!empty($filters['start_date'])) {
    $initialsQuery->whereDate('date', '>=', $filters['start_date']);
}
if (!empty($filters['end_date'])) {
    $initialsQuery->whereDate('date', '<=', $filters['end_date']);
}
if (!empty($filters['shift'])) {
    $initialsQuery->where('shift', $filters['shift']);
}

$initials = $initialsQuery->distinct()
    ->pluck('operator_initials')
    ->sort();

$machines = collect();
if (auth()->check() && auth()->user()->role === 'admin') {
    $machines = InProcessChecksheet::where('plant_id', $plantId)
        ->whereNotNull('code_machine')
        ->distinct()
        ->pluck('code_machine')
        ->sort();
}

$partDimensionStandards = $this->getConsolidatedStandards();
```

#### ✅ Kode Baru (Disamakan dengan Pola Sub-Assy):
```php
// Data for filters (Cached per plant to avoid 4x subquery scans over 28,000+ rows on every page load)
$plantId = \App\Models\Plant::resolveId($filters['plant']);

$items = \Illuminate\Support\Facades\Cache::remember("in_proc_filter_items_{$plantId}", 1800, function () use ($plantId) {
    return Item::where('plant_id', $plantId)->orderBy('name')->get();
});

$customers = \Illuminate\Support\Facades\Cache::remember("in_proc_filter_cust_{$plantId}", 1800, function () use ($plantId) {
    return Item::where('plant_id', $plantId)
        ->whereNotNull('customer')
        ->where('customer', '!=', '')
        ->distinct()
        ->pluck('customer')
        ->sort();
});

$initials = \Illuminate\Support\Facades\Cache::remember("in_proc_filter_init_{$plantId}", 1800, function () use ($plantId) {
    return InProcessChecksheet::where('plant_id', $plantId)
        ->where('date', '>=', now()->subDays(90))
        ->whereNotNull('operator_initials')
        ->distinct()
        ->pluck('operator_initials')
        ->sort();
});

$machines = \Illuminate\Support\Facades\Cache::remember("in_proc_filter_mach_{$plantId}", 3600, function () use ($plantId) {
    return InProcessChecksheet::where('plant_id', $plantId)
        ->whereNotNull('code_machine')
        ->distinct()
        ->pluck('code_machine')
        ->sort()
        ->values();
});

$partDimensionStandards = \Illuminate\Support\Facades\Cache::remember("in_proc_standards", 43200, function () {
    return $this->getConsolidatedStandards();
});
```

---

### 2.2. Service Layer: `app/Services/InProcessChecksheetService.php`

#### A. Kueri Pencarian Scan QR Code (`qr_raw`)

##### ❌ Kode Lama:
```php
if (!empty($filters['qr_raw'])) {
    $query->where('in_process_checksheets.qrcode', 'like', "%{$filters['qr_raw']}%");
}
```

##### ✅ Kode Baru (Mendahulukan Exact Match berbasis B-Tree Index):
```php
// QR Raw filter (Prioritize B-Tree index lookup for exact QR/Unique/SAP code scans)
if (!empty($filters['qr_raw'])) {
    $qr = trim($filters['qr_raw']);
    $query->where(function ($q) use ($qr) {
        $q->where('in_process_checksheets.qrcode', $qr)
          ->orWhere('in_process_checksheets.unique_code_id', $qr)
          ->orWhere('in_process_checksheets.sap_code', $qr)
          ->orWhere('in_process_checksheets.qrcode', 'like', "%{$qr}%");
    });
}
```

#### B. Invalidation Cache Otomatis saat Input Baru (`createChecksheet`)

##### ✅ Penambahan Kode di `createChecksheet()`:
```php
DB::commit();

// Clear filter cache for this plant so dropdowns refresh immediately
\Illuminate\Support\Facades\Cache::forget("in_proc_filter_init_{$checksheet->plant_id}");
\Illuminate\Support\Facades\Cache::forget("in_proc_filter_mach_{$checksheet->plant_id}");
```

---

## 3. Rencana Verifikasi & Pengujian (*Verification Plan*)

1. **Uji Kecepatan Respon Index Page**:
   Mengukur waktu eksekusi `InProcessChecksheetController::index()` via script CLI & browser DevTools.
2. **Uji Kecepatan Scan QR Code**:
   Memastikan pencarian QR Code via B-Tree Index berjalan **< 1 ms**.
3. **Uji Akurasi Validasi & Filter**:
   Memastikan seluruh filter (Tanggal, Item, Customer, Shift, Mesin, Status Approval, Search) dan validasi simpan/edit/hapus berjalan 100% akurat.
4. **Uji Auto-Reset Cache**:
   Memastikan saat checksheet In-Process baru diinput, pilihan di dropdown filter langsung ter-update secara otomatis.
