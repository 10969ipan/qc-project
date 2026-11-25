<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\DandoryOperatorConfig;
use App\Models\Item;
use App\Models\LevelingAudit;
use App\Models\LevelingAuditResult;
use App\Models\LevelingCheckItem;
use App\Models\LevelingRecapApproval;
use App\Models\OperatorComplianceSchedule;
use App\Models\Plant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class LevelingInspectorService
{
    private const CACHE_ACTIVE_ITEMS = 'leveling_active_check_items_v1';

    /**
     * Ambil daftar Master Point Check aktif (di-cache & diurutkan sesuai urutan prinsip baku).
     */
    public function getActiveCheckItems(): Collection
    {
        return Cache::remember(self::CACHE_ACTIVE_ITEMS, 3600, function () {
            $items = LevelingCheckItem::active()
                ->orderBy('level')
                ->orderBy('order_no')
                ->orderBy('id')
                ->get();

            return $this->sortItemsByPrinsip($items);
        });
    }

    public function clearMasterItemsCache(): void
    {
        Cache::forget(self::CACHE_ACTIVE_ITEMS);
    }

    private function sortItemsByPrinsip(Collection $items): Collection
    {
        $orderMap = array_flip(LevelingCheckItem::DEFAULT_PRINSIP_ORDER);

        return $items->sort(function ($a, $b) use ($orderMap) {
            $pa = $orderMap[strtoupper($a->prinsip)] ?? 999;
            $pb = $orderMap[strtoupper($b->prinsip)] ?? 999;
            if ($pa !== $pb) {
                return $pa <=> $pb;
            }
            $cmpP = strcmp(strtoupper($a->prinsip), strtoupper($b->prinsip));
            if ($cmpP !== 0) {
                return $cmpP;
            }
            if ((int) $a->level !== (int) $b->level) {
                return (int) $a->level <=> (int) $b->level;
            }
            if ((int) $a->order_no !== (int) $b->order_no) {
                return (int) $a->order_no <=> (int) $b->order_no;
            }
            return (int) $a->id <=> (int) $b->id;
        })->values();
    }

    /**
     * Gabungkan Master Point Check aktif dengan item historis yang sudah punya hasil di audit ini
     * agar audit lama tidak kehilangan baris / berubah skor jika master item dinonaktifkan.
     */
    private function getCheckItemsForAudit(?LevelingAudit $audit, Collection $resultsByItemId): Collection
    {
        $activeItems = $this->getActiveCheckItems();
        if (!$audit || $resultsByItemId->isEmpty()) {
            return $activeItems;
        }

        $activeIds = $activeItems->pluck('id')->all();
        $missingItemIds = $resultsByItemId->keys()
            ->filter(fn($id) => !empty($id) && !in_array((int) $id, $activeIds, true))
            ->values()
            ->all();

        if (empty($missingItemIds)) {
            return $activeItems;
        }

        $historicalItems = LevelingCheckItem::whereIn('id', $missingItemIds)->get();
        return $this->sortItemsByPrinsip($activeItems->concat($historicalItems));
    }

    /**
     * Pre-compute seluruh struktur tabel Point Check + Result Level per blok level (bebas N+1 & logika di Blade).
     */
    public function buildAuditTableData(?LevelingAudit $audit): array
    {
        $resultsByItemId = collect();

        if ($audit) {
            $resultsByItemId = LevelingAuditResult::where('audit_id', $audit->id)
                ->get()
                ->keyBy('check_item_id');
        }

        $itemsToRender = $this->getCheckItemsForAudit($audit, $resultsByItemId);

        $prinsipGroups = [];
        $groupedByPrinsip = $itemsToRender->groupBy('prinsip');

        $totalItems = $itemsToRender->count();
        $totalOk = 0;
        $totalNg = 0;

        foreach ($groupedByPrinsip as $prinsip => $pItems) {
            $levelsData = [];
            $groupedByLevel = $pItems->groupBy('level')->sortKeys();

            foreach ($groupedByLevel as $lvl => $lItems) {
                $rows = [];
                $rowNum = 1;
                foreach ($lItems as $item) {
                    $res = $resultsByItemId->get($item->id);
                    if ($res && $res->result === 'OK') {
                        $totalOk++;
                    } elseif ($res && $res->result === 'NG') {
                        $totalNg++;
                    }

                    $rows[] = [
                        'item'          => $item,
                        'row_num'       => $rowNum++,
                        'result'        => $res,
                        'guidance_html' => $this->formatGuidanceHtml($item->guidance),
                    ];
                }

                $levelsData[] = [
                    'level'   => (int) $lvl,
                    'rowspan' => count($rows),
                    'rows'    => $rows,
                ];
            }

            $prinsipGroups[] = [
                'prinsip'        => $prinsip,
                'rowspan'        => $pItems->count(),
                'achieved_level' => $this->calculatePrinsipLevel($pItems, $resultsByItemId),
                'levels'         => $levelsData,
            ];
        }

        $filled = $totalOk + $totalNg;
        $score = $this->calculateAverageLevelScore(array_column($prinsipGroups, 'achieved_level'));

        return [
            'prinsip_groups' => $prinsipGroups,
            'summary'        => [
                'total_items'  => $totalItems,
                'total_ok'     => $totalOk,
                'total_ng'     => $totalNg,
                'total_empty'  => max(0, $totalItems - $filled),
                'score'        => $score,
            ],
        ];
    }

    /**
     * Hitung rata-rata dari nilai Level per PRINSIP (hanya prinsip yang sudah lengkap bernilai 0-3).
     */
    public function calculateAverageLevelScore(array $prinsipLevels): ?float
    {
        $numeric = [];
        foreach ($prinsipLevels as $lv) {
            if (is_numeric($lv)) {
                $numeric[] = (float) $lv;
            }
        }
        if (empty($numeric)) {
            return null;
        }

        return round(array_sum($numeric) / count($numeric), 2);
    }

    /**
     * Rumus penilaian Result -> Level per PRINSIP (total skor independen Level 1-3):
     * - Jika masih ada point yang belum diisi (kosong) pada prinsip ini -> '-'
     * - Untuk setiap level (1, 2, 3) pada prinsip ini:
     *   - Semua point OK -> nilai level = 1
     *   - Ada minimal 1 NG (berapa pun jumlahnya) -> nilai level = 0
     * - Total nilai prinsip = Nilai Level 1 + Nilai Level 2 + Nilai Level 3 (0 s/d 3)
     */
    public function calculatePrinsipLevel(Collection $prinsipItems, Collection $resultsByItemId): string
    {
        if ($prinsipItems->isEmpty()) {
            return '-';
        }

        $levelHasNg = [];

        foreach ($prinsipItems as $item) {
            $res = $resultsByItemId->get($item->id);
            $val = $res ? $res->result : null;
            if ($val === null) {
                return '-';
            }

            $lvl = (int) $item->level;
            if (!array_key_exists($lvl, $levelHasNg)) {
                $levelHasNg[$lvl] = false;
            }
            if ($val === 'NG') {
                $levelHasNg[$lvl] = true;
            }
        }

        $total = 0;
        foreach ($levelHasNg as $hasNg) {
            if (!$hasNg) {
                $total++;
            }
        }

        return (string) $total;
    }

    /**
     * Resolve plant_id dari plant_code dengan cache 1 jam (hindari query Plant::whereRaw berulang).
     */
    private function resolvePlantId(string $plantCode): ?string
    {
        $clean = strtolower($plantCode);
        if ($clean === '' || $clean === 'total') {
            return null;
        }

        $map = Cache::remember('leveling_plant_code_id_map_v1', 3600, function () {
            return Plant::all(['id', 'code'])
                ->mapWithKeys(fn($p) => [strtolower((string) $p->code) => (string) $p->id])
                ->all();
        });

        return $map[$clean] ?? null;
    }

    /**
     * Ambil daftar Inspector aktif di plant tertentu.
     */
    public function getInspectorsForPlant(string $plantCode, ?string $search = null, int $limit = 100): Collection
    {
        $plantId = $this->resolvePlantId($plantCode);

        $query = User::select(['id', 'name', 'role', 'plant_id'])
            ->whereIn('role', ['operator', 'inspector'])
            ->where('is_active', true);

        if ($plantId) {
            $query->where('plant_id', $plantId);
        }

        if ($search !== null && trim($search) !== '') {
            $term = '%' . trim($search) . '%';
            $query->where('name', 'LIKE', $term);
        }

        return $query->orderBy('name')->limit($limit)->get();
    }

    /**
     * Ambil Bagian inspector dari jadwal Kepatuhan Operator (1 query saja, jadwal terbaru).
     */
    public function getBagianForInspector(int $inspectorId): ?string
    {
        return OperatorComplianceSchedule::where('operator_id', $inspectorId)
            ->whereNotNull('bagian')
            ->where('bagian', '!=', '')
            ->latest('schedule_date')
            ->value('bagian');
    }

    /**
     * Cari Part (items) sesuai DandoryOperatorConfig milik inspector yang diaudit.
     * Dibaca langsung (real-time) agar perubahan di Kepatuhan Operator & Dandory langsung terbaca tanpa delay cache.
     */
    public function searchPartsForInspector(string $plantCode, int $inspectorId, ?string $search = null, int $limit = 20): array
    {
        $cleanPlant = strtolower($plantCode);
        $bagian = $this->getBagianForInspector($inspectorId);

        $config = DandoryOperatorConfig::where('plant_code', $cleanPlant)
            ->where('operator_id', $inspectorId)
            ->first(['bagian', 'default_table', 'allowed_categories']);

        $allowedCategories = ($config && is_array($config->allowed_categories))
            ? array_values(array_filter($config->allowed_categories))
            : [];

        if (empty($allowedCategories) && !empty($bagian)) {
            $sameBagianConfig = DandoryOperatorConfig::where('plant_code', $cleanPlant)
                ->where('bagian', $bagian)
                ->whereNotNull('allowed_categories')
                ->latest('updated_at')
                ->first(['allowed_categories']);

            if ($sameBagianConfig && is_array($sameBagianConfig->allowed_categories)) {
                $allowedCategories = array_values(array_filter($sameBagianConfig->allowed_categories));
            }
        }

        $meta = [
            'bagian'             => $config?->bagian ?: $bagian,
            'allowed_categories' => $allowedCategories,
        ];

        $allowedCategories = $meta['allowed_categories'];
        $fallbackAll = empty($allowedCategories);
        $plantId = $this->resolvePlantId($cleanPlant);

        $query = Item::select(['id', 'name', 'part_number', 'sap_code', 'category_id', 'plant_id'])
            ->when($plantId, function ($q) use ($plantId) {
                $q->where(function ($sq) use ($plantId) {
                    $sq->where('plant_id', $plantId)->orWhereNull('plant_id');
                });
            });

        if (!$fallbackAll) {
            $query->whereIn('category_id', $allowedCategories);
        }

        if ($search !== null && trim($search) !== '') {
            $term = '%' . trim($search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'LIKE', $term)
                    ->orWhere('part_number', 'LIKE', $term)
                    ->orWhere('sap_code', 'LIKE', $term);
            });
        }

        $items = $query->orderBy('name')->limit($limit)->get();

        return [
            'items'         => $items,
            'bagian'        => $meta['bagian'],
            'default_table' => $config?->default_table,
            'fallback_all'  => $fallbackAll,
        ];
    }

    /**
     * Daftar riwayat audit berdasarkan plant, tanggal (opsional), dan inspector (opsional).
     * Memakai perbandingan DATE langsung (tanpa whereDate) agar composite index (plant_code, audit_date) aktif.
     */
    public function getAuditsForFilter(string $plantCode, ?string $date = null, ?int $inspectorId = null, int $limit = 50): Collection
    {
        return LevelingAudit::with(['inspector:id,name', 'auditor:id,name'])
            ->where('plant_code', strtolower($plantCode))
            ->when(!empty($date), fn($q) => $q->where('audit_date', $date))
            ->when($inspectorId, fn($q) => $q->where('inspector_id', $inspectorId))
            ->orderByDesc('audit_date')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    /**
     * Cari Riwayat Audit berdasarkan ketikan (nama inspector, nama part, part no, atau tanggal).
     */
    public function searchAudits(string $plantCode, ?string $search = null, ?string $date = null, ?int $inspectorId = null, int $limit = 10): Collection
    {
        $query = LevelingAudit::query()
            ->select([
                'leveling_audits.id',
                'leveling_audits.audit_date',
                'leveling_audits.inspector_id',
                'leveling_audits.part_name',
                'leveling_audits.part_number',
                'insp.name as inspector_name',
            ])
            ->leftJoin('users as insp', 'insp.id', '=', 'leveling_audits.inspector_id')
            ->where('leveling_audits.plant_code', strtolower($plantCode))
            ->when(!empty($date), fn($q) => $q->where('leveling_audits.audit_date', $date))
            ->when($inspectorId, fn($q) => $q->where('leveling_audits.inspector_id', $inspectorId));

        $trimmedSearch = $search !== null ? trim($search) : '';
        if ($trimmedSearch !== '') {
            $term = '%' . $trimmedSearch . '%';
            $hasSlash = str_contains($trimmedSearch, '/');
            $exactIsoDate = null;
            if ($hasSlash && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $trimmedSearch)) {
                try {
                    $exactIsoDate = Carbon::createFromFormat('d/m/Y', $trimmedSearch)->format('Y-m-d');
                } catch (\Throwable $e) {
                    $exactIsoDate = null;
                }
            }

            $query->where(function ($q) use ($term, $hasSlash, $exactIsoDate) {
                $q->where('insp.name', 'LIKE', $term)
                    ->orWhere('leveling_audits.part_name', 'LIKE', $term)
                    ->orWhere('leveling_audits.part_number', 'LIKE', $term)
                    ->orWhere('leveling_audits.bagian', 'LIKE', $term);

                if ($exactIsoDate !== null) {
                    $q->orWhere('leveling_audits.audit_date', $exactIsoDate);
                } elseif ($hasSlash) {
                    $q->orWhereRaw("DATE_FORMAT(leveling_audits.audit_date, '%d/%m/%Y') LIKE ?", [$term]);
                }
            });
        }

        return $query->orderByDesc('leveling_audits.audit_date')
            ->orderByDesc('leveling_audits.id')
            ->limit($limit)
            ->get();
    }

    /**
     * Hapus 1 sesi audit beserta file evidence terkait (khusus auditor pembuat atau admin).
     */
    public function deleteAudit(int $auditId, User $user): void
    {
        $audit = LevelingAudit::with('results')->findOrFail($auditId);

        if (!$this->canEditAudit($audit, $user)) {
            throw ValidationException::withMessages([
                'audit' => ['Hapus audit hanya dapat dilakukan oleh auditor pembuat (' . $audit->auditor_name . ') atau level SPV ke atas.'],
            ]);
        }

        foreach ($audit->results as $res) {
            foreach ($res->getEvidencePathsList() as $p) {
                Storage::disk('public')->delete($p);
            }
            foreach ($res->getCorrectiveEvidencePathsList() as $p) {
                Storage::disk('public')->delete($p);
            }
        }

        $partInfo = $audit->part_name;
        $auditDate = $audit->audit_date?->format('d/m/Y') ?? '-';
        $this->logActivity($user, 'delete', $audit, "Menghapus audit Leveling Inspector ({$auditDate} - {$partInfo})");

        $audit->delete();
    }

    /**
     * Buat header audit baru (tanggal otomatis = hari ini, auditor = user login).
     */
    public function createAudit(array $data, User $auditor): LevelingAudit
    {
        $plantCode = strtolower($data['plant']);
        $today = Carbon::today()->toDateString();

        $exists = LevelingAudit::where('plant_code', $plantCode)
            ->where('audit_date', $today)
            ->where('inspector_id', $data['inspector_id'])
            ->where('item_id', $data['item_id'])
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'item_id' => ['Audit untuk inspector dan part yang sama pada hari ini sudah ada. Silakan pilih dari Riwayat Audit atau pilih part lain.'],
            ]);
        }

        $item = Item::findOrFail($data['item_id']);

        $audit = LevelingAudit::create([
            'plant_code'   => $plantCode,
            'audit_date'   => $today,
            'inspector_id' => $data['inspector_id'],
            'bagian'       => trim($data['bagian']),
            'meja_mesin'   => !empty($data['meja_mesin']) ? trim($data['meja_mesin']) : null,
            'item_id'      => $item->id,
            'part_name'    => $item->name,
            'part_number'  => $item->part_number,
            'auditor_id'   => $auditor->id,
            'auditor_name' => $auditor->name,
        ]);

        $this->logActivity($auditor, 'create', $audit, "Membuat audit Leveling Inspector untuk part {$item->name}");

        return $audit->load(['inspector:id,name', 'auditor:id,name']);
    }

    /**
     * Cek apakah user adalah auditor pembuat audit atau level SPV ke atas sehingga boleh mengubah data audit.
     */
    public function canEditAudit(?LevelingAudit $audit, ?User $user): bool
    {
        if (!$audit || !$user) {
            return false;
        }
        if (in_array($user->role, \App\Models\OperatorComplianceItem::MASTER_MANAGER_ROLES, true)) {
            return true;
        }

        return (int) $audit->auditor_id === (int) $user->id;
    }

    private function assertCanEditAudit(LevelingAudit $audit, User $user): void
    {
        if (!$this->canEditAudit($audit, $user)) {
            abort(403, 'Hanya auditor pembuat audit atau level SPV ke atas yang dapat mengubah data audit ini.');
        }
    }

    /**
     * Simpan hasil OK/NG (atau reset null) pada 1 point check + hitung ulang skor & level prinsip.
     */
    public function saveResult(int $auditId, int $checkItemId, ?string $resultValue, User $user): array
    {
        $audit = LevelingAudit::findOrFail($auditId);
        $this->assertCanEditAudit($audit, $user);
        $checkItem = $this->getActiveCheckItems()->firstWhere('id', $checkItemId)
            ?? LevelingCheckItem::findOrFail($checkItemId);

        $existing = LevelingAuditResult::where('audit_id', $audit->id)
            ->where('check_item_id', $checkItem->id)
            ->first(['id', 'ng_status', 'corrective_evidence_path', 'corrective_evidence_paths']);

        $updateData = [
            'prinsip'     => $checkItem->prinsip,
            'level'       => $checkItem->level,
            'point_check' => $checkItem->point_check,
            'result'      => $resultValue,
        ];

        if ($resultValue === 'NG') {
            $updateData['ng_status'] = !empty($existing?->ng_status) ? $existing->ng_status : 'open';
        } else {
            // Reset bersih data perbaikan NG lama saat diubah ke OK atau dikosongkan
            if ($existing) {
                foreach ($existing->getCorrectiveEvidencePathsList() as $p) {
                    Storage::disk('public')->delete($p);
                }
            }
            $updateData['ng_status']                 = null;
            $updateData['why_1']                     = null;
            $updateData['why_2']                     = null;
            $updateData['why_3']                     = null;
            $updateData['root_cause']                = null;
            $updateData['corrective_action']         = null;
            $updateData['corrective_evidence_path']  = null;
            $updateData['corrective_evidence_paths'] = null;
            $updateData['corrective_date']           = null;
            $updateData['corrected_by']              = null;
            $updateData['verified_by']               = null;
            $updateData['verified_at']               = null;
            $updateData['reject_reason']             = null;
        }

        if ($existing) {
            $existing->update($updateData);
            $record = $existing;
        } else {
            $record = LevelingAuditResult::create(array_merge([
                'audit_id'      => $audit->id,
                'check_item_id' => $checkItem->id,
            ], $updateData));
        }

        $recalc = $this->recalculateAuditSummary($audit, $checkItem->prinsip);

        $this->logActivity($user, 'update', $record, "Set hasil Leveling [{$checkItem->prinsip} L{$checkItem->level}] = " . ($resultValue ?? 'KOSONG'));

        return [
            'result'         => $record,
            'prinsip'        => $checkItem->prinsip,
            'level'          => (int) $checkItem->level,
            'achieved_level' => $recalc['achieved_level'],
            'summary'        => $recalc['summary'],
            'open_ng_count'  => $this->countOpenNg($audit->plant_code),
        ];
    }

    /**
     * Simpan hasil OK/NG secara massal (bulk) menggunakan 1 query UPSERT (super cepat, bebas N+1).
     */
    public function bulkSaveResults(int $auditId, array $checkItemIds, string $resultValue, User $user): array
    {
        $audit = LevelingAudit::findOrFail($auditId);
        $this->assertCanEditAudit($audit, $user);
        $ids = array_values(array_unique(array_map('intval', $checkItemIds)));
        $checkItems = LevelingCheckItem::whereIn('id', $ids)->get()->keyBy('id');

        $existingMap = LevelingAuditResult::where('audit_id', $audit->id)
            ->whereIn('check_item_id', $ids)
            ->get()
            ->keyBy('check_item_id');

        $now = now();
        $upsertRows = [];
        $affectedBlocks = [];
        $filesToDelete = [];

        foreach ($checkItems as $itemId => $checkItem) {
            $existing = $existingMap->get($itemId);
            $row = [
                'audit_id'      => $audit->id,
                'check_item_id' => $checkItem->id,
                'prinsip'       => $checkItem->prinsip,
                'level'         => $checkItem->level,
                'point_check'   => $checkItem->point_check,
                'result'        => $resultValue,
                'created_at'    => $existing?->created_at ?? $now,
                'updated_at'    => $now,
            ];

            if ($resultValue === 'NG') {
                $row['ng_status'] = !empty($existing?->ng_status) ? $existing->ng_status : 'open';
            } else {
                if ($existing) {
                    foreach ($existing->getCorrectiveEvidencePathsList() as $p) {
                        $filesToDelete[] = $p;
                    }
                }
                $row['ng_status']                 = null;
                $row['why_1']                     = null;
                $row['why_2']                     = null;
                $row['why_3']                     = null;
                $row['root_cause']                = null;
                $row['corrective_action']         = null;
                $row['corrective_evidence_path']  = null;
                $row['corrective_evidence_paths'] = null;
                $row['corrective_date']           = null;
                $row['corrected_by']              = null;
                $row['verified_by']               = null;
                $row['verified_at']               = null;
                $row['reject_reason']             = null;
            }

            $upsertRows[] = $row;

            $affectedPrinsips[$checkItem->prinsip] = true;
        }

        if (!empty($upsertRows)) {
            $updateCols = $resultValue === 'NG'
                ? ['prinsip', 'level', 'point_check', 'result', 'ng_status', 'updated_at']
                : [
                    'prinsip', 'level', 'point_check', 'result', 'ng_status',
                    'why_1', 'why_2', 'why_3', 'root_cause', 'corrective_action',
                    'corrective_evidence_path', 'corrective_evidence_paths',
                    'corrective_date', 'corrected_by', 'verified_by', 'verified_at',
                    'reject_reason', 'updated_at',
                ];

            // 1 single batch SQL UPSERT untuk seluruh baris terpilih
            LevelingAuditResult::upsert($upsertRows, ['audit_id', 'check_item_id'], $updateCols);
        }

        foreach ($filesToDelete as $p) {
            Storage::disk('public')->delete($p);
        }

        // 1 query ringan (hanya 4 kolom inti) untuk ambil hasil audit terbaru & hitung ulang summary
        $resultsByItemId = LevelingAuditResult::where('audit_id', $audit->id)
            ->get(['id', 'check_item_id', 'result', 'ng_status'])
            ->keyBy('check_item_id');
        $itemsForAudit = $this->getCheckItemsForAudit($audit, $resultsByItemId);

        $updatedRows = [];
        foreach ($checkItems as $itemId => $checkItem) {
            $rec = $resultsByItemId->get($itemId);
            if ($rec) {
                $updatedRows[] = [
                    'item_id'   => (int) $itemId,
                    'result_id' => (int) $rec->id,
                    'result'    => $rec->result,
                    'ng_status' => $rec->ng_status,
                ];
            }
        }

        $totalOk = 0;
        $totalNg = 0;
        foreach ($itemsForAudit as $it) {
            $res = $resultsByItemId->get($it->id);
            if ($res && $res->result === 'OK') {
                $totalOk++;
            } elseif ($res && $res->result === 'NG') {
                $totalNg++;
            }
        }

        $filled = $totalOk + $totalNg;

        $allPrinsipLevels = [];
        foreach ($itemsForAudit->groupBy('prinsip') as $pKey => $pItems) {
            $allPrinsipLevels[$pKey] = $this->calculatePrinsipLevel($pItems, $resultsByItemId);
        }
        $score = $this->calculateAverageLevelScore($allPrinsipLevels);

        $audit->update([
            'total_ok' => $totalOk,
            'total_ng' => $totalNg,
            'score'    => $score,
        ]);

        $achievedLevels = [];
        foreach (array_keys($affectedPrinsips ?? []) as $prinsipKey) {
            $achievedLevels[$prinsipKey] = $allPrinsipLevels[$prinsipKey] ?? '-';
        }

        $count = count($updatedRows);
        $this->logActivity($user, 'update', $audit, "Bulk set {$count} point check Leveling = {$resultValue}");

        return [
            'updated_rows'    => $updatedRows,
            'achieved_levels' => $achievedLevels,
            'summary'         => [
                'total_items' => $itemsForAudit->count(),
                'total_ok'    => $totalOk,
                'total_ng'    => $totalNg,
                'total_empty' => max(0, $itemsForAudit->count() - $filled),
                'score'       => $score,
            ],
            'open_ng_count'   => $this->countOpenNg($audit->plant_code),
        ];
    }

    /**
     * Hitung ulang total OK/NG/Score (rata-rata level) pada audit dan hasil level untuk PRINSIP tertentu.
     */
    private function recalculateAuditSummary(LevelingAudit $audit, string $targetPrinsip): array
    {
        $resultsByItemId = LevelingAuditResult::where('audit_id', $audit->id)
            ->get(['id', 'check_item_id', 'result', 'ng_status'])
            ->keyBy('check_item_id');

        $itemsForAudit = $this->getCheckItemsForAudit($audit, $resultsByItemId);

        $totalOk = 0;
        $totalNg = 0;

        foreach ($itemsForAudit as $it) {
            $res = $resultsByItemId->get($it->id);
            if ($res && $res->result === 'OK') {
                $totalOk++;
            } elseif ($res && $res->result === 'NG') {
                $totalNg++;
            }
        }

        $filled = $totalOk + $totalNg;
        $allPrinsipLevels = [];
        foreach ($itemsForAudit->groupBy('prinsip') as $pKey => $pItems) {
            $allPrinsipLevels[$pKey] = $this->calculatePrinsipLevel($pItems, $resultsByItemId);
        }
        $score = $this->calculateAverageLevelScore($allPrinsipLevels);

        $audit->update([
            'total_ok' => $totalOk,
            'total_ng' => $totalNg,
            'score'    => $score,
        ]);

        $achievedLevel = $allPrinsipLevels[$targetPrinsip] ?? '-';

        return [
            'achieved_level' => $achievedLevel,
            'summary'        => [
                'total_items' => $itemsForAudit->count(),
                'total_ok'    => $totalOk,
                'total_ng'    => $totalNg,
                'total_empty' => max(0, $itemsForAudit->count() - $filled),
                'score'       => $score,
            ],
        ];
    }

    /**
     * Simpan foto dengan resize & kompresi otomatis (maks 1600px, kualitas 80%) agar ringan saat render.
     * Fallback otomatis ke simpan file asli jika ekstensi GD tidak tersedia.
     */
    private function storeOptimizedImage(UploadedFile $file, string $dir): string
    {
        if (!extension_loaded('gd')) {
            return $file->store($dir, 'public');
        }

        try {
            $realPath = $file->getRealPath();
            $info = @getimagesize($realPath);
            if (!$info || empty($info[0]) || empty($info[1])) {
                return $file->store($dir, 'public');
            }

            [$origW, $origH, $imgType] = $info;
            $src = match ($imgType) {
                IMAGETYPE_JPEG => @imagecreatefromjpeg($realPath),
                IMAGETYPE_PNG  => @imagecreatefrompng($realPath),
                IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($realPath) : false,
                default        => false,
            };

            if (!$src) {
                return $file->store($dir, 'public');
            }

            // Perbaiki orientasi EXIF foto kamera HP (khusus JPEG)
            if ($imgType === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
                $exif = @exif_read_data($realPath);
                $orientation = $exif['Orientation'] ?? 1;
                if ($orientation === 3) {
                    $src = imagerotate($src, 180, 0);
                } elseif ($orientation === 6) {
                    $src = imagerotate($src, -90, 0);
                    [$origW, $origH] = [$origH, $origW];
                } elseif ($orientation === 8) {
                    $src = imagerotate($src, 90, 0);
                    [$origW, $origH] = [$origH, $origW];
                }
            }

            $maxDim = 1600;
            $scale = min(1, $maxDim / max($origW, $origH));
            $newW = max(1, (int) round($origW * $scale));
            $newH = max(1, (int) round($origH * $scale));

            $dst = imagecreatetruecolor($newW, $newH);
            // Background putih agar PNG transparan rapi saat dikonversi ke JPEG
            $white = imagecolorallocate($dst, 255, 255, 255);
            imagefill($dst, 0, 0, $white);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

            ob_start();
            imagejpeg($dst, null, 80);
            $binary = ob_get_clean();

            imagedestroy($src);
            imagedestroy($dst);

            if ($binary === false || $binary === '') {
                return $file->store($dir, 'public');
            }

            $filename = pathinfo($file->hashName(), PATHINFO_FILENAME) . '.jpg';
            $relativePath = trim($dir, '/') . '/' . $filename;
            Storage::disk('public')->put($relativePath, $binary);

            return $relativePath;
        } catch (\Throwable $e) {
            return $file->store($dir, 'public');
        }
    }

    /**
     * Upload satu atau lebih foto evidence temuan (dikompresi otomatis, ditambahkan ke daftar foto).
     *
     * @param UploadedFile[] $files
     */
    public function uploadEvidences(int $auditId, int $checkItemId, array $files, User $user, ?string $evidenceNote = null): LevelingAuditResult
    {
        $audit = LevelingAudit::findOrFail($auditId);
        $this->assertCanEditAudit($audit, $user);
        $checkItem = LevelingCheckItem::findOrFail($checkItemId);

        $existing = LevelingAuditResult::where('audit_id', $audit->id)
            ->where('check_item_id', $checkItem->id)
            ->first();

        $cleanNote = trim((string) $evidenceNote);
        $finalNote = $cleanNote !== '' ? $cleanNote : trim((string) ($existing?->evidence_note ?? ''));
        if ($finalNote === '') {
            throw ValidationException::withMessages([
                'evidence_note' => ['Keterangan evidence wajib diisi.'],
            ]);
        }

        $paths = $existing ? $existing->getEvidencePathsList() : [];
        $dir = 'leveling/' . strtolower($audit->plant_code) . '/' . date('Y-m');

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $paths[] = $this->storeOptimizedImage($file, $dir);
            }
        }

        $record = LevelingAuditResult::updateOrCreate(
            [
                'audit_id'      => $audit->id,
                'check_item_id' => $checkItem->id,
            ],
            [
                'prinsip'        => $checkItem->prinsip,
                'level'          => $checkItem->level,
                'point_check'    => $checkItem->point_check,
                'evidence_path'  => $paths[0] ?? null,
                'evidence_paths' => !empty($paths) ? array_values($paths) : null,
                'evidence_note'  => $finalNote,
            ]
        );

        $count = count($files);
        $this->logActivity($user, 'upload', $record, "Upload {$count} foto evidence Leveling [{$checkItem->prinsip} L{$checkItem->level}]");

        return $record;
    }

    /**
     * Edit keterangan evidence untuk 1 baris Point Check.
     */
    public function updateEvidenceNote(int $resultId, string $note, User $user): LevelingAuditResult
    {
        $record = LevelingAuditResult::with('audit')->findOrFail($resultId);
        $this->assertCanEditAudit($record->audit, $user);
        $cleanNote = trim($note);
        if ($cleanNote === '') {
            throw ValidationException::withMessages([
                'evidence_note' => ['Keterangan evidence wajib diisi.'],
            ]);
        }

        $record->update(['evidence_note' => $cleanNote]);
        $this->logActivity($user, 'update', $record, 'Edit keterangan evidence temuan Leveling');

        return $record->fresh();
    }

    /**
     * Hapus foto evidence temuan (per index tertentu atau seluruhnya jika index null).
     */
    public function deleteEvidence(int $resultId, User $user, ?int $index = null): LevelingAuditResult
    {
        $record = LevelingAuditResult::with('audit')->findOrFail($resultId);
        $this->assertCanEditAudit($record->audit, $user);
        $paths = $record->getEvidencePathsList();

        if ($index !== null && array_key_exists($index, $paths)) {
            Storage::disk('public')->delete($paths[$index]);
            unset($paths[$index]);
            $paths = array_values($paths);
        } else {
            foreach ($paths as $p) {
                Storage::disk('public')->delete($p);
            }
            $paths = [];
        }

        $updatePayload = [
            'evidence_path'  => $paths[0] ?? null,
            'evidence_paths' => !empty($paths) ? $paths : null,
        ];
        if (empty($paths)) {
            $updatePayload['evidence_note'] = null;
        }
        $record->update($updatePayload);

        $this->logActivity($user, 'delete', $record, 'Menghapus foto evidence temuan Leveling');

        return $record->fresh();
    }

    /**
     * Hitung jumlah temuan NG yang belum Closed (open + waiting_verify) untuk plant terpilih.
     * Memakai JOIN langsung (lebih cepat dari whereHas EXISTS subquery).
     */
    public function countOpenNg(string $plantCode): int
    {
        return (int) DB::table('leveling_audit_results as r')
            ->join('leveling_audits as a', 'a.id', '=', 'r.audit_id')
            ->where('a.plant_code', strtolower($plantCode))
            ->whereIn('r.ng_status', ['open', 'waiting_verify'])
            ->count();
    }

    /**
     * Daftar temuan NG untuk modal Verifikasi Perbaikan NG (JOIN + eager loading, index-friendly).
     */
    public function listNgFindings(string $plantCode, array $filters = []): Collection
    {
        $query = LevelingAuditResult::query()
            ->select('leveling_audit_results.*')
            ->join('leveling_audits as a', 'a.id', '=', 'leveling_audit_results.audit_id')
            ->with([
                'audit:id,plant_code,audit_date,inspector_id,bagian,meja_mesin,part_name,part_number,auditor_id,auditor_name',
                'audit.inspector:id,name',
                'corrector:id,name',
                'verifier:id,name',
            ])
            ->where('a.plant_code', strtolower($plantCode))
            ->where('leveling_audit_results.result', 'NG')
            ->whereNotNull('leveling_audit_results.ng_status');

        if (!empty($filters['inspector_id'])) {
            $query->where('a.inspector_id', (int) $filters['inspector_id']);
        }
        if (!empty($filters['date_from'])) {
            $query->where('a.audit_date', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->where('a.audit_date', '<=', $filters['date_to']);
        }
        if (!empty($filters['status']) && in_array($filters['status'], ['open', 'waiting_verify', 'closed'], true)) {
            $query->where('leveling_audit_results.ng_status', $filters['status']);
        }

        return $query->orderByRaw("FIELD(leveling_audit_results.ng_status, 'open', 'waiting_verify', 'closed')")
            ->orderByDesc('leveling_audit_results.updated_at')
            ->get();
    }

    /**
     * Input Why 1-3, Root Cause, tindakan perbaikan NG + upload satu/lebih foto evidence after.
     *
     * @param UploadedFile[] $files
     */
    public function submitNgCorrection(int $resultId, array $data, array $files, User $user): LevelingAuditResult
    {
        $record = LevelingAuditResult::with('audit')->findOrFail($resultId);

        if ($record->result !== 'NG') {
            throw ValidationException::withMessages([
                'result' => ['Item ini bukan temuan NG.'],
            ]);
        }

        $dir = 'leveling/' . strtolower($record->audit->plant_code) . '/' . date('Y-m');
        $newPaths = [];
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $newPaths[] = $this->storeOptimizedImage($file, $dir);
            }
        }

        // Hapus foto perbaikan lama jika resubmit setelah reject
        foreach ($record->getCorrectiveEvidencePathsList() as $oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        $why1 = $this->sanitizeGuidanceHtml((string) ($data['why_1'] ?? ''));
        $why2 = $this->sanitizeGuidanceHtml(isset($data['why_2']) ? (string) $data['why_2'] : null);
        $why3 = $this->sanitizeGuidanceHtml(isset($data['why_3']) ? (string) $data['why_3'] : null);
        $rootCause = $this->sanitizeGuidanceHtml((string) ($data['root_cause'] ?? ''));
        $corrAction = $this->sanitizeGuidanceHtml((string) ($data['corrective_action'] ?? ''));

        if (trim(strip_tags((string) $why1)) === '' || trim(strip_tags((string) $rootCause)) === '' || trim(strip_tags((string) $corrAction)) === '') {
            throw ValidationException::withMessages([
                'why_1' => ['Why 1, Root Cause, dan Tindakan Perbaikan wajib diisi.'],
            ]);
        }

        $record->update([
            'why_1'                     => $why1,
            'why_2'                     => ($why2 !== null && trim(strip_tags($why2)) !== '') ? $why2 : null,
            'why_3'                     => ($why3 !== null && trim(strip_tags($why3)) !== '') ? $why3 : null,
            'root_cause'                => $rootCause,
            'corrective_action'         => $corrAction,
            'corrective_date'           => $data['corrective_date'],
            'corrective_evidence_path'  => $newPaths[0] ?? null,
            'corrective_evidence_paths' => !empty($newPaths) ? array_values($newPaths) : null,
            'corrected_by'              => $user->id,
            'ng_status'                 => 'waiting_verify',
            'reject_reason'             => null,
        ]);

        $this->logActivity($user, 'update', $record, 'Input Why Analysis, Root Cause, tindakan perbaikan & evidence NG Leveling');

        return $record->fresh(['audit.inspector:id,name', 'corrector:id,name', 'verifier:id,name']);
    }

    /**
     * Edit inline teks tindakan perbaikan NG (Why 1-3, Root Cause, Tindakan, Tanggal) tanpa mengubah foto.
     */
    public function updateNgCorrectionText(int $resultId, array $data, User $user): LevelingAuditResult
    {
        $record = LevelingAuditResult::with('audit')->findOrFail($resultId);

        if ($record->result !== 'NG') {
            throw ValidationException::withMessages([
                'result' => ['Item ini bukan temuan NG.'],
            ]);
        }

        if ($record->ng_status === 'closed') {
            throw ValidationException::withMessages([
                'ng_status' => ['Temuan NG yang sudah Closed tidak dapat diedit.'],
            ]);
        }

        $why1 = $this->sanitizeGuidanceHtml((string) ($data['why_1'] ?? ''));
        $why2 = $this->sanitizeGuidanceHtml(isset($data['why_2']) ? (string) $data['why_2'] : null);
        $why3 = $this->sanitizeGuidanceHtml(isset($data['why_3']) ? (string) $data['why_3'] : null);
        $rootCause = $this->sanitizeGuidanceHtml((string) ($data['root_cause'] ?? ''));
        $corrAction = $this->sanitizeGuidanceHtml((string) ($data['corrective_action'] ?? ''));

        if (trim(strip_tags((string) $why1)) === '' || trim(strip_tags((string) $rootCause)) === '' || trim(strip_tags((string) $corrAction)) === '') {
            throw ValidationException::withMessages([
                'why_1' => ['Why 1, Root Cause, dan Tindakan Perbaikan wajib diisi.'],
            ]);
        }

        $record->update([
            'why_1'             => $why1,
            'why_2'             => ($why2 !== null && trim(strip_tags($why2)) !== '') ? $why2 : null,
            'why_3'             => ($why3 !== null && trim(strip_tags($why3)) !== '') ? $why3 : null,
            'root_cause'        => $rootCause,
            'corrective_action' => $corrAction,
            'corrective_date'   => $data['corrective_date'],
            'corrected_by'      => $user->id,
        ]);

        $this->logActivity($user, 'update', $record, 'Edit inline tindakan perbaikan NG Leveling');

        return $record->fresh(['audit.inspector:id,name', 'corrector:id,name', 'verifier:id,name']);
    }

    /**
     * Edit inline foto evidence perbaikan NG (pertahankan sebagian/hapus lama + tambah foto baru).
     *
     * @param int[] $keepIndices
     * @param UploadedFile[] $newFiles
     */
    public function updateNgCorrectionPhotos(int $resultId, array $keepIndices, array $newFiles, User $user): LevelingAuditResult
    {
        $record = LevelingAuditResult::with('audit')->findOrFail($resultId);

        if ($record->result !== 'NG') {
            throw ValidationException::withMessages([
                'result' => ['Item ini bukan temuan NG.'],
            ]);
        }

        if ($record->ng_status === 'closed') {
            throw ValidationException::withMessages([
                'ng_status' => ['Temuan NG yang sudah Closed tidak dapat diubah fotonya.'],
            ]);
        }

        $existingPaths = $record->getCorrectiveEvidencePathsList();
        $keepSet = array_flip(array_map('intval', $keepIndices));

        $finalPaths = [];
        $toDelete = [];
        foreach ($existingPaths as $idx => $path) {
            if (isset($keepSet[$idx])) {
                $finalPaths[] = $path;
            } else {
                $toDelete[] = $path;
            }
        }

        $validNewFiles = array_values(array_filter($newFiles, fn($f) => $f instanceof UploadedFile));
        if (count($finalPaths) + count($validNewFiles) < 1) {
            throw ValidationException::withMessages([
                'new_evidences' => ['Minimal harus ada 1 foto evidence perbaikan.'],
            ]);
        }
        if (count($finalPaths) + count($validNewFiles) > 10) {
            throw ValidationException::withMessages([
                'new_evidences' => ['Total foto perbaikan maksimal 10 foto.'],
            ]);
        }

        $dir = 'leveling/' . strtolower($record->audit->plant_code) . '/' . date('Y-m');
        foreach ($validNewFiles as $file) {
            $finalPaths[] = $this->storeOptimizedImage($file, $dir);
        }

        foreach ($toDelete as $oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        $record->update([
            'corrective_evidence_path'  => $finalPaths[0] ?? null,
            'corrective_evidence_paths' => array_values($finalPaths),
            'corrected_by'              => $user->id,
        ]);

        $this->logActivity($user, 'update', $record, 'Ubah foto evidence perbaikan NG Leveling');

        return $record->fresh(['audit.inspector:id,name', 'corrector:id,name', 'verifier:id,name']);
    }

    /**
     * Verifikasi perbaikan NG (Approve / Reject) — dilakukan oleh auditor yang sama (atau admin).
     */
    public function verifyNgFinding(int $resultId, string $action, ?string $rejectReason, User $user): LevelingAuditResult
    {
        $record = LevelingAuditResult::with('audit')->findOrFail($resultId);

        if ($action === 'unverify') {
            if ($record->ng_status !== 'closed') {
                throw ValidationException::withMessages([
                    'action' => ['Hanya temuan NG berstatus Closed yang dapat dibatalkan verifikasinya.'],
                ]);
            }

            $isAllowed = in_array($user->role, ['admin', 'superadmin'], true)
                || ($record->audit && (int) $record->audit->auditor_id === (int) $user->id)
                || (int) $record->verified_by === (int) $user->id;

            if (!$isAllowed) {
                throw ValidationException::withMessages([
                    'action' => ['Batal verifikasi hanya dapat dilakukan oleh auditor yang bersangkutan atau admin.'],
                ]);
            }

            $record->update([
                'ng_status'   => 'waiting_verify',
                'verified_by' => null,
                'verified_at' => null,
            ]);
            $this->logActivity($user, 'update', $record, 'Batal verifikasi perbaikan NG Leveling (kembali ke Waiting Verify)');

            return $record->fresh(['audit.inspector:id,name', 'corrector:id,name', 'verifier:id,name']);
        }

        if ($record->ng_status !== 'waiting_verify') {
            throw ValidationException::withMessages([
                'action' => ['Temuan NG belum berstatus Waiting Verify.'],
            ]);
        }

        if (!in_array($user->role, \App\Models\OperatorComplianceItem::MASTER_MANAGER_ROLES, true)) {
            throw ValidationException::withMessages([
                'action' => ['Verifikasi hanya dapat dilakukan oleh level SPV ke atas.'],
            ]);
        }

        if ($action === 'approve') {
            $record->update([
                'ng_status'     => 'closed',
                'verified_by'   => $user->id,
                'verified_at'   => now(),
                'reject_reason' => null,
            ]);
            $this->logActivity($user, 'approve', $record, 'Approve verifikasi perbaikan NG Leveling');
        } else {
            $record->update([
                'ng_status'     => 'open',
                'verified_by'   => $user->id,
                'verified_at'   => now(),
                'reject_reason' => trim((string) $rejectReason),
            ]);
            $this->logActivity($user, 'reject', $record, 'Reject perbaikan NG Leveling: ' . trim((string) $rejectReason));
        }

        return $record->fresh(['audit.inspector:id,name', 'corrector:id,name', 'verifier:id,name']);
    }

    /**
     * CRUD Dinamis Master Point Check (tanpa seeder).
     */
    public function listAllMasterItems(): Collection
    {
        $items = LevelingCheckItem::orderBy('level')
            ->orderBy('order_no')
            ->orderBy('id')
            ->get();

        return $this->sortItemsByPrinsip($items)->map(function ($it) {
            $it->guidance_html = $this->formatGuidanceHtml($it->guidance);
            return $it;
        });
    }

    /**
     * Sanitasi HTML dari Rich Text Editor Guidance Penilaian agar aman dari XSS.
     */
    public function sanitizeGuidanceHtml(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }
        $trimmed = trim($html);
        if ($trimmed === '' || $trimmed === '<br>' || $trimmed === '<p><br></p>') {
            return null;
        }

        $allowed = '<p><br><b><strong><i><em><u><s><strike><sub><sup><ul><ol><li><div><span><font><hr><blockquote>';
        $clean = strip_tags($trimmed, $allowed);
        // Hapus event handler inline (onclick, onerror, dll), skema javascript:, dan paksa ukuran font seragam
        $clean = preg_replace('/\son[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $clean);
        $clean = preg_replace('/javascript\s*:/i', '', $clean);
        $clean = preg_replace('/\ssize\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $clean);
        $clean = preg_replace('/font-size\s*:\s*[^;"\']+;?/i', '', $clean);

        return trim($clean);
    }

    /**
     * Ubah Guidance Penilaian menjadi HTML rapi (baik yang sudah rich-HTML maupun plain text).
     */
    public function formatGuidanceHtml(?string $guidance): string
    {
        if ($guidance === null || trim($guidance) === '') {
            return '<span class="text-muted">-</span>';
        }

        // Jika sudah mengandung tag HTML dari editor, sanitasi dan kembalikan langsung
        if (preg_match('/<(p|div|ul|ol|li|b|strong|i|em|u|br|span|font)\b/i', $guidance)) {
            return $this->sanitizeGuidanceHtml($guidance) ?? '';
        }

        // Format otomatis untuk teks polos agar rapi di tabel (list bernomor, label Metode/Contoh)
        $lines = preg_split('/\r\n|\r|\n/', trim($guidance));
        $html = '';
        $inOl = false;

        foreach ($lines as $rawLine) {
            $line = trim($rawLine);
            if ($line === '') {
                if ($inOl) {
                    $html .= '</ol>';
                    $inOl = false;
                }
                continue;
            }

            if (preg_match('/^\d+\.\s*(.+)$/', $line, $m)) {
                if (!$inOl) {
                    $html .= '<ol>';
                    $inOl = true;
                }
                $html .= '<li>' . e($m[1]) . '</li>';
            } else {
                if ($inOl) {
                    $html .= '</ol>';
                    $inOl = false;
                }
                $escaped = e($line);
                $escaped = preg_replace('/^(Metode\s*:|Contoh\s*:)/i', '<strong>$1</strong>', $escaped);
                $html .= '<p>' . $escaped . '</p>';
            }
        }

        if ($inOl) {
            $html .= '</ol>';
        }

        return $html;
    }

    public function storeMasterItem(array $data, User $user): LevelingCheckItem
    {
        $prinsip = strtoupper(trim($data['prinsip']));
        $level = (int) $data['level'];
        $orderNo = !empty($data['order_no'])
            ? (int) $data['order_no']
            : ((int) LevelingCheckItem::where('prinsip', $prinsip)->where('level', $level)->max('order_no') + 1);

        $item = LevelingCheckItem::create([
            'prinsip'     => $prinsip,
            'level'       => $level,
            'order_no'    => $orderNo,
            'point_check' => trim($data['point_check']),
            'guidance'    => isset($data['guidance']) ? $this->sanitizeGuidanceHtml((string) $data['guidance']) : null,
            'is_active'   => true,
        ]);

        $this->clearMasterItemsCache();
        $this->logActivity($user, 'create', $item, "Tambah Master Point Check Leveling [{$prinsip} L{$level}]");

        return $item;
    }

    public function updateMasterItem(int $id, array $data, User $user): LevelingCheckItem
    {
        $item = LevelingCheckItem::findOrFail($id);

        $item->update([
            'prinsip'     => strtoupper(trim($data['prinsip'])),
            'level'       => (int) $data['level'],
            'order_no'    => !empty($data['order_no']) ? (int) $data['order_no'] : $item->order_no,
            'point_check' => trim($data['point_check']),
            'guidance'    => isset($data['guidance']) ? $this->sanitizeGuidanceHtml((string) $data['guidance']) : null,
            'is_active'   => array_key_exists('is_active', $data) ? (bool) $data['is_active'] : $item->is_active,
        ]);

        $this->clearMasterItemsCache();
        $this->logActivity($user, 'update', $item, "Edit Master Point Check Leveling #{$item->id}");

        return $item;
    }

    public function toggleMasterItemStatus(int $id, User $user): LevelingCheckItem
    {
        $item = LevelingCheckItem::findOrFail($id);
        $item->update(['is_active' => !$item->is_active]);

        $this->clearMasterItemsCache();
        $statusLabel = $item->is_active ? 'Aktif' : 'Nonaktif';
        $this->logActivity($user, 'update', $item, "Ubah status Master Point Check #{$item->id} menjadi {$statusLabel}");

        return $item;
    }

    /**
     * Bangun data Rekap & Monitoring Leveling Tahunan (Periode 1: Jan-Jun, Periode 2: Jul-Des) + Approval 4 tahap.
     * Seluruh perhitungan & relasi di-precompute di Service (bebas N+1 di Blade).
     */
    public function buildAnnualRecapData(string $plantCode, int $year, ?string $bagianFilter = null, ?User $currentUser = null): array
    {
        $cleanPlant = strtolower($plantCode);
        $prinsipList = LevelingCheckItem::DEFAULT_PRINSIP_ORDER;
        $activeItems = $this->getActiveCheckItems();
        $activeByPrinsip = $activeItems->groupBy('prinsip');

        // 1. Ambil seluruh inspector aktif plant ini
        $inspectors = $this->getInspectorsForPlant($cleanPlant, null, 500);
        $inspectorIds = $inspectors->pluck('id')->all();

        // 2. Batch-load bagian terbaru dari jadwal Kepatuhan Operator (1 query flat)
        $bagianMap = [];
        if (!empty($inspectorIds)) {
            $bagianMap = OperatorComplianceSchedule::whereIn('operator_id', $inspectorIds)
                ->whereNotNull('bagian')
                ->where('bagian', '!=', '')
                ->orderByDesc('schedule_date')
                ->get(['operator_id', 'bagian'])
                ->unique('operator_id')
                ->pluck('bagian', 'operator_id')
                ->all();
        }

        // 3. Batch-load seluruh audit tahun terpilih memakai range BETWEEN agar composite index (plant_code, audit_date) aktif
        $audits = LevelingAudit::with([
            'results:id,audit_id,check_item_id,prinsip,level,result,ng_status',
        ])
            ->where('plant_code', $cleanPlant)
            ->whereBetween('audit_date', ["{$year}-01-01", "{$year}-12-31"])
            ->orderByDesc('audit_date')
            ->orderByDesc('id')
            ->get();

        $auditsByInspector = $audits->groupBy('inspector_id');

        // 4. Batch-load seluruh approval rekap tahun terpilih (1 query + eager load approver)
        $approvalsByInspector = LevelingRecapApproval::with([
            'auditorApprover:id,name',
            'leaderApprover:id,name',
            'spvApprover:id,name',
            'asstMgrApprover:id,name',
        ])
            ->where('plant_code', $cleanPlant)
            ->where('year', $year)
            ->get()
            ->keyBy('inspector_id');

        $rows = [];
        $totalP1Done = 0;
        $totalP2Done = 0;
        $totalOpenNg = 0;

        foreach ($inspectors as $insp) {
            $inspAudits = $auditsByInspector->get($insp->id, collect());
            $latestAuditOverall = $inspAudits->first();
            $bagian = $latestAuditOverall?->bagian ?: ($bagianMap[$insp->id] ?? '-');

            if (!empty($bagianFilter) && strcasecmp(trim((string) $bagian), trim($bagianFilter)) !== 0) {
                continue;
            }

            $p1Audits = $inspAudits->filter(fn($a) => $a->audit_date && (int) $a->audit_date->month <= 6);
            $p2Audits = $inspAudits->filter(fn($a) => $a->audit_date && (int) $a->audit_date->month >= 7);

            $p1Data = $this->summarizeAuditForRecap($p1Audits->first(), $prinsipList, $activeByPrinsip);
            $p2Data = $this->summarizeAuditForRecap($p2Audits->first(), $prinsipList, $activeByPrinsip);

            if ($p1Data !== null) {
                $totalP1Done++;
            }
            if ($p2Data !== null) {
                $totalP2Done++;
            }

            $inspOpenNg = 0;
            foreach ($inspAudits as $a) {
                $inspOpenNg += $a->results
                    ->where('result', 'NG')
                    ->whereIn('ng_status', ['open', 'waiting_verify'])
                    ->count();
            }
            $totalOpenNg += $inspOpenNg;

            $auditorIds = $inspAudits->pluck('auditor_id')->filter()->map(fn($v) => (int) $v)->unique()->values()->all();
            $pendingAuditorName = $latestAuditOverall?->auditor_name ?: null;
            $approvalRecord = $approvalsByInspector->get($insp->id);
            $approvalStages = $this->formatRecapApprovalStages(
                $approvalRecord,
                $inspAudits->isNotEmpty(),
                $auditorIds,
                $currentUser,
                $pendingAuditorName
            );

            $rows[] = [
                'inspector_id'   => $insp->id,
                'inspector_name' => $insp->name,
                'bagian'         => $bagian,
                'p1'             => $p1Data,
                'p1_count'       => $p1Audits->count(),
                'p2'             => $p2Data,
                'p2_count'       => $p2Audits->count(),
                'open_ng_count'  => $inspOpenNg,
                'has_audit'      => $inspAudits->isNotEmpty(),
                'approval'       => $approvalStages,
            ];
        }

        $totalInspectors = count($rows);

        return [
            'prinsip_list' => $prinsipList,
            'rows'         => $rows,
            'summary'      => [
                'total_inspectors' => $totalInspectors,
                'p1_done'          => $totalP1Done,
                'p1_pct'           => $totalInspectors > 0 ? round(($totalP1Done / $totalInspectors) * 100, 1) : 0,
                'p2_done'          => $totalP2Done,
                'p2_pct'           => $totalInspectors > 0 ? round(($totalP2Done / $totalInspectors) * 100, 1) : 0,
                'total_open_ng'    => $totalOpenNg,
            ],
        ];
    }

    /**
     * Ringkas 1 audit untuk tampilan Rekap Tahunan (level tercapai per prinsip, skor, tanggal).
     */
    private function summarizeAuditForRecap(?LevelingAudit $audit, array $prinsipList, Collection $activeByPrinsip): ?array
    {
        if (!$audit) {
            return null;
        }

        $resultsByItemId = $audit->results->keyBy('check_item_id');
        $prinsipLevels = [];

        foreach ($prinsipList as $prinsip) {
            $pItems = $activeByPrinsip->get($prinsip, collect());
            $prinsipLevels[$prinsip] = $this->calculatePrinsipLevel($pItems, $resultsByItemId);
        }

        return [
            'audit_id'       => $audit->id,
            'audit_date'     => $audit->audit_date?->format('d/m/Y') ?? '-',
            'part_name'      => $audit->part_name,
            'score'          => $this->calculateAverageLevelScore($prinsipLevels),
            'total_ok'       => $audit->total_ok,
            'total_ng'       => $audit->total_ng,
            'prinsip_levels' => $prinsipLevels,
        ];
    }

    /**
     * Format status 4 tahap approval berjenjang (Auditor -> Leader/Karu/Kashift -> SPV -> Asst. Manager).
     */
    public function formatRecapApprovalStages(?LevelingRecapApproval $record, bool $hasAudit, array $auditorIds, ?User $currentUser, ?string $pendingAuditorName = null): array
    {
        $stageOrder = array_keys(LevelingRecapApproval::STAGES);
        $relMap = [
            'auditor'  => ['by' => 'auditor_approved_by',  'at' => 'auditor_approved_at',  'rel' => 'auditorApprover'],
            'leader'   => ['by' => 'leader_approved_by',   'at' => 'leader_approved_at',   'rel' => 'leaderApprover'],
            'spv'      => ['by' => 'spv_approved_by',      'at' => 'spv_approved_at',      'rel' => 'spvApprover'],
            'asst_mgr' => ['by' => 'asst_mgr_approved_by', 'at' => 'asst_mgr_approved_at', 'rel' => 'asstMgrApprover'],
        ];

        $approvedFlags = [];
        foreach ($stageOrder as $st) {
            $byCol = $relMap[$st]['by'];
            $approvedFlags[$st] = (bool) ($record && $record->$byCol);
        }

        $result = [];
        foreach ($stageOrder as $idx => $st) {
            $byCol = $relMap[$st]['by'];
            $atCol = $relMap[$st]['at'];
            $relName = $relMap[$st]['rel'];

            $isApproved = $approvedFlags[$st];
            $prevApproved = ($idx === 0) ? true : $approvedFlags[$stageOrder[$idx - 1]];
            $nextApproved = ($idx === count($stageOrder) - 1) ? false : $approvedFlags[$stageOrder[$idx + 1]];

            $hasRoleForStage = false;
            if ($currentUser) {
                $allowedRoles = LevelingRecapApproval::STAGE_ROLES[$st] ?? [];
                $hasRoleForStage = in_array($currentUser->role, $allowedRoles, true)
                    || ($st === 'auditor' && in_array((int) $currentUser->id, $auditorIds, true));
            }

            $canApprove = $hasAudit && !$isApproved && $prevApproved && $hasRoleForStage;
            $canCancel = $isApproved && !$nextApproved && $currentUser && (
                $currentUser->role === 'admin' || (int) ($record?->$byCol) === (int) $currentUser->id
            );

            $result[] = [
                'stage'         => $st,
                'label'         => LevelingRecapApproval::STAGES[$st],
                'approved'      => $isApproved,
                'approver_name' => $isApproved ? ($record?->$relName?->name ?? '-') : null,
                'approved_at'   => ($isApproved && $record?->$atCol) ? $record->$atCol->format('d/m/y') : null,
                'pending_name'  => (!$isApproved && $st === 'auditor') ? $pendingAuditorName : null,
                'can_approve'   => $canApprove,
                'can_cancel'    => $canCancel,
            ];
        }

        return $result;
    }

    /**
     * Proses Approve / Cancel Approval berjenjang pada Rekap Tahunan.
     */
    public function processRecapApproval(string $plantCode, int $year, int $inspectorId, string $stage, string $action, User $user): array
    {
        $cleanPlant = strtolower($plantCode);
        $stageOrder = array_keys(LevelingRecapApproval::STAGES);
        $stageIdx = array_search($stage, $stageOrder, true);

        if ($stageIdx === false) {
            throw ValidationException::withMessages(['stage' => ['Tahap approval tidak valid.']]);
        }

        $auditsForInsp = LevelingAudit::where('plant_code', $cleanPlant)
            ->where('inspector_id', $inspectorId)
            ->whereBetween('audit_date', ["{$year}-01-01", "{$year}-12-31"])
            ->orderByDesc('audit_date')
            ->orderByDesc('id')
            ->get(['auditor_id', 'auditor_name']);

        $auditorIds = $auditsForInsp->pluck('auditor_id')->filter()->map(fn($v) => (int) $v)->unique()->values()->all();
        $pendingAuditorName = $auditsForInsp->first()?->auditor_name ?: null;
        $hasAudit = $auditsForInsp->isNotEmpty();

        if (!$hasAudit) {
            throw ValidationException::withMessages([
                'inspector_id' => ['Inspector belum memiliki data audit pada tahun ' . $year . '.'],
            ]);
        }

        $record = LevelingRecapApproval::firstOrCreate([
            'plant_code'   => $cleanPlant,
            'year'         => $year,
            'inspector_id' => $inspectorId,
        ]);

        $colMap = [
            'auditor'  => ['by' => 'auditor_approved_by',  'at' => 'auditor_approved_at'],
            'leader'   => ['by' => 'leader_approved_by',   'at' => 'leader_approved_at'],
            'spv'      => ['by' => 'spv_approved_by',      'at' => 'spv_approved_at'],
            'asst_mgr' => ['by' => 'asst_mgr_approved_by', 'at' => 'asst_mgr_approved_at'],
        ];

        $byCol = $colMap[$stage]['by'];
        $atCol = $colMap[$stage]['at'];
        $stageLabel = LevelingRecapApproval::STAGES[$stage];

        if ($action === 'approve') {
            $allowedRoles = LevelingRecapApproval::STAGE_ROLES[$stage] ?? [];
            $isAllowed = in_array($user->role, $allowedRoles, true)
                || ($stage === 'auditor' && in_array((int) $user->id, $auditorIds, true));

            if (!$isAllowed) {
                throw ValidationException::withMessages([
                    'stage' => ["Anda tidak memiliki wewenang untuk approval tahap {$stageLabel}."],
                ]);
            }

            if ($stageIdx > 0) {
                $prevStage = $stageOrder[$stageIdx - 1];
                $prevByCol = $colMap[$prevStage]['by'];
                if (!$record->$prevByCol) {
                    $prevLabel = LevelingRecapApproval::STAGES[$prevStage];
                    throw ValidationException::withMessages([
                        'stage' => ["Approval tahap {$prevLabel} harus diselesaikan terlebih dahulu."],
                    ]);
                }
            }

            $record->update([
                $byCol => $user->id,
                $atCol => now(),
            ]);
            $this->logActivity($user, 'approve', $record, "Approve Rekap Leveling ({$year}) tahap {$stageLabel}");
        } else {
            if (!$record->$byCol) {
                throw ValidationException::withMessages([
                    'stage' => ["Tahap {$stageLabel} belum di-approve."],
                ]);
            }

            if ($stageIdx < count($stageOrder) - 1) {
                $nextStage = $stageOrder[$stageIdx + 1];
                $nextByCol = $colMap[$nextStage]['by'];
                if ($record->$nextByCol) {
                    $nextLabel = LevelingRecapApproval::STAGES[$nextStage];
                    throw ValidationException::withMessages([
                        'stage' => ["Batalkan approval tahap {$nextLabel} terlebih dahulu."],
                    ]);
                }
            }

            if ($user->role !== 'admin' && (int) $record->$byCol !== (int) $user->id) {
                throw ValidationException::withMessages([
                    'stage' => ['Pembatalan approval hanya dapat dilakukan oleh approver terkait atau admin.'],
                ]);
            }

            $record->update([
                $byCol => null,
                $atCol => null,
            ]);
            $this->logActivity($user, 'update', $record, "Batal Approval Rekap Leveling ({$year}) tahap {$stageLabel}");
        }

        $fresh = $record->fresh([
            'auditorApprover:id,name',
            'leaderApprover:id,name',
            'spvApprover:id,name',
            'asstMgrApprover:id,name',
        ]);

        return $this->formatRecapApprovalStages($fresh, true, $auditorIds, $user, $pendingAuditorName);
    }

    private function logActivity(User $user, string $action, $model, string $description): void
    {
        try {
            ActivityLog::create([
                'user_id'     => $user->id,
                'action'      => $action,
                'model_type'  => get_class($model),
                'model_id'    => $model->id ?? null,
                'description' => $description,
                'ip_address'  => request()?->ip(),
                'user_agent'  => request()?->userAgent(),
            ]);
        } catch (\Throwable $e) {
            // Jangan gagalkan transaksi utama hanya karena log
        }
    }
}
