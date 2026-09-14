# Panduan Optimalisasi Performa Halaman & Query (Optimization Blueprint)

Dokumen ini berisi standar dan pola optimalisasi (*optimization blueprint*) yang diterapkan pada menu **Checksheet In-Process** dan **Notification API**. Pola ini dapat diterapkan secara bertahap pada menu-menu lain di aplikasi (misalnya: *Plating, Cross Cut, Sortir, Painting, Incoming Parts*, dll.).

---

## 🚀 5 Pilar Utama Optimalisasi

```
[1. Database Indexing] ──► [2. Permission Memory-Cache] ──► [3. Eliminasi CAST/JSON_EXTRACT] ──► [4. Controller Pre-Computation] ──► [5. Blade Cleanup]
```

---

### Pilar 1: Database Indexing & Virtual Column (Safe & Non-Destructive)

#### Masalah:
Query filter berulang pada kolom `plant_id`, `date`, `created_at`, `entry_method`, atau `scan_method` yang tidak memiliki *Composite Index* memaksa MySQL membaca seluruh baris data (*Full Table Scan*).

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
        if (Schema::hasTable('nama_tabel')) {
            $indexes1 = DB::select("SHOW INDEX FROM nama_tabel WHERE Key_name = 'idx_tabel_plant_date'");
            if (empty($indexes1)) {
                DB::statement("ALTER TABLE nama_tabel ADD INDEX idx_tabel_plant_date (plant_id, date, created_at)");
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('nama_tabel')) {
            $indexes1 = DB::select("SHOW INDEX FROM nama_tabel WHERE Key_name = 'idx_tabel_plant_date'");
            if (!empty($indexes1)) {
                DB::statement("ALTER TABLE nama_tabel DROP INDEX idx_tabel_plant_date");
            }
        }
    }
};
```

---

### Pilar 2: In-Memory Request Caching untuk Permissions (`User.php`)

#### Masalah:
Metode `auth()->user()->hasPermission($menuId, $action)` dipanggil belasan hingga puluhan kali dalam 1 siklus render halaman Blade untuk mengecek tombol *Export, Edit, Delete*. Hal ini memicu puluhan query SQL yang sama persis (`SELECT * FROM user_permissions...`).

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

### Pilar 3: Optimalisasi Query JSON & REGEXP

#### Masalah:
- `JSON_EXTRACT(data, '$.plant_id')` di klausa WHERE memicu *Full Table Scan*.
- `CAST(kolom_text AS CHAR) REGEXP` mematikan query optimizer engine MySQL.

#### Solusi:
- Gunakan virtual generated column `notif_plant_id` yang ter-indeks jika menyaring data JSON.
- Gunakan `kolom REGEXP '[0-9]'` langsung tanpa pembungkus `CAST(... AS CHAR)`.

```php
// Contoh pada NotificationController.php
$hasNotifPlantCol = Schema::hasColumn('notifications', 'notif_plant_id');
if ($user->role !== 'admin') {
    $query->where(function ($q) use ($user, $hasNotifPlantCol) {
        if ($hasNotifPlantCol) {
            $q->where('notif_plant_id', $user->plant_id)
                ->orWhereNull('notif_plant_id');
        } else {
            $q->whereRaw("JSON_EXTRACT(data, '$.plant_id') = ?", [$user->plant_id])
                ->orWhereRaw("JSON_EXTRACT(data, '$.plant_id') IS NULL");
        }
    });
}
```

---

### Pilar 4: Pre-computation di Level Controller

#### Masalah:
Memanggil method Service kompleks (seperti kalkulasi NG dimensi / no-dimension row) di dalam `@foreach` tabel Blade menyebabkan instansiasi service dan penafsiran JSON berulang kali di layer view.

#### Solusi:
Hitung status/flag helper di Controller **hanya untuk data paginasi aktif** (misal 10-20 baris) sebelum dikirim ke Blade view.

```php
// app/Http/Controllers/NamaController.php

$checksheets = $this->service->getFilteredChecksheets($filters);

// Pre-compute status baris hanya untuk item di halaman aktif
foreach ($checksheets->items() as $item) {
    $item->is_dimension_ng = $this->service->isDimensionNg($item, $standards);
    $item->is_no_dimension_row = $this->service->isNoDimensionRow($item);
}

return view('nama_menu.index', compact('checksheets', ...));
```

---

### Pilar 5: Cleanup Blade View

#### Solusi:
Gunakan properti pre-computed di Blade dengan *fallback safe operator* (`??`).

```blade
{{-- resources/views/nama_menu/index.blade.php --}}

@foreach($checksheets as $checksheet)
    @php
        $isDimensionNgRow = $checksheet->is_dimension_ng ?? app(InProcessChecksheetService::class)->isDimensionNg($checksheet, $standards);
        $isNoDimensionRow = $checksheet->is_no_dimension_row ?? app(InProcessChecksheetService::class)->isNoDimensionRow($checksheet);
        $isRowHidden = $isHiddenItem || ($hideNgRows == '1' && $isDimensionNgRow) || ($hideNoDimensionRows == '1' && $isNoDimensionRow);
    @endphp
    <tr class="{{ $isRowHidden ? 'bg-light-hidden' : '' }}">
        ...
    </tr>
@endforeach
```

---

## 📂 Daftar File Perubahan Sesi Ini

1. **Migration Database**: [database/migrations/2026_09_14_000001_add_indexes_for_in_process_and_notifications_performance.php](file:///d:/laragon/www/qc-project/database/migrations/2026_09_14_000001_add_indexes_for_in_process_and_notifications_performance.php)
2. **User Model**: [app/Models/User.php](file:///d:/laragon/www/qc-project/app/Models/User.php)
3. **Notification Controller**: [app/Http/Controllers/NotificationController.php](file:///d:/laragon/www/qc-project/app/Http/Controllers/NotificationController.php)
4. **In-Process Service**: [app/Services/InProcessChecksheetService.php](file:///d:/laragon/www/qc-project/app/Services/InProcessChecksheetService.php)
5. **In-Process Controller**: [app/Http/Controllers/InProcessChecksheetController.php](file:///d:/laragon/www/qc-project/app/Http/Controllers/InProcessChecksheetController.php)
6. **In-Process View**: [resources/views/in_process/index.blade.php](file:///d:/laragon/www/qc-project/resources/views/in_process/index.blade.php)
7. **Dokumentasi Panduan**: [docs/optimization_guide.md](file:///d:/laragon/www/qc-project/docs/optimization_guide.md)
