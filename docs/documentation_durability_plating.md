# Documentation: Modul Durability Plating (Standard Performance Test)

Halo! Ini dokumentasi praktis buat modul **Durability Plating (Standard Performance Test)**. Dokumentasi ini dibuat biar siapa pun dev yang baru megang modul ini bisa langsung paham alur kerja, skema DB, dan contoh kodenya dari A sampai Z.

---

## Ringkasan Singkat (TL;DR)

Modul **Durability Plating** dipake buat nyatet & ngelaporin hasil 5 jenis pengujian fisik lab pada sampel plating:
1. **Thickness** (Ketebalan lapisan Nickel/Ni, Chrome/Cr, Copper/Cu)
2. **Corrodkote** (Uji korosi larutan)
3. **CASS** (Copper-Accelerated Salt Spray test)
4. **Salt Spray** (Uji semprot garam)
5. **Porecount** (Hitung jumlah pori-pori/porous)

Fitur unik di modul ini:
- **2 Tahap Pengecekan (Data 1 / Produksi & Data 2 / Trial)**: Sekali simpan (`store`), sistem langsung buat 2 record berpasangan sekaligus (Data 1 `is_trial = false` dan Data 2 Trial `is_trial = true` yang terhubung via `data1_id`).
- **Approval 4 Tingkat**: Harus disetujui oleh Supervisor QC, Supervisor Plating, Asst Manager QC, dan Asst Manager Plating.
- **3 Slot Foto Evidence**: `BEFORE TEST` (kondisi awal), `AFTER TEST Data 1`, dan `AFTER TEST Data 2 (Trial)`.
- **Link Thickness Otomatis**: Kolom Thickness di tabel tes lain cuma nampilin link data kalau data ketebalan fisik memang diisi. Kalau kosong, nampilin teks `"tidak ada data"`.
- **Filter Autocomplete Dynamic**: Dropdown filter part cuma nampilin barang yang emang punya riwayat pengujian di jenis tes itu.

---

## File-File yang Dipakai (Struktur Kode)

Ini lokasi file utama modul Durability Plating:

- **Model Master**: `app/Models/StandardPerformanceTest.php`
- **Model Transaksi**: `app/Models/DurabilityThicknessReport.php`
- **Controller**: `app/Http/Controllers/DurabilityThicknessReportController.php`
- **Service Layer**: `app/Services/DurabilityThicknessReportService.php`
- **Blade Views (Tampilan)**:
  - Laporan Thickness: `resources/views/durability/thickness/index.blade.php`
  - Laporan Corrodkote: `resources/views/durability/corrodkote/index.blade.php`
  - Laporan CASS: `resources/views/durability/cass/index.blade.php`
  - Laporan Salt Spray: `resources/views/durability/salt_spray/index.blade.php`
  - Laporan Porecount: `resources/views/durability/porecount/index.blade.php`
  - Master Specs: `resources/views/durability/standards/index.blade.php`

---

## Database & Kolom Utama (`durability_thickness_reports`)

Semua 5 jenis tes disimpen di **satu baris tabel** (`durability_thickness_reports`):

| Nama Kolom | Tipe Data | Penjelasan Singkat |
| :--- | :--- | :--- |
| `id` | `bigint` | ID Laporan |
| `data1_id` | `bigint` | Link ID Data 1 (Khusus buat Data 2 Trial) |
| `is_trial` | `boolean` | `false` = Data 1 (Produksi), `true` = Data 2 (Trial) |
| `standard_performance_test_id` | `bigint` | Foreign Key ke Master Standar Test |
| `part_name` / `part_number` | `varchar` | Nama part & Nomor part |
| `actual_ni` / `actual_cr` / `actual_cu` | `varchar` | Hasil ukur ketebalan Ni, Cr, Cu |
| `corrodkote_actual` / `corrodkote_hours` | `varchar` | Hasil & waktu chamber Corrodkote |
| `cass_actual` / `cass_hours` | `varchar` | Hasil & waktu chamber CASS |
| `salt_spray_actual` / `salt_spray_hours` | `varchar` | Hasil & waktu chamber Salt Spray |
| `porecount_actual` / `porecount_hours` | `varchar` | Hasil & waktu chamber Porecount |
| `evidence_before` | `text` | Path foto sebelum diuji |
| `evidence_after` | `text` | Path foto sesudah diuji (Data 1) |
| `evidence_after_trial` | `text` | Path foto sesudah diuji (Data 2 Trial) |
| `supervisor_qc` / `supervisor_plating` | `varchar` | Status Approval Supervisor QC & Plating |
| `asst_manager_qc` / `asst_manager_plating` | `varchar` | Status Approval Asst Manager QC & Plating |

---

## Contoh Kode & Alur Kodenya

### 1. Sekali Simpan Langsung Bikin 2 Data (Data 1 & Data 2 Trial)
Di `DurabilityThicknessReportService.php`, saat fungsi store dipanggil, sistem bikin 2 record berpasangan:

Contoh Kode Store Service (`app/Services/DurabilityThicknessReportService.php`):
```php
public function createReport(array $data): DurabilityThicknessReport
{
    DB::beginTransaction();
    try {
        // 1. Simpan Data 1 (Pengujian Utama Produksi)
        $report1 = DurabilityThicknessReport::create(array_merge($data, [
            'is_trial' => false,
            'data1_id' => null
        ]));

        // 2. Simpan Data 2 (Pengujian Trial Pembanding) yang terhubung ke Data 1
        $dataTrial = $data;
        $dataTrial['is_trial'] = true;
        $dataTrial['data1_id'] = $report1->id;
        $report2 = DurabilityThicknessReport::create($dataTrial);

        DB::commit();
        return $report1;
    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
}
```

### 2. Evaluasi Link Thickness Otomatis
Kolom ketebalan di tabel non-thickness cuma nampilin link data kalau data ketebalan memang terisi.

Contoh Kode Evaluasi Blade (`resources/views/durability/corrodkote/index.blade.php`):
```blade
@php
    $hasThickness = !empty($report->actual_ni) || !empty($report->actual_cr) || !empty($report->actual_cu);
@endphp

<td>
    @if($hasThickness)
        <a href="{{ route('durability.thickness.index', ['search' => $report->part_number]) }}" class="badge badge-info">
            <i class="fas fa-external-link-alt"></i> Data
        </a>
    @else
        <span class="text-muted small">tidak ada data</span>
    @endif
</td>
```

### 3. Hapus Parsial Tes (`clearTestData`)
Kalau user ngapus tes Corrodkote, baris di DB gak dihapus penuh kalau masih ada data tes lain.

Contoh Kode Clear Test (`app/Services/DurabilityThicknessReportService.php`):
```php
public function clearTestData(DurabilityThicknessReport $report, string $testType): void
{
    // Restrukturisasi field khusus tes tertentu menjadi null
    if ($testType === 'corrodkote') {
        $report->corrodkote_actual = null;
        $report->corrodkote_hours = null;
    }
    
    // Cek apakah seluruh 5 jenis tes sudah kosong
    if ($report->isAllTestsEmpty()) {
        $report->delete(); // Hapus permanen dari DB jika kosong total
    } else {
        $report->save(); // Simpan perubahan jika masih ada tes lain
    }
}
```

---

## Checklist Perintah Pemeliharaan

```bash
# Clear Cache Laravel
php artisan config:clear
php artisan view:clear
php artisan cache:clear

# Cek Syntax PHP Controller & Service
php -l app/Services/DurabilityThicknessReportService.php
php -l app/Http/Controllers/DurabilityThicknessReportController.php
```

---
*Dokumentasi ini dibuat oleh Irfan (Service Quality) — diperbarui 27 Agustus 2026.*
