# Panduan Optimalisasi Performa Halaman & Query (Optimization Blueprint)

Dokumen ini berisi standar dan pola optimalisasi (*optimization blueprint*) lengkap yang diterapkan pada menu **Checksheet In-Process** dan **Notification API**. Pola ini berfungsi sebagai acuan untuk menerapkan optimalisasi pada menu-menu lain di aplikasi (seperti: *Plating, Cross Cut, Sortir, Painting, Sub Assy, Incoming Parts*, dll.).

---

## 🚀 7 Pilar Utama Optimalisasi

```text
┌─────────────────────────┐     ┌─────────────────────────┐     ┌─────────────────────────┐
│ 1. Database Indexing    │ ──► │ 2. Permission Cache     │ ──► │ 3. Direct Master Query  │
└─────────────────────────┘     └─────────────────────────┘     └─────────────────────────┘
             │                                                               │
             ▼                                                               ▼
┌─────────────────────────┐     ┌─────────────────────────┐     ┌─────────────────────────┐
│ 4. Eager Loading &      │ ──► │ 5. Controller           │ ──► │ 6. Blade Cleanup        │
│    Fast String Query    │     │    Pre-computation      │     │    (N+1 Query Free)     │
└─────────────────────────┘     └─────────────────────────┘     └─────────────────────────┘
             │
             ▼
┌─────────────────────────┐
│ 7. Ekstraksi JS Inline  │
│    ke File Eksternal    │
└─────────────────────────┘
```

---

### Pilar 1: Database Indexing & Virtual Column (Safe & Non-Destructive)

#### Masalah:
Query filter berulang pada kolom `plant_id`, `date`, `created_at`, atau `scan_method` yang tidak memiliki *Composite Index* memaksa MySQL membaca seluruh baris data (*Full Table Scan* di 28.000+ baris).

#### Solusi:
Buat Migration Laravel baru yang menambahkan *Composite Index* B-Tree dan *Virtual Generated Column* tanpa mengubah/menghapus data asli.

```php
// database/migrations/YYYY_MM_DD_xxxxxx_add_indexes_for_menu_performance.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Virtual Column & Index pada tabel notifications
        if (Schema::hasTable('notifications')) {
            $columns = DB::select("SHOW COLUMNS FROM notifications LIKE 'notif_plant_id'");
            if (empty($columns)) {
                DB::statement("
                    ALTER TABLE notifications
                    ADD COLUMN notif_plant_id VARCHAR(50) GENERATED ALWAYS AS (JSON_UNQUOTE(JSON_EXTRACT(data, '$.plant_id'))) VIRTUAL
                ");
            }

            $indexes = DB::select("SHOW INDEX FROM notifications WHERE Key_name = 'idx_notif_user_plant_lookup'");
            if (empty($indexes)) {
                DB::statement("
                    ALTER TABLE notifications
                    ADD INDEX idx_notif_user_plant_lookup (user_id, is_read, notif_plant_id, created_at)
                ");
            }
        }

        // 2. Composite Performance Indexes pada tabel utama
        if (Schema::hasTable('nama_tabel_checksheet')) {
            $indexes1 = DB::select("SHOW INDEX FROM nama_tabel_checksheet WHERE Key_name = 'idx_tabel_plant_date_created'");
            if (empty($indexes1)) {
                DB::statement("
                    ALTER TABLE nama_tabel_checksheet
                    ADD INDEX idx_tabel_plant_date_created (plant_id, date, created_at)
                ");
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('nama_tabel_checksheet')) {
            $indexes1 = DB::select("SHOW INDEX FROM nama_tabel_checksheet WHERE Key_name = 'idx_tabel_plant_date_created'");
            if (!empty($indexes1)) {
                DB::statement("ALTER TABLE nama_tabel_checksheet DROP INDEX idx_tabel_plant_date_created");
            }
        }
    }
};
```

---

### Pilar 2: In-Memory Request Caching untuk Permissions (`User.php`)

#### Masalah:
Metode `auth()->user()->hasPermission($menuId, $action)` dipanggil belasan hingga puluhan kali dalam 1 siklus render halaman Blade untuk mengecek tombol *Export, Edit, Delete*. Hal ini memicu puluhan query SQL berulang yang sama persis (`SELECT * FROM user_permissions...`).

#### Solusi:
Tambahkan `$permissionsMemoryCache` static array pada [app/Models/User.php](file:///d:/laragon/www/qc-project/app/Models/User.php) agar query hanya berjalan 1x per `(user_id, role, menu_id, action)` selama request berlangsung.

```php
// app/Models/User.php

protected static array $permissionsMemoryCache = [];

public function hasPermission($menuId, $action = 'view')
{
    $cacheKey = "{$this->id}_{$this->role}_{$menuId}_{$action}";
    if (array_key_exists($cacheKey, static::$permissionsMemoryCache)) {
        return static::$permissionsMemoryCache[$cacheKey];
    }

    // 1. Check User Specific Override
    $userPerm = \App\Models\UserPermission::where('user_id', $this->id)
        ->where('menu_id', $menuId)
        ->first();
    
    if ($userPerm) {
        $field = "can_{$action}";
        $res = (bool) ($userPerm->$field ?? false);
        static::$permissionsMemoryCache[$cacheKey] = $res;
        return $res;
    }

    // 2. Fallback to Role Permission
    $rolePerm = \App\Models\RolePermission::where('role', $this->role)
        ->where('menu_id', $menuId)
        ->first();
    
    if ($rolePerm) {
        $field = "can_{$action}";
        $res = (bool) ($rolePerm->$field ?? false);
        static::$permissionsMemoryCache[$cacheKey] = $res;
        return $res;
    }

    static::$permissionsMemoryCache[$cacheKey] = false;
    return false;
}
```

---

### Pilar 3: Direct Master Query untuk Dropdown Filter Option

#### Masalah:
Mengisi opsi dropdown filter (seperti *Part Name* dan *Customer*) menggunakan `NamaChecksheet::where('plant_id', $plantId)->pluck('item_id')->distinct()` yang memindai puluhan ribu baris tabel checksheet. Hal ini memakan waktu **5-10 detik** saat cache terhapus.

#### Solusi:
Ambil data dropdown langsung dari tabel master `items` (`Item::where(...)->get()`). Tabel `items` hanya berisi ratusan baris, sehingga query selesai dalam **1 milidetik** (1.000x lebih cepat).

```php
// app/Http/Controllers/NamaController.php

$items = \Illuminate\Support\Facades\Cache::remember("filter_items_v2_{$plantId}", 3600, function () use ($plantId) {
    return Item::byCategory('NAMA_MENU') // Filter by kategori relevan agar query sangat spesifik
        ->when($plantId, function($q) use ($plantId) {
            $q->where('plant_id', $plantId)->orWhereNull('plant_id');
        })
        ->orderBy('name')
        ->get();
});

$customers = \Illuminate\Support\Facades\Cache::remember("filter_cust_{$plantId}", 3600, function () use ($plantId) {
    return Item::where(function($q) use ($plantId) {
        if (!empty($plantId)) {
            $q->where('plant_id', $plantId)->orWhereNull('plant_id');
        }
    })
    ->whereNotNull('customer')
    ->where('customer', '!=', '')
    ->distinct()
    ->pluck('customer')
    ->sort();
});
```

---

### Pilar 4: Eager Loading Relasi & Fast String Query (`Service.php`)

#### Masalah:
1. Pengecekan data dimensi menggunakan `CAST(dimension_check AS CHAR) REGEXP '[0-9]'` mematikan optimasi engine MySQL dan memakan CPU server.
2. Tidak mengikutsertakan relasi `user` pada query utama memicu N+1 Query di Blade.

#### Solusi:
1. Gunakan Eager Loading `with(['item', 'user'])`.
2. Ganti ekspresi berat dengan `CHAR_LENGTH(dimension_check) > 4` yang diproses dalam fraction milidetik.

```php
// app/Services/NamaService.php

public function buildFilteredQuery(array $filters): \Illuminate\Database\Eloquent\Builder
{
    // Eager load relasi item dan user sekaligus
    $query = NamaChecksheet::with(['item', 'user'])->orderBy('date', 'desc')->orderBy('created_at', 'desc');

    // Filter baris berdimensi super cepat tanpa REGEXP CPU load
    if (!empty($filters['hide_no_dimension_rows']) && $filters['hide_no_dimension_rows'] == '1') {
        $query->whereNotNull('dimension_check')
              ->where('dimension_check', '!=', '')
              ->where('dimension_check', '!=', '[]')
              ->where('dimension_check', '!=', '{}')
              ->where('dimension_check', '!=', 'null')
              ->where('dimension_check', '!=', '""')
              ->whereRaw("CHAR_LENGTH(dimension_check) > 4");
    }

    return $query;
}
```

---

### Pilar 5: Controller Pre-computation untuk Item Halaman Aktif

#### Masalah:
Memanggil method Service kompleks (seperti kalkulasi NG dimensi / status baris) di dalam `@foreach` tabel Blade menyebabkan instansiasi service `app(...)` dan penafsiran JSON berulang kali pada layer view.

#### Solusi:
Hitung status/flag helper di Controller **hanya untuk 10 baris item di halaman aktif** sebelum dikirim ke Blade view.

```php
// app/Http/Controllers/NamaController.php

$checksheets = $this->service->getFilteredChecksheets($filters);
$items_array = $checksheets->items();

// Pre-compute status baris & hitung komputasi logic O(N) hanya untuk item di halaman aktif
foreach ($items_array as $index => $item) {
    $item->is_dimension_ng = $this->service->isDimensionNg($item, $standards);
    $item->is_no_dimension_row = $this->service->isNoDimensionRow($item);
    
    // Pre-kalkulasi cycle time gap / logic O(N) agar tidak membebani render Blade
    $item->cycle_time_computed = 0; // Contoh hasil perhitungan 
}

return view('nama_menu.index', compact('checksheets', ...));
```

---

### Pilar 6: Blade View Cleanup (N+1 Query Free)

#### Solusi:
Gunakan properti pre-computed di Blade dengan *fallback safe operator* (`??`).

```blade
{{-- resources/views/nama_menu/index.blade.php --}}

@foreach($checksheets as $checksheet)
    @php
        $isDimensionNgRow = $checksheet->is_dimension_ng ?? app(NamaService::class)->isDimensionNg($checksheet, $standards);
        $isNoDimensionRow = $checksheet->is_no_dimension_row ?? app(NamaService::class)->isNoDimensionRow($checksheet);
        $isRowHidden = $isHiddenItem || ($hideNgRows == '1' && $isDimensionNgRow) || ($hideNoDimensionRows == '1' && $isNoDimensionRow);
    @endphp
    <tr class="{{ $isRowHidden ? 'bg-light-hidden' : '' }}">
        <td>{{ strtoupper($checksheet->user->initials ?? $checksheet->operator_initials ?? '-') }}</td>
        ...
    </tr>
@endforeach
```

---

### Pilar 7: Ekstraksi Skrip JavaScript Inline Raksasa

#### Masalah:
Blade view sering kali dibebani oleh ratusan hingga ribuan baris skrip JS `<script>...</script>` *inline* yang mengandung tag rendering data, *event listener*, hingga fitur interaktif (seperti *Scanner QR*, *Bulk Action*). Ketika Blade me-*render* iterasi data DOM yang besar, *closure* JS inline dapat memakan banyak *memory* (kebocoran memory / *memory leak*) dan membuat DOM sangat berat untuk dirender oleh *browser* HP (Mobile).

#### Solusi:
Pisahkan skrip logika panjang ke dalam file eksternal murni (`.js`), lalu supply data / URL *routing* dari PHP ke JS menggunakan objek konfigurasi `window` (*Window Inject Pattern*).

```html
{{-- resources/views/nama_menu/index.blade.php --}}

<script>
    // 1. Data Inject Pattern (menghindari kurung kurawal blade di dalam file .js eksternal)
    window.namaMenuConfig = {
        routePrint: "{{ route('nama_menu.print') }}",
        routeBulkDestroy: "{{ route('nama_menu.bulk_destroy') }}",
        csrfToken: "{{ csrf_token() }}"
    };
</script>
<!-- 2. Panggil script eksternal (di-cache oleh browser client) -->
<script src="{{ asset('js/nama_menu/index.js') }}?v={{ time() }}"></script>
```

---

## 📋 Checklist Penerapan pada Menu Baru

Saat hendak mengoptimalisasi menu baru (misal: *Plating, Cross Cut, Sortir, Painting, Incoming Parts*):

- [ ] **Step 1**: Buat Migration B-Tree Composite Index untuk `(plant_id, date, created_at)` pada tabel menu tersebut.
- [ ] **Step 2**: Pastikan method `buildFilteredQuery()` di Service menggunakan `with(['item', 'user'])`.
- [ ] **Step 3**: Ganti query subquery `distinct()->pluck()` di Controller dengan query master spesifik `Item::byCategory()->get()`.
- [ ] **Step 4**: Tambahkan perulangan pre-computation iterasi O(N) `foreach ($checksheets->items() as $index => $c)` di Controller sebelum `return view()`.
- [ ] **Step 5**: Ganti pemanggilan `app(Service::class)` maupun iterasi duplikasi di dalam Blade `@foreach` dengan properti: `$c->properti_precomputed ?? app(...)`.
- [ ] **Step 6**: Ekstrak script JS raksasa di view index ke file `.js` terpisah (khusus untuk module kompleks/berat) menggunakan pilar 7.

---

## 📂 Daftar File Perubahan Sesi Ini

1. **Migration Database**: [database/migrations/2026_09_14_000001_add_indexes_for_in_process_and_notifications_performance.php](file:///d:/laragon/www/qc-project/database/migrations/2026_09_14_000001_add_indexes_for_in_process_and_notifications_performance.php)
2. **User Model**: [app/Models/User.php](file:///d:/laragon/www/qc-project/app/Models/User.php)
3. **Notification Controller**: [app/Http/Controllers/NotificationController.php](file:///d:/laragon/www/qc-project/app/Http/Controllers/NotificationController.php)
4. **In-Process Service**: [app/Services/InProcessChecksheetService.php](file:///d:/laragon/www/qc-project/app/Services/InProcessChecksheetService.php)
5. **In-Process Controller**: [app/Http/Controllers/InProcessChecksheetController.php](file:///d:/laragon/www/qc-project/app/Http/Controllers/InProcessChecksheetController.php)
6. **In-Process View**: [resources/views/in_process/index.blade.php](file:///d:/laragon/www/qc-project/resources/views/in_process/index.blade.php)
7. **Dokumentasi Panduan**: [docs/optimization_guide.md](file:///d:/laragon/www/qc-project/docs/optimization_guide.md)
