# 📝 Rencana Optimasi Performa Modul First Piece Approval (Sub-Assy Pattern)

**Lokasi File Dokumen**: `docs/plan_fpa_update_performa.md`  
**Tanggal**: 22 Agustus 2026  
**Target Modul**: First Piece Approval (`first_piece_approval`)

---

## 1. Pendahuluan & Tujuan Optimasi

Dokumen ini berisi detail rencana pengodean (*Detailed Code Implementation Plan*) untuk mengoptimasi kinerja backend dan kueri database pada modul **First Piece Approval**, dengan mengadopsi pola pengodean yang telah terbukti pada modul **Sub-Assy**.

Target optimasi:
* Menurunkan waktu respon halaman index & paginasi dari **> 100 ms / beberapa detik** menjadi **< 40 ms**.
* Mengeliminasi **4x Subquery Full Table Scan** pada tabel `first_piece_approvals`.
* Memastikan validasi CRUD dan akurasi filter data tetap terjaga 100%.

---

## 2. Detail Perubahan Kode (*Detailed Code Changes*)

### 2.1. Controller Layer: `app/Http/Controllers/FirstPieceApprovalController.php`

#### ❌ Kode Lama (Sebelum Optimasi):
```php
// Executed on every index page load / pagination
$items = Item::whereIn('id', function($query) use ($plantId) {
    $query->select('item_id')->from('first_piece_approvals')->where('plant_id', $plantId);
})->orderBy('name')->get();

$customers = Item::whereIn('id', function($query) use ($plantId) {
    $query->select('item_id')->from('first_piece_approvals')->where('plant_id', $plantId);
})->whereNotNull('customer')->distinct()->pluck('customer')->sort();

$initials = FirstPieceApproval::where('plant_id', $plantId)
    ->whereNotNull('operator_initials')
    ->distinct()
    ->pluck('operator_initials')
    ->sort();

$machines = collect();
if (auth()->check() && auth()->user()->role === 'admin') {
    $machines = FirstPieceApproval::where('plant_id', $plantId)
        ->whereNotNull('code_machine')
        ->distinct()
        ->pluck('code_machine')
        ->sort(SORT_NUMERIC)
        ->values();
}

$partDimensionStandards = $this->getConsolidatedStandards();
```

#### ✅ Kode Baru (Disamakan dengan Pola Sub-Assy):
```php
// Data for filters (Cached per plant to avoid 4x subquery scans on every page load)
$plantId = \App\Models\Plant::resolveId($filters['plant']);

$items = \Illuminate\Support\Facades\Cache::remember("fpa_filter_items_{$plantId}", 1800, function () use ($plantId) {
    return Item::where('plant_id', $plantId)->orderBy('name')->get();
});

$customers = \Illuminate\Support\Facades\Cache::remember("fpa_filter_cust_{$plantId}", 1800, function () use ($plantId) {
    return Item::where('plant_id', $plantId)
        ->whereNotNull('customer')
        ->where('customer', '!=', '')
        ->distinct()
        ->pluck('customer')
        ->sort();
});

$initials = \Illuminate\Support\Facades\Cache::remember("fpa_filter_init_{$plantId}", 1800, function () use ($plantId) {
    return FirstPieceApproval::where('plant_id', $plantId)
        ->where('date', '>=', now()->subDays(90))
        ->whereNotNull('operator_initials')
        ->distinct()
        ->pluck('operator_initials')
        ->sort();
});

$machines = \Illuminate\Support\Facades\Cache::remember("fpa_filter_mach_{$plantId}", 3600, function () use ($plantId) {
    return FirstPieceApproval::where('plant_id', $plantId)
        ->whereNotNull('code_machine')
        ->distinct()
        ->pluck('code_machine')
        ->sort(SORT_NUMERIC)
        ->values();
});

$partDimensionStandards = \Illuminate\Support\Facades\Cache::remember("fpa_standards", 43200, function () {
    return $this->getConsolidatedStandards();
});
```

---

### 2.2. Service Layer: `app/Services/FirstPieceApprovalService.php`

#### A. Invalidation Cache Otomatis saat Input Baru (`createChecksheet`)

##### ✅ Penambahan Kode di `createChecksheet()`:
```php
DB::commit();

// Clear filter cache for this plant so dropdowns refresh immediately
\Illuminate\Support\Facades\Cache::forget("fpa_filter_init_{$checksheet->plant_id}");
\Illuminate\Support\Facades\Cache::forget("fpa_filter_mach_{$checksheet->plant_id}");
```

---

## 3. Rencana Verifikasi & Pengujian (*Verification Plan*)

1. **Uji Kecepatan Respon Index Page**:
   Mengukur waktu eksekusi `FirstPieceApprovalController::index()` via script CLI & browser DevTools.
2. **Uji Akurasi Validasi & Filter**:
   Memastikan seluruh filter (Tanggal, Item, Customer, Shift, Mesin, Status Approval, Search) dan validasi simpan/edit/hapus berjalan 100% akurat.
3. **Uji Auto-Reset Cache**:
   Memastikan saat checksheet First Piece Approval baru diinput, pilihan di dropdown filter langsung ter-update secara otomatis.
