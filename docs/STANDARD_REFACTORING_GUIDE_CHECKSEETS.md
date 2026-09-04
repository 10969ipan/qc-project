# Panduan Standar Refactoring & Implementasi Checksheet QC

Dokumen ini berisi standar arsitektur, potongan kode (code snippets), dan petunjuk penerapan langkah-demi-langkah dari hasil refactoring modul Incoming (Parts, Material, Sub-Part, Export, Chemical). Panduan ini dirancang untuk diterapkan pada modul berikutnya:
1. **Sub-Assy Checksheet** (`sub_assy`)
2. **In-Process Checksheet** (`in_process`)
3. **Plating Checksheet** (`plating`)
4. **Painting Checksheet** (`painting`)
5. **Sortir Checksheet** (`sortir`)

---

## 1. Standar Restorasi 4 Peran Approval

### 1.1 Urutan dan Penamaan Peran (Role Order)
Setiap modul checksheet wajib mendukung 4 tingkat persetujuan secara berurutan:
- `kashift` -> **Kepala Regu (KR)** untuk Plant Jakarta, **Kashift QC** untuk Plant Karawang / Lainnya.
- `supervisor` -> **Supervisor QC (SPV)**
- `asst_manager` -> **Asst Manager QC (AM)**
- `manager` -> **Manager QC (MGR)**

### 1.2 Header Tabel (`<thead>`)
```blade
<th colspan="4" class="align-middle">APPROVAL STATUS</th>
...
<tr class="text-center">
    <th style="font-size: 10px; min-width: 120px;">{{ $plantCode === 'jakarta' ? 'Kepala Regu' : 'Kashift QC' }}</th>
    <th style="font-size: 10px; min-width: 120px;">Supervisor QC</th>
    <th style="font-size: 10px; min-width: 120px;">Asst Manager QC</th>
    <th style="font-size: 10px; min-width: 120px;">Manager QC</th>
</tr>
```

### 1.3 Isi Baris Tabel (`<tbody>`)
Gunakan perulangan `@foreach ($approvalOrder as $role)` untuk menjamin presisi 4 kolom dan menghindari kesalahan pergeseran kolom:
```blade
@if(request('view_mode') !== 'verifikasi')
    {{-- Unified Approval Columns (4 Roles) --}}
    @foreach ($approvalOrder as $role)
        @php
            $field = getApprovalField($role);
            $dateField = getApprovalDateField($role);
            $status = $cs->$field;
            $date = $cs->$dateField;
        @endphp
        <td class="align-middle text-center" style="white-space: nowrap; min-width: 120px;">
            @if($status === 'REJECTED')
                <span class="badge badge-danger px-2 py-1" style="font-size: 0.65rem;" data-toggle="tooltip" title="{{ $cs->rejection_remarks }}">
                    <i class="fas fa-times-circle mr-1"></i> REJECTED
                </span>
                <div class="text-muted mt-1" style="font-size: 0.62rem; line-height: 1.2;">
                    <div>oleh {{ getRejectorName($cs->rejection_remarks) }}</div>
                    @if($date)
                        <div>{{ \Carbon\Carbon::parse($date)->format('d/m/Y H:i') }}</div>
                    @endif
                </div>
            @elseif($status && $status !== 'Pending')
                <span class="badge badge-success px-2 py-1" style="font-size: 0.65rem;">
                    <i class="fas fa-check-circle mr-1"></i> APPROVED
                </span>
                <div class="text-muted mt-1" style="font-size: 0.62rem; line-height: 1.2;">
                    <div>oleh {{ $status }}</div>
                    @if($date)
                        <div>{{ \Carbon\Carbon::parse($date)->format('d/m/Y H:i') }}</div>
                    @endif
                </div>
            @else
                <span class="badge badge-warning text-dark px-2 py-1" style="font-size: 0.65rem;">
                    <i class="fas fa-clock mr-1"></i> PENDING
                </span>
            @endif
        </td>
    @endforeach
@endif
```

---

## 2. Pengamanan Top `@php` Block & Variabel Permisi

Untuk menghindari error `Undefined variable $canEdit`, `$canDelete`, `$canExport`, atau `$approvalOrder`, setiap file `index.blade.php` wajib memiliki blok `@php` terpusat di bagian atas:

```blade
@php
    $plant = request('plant') ?? auth()->user()->plant_id;
    $plantCode = (is_string($plant) && strlen($plant) > 30) ? \App\Models\Plant::where('id', $plant)->value('code') : (string) $plant;
    $plantCode = strtolower($plantCode ?: 'karawang');

    // Fallback order approval 4 role
    $approvalOrder = $approvalOrder ?? ['kashift', 'supervisor', 'asst_manager', 'manager'];

    // Resolve permisi via AppMenu
    $currentMenu = \App\Models\AppMenu::where('route', 'NAMA_ROUTE_INDEX_MODUL')->first();
    $menuId = $currentMenu ? $currentMenu->id : null;
    $canExport = $menuId ? auth()->user()->hasPermission($menuId, 'export') : true;
    $canEdit   = $menuId ? auth()->user()->hasPermission($menuId, 'edit') : true;
    $canDelete = $menuId ? auth()->user()->hasPermission($menuId, 'delete') : true;

    // Document Header dari Master Setting
    $docHeader = $docHeader ?? \App\Models\GeneralSetting::getDocHeader('KEY_MODUL', $plantCode, [
        'no_dokumen' => 'QC-XXX-F-XXXX',
        'tgl_terbit' => '01/01/2026',
        'revisi'     => '-',
        'halaman'    => '- / -'
    ]);
@endphp
```

---

## 3. Query Caching Per Plant & Cache Invalidation

### 3.1 Caching di Controller
Gunakan `Cache::remember` yang menyertakan `plantId` dalam `cacheKey` untuk mengisolasi data master (Item/Supplier/Customer) antar plant:

```php
public function index(Request $request)
{
    $plantFilter = $request->get('plant', auth()->user()->plant_id);
    $plantId = Plant::resolveId($plantFilter);

    $filters = $request->only(['id', 'plant', 'start_date', 'end_date', 'approval_status', 'item_id', 'supplier', 'start_tgl_datang', 'end_tgl_datang']);
    $checksheets = $this->checksheetService->getFilteredChecksheets($filters);

    $cacheKey = "NAMA_MODUL_filters_" . md5(json_encode([$plantId]));
    $cachedData = Cache::remember($cacheKey, 1800, function() use ($plantId) {
        $items = Item::byCategory('KATEGORI_ITEM')->where('plant_id', $plantId)->orderBy('name')->get();
        $suppliers = $items->pluck('customer')->filter()->unique()->sort()->values();
        return compact('items', 'suppliers');
    });

    $items = $cachedData['items'];
    $suppliers = $cachedData['suppliers'];

    $approvalOrder = ['kashift', 'supervisor', 'asst_manager', 'manager'];
    $docHeader = GeneralSetting::getDocHeader('KEY_MODUL', $plantFilter, [ ... ]);

    return view('MODUL.index', compact('checksheets', 'items', 'suppliers', 'approvalOrder', 'docHeader'));
}
```

### 3.2 Invalidation di Service Layer
Setiap fungsi `createChecksheet` dan `updateChecksheet` pada Service Layer wajib menghapus cache terkait:

```php
// Pada Service Class (misal: SubAssyChecksheetService.php)
DB::commit();
Cache::forget("NAMA_MODUL_filters_" . md5(json_encode([$checksheet->plant_id])));
```

---

## 4. Efisiensi Penggabungan Kolom (Consolidated Columns)

### 4.1 Kolom Informasi Pemeriksaan (Checked)
Gabungkan Tanggal Check, Shift, dan Inisial Operator dalam 1 sel:
```blade
<td class="align-middle text-nowrap font-weight-bold" style="font-size: 0.70rem;">
    {{ date('d-m-Y', strtotime($cs->date)) }} / {{ $cs->shift ?? '1' }} / {{ $cs->operator_initials ?? '-' }}
</td>
```

### 4.2 Kolom Waktu Check & Cycle Time
```blade
@php
    $sec = (int) ($cs->cycle_time ?? 0);
    $ctStr = ($sec > 0) ? (($sec < 60) ? ($sec . 's') : (floor($sec / 60) . 'm' . (($sec % 60 > 0) ? ' ' . ($sec % 60) . 's' : ''))) : '-';
@endphp
<td class="align-middle text-nowrap">
    {{ $cs->created_at ? $cs->created_at->copy()->subSeconds($sec)->format('H:i') : '-' }} - {{ $cs->created_at ? $cs->created_at->format('H:i') : '-' }} <span class="text-muted">({{ $ctStr }})</span>
</td>
```

### 4.3 Penggabungan Item Name & Supplier (Jika Tidak Ada Part No)
Untuk modul tanpa `Part No` (seperti Material & Chemical):
```blade
<td class="align-middle text-left text-nowrap">
    <span class="font-weight-bold text-gray-800">{{ $cs->item->name ?? '-' }}</span><br>
    <small class="text-muted">{{ $cs->item->customer ?? '-' }}</small>
</td>
```

---

## 5. Layout Auto-Responsive Detail NG

Gunakan tampilan tanpa padding tinggi agar tidak merusak tinggi baris tabel saat terdapat banyak item NG:

```blade
@if(request('view_mode') !== 'verifikasi')
    <td class="align-middle text-center" style="width: 45px; min-width: 45px; padding: 2px 4px !important;">
        @if(count($pcsLines) > 0)
            <span class="text-danger font-weight-bold" style="font-size: 0.68rem; line-height: 1.1; display: block;">{!! implode('<br>', $pcsLines) !!}</span>
        @else
            -
        @endif
    </td>
    <td class="align-middle text-center text-nowrap" style="min-width: 70px; padding: 2px 4px !important;">
        @if(count($nameLines) > 0)
            <span class="text-danger font-weight-bold" style="font-size: 0.68rem; line-height: 1.1; display: block;">{!! implode('<br>', $nameLines) !!}</span>
        @else
            -
        @endif
    </td>
@endif
```

---

## 6. Standar Cetak Full Black Monochrome (`print.blade.php`)

Gunakan CSS berikut di setiap tampilan `print.blade.php` untuk memastikan hasil cetak 100% hitam pekat (`#000`), bebas warna abu-abu, dan terpotong rapi dalam mode Landscape A4:

```html
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Checksheet - {{ $plantName }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 5mm;
        }
        body {
            font-family: Arial, sans-serif;
            color: #000 !important;
            background: #fff !important;
            font-size: 9px;
            margin: 0;
            padding: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            color: #000 !important;
        }
        th, td {
            border: 1px solid #000 !important;
            padding: 3px 4px;
            color: #000 !important;
            text-align: center;
            vertical-align: middle;
        }
        th {
            background-color: #f2f2f2 !important;
            font-weight: bold;
            text-transform: uppercase;
            -webkit-print-color-adjust: exact;
        }
        .text-left { text-align: left !important; }
        .font-weight-bold { font-weight: bold !important; }
        .no-print { display: none !important; }

        @media print {
            body { -webkit-print-color-adjust: exact; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body onload="window.print()">
    <!-- Header Dokumen Formal & Tabel Data Full Black -->
</body>
</html>
```

---

## 7. Checklist Implementasi per Modul

### 7.1 Sub-Assy (`sub_assy`)
- [ ] Tambahkan 4 role approval pada view `resources/views/sub_assy/index.blade.php`.
- [ ] Terapkan `Cache::remember` per `plantId` di `SubAssyChecksheetController.php`.
- [ ] Tambahkan `Cache::forget` di `SubAssyChecksheetService.php`.
- [ ] Tambahkan route `/report/sub-assy/print` & buat `resources/views/sub_assy/print.blade.php`.

### 7.2 In-Process (`in_process`)
- [ ] Verifikasi ketersediaan 4 role approval di view `in_process/index.blade.php`.
- [ ] Terapkan `Cache::remember` per `plantId` di `InProcessChecksheetController.php`.
- [ ] Tambahkan `Cache::forget` di `InProcessChecksheetService.php`.
- [ ] Pastikan tampilan print `in_process/print.blade.php` 100% full black `#000`.

### 7.3 Plating (`plating`)
- [ ] Sesuaikan header `<thead>` & loop `@foreach($approvalOrder)` 4 role di `plating/index.blade.php`.
- [ ] Terapkan `Cache::remember` per `plantId` di `PlatingChecksheetController.php`.
- [ ] Tambahkan `Cache::forget` di `PlatingChecksheetService.php`.
- [ ] Buat / perbarui `plating/print.blade.php` mode landscape full black.

### 7.4 Painting (`painting`)
- [ ] Restorasi 4 role approval di `painting/index.blade.php`.
- [ ] Terapkan `Cache::remember` per `plantId` di `PaintingChecksheetController.php`.
- [ ] Tambahkan `Cache::forget` di `PaintingChecksheetService.php`.
- [ ] Buat / perbarui `painting/print.blade.php` mode landscape full black.

### 7.5 Sortir (`sortir`)
- [ ] Restorasi 4 role approval di `sortir/index.blade.php`.
- [ ] Terapkan `Cache::remember` per `plantId` di `SortirChecksheetController.php`.
- [ ] Tambahkan `Cache::forget` di `SortirChecksheetService.php`.
- [ ] Perbarui `sortir/print.blade.php` mode landscape full black.
