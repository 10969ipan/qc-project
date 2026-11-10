<?php

namespace App\Services;

use App\Models\DandoryChecksheet;
use App\Models\DandoryItem;
use App\Models\DandoryOperatorConfig;
use App\Models\GeneralSetting;
use App\Models\Item;
use App\Models\User;
use App\Models\Plant;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class DandoryChecksheetService
{
    /**
     * Ambil data transaksi Dandory terfilter dengan eager loading
     * ponytail: lean eloquent query without N+1
     */
    public function getFilteredChecksheets(array $filters, ?int $perPage = null)
    {
        $query = DandoryChecksheet::with(['operator:id,name,initials', 'item:id,name,part_number,sap_code'])
            ->where('plant_code', $filters['plant_code'] ?? 'karawang');

        if (!empty($filters['operator_id'])) {
            $query->where('operator_id', $filters['operator_id']);
        }

        if (!empty($filters['table_no'])) {
            $query->where('table_no', $filters['table_no']);
        }

        if (!empty($filters['shift'])) {
            $query->where('shift', $filters['shift']);
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('check_date', [$filters['start_date'], $filters['end_date']]);
        } elseif (!empty($filters['start_date'])) {
            $query->whereDate('check_date', '>=', $filters['start_date']);
        } elseif (!empty($filters['end_date'])) {
            $query->whereDate('check_date', '<=', $filters['end_date']);
        } elseif (!empty($filters['date'])) {
            $query->whereDate('check_date', $filters['date']);
        } elseif (!empty($filters['month']) && !empty($filters['year'])) {
            $query->whereYear('check_date', $filters['year'])
                  ->whereMonth('check_date', $filters['month']);
        }

        $query->orderBy('check_date', 'desc')
              ->orderBy('created_at', 'desc');

        if ($perPage) {
            return $query->paginate($perPage)->withQueryString();
        }

        return $query->get();
    }

    /**
     * Ambil master parameter standar Dandory (9 Awal & 10 Akhir)
     */
    public function getMasterItems()
    {
        return Cache::remember('dandory_master_items_active', 3600, function () {
            return DandoryItem::active()->orderBy('order_no')->get();
        });
    }

    /**
     * Ambil daftar operator/inspector aktif per plant
     */
    public function getPlantOperators(string $plantCode)
    {
        $plant = Plant::whereRaw('LOWER(code) = ?', [strtolower($plantCode)])->first();

        $query = User::whereIn('role', ['operator', 'inspector'])->where('is_active', true);
        if ($plant) {
            $query->where('plant_id', $plant->id);
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Ambil daftar meja per plant dari GeneralSetting
     * ponytail: fallback arrays avoid empty state
     */
    public function getPlantTables(string $plantCode): array
    {
        $key = 'dandory_tables_' . strtolower($plantCode);
        $defaults = strtolower($plantCode) === 'jakarta'
            ? ['Meja 1', 'Meja 2', 'Meja 3', 'Meja 4', 'Meja 5', 'Meja 6', 'Meja 7', 'Meja 8']
            : ['Meja 1', 'Meja 2', 'Meja 3', 'Meja 4', 'Meja 5', 'Meja 6', 'Meja 7', 'Meja 8', 'Meja 9', 'Meja 10'];

        $setting = GeneralSetting::getValue($key, $defaults);
        return is_array($setting) ? $setting : $defaults;
    }

    /**
     * Simpan daftar meja per plant ke GeneralSetting
     */
    public function savePlantTables(string $plantCode, array $tables): void
    {
        $key = 'dandory_tables_' . strtolower($plantCode);
        GeneralSetting::updateOrCreate(
            ['key' => $key],
            [
                'plant_code'  => strtolower($plantCode),
                'category'    => 'dandory',
                'value'       => json_encode(array_values(array_filter(array_map('trim', $tables)))),
                'description' => 'Daftar nama meja inspeksi untuk Dandory Quality Plant ' . ucfirst($plantCode),
            ]
        );
        Cache::forget('general_settings_' . $key);
    }

    /**
     * Cek apakah posisi/bagian operator berbasis mesin (khusus Inspector Inproses)
     */
    public function isMachineBasedBagian(?string $bagian): bool
    {
        if (empty($bagian)) {
            return false;
        }
        $b = strtolower(trim($bagian));
        return str_contains($b, 'inproses') || str_contains($b, 'in process') || str_contains($b, 'in-process');
    }

    /**
     * Format penamaan Meja/Mesin secara dinamis sesuai tipe bagian operator.
     * Jika posisi operator atau catatan jabatan berbasis mesin (Inspector Inproses),
     * teks 'Meja X' otomatis diubah menjadi 'Mesin X' pada tampilan.
     */
    public function formatLocationNo(?string $tableNo, bool $isMachineBased = false, ?string $jabatan = null): string
    {
        $raw = trim($tableNo ?? '');
        if ($raw === '' || $raw === '-') {
            return '-';
        }

        $isMachine = $isMachineBased || $this->isMachineBasedBagian($jabatan);

        if ($isMachine) {
            if (preg_match('/^meja\s+(\d+.*)$/i', $raw, $matches)) {
                $suffix = trim($matches[1]);
                return $suffix !== '' ? "Mesin {$suffix}" : 'Mesin';
            }
            if (is_numeric($raw)) {
                return "Mesin {$raw}";
            }
            return $raw;
        }

        if (preg_match('/^mesin\s+(\d+.*)$/i', $raw, $matches)) {
            $suffix = trim($matches[1]);
            return $suffix !== '' ? "Meja {$suffix}" : 'Meja';
        }
        if (is_numeric($raw)) {
            return "Meja {$raw}";
        }
        return $raw;
    }

    /**
     * Ambil daftar mesin per plant (otomatis dari checksheet in_process atau fallback)
     */
    public function getPlantMachines(string $plantCode): array
    {
        $plant = Plant::whereRaw('LOWER(code) = ?', [strtolower($plantCode)])->first();
        $plantId = $plant ? $plant->id : null;

        $rawMachines = Cache::remember("dandory_plant_machines_{$plantCode}", 1800, function () use ($plantId) {
            return DB::table('in_process_checksheets')
                ->when($plantId, function ($q) use ($plantId) {
                    $q->where('plant_id', $plantId);
                })
                ->whereNotNull('code_machine')
                ->where('code_machine', '!=', '')
                ->where('code_machine', '!=', '-')
                ->distinct()
                ->pluck('code_machine')
                ->values();
        });

        if ($rawMachines->isEmpty()) {
            $rawMachines = collect(range(1, 25))->map(fn($n) => (string) $n);
        }

        $normalized = $rawMachines->map(function ($m) {
            $trimmed = trim($m);
            if (is_numeric($trimmed)) {
                return (string) intval($trimmed);
            }
            return $trimmed;
        })->unique()->values();

        $sorted = $normalized->sort(function ($a, $b) {
            $aIsNum = is_numeric($a);
            $bIsNum = is_numeric($b);
            if ($aIsNum && $bIsNum) {
                return (int)$a <=> (int)$b;
            }
            if ($aIsNum) return -1;
            if ($bIsNum) return 1;
            return strnatcasecmp($a, $b);
        })->values();

        return $sorted->map(function ($m) {
            if (str_starts_with(strtoupper($m), 'MESIN')) {
                return $m;
            }
            return 'Mesin ' . $m;
        })->unique()->values()->toArray();
    }

    /**
     * Ambil posisi/bagian operator langsung dari modul Kepatuhan Operator
     * (Mengutamakan data jadwal/checksheet kepatuhan operator)
     */
    public function getOperatorBagianFromCompliance(int $operatorId): string
    {
        // 1. Ambil dari jadwal kepatuhan operator terbaru yang terdaftar
        $schedule = \App\Models\OperatorComplianceSchedule::where('operator_id', $operatorId)
            ->whereNotNull('bagian')
            ->where('bagian', '!=', '')
            ->latest('schedule_date')
            ->first();

        if ($schedule && !empty($schedule->bagian)) {
            return $schedule->bagian;
        }

        // 2. Ambil dari checksheet kepatuhan operator terbaru
        $checksheet = \App\Models\OperatorComplianceChecksheet::where('user_id', $operatorId)
            ->whereNotNull('bagian')
            ->where('bagian', '!=', '')
            ->latest('id')
            ->first();

        if ($checksheet && !empty($checksheet->bagian)) {
            return $checksheet->bagian;
        }

        return 'Quality Control';
    }

    /**
     * Ambil atau buat konfigurasi posisi/bagian operator & kategori part
     */
    public function getOperatorConfig(string $plantCode, int $operatorId)
    {
        $config = DandoryOperatorConfig::where('plant_code', strtolower($plantCode))
            ->where('operator_id', $operatorId)
            ->first();

        $complianceBagian = $this->getOperatorBagianFromCompliance($operatorId);

        if (!$config) {
            // Cek apakah ada operator lain di plant ini dengan bagian sama yang sudah menyimpan kategori
            $existingSameBagian = DandoryOperatorConfig::where('plant_code', strtolower($plantCode))
                ->where('bagian', $complianceBagian)
                ->whereNotNull('allowed_categories')
                ->latest('updated_at')
                ->first();

            $config = new DandoryOperatorConfig([
                'plant_code'         => strtolower($plantCode),
                'operator_id'        => $operatorId,
                'bagian'             => $complianceBagian,
                'default_table'      => 'Meja 1',
                'allowed_categories' => ($existingSameBagian && is_array($existingSameBagian->allowed_categories))
                    ? $existingSameBagian->allowed_categories
                    : [],
            ]);
        } else {
            // Jika bagian di config masih kosong, isi dengan bagian kepatuhan operator
            if (empty($config->bagian)) {
                $config->bagian = $complianceBagian;
            }
            // Jika kategori masih kosong, warisi dari operator lain dengan bagian yang sama
            if (empty($config->allowed_categories) || count((array) $config->allowed_categories) === 0) {
                $existingSameBagian = DandoryOperatorConfig::where('plant_code', strtolower($plantCode))
                    ->where('bagian', $config->bagian)
                    ->whereNotNull('allowed_categories')
                    ->latest('updated_at')
                    ->first();
                if ($existingSameBagian && is_array($existingSameBagian->allowed_categories) && count($existingSameBagian->allowed_categories) > 0) {
                    $config->allowed_categories = $existingSameBagian->allowed_categories;
                }
            }
        }

        return $config;
    }

    /**
     * Simpan konfigurasi posisi/bagian operator & kategori part yang diizinkan
     */
    public function saveOperatorConfig(string $plantCode, int $operatorId, array $data, int $updatedBy): DandoryOperatorConfig
    {
        return DandoryOperatorConfig::updateOrCreate(
            [
                'plant_code'  => strtolower($plantCode),
                'operator_id' => $operatorId,
            ],
            [
                'bagian'             => $data['bagian'] ?? 'Quality Control',
                'default_table'      => $data['default_table'] ?? null,
                'allowed_categories' => $data['allowed_categories'] ?? [],
                'updated_by'         => $updatedBy,
            ]
        );
    }

    /**
     * Ambil master items (Part) yang sesuai dengan posisi/kategori operator di plant ini
     * ponytail: fast direct query filtered by category ids if configured
     */
    public function getItemsForOperator(string $plantCode, int $operatorId)
    {
        $opConfig = DandoryOperatorConfig::where('plant_code', strtolower($plantCode))
            ->where('operator_id', $operatorId)
            ->first();

        // Jika user belum memilih bagian atau kategori, default part item = kosong (null/empty collection)
        if (!$opConfig || empty($opConfig->bagian) || empty($opConfig->allowed_categories)) {
            return collect([]);
        }

        $allowedCats = array_filter((array) $opConfig->allowed_categories);
        if (empty($allowedCats)) {
            return collect([]);
        }

        $plant = Plant::whereRaw('LOWER(code) = ?', [strtolower($plantCode)])->first();
        $plantId = $plant ? $plant->id : null;

        return Item::select(['id', 'name', 'part_number', 'sap_code', 'category_id', 'plant_id'])
            ->when($plantId, function ($q) use ($plantId) {
                $q->where(function ($sq) use ($plantId) {
                    $sq->where('plant_id', $plantId)->orWhereNull('plant_id');
                });
            })
            ->whereIn('category_id', $allowedCats)
            ->orderBy('name')
            ->get();
    }

    /**
     * Ambil smart preset (histori item '-' / NA pada part yang sama sebelumnya)
     * ponytail: 1-line history lookup
     */
    public function getLastSessionPartPreset(int $operatorId, ?string $itemId): array
    {
        if (!$itemId) return [];

        $lastRow = DandoryChecksheet::where('operator_id', $operatorId)
            ->where('item_id', $itemId)
            ->whereNotNull('awal_checks')
            ->latest('id')
            ->first();

        if (!$lastRow) return [];

        return [
            'awal_checks'  => $lastRow->awal_checks ?? [],
            'akhir_checks' => $lastRow->akhir_checks ?? [],
        ];
    }

    /**
     * Buat sesi transaksi baru (Awal Dandory)
     */
    public function storeAwal(array $data, int $userId): DandoryChecksheet
    {
        $item = !empty($data['item_id']) ? Item::find($data['item_id']) : null;
        $partName = $item ? $item->name : ($data['part_name'] ?? '-');
        $bagian = $data['jabatan'] ?? $this->getOperatorBagianFromCompliance((int)$data['operator_id']);

        $notesAwal = !empty($data['notes']) ? trim($data['notes']) : null;

        return DandoryChecksheet::create([
            'plant_code'        => strtolower($data['plant_code'] ?? 'karawang'),
            'check_date'        => $data['check_date'] ?? date('Y-m-d'),
            'shift'             => $data['shift'] ?? 'Shift 1',
            'table_no'          => $data['table_no'] ?? 'Meja 1',
            'operator_id'       => $data['operator_id'],
            'jabatan'           => $bagian,
            'item_id'           => $item?->id,
            'part_name'         => $partName,
            'awal_checks'       => $data['awal_checks'] ?? [],
            'akhir_checks'      => null,
            'awal_completed_at' => now(),
            'notes_awal'        => $notesAwal,
            'notes_akhir'       => null,
            'notes'             => $notesAwal,
            'created_by'        => $userId,
        ]);
    }

    /**
     * Selesaikan sesi transaksi (Akhir Dandory)
     */
    public function storeAkhir(DandoryChecksheet $checksheet, array $akhirChecks, ?string $notes = null): DandoryChecksheet
    {
        $notesAkhir = !empty($notes) ? trim($notes) : null;
        $notesAwal  = $checksheet->notes_awal ?: $checksheet->notes;

        // Gabungkan kedua catatan tanpa menghilangkan catatan awal
        $combinedNotes = null;
        if ($notesAwal && $notesAkhir) {
            $combinedNotes = "[Awal] " . $notesAwal . " | [Akhir] " . $notesAkhir;
        } elseif ($notesAwal) {
            $combinedNotes = $notesAwal;
        } elseif ($notesAkhir) {
            $combinedNotes = $notesAkhir;
        }

        $checksheet->update([
            'akhir_checks'       => $akhirChecks,
            'akhir_completed_at' => now(),
            'notes_awal'         => $notesAwal,
            'notes_akhir'        => $notesAkhir,
            'notes'              => $combinedNotes,
        ]);

        return $checksheet;
    }

    /**
     * Toggle nilai sel matriks langsung via AJAX (✓ / ✕ / - / null)
     */
    public function toggleCell(DandoryChecksheet $checksheet, string $phase, int $orderNo, ?string $status, ?string $reason = null): DandoryChecksheet
    {
        $field = $phase === 'akhir' ? 'akhir_checks' : 'awal_checks';
        $checks = (array) ($checksheet->$field ?? []);

        if ($status === null || $status === '') {
            unset($checks[(string) $orderNo]);
        } else {
            $checks[(string) $orderNo] = $status;
        }

        $updateData = [$field => $checks];

        $masterItems = $this->getMasterItems();
        $item = $masterItems->where('phase', $phase)->where('order_no', $orderNo)->first();
        $itemName = $item ? $item->item_check : "Item $orderNo";

        $noteField = $phase === 'akhir' ? 'notes_akhir' : 'notes_awal';
        $currNote = $checksheet->$noteField;

        // Jika status NG dan ada keterangan alasan, tambahkan / perbarui keterangan item ini
        if ($status === 'NG' && !empty(trim((string)$reason))) {
            $reasonTrimmed = trim((string)$reason);
            $entry = "[$itemName] $reasonTrimmed";

            $parts = !empty($currNote) ? explode(' | ', $currNote) : [];
            $filtered = [];
            foreach ($parts as $p) {
                $p = trim($p);
                if ($p !== '' && !str_starts_with($p, "[$itemName]")) {
                    $filtered[] = $p;
                }
            }
            $filtered[] = $entry;
            $newNote = !empty($filtered) ? implode(' | ', $filtered) : null;
            $updateData[$noteField] = $newNote;
        } else {
            // Jika status berubah bukan NG (misal OK, NA, atau null), bersihkan catatan alasan untuk item ini
            if (!empty($currNote)) {
                $parts = explode(' | ', $currNote);
                $filtered = [];
                foreach ($parts as $p) {
                    $p = trim($p);
                    if ($p !== '' && !str_starts_with($p, "[$itemName]")) {
                        $filtered[] = $p;
                    }
                }
                $newNote = !empty($filtered) ? implode(' | ', $filtered) : null;
                $updateData[$noteField] = $newNote;
            }
        }

        // Sinkronkan catatan gabungan 'notes'
        $notesAwal = array_key_exists('notes_awal', $updateData) ? $updateData['notes_awal'] : $checksheet->notes_awal;
        $notesAkhir = array_key_exists('notes_akhir', $updateData) ? $updateData['notes_akhir'] : $checksheet->notes_akhir;

        if ($notesAwal && $notesAkhir) {
            $updateData['notes'] = "[Awal] $notesAwal | [Akhir] $notesAkhir";
        } elseif ($notesAwal) {
            $updateData['notes'] = $notesAwal;
        } elseif ($notesAkhir) {
            $updateData['notes'] = $notesAkhir;
        } else {
            $updateData['notes'] = null;
        }

        $masterItems = $this->getMasterItems();
        $awalTotal   = $masterItems->where('phase', 'awal')->count() ?: 9;
        $akhirTotal  = $masterItems->where('phase', 'akhir')->count() ?: 10;

        if ($phase === 'awal') {
            if (count($checks) >= $awalTotal) {
                if (empty($checksheet->awal_completed_at)) {
                    $updateData['awal_completed_at'] = now();
                }
            } elseif (count($checks) === 0) {
                $updateData['awal_completed_at'] = null;
            }
        }

        if ($phase === 'akhir') {
            if (count($checks) >= $akhirTotal) {
                if (empty($checksheet->akhir_completed_at)) {
                    $updateData['akhir_completed_at'] = now();
                }
            } else {
                $updateData['akhir_completed_at'] = null;
            }
        }

        $checksheet->update($updateData);
        return $checksheet;
    }

    /**
     * Toggle verifikasi Diperiksa Leader
     */
    public function toggleLeaderVerification(DandoryChecksheet $checksheet, int $leaderUserId, string $leaderName): DandoryChecksheet
    {
        $newChecked = !$checksheet->leader_checked;
        $checksheet->update([
            'leader_checked'    => $newChecked,
            'leader_user_id'    => $newChecked ? $leaderUserId : null,
            'leader_name'       => $newChecked ? $leaderName : null,
            'leader_checked_at' => $newChecked ? now() : null,
            'is_rejected'       => false,
            'rejection_remarks' => null,
            'rejected_by'       => null,
            'rejector_name'     => null,
            'rejected_at'       => null,
        ]);

        return $checksheet;
    }

    /**
     * Reject checksheet oleh Leader
     */
    public function rejectChecksheet(DandoryChecksheet $checksheet, int $userId, string $userName, string $remarks): DandoryChecksheet
    {
        $checksheet->update([
            'leader_checked'    => false,
            'leader_user_id'    => null,
            'leader_name'       => null,
            'leader_checked_at' => null,
            'is_rejected'       => true,
            'rejection_remarks' => $remarks,
            'rejected_by'       => $userId,
            'rejector_name'     => $userName,
            'rejected_at'       => now(),
        ]);

        return $checksheet;
    }

    /**
     * Verifikasi massal (Bulk verify) per hari/shift oleh Leader
     */
    public function bulkVerify(array $ids, int $leaderUserId, string $leaderName): int
    {
        return DandoryChecksheet::whereIn('id', $ids)
            ->where('leader_checked', false)
            ->update([
                'leader_checked'    => true,
                'leader_user_id'    => $leaderUserId,
                'leader_name'       => $leaderName,
                'leader_checked_at' => now(),
                'is_rejected'       => false,
                'rejection_remarks' => null,
                'rejected_by'       => null,
                'rejector_name'     => null,
                'rejected_at'       => null,
            ]);
    }
}

