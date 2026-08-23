# 📝 Rencana Optimasi Performa Modul Incoming Parts (Sub-Assy Pattern)

**Lokasi File Dokumen**: `docs/plan_incomingpart_update_performa.md`  
**Tanggal**: 22 Agustus 2026  
**Target Modul**: Incoming Parts (`incoming/parts`) & `resources/views/incoming/parts/index.blade.php`

---

## 1. Pendahuluan & Tujuan Optimasi

Dokumen ini berisi detail rencana pengodean (*Detailed Code Implementation Plan*) untuk mengoptimasi kinerja backend dan kueri database pada modul **Incoming Parts**, dengan mengadopsi pola pengodean Sub-Assy.

Target optimasi:
* Menurunkan waktu respon halaman index & paginasi dari **> 100 ms / beberapa detik** menjadi **< 40 ms**.
* Mengeliminasi **Subquery Full Table Scan** pada tabel `incoming_parts`.
* Mengisolasikan data filter per Plant secara presisi dan ter-cache di RAM server.
* Memastikan validasi CRUD dan akurasi filter data tetap terjaga 100%.

---

## 2. Detail Perubahan Kode (*Detailed Code Changes*)

### 2.1. Controller Layer: `app/Http/Controllers/IncomingPartController.php`

#### ❌ Kode Lama (Sebelum Optimasi):
```php
$items = Item::byCategory($categories)->orderBy('name')->get();

$customers = Item::whereIn('id', function ($query) use ($plantId) {
    $query->select('item_id')->from('incoming_parts')->where('plant_id', $plantId);
})->whereNotNull('customer')->distinct()->pluck('customer')->sort();

$initialsQuery = IncomingPart::where('plant_id', $plantId)
    ->whereNotNull('operator_initials');
$initials = $initialsQuery->distinct()->pluck('operator_initials')->sort();
```

#### ✅ Kode Baru (Disamakan dengan Pola Sub-Assy - Isolated & Cached per Plant):
```php
// Data for filters (Cached per plant to avoid subquery scans on every page load)
$plantInput = $request->get('plant', auth()->user()->plant_id);
$plantId = Plant::resolveId($plantInput);

$items = \Illuminate\Support\Facades\Cache::remember("incomingpart_filter_items_{$plantId}", 1800, function () use ($categories, $plantId) {
    return Item::byCategory($categories)->where('plant_id', $plantId)->orderBy('name')->get();
});

$customers = \Illuminate\Support\Facades\Cache::remember("incomingpart_filter_cust_{$plantId}", 1800, function () use ($plantId) {
    return Item::where('plant_id', $plantId)
        ->whereNotNull('customer')
        ->where('customer', '!=', '')
        ->distinct()
        ->pluck('customer')
        ->sort();
});

$initials = \Illuminate\Support\Facades\Cache::remember("incomingpart_filter_init_{$plantId}", 1800, function () use ($plantId) {
    return IncomingPart::where('plant_id', $plantId)
        ->where('date', '>=', now()->subDays(90))
        ->whereNotNull('operator_initials')
        ->where('operator_initials', '!=', '')
        ->distinct()
        ->pluck('operator_initials')
        ->sort();
});
```

---

### 2.2. Service Layer: `app/Services/IncomingPartService.php`

#### A. Invalidation Cache Otomatis saat Simpan Data Checksheet Baru (`createChecksheet`)

```php
DB::commit();

// Clear filter cache so dropdown options refresh immediately
\Illuminate\Support\Facades\Cache::forget("incomingpart_filter_init_{$checksheet->plant_id}");
```

---

## 3. Rencana Verifikasi & Pengujian (*Verification Plan*)

1. **Uji Kecepatan Respon Index Page**:
   Mengukur waktu eksekusi `IncomingPartController::index()` via CLI/DevTools (target **< 40 ms**).
2. **Uji Akurasi Validasi & Filter**:
   Memastikan seluruh filter (Tanggal, Item, Customer, Shift, Status Approval, Search) dan validasi simpan/edit/hapus berjalan 100% akurat.
3. **Uji Auto-Reset Cache**:
   Memastikan saat checksheet Incoming Part baru diinput, pilihan di dropdown filter langsung ter-update secara otomatis.
