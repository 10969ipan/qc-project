# 📝 Rencana Optimasi Performa Form Sortir Create (Sub-Assy & NG Item Filtering Pattern)

**Lokasi File Dokumen**: `docs/plan_sortir_create_update_performa.md`  
**Tanggal**: 22 Agustus 2026  
**Target File/Menu**: `resources/views/sortir/create.blade.php` (`SortirChecksheetController@create`)

---

## 1. Pendahuluan & Analisis Masalah

Saat operator/inspector membuka form **Tambah Sortir** (`/sortir/create` atau `resources/views/sortir/create.blade.php`), controller memanggil metode `SortirChecksheetService::getAvailableNgItems()`.

### ❌ Titik Kemacetan (Bottleneck):
1. **Full Scan Data NG Historis Tanpa Batas Tanggal**:
   Metode `getAvailableNgItems()` mengambil **seluruh baris transaksi NG historis** dari 3 tabel modul sekaligus:
   * `SubAssyChecksheet::where('judgment', 'NG')->whereNotNull('next_proses')->get()`
   * `InProcessChecksheet::where('judgment', 'NG')->whereNotNull('next_proses')->get()`
   * `CrossCutChecksheet::where('position_remark_judgment', 'NG')->whereNotNull('next_proses')->get()`
2. **Kueri Agregasi `SortirChecksheet` yang Berulang**:
   `SortirChecksheet::selectRaw('source_type, source_id, SUM(total_qty)...')` dihitung ulang dari awal pada setiap kali form create dimuat.

---

## 2. Detail Perubahan Kode (*Proposed Code Changes*)

### 2.1. Service Layer: `app/Services/SortirChecksheetService.php`

#### A. Pembatasan Rentang Tanggal NG Active Items (30 Hari Terbaru)
Menambahkan filter rentang tanggal 30 hari terakhir pada pencarian item NG aktif agar database tidak memuat data NG lama yang sudah selesai disortir:

```php
// Limit active NG search to past 30 days to prevent full historical table scans
$cutoffDate = now()->subDays(30);

$querySubAssy = SubAssyChecksheet::where('judgment', 'NG')
    ->where('date', '>=', $cutoffDate)
    ->whereNotNull('next_proses')
    ->with('item:id,name,part_number,sap_code');

$queryInProcess = InProcessChecksheet::where('judgment', 'NG')
    ->where('date', '>=', $cutoffDate)
    ->whereNotNull('next_proses')
    ->with('item:id,name,part_number,sap_code');

$queryCrossCut = CrossCutChecksheet::where('position_remark_judgment', 'NG')
    ->where('qc_datetime', '>=', $cutoffDate)
    ->whereNotNull('next_proses')
    ->with('item:id,name,part_number,sap_code');
```

#### B. Caching `getAvailableNgItems()` per Plant ID
Membungkus hasil pemetaan `$ngItems` dengan `Cache::remember()` pendek (TTL 5-10 menit) per Plant ID:

```php
$cacheKey = "sortir_available_ng_items_{$plantId}";
return Cache::remember($cacheKey, 300, function() use ($filters) {
    // Excecute optimized NG items query (30 days limit)
});
```

#### C. Invalidation Cache saat Input Sortir / Checksheet NG Baru
Menambahkan `Cache::forget("sortir_available_ng_items_{$plantId}")` saat ada input checksheet Sortir baru atau temuan NG baru disubmit.

---

## 3. Rencana Verifikasi & Pengujian (*Verification Plan*)

1. **Uji Kecepatan Respon Form Create**:
   Mengukur waktu muat halaman `sortir/create` via CLI/DevTools (target **< 30 ms**).
2. **Uji Akurasi Sisa Qty Sortir (`remaining_qty`)**:
   Memastikan kalkulasi sisa kuantitas barang NG 30 hari terakhir yang perlu disortir tetap 100% akurat.
3. **Uji Auto-Reset Cache**:
   Memastikan saat ada temuan NG baru dari Sub-Assy/In-Process/Cross-Cut, daftar item di pilihan form Sortir langsung muncul secara otomatis.
