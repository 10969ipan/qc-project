<?php

namespace App\Http\Controllers;

use App\Http\Requests\Leveling\ApproveLevelingRecapRequest;
use App\Http\Requests\Leveling\BulkSaveLevelingResultRequest;
use App\Http\Requests\Leveling\CorrectLevelingNgRequest;
use App\Http\Requests\Leveling\SaveLevelingResultRequest;
use App\Http\Requests\Leveling\StoreLevelingAuditRequest;
use App\Http\Requests\Leveling\StoreLevelingCheckItemRequest;
use App\Http\Requests\Leveling\UpdateLevelingNgPhotosRequest;
use App\Http\Requests\Leveling\UpdateLevelingNgTextRequest;
use App\Http\Requests\Leveling\UploadLevelingEvidenceRequest;
use App\Http\Requests\Leveling\VerifyLevelingNgRequest;
use App\Models\AppMenu;
use App\Models\LevelingAudit;
use App\Models\LevelingRecapApproval;
use App\Models\OperatorComplianceItem;
use App\Models\User;
use App\Services\LevelingInspectorService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LevelingInspectorController extends Controller
{
    public function __construct(private LevelingInspectorService $service)
    {
    }

    /**
     * Cek hak akses menu (Leader/Karu/Kashift ke atas + izin AppMenu per plant).
     */
    private function authorizeAccess(Request $request, string $plantCode): void
    {
        $user = $request->user();
        if (!$user || !in_array($user->role, LevelingRecapApproval::REKAP_ACCESS_ROLES, true)) {
            abort(403, 'Menu Leveling Inspector hanya dapat diakses oleh level Leader/Karu/Kashift ke atas.');
        }

        if ($user->role !== 'admin') {
            $menu = AppMenu::where('route', 'checksheet.leveling_inspector.index')
                ->where('plant_code', strtolower($plantCode))
                ->first();

            if ($menu && !$user->hasPermission($menu->id, 'view')) {
                abort(403, 'Anda tidak memiliki hak akses untuk membuka menu Leveling Inspector pada plant ini.');
            }
        }
    }

    /**
     * Cek hak akses fitur manajemen (Audit Baru, Daftar Temuan NG, Master Point) — hanya SPV ke atas.
     */
    private function authorizeSpvUp(Request $request, string $plantCode): void
    {
        $this->authorizeAccess($request, $plantCode);
        $user = $request->user();
        if (!$user || !in_array($user->role, OperatorComplianceItem::MASTER_MANAGER_ROLES, true)) {
            abort(403, 'Fitur ini hanya dapat diakses oleh level SPV ke atas.');
        }
    }

    private function resolvePlantCode(Request $request): string
    {
        $user = $request->user();
        $default = $user && $user->plant ? $user->plant->code : 'karawang';
        return strtolower($request->get('plant', $default));
    }

    public function index(Request $request)
    {
        $plantCode = $this->resolvePlantCode($request);
        $this->authorizeAccess($request, $plantCode);

        $inspectorId = $request->filled('inspector_id') ? (int) $request->get('inspector_id') : null;
        $auditId = $request->filled('audit_id') ? (int) $request->get('audit_id') : null;
        $explicitDate = $request->filled('date') ? $request->get('date') : null;

        $selectedInspector = $inspectorId
            ? User::select(['id', 'name'])->find($inspectorId)
            : null;
        $inspectors = $selectedInspector ? collect([$selectedInspector]) : collect();

        $selectedAudit = null;
        if ($auditId) {
            $selectedAudit = LevelingAudit::with(['inspector:id,name', 'auditor:id,name'])
                ->where('plant_code', $plantCode)
                ->find($auditId);
        }
        if (!$selectedAudit) {
            $selectedAudit = $this->service->getAuditsForFilter($plantCode, $explicitDate, $inspectorId, 1)->first();
        }

        $date = $explicitDate ?? '';

        $currentUser = $request->user();
        $canManageAuditTools = $currentUser && in_array($currentUser->role, OperatorComplianceItem::MASTER_MANAGER_ROLES, true);
        $canEditSelectedAudit = $this->service->canEditAudit($selectedAudit, $currentUser);

        $tableData = $this->service->buildAuditTableData($selectedAudit);
        $openNgCount = $canManageAuditTools ? $this->service->countOpenNg($plantCode) : 0;
        $bagianOptions = $canManageAuditTools ? OperatorComplianceItem::getBagianOptions() : [];
        $mejaOptions = $canManageAuditTools
            ? array_map(static fn (int $i): string => 'Meja ' . $i, range(1, 20))
            : [];
        $machineNumbers = in_array(strtolower($plantCode), ['jakarta', 'jkt'], true)
            ? [1, 2, 3, 4, 5, 6, 8, 9, 10, 11, 12, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23]
            : [1, 2, 3, 4, 5, 6, 7, 8, 9, 11, 12, 14, 15, 16, 17, 18, 19];
        $mesinOptions = $canManageAuditTools
            ? array_map(static fn (int $n): string => 'Mesin ' . $n, $machineNumbers)
            : [];

        return view('leveling_inspector.index', [
            'plantCode'            => $plantCode,
            'date'                 => $date,
            'inspectorId'          => $inspectorId,
            'inspectors'           => $inspectors,
            'selectedAudit'        => $selectedAudit,
            'prinsipGroups'        => $tableData['prinsip_groups'],
            'summary'              => $tableData['summary'],
            'openNgCount'          => $openNgCount,
            'bagianOptions'        => $bagianOptions,
            'mejaOptions'          => $mejaOptions,
            'mesinOptions'         => $mesinOptions,
            'canManageAuditTools'  => $canManageAuditTools,
            'canEditSelectedAudit' => $canEditSelectedAudit,
        ]);
    }

    private function authorizeRekapAccess(Request $request, string $plantCode): void
    {
        $user = $request->user();
        if (!$user || !in_array($user->role, LevelingRecapApproval::REKAP_ACCESS_ROLES, true)) {
            abort(403, 'Halaman Rekap Leveling Inspector hanya dapat diakses oleh Leader/Karu/Kashift ke atas.');
        }

        if ($user->role !== 'admin') {
            $menu = AppMenu::where('route', 'checksheet.leveling_inspector.index')
                ->where('plant_code', strtolower($plantCode))
                ->first();

            if ($menu && !$user->hasPermission($menu->id, 'view')) {
                abort(403, 'Anda tidak memiliki hak akses untuk membuka menu Leveling Inspector pada plant ini.');
            }
        }
    }

    public function rekap(Request $request)
    {
        $plantCode = $this->resolvePlantCode($request);
        $this->authorizeRekapAccess($request, $plantCode);

        $year = (int) $request->get('year', Carbon::now()->year);
        $bagianFilter = $request->filled('bagian') ? trim((string) $request->get('bagian')) : null;

        $recapData = $this->service->buildAnnualRecapData($plantCode, $year, $bagianFilter, $request->user());
        $bagianOptions = OperatorComplianceItem::getBagianOptions();

        return view('leveling_inspector.rekap', [
            'plantCode'     => $plantCode,
            'year'          => $year,
            'bagianFilter'  => $bagianFilter,
            'bagianOptions' => $bagianOptions,
            'prinsipList'   => $recapData['prinsip_list'],
            'rows'          => $recapData['rows'],
            'summary'       => $recapData['summary'],
        ]);
    }

    public function rekapPrint(Request $request)
    {
        $plantCode = $this->resolvePlantCode($request);
        $this->authorizeRekapAccess($request, $plantCode);

        $year = (int) $request->get('year', Carbon::now()->year);
        $bagianFilter = $request->filled('bagian') ? trim((string) $request->get('bagian')) : null;

        $recapData = $this->service->buildAnnualRecapData($plantCode, $year, $bagianFilter, $request->user());

        return view('leveling_inspector.rekap_print', [
            'plantCode'    => $plantCode,
            'year'         => $year,
            'bagianFilter' => $bagianFilter,
            'prinsipList'  => $recapData['prinsip_list'],
            'rows'         => $recapData['rows'],
            'summary'      => $recapData['summary'],
        ]);
    }

    public function approveRekap(ApproveLevelingRecapRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $stages = $this->service->processRecapApproval(
            $validated['plant_code'],
            (int) $validated['year'],
            (int) $validated['inspector_id'],
            $validated['stage'],
            $validated['action'],
            $request->user()
        );

        $msg = $validated['action'] === 'approve'
            ? 'Approval berhasil disimpan.'
            : 'Approval berhasil dibatalkan.';

        return response()->json([
            'success'  => true,
            'message'  => $msg,
            'approval' => $stages,
        ]);
    }

    public function print(Request $request)
    {
        $plantCode = $this->resolvePlantCode($request);
        $this->authorizeAccess($request, $plantCode);

        $auditId = (int) $request->get('audit_id');
        $audit = LevelingAudit::with(['inspector:id,name', 'auditor:id,name'])
            ->where('plant_code', $plantCode)
            ->findOrFail($auditId);

        $tableData = $this->service->buildAuditTableData($audit);

        return view('leveling_inspector.print', [
            'plantCode'     => $plantCode,
            'audit'         => $audit,
            'prinsipGroups' => $tableData['prinsip_groups'],
            'summary'       => $tableData['summary'],
        ]);
    }

    public function storeAudit(StoreLevelingAuditRequest $request): JsonResponse
    {
        $audit = $this->service->createAudit($request->validated(), $request->user());

        return response()->json([
            'success'      => true,
            'message'      => 'Audit Leveling Inspector berhasil dibuat.',
            'audit'        => $audit,
            'redirect_url' => route('checksheet.leveling_inspector.index', [
                'plant'        => $audit->plant_code,
                'inspector_id' => $audit->inspector_id,
                'audit_id'     => $audit->id,
            ]),
        ]);
    }

    public function destroyAudit(Request $request, int $id): JsonResponse
    {
        $plantCode = $this->resolvePlantCode($request);
        $this->authorizeAccess($request, $plantCode);

        $this->service->deleteAudit($id, $request->user());

        return response()->json([
            'success'      => true,
            'message'      => 'Data audit Leveling Inspector berhasil dihapus.',
            'redirect_url' => route('checksheet.leveling_inspector.index', ['plant' => $plantCode]),
        ]);
    }

    public function saveResult(SaveLevelingResultRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $data = $this->service->saveResult(
            (int) $validated['audit_id'],
            (int) $validated['check_item_id'],
            $validated['result'] ?? null,
            $request->user()
        );

        return response()->json([
            'success'        => true,
            'result_id'      => $data['result']->id,
            'result'         => $data['result']->result,
            'ng_status'      => $data['result']->ng_status,
            'prinsip'        => $data['prinsip'],
            'level'          => $data['level'],
            'achieved_level' => $data['achieved_level'],
            'summary'        => $data['summary'],
            'open_ng_count'  => $data['open_ng_count'],
        ]);
    }

    public function bulkSaveResult(BulkSaveLevelingResultRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $data = $this->service->bulkSaveResults(
            (int) $validated['audit_id'],
            $validated['check_item_ids'],
            $validated['result'],
            $request->user()
        );

        return response()->json([
            'success'         => true,
            'message'         => count($data['updated_rows']) . ' point check berhasil di-set ' . $validated['result'] . '.',
            'updated_rows'    => $data['updated_rows'],
            'achieved_levels' => $data['achieved_levels'],
            'summary'         => $data['summary'],
            'open_ng_count'   => $data['open_ng_count'],
        ]);
    }

    public function uploadEvidence(UploadLevelingEvidenceRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $files = $request->file('evidences');
        if (empty($files) && $request->hasFile('evidence')) {
            $files = [$request->file('evidence')];
        }

        $record = $this->service->uploadEvidences(
            (int) $validated['audit_id'],
            (int) $validated['check_item_id'],
            is_array($files) ? $files : [$files],
            $request->user(),
            $validated['evidence_note'] ?? null
        );

        $urls = array_map(fn($p) => Storage::url($p), $record->getEvidencePathsList());

        return response()->json([
            'success'       => true,
            'message'       => 'Foto evidence berhasil diupload.',
            'result_id'     => $record->id,
            'evidence_url'  => $urls[0] ?? null,
            'evidence_urls' => $urls,
            'evidence_note' => $record->evidence_note,
        ]);
    }

    public function updateEvidenceNote(Request $request, int $id): JsonResponse
    {
        $this->authorizeAccess($request, $this->resolvePlantCode($request));
        $validated = $request->validate([
            'evidence_note' => ['required', 'string', 'max:1000'],
        ], [
            'evidence_note.required' => 'Keterangan evidence wajib diisi.',
        ]);

        $record = $this->service->updateEvidenceNote($id, $validated['evidence_note'], $request->user());

        return response()->json([
            'success'       => true,
            'message'       => 'Keterangan evidence berhasil diperbarui.',
            'result_id'     => $record->id,
            'evidence_note' => $record->evidence_note,
        ]);
    }

    public function destroyEvidence(Request $request, int $id): JsonResponse
    {
        $this->authorizeAccess($request, $this->resolvePlantCode($request));
        $index = $request->filled('index') ? (int) $request->input('index') : null;
        $record = $this->service->deleteEvidence($id, $request->user(), $index);

        $urls = array_map(fn($p) => Storage::url($p), $record->getEvidencePathsList());

        return response()->json([
            'success'       => true,
            'message'       => 'Foto evidence berhasil dihapus.',
            'result_id'     => $record->id,
            'evidence_urls' => $urls,
            'evidence_note' => $record->evidence_note,
        ]);
    }

    public function lookupInspectors(Request $request): JsonResponse
    {
        $plantCode = $this->resolvePlantCode($request);
        $this->authorizeAccess($request, $plantCode);

        $inspectors = $this->service->getInspectorsForPlant($plantCode, $request->get('q'), 30);

        return response()->json([
            'success' => true,
            'data'    => $inspectors,
        ]);
    }

    public function lookupAudits(Request $request): JsonResponse
    {
        $plantCode = $this->resolvePlantCode($request);
        $this->authorizeAccess($request, $plantCode);

        $date = $request->filled('date') ? (string) $request->get('date') : null;
        $inspectorId = $request->filled('inspector_id') ? (int) $request->get('inspector_id') : null;

        $audits = $this->service->searchAudits($plantCode, $request->get('q'), $date, $inspectorId, 10);

        return response()->json([
            'success' => true,
            'data'    => $audits->map(fn($a) => [
                'id'    => $a->id,
                'label' => ($a->audit_date ? $a->audit_date->format('d/m/Y') : '-')
                    . ' — ' . ($a->inspector_name ?? '-')
                    . ' — ' . $a->part_name
                    . ($a->part_number ? ' (' . $a->part_number . ')' : ''),
            ])->values(),
        ]);
    }

    public function lookupBagian(Request $request): JsonResponse
    {
        $plantCode = $this->resolvePlantCode($request);
        $this->authorizeAccess($request, $plantCode);

        $inspectorId = (int) $request->get('inspector_id');
        $bagian = $inspectorId > 0 ? $this->service->getBagianForInspector($inspectorId) : null;

        return response()->json([
            'success' => true,
            'bagian'  => $bagian,
        ]);
    }

    public function lookupParts(Request $request): JsonResponse
    {
        $plantCode = $this->resolvePlantCode($request);
        $this->authorizeAccess($request, $plantCode);

        $inspectorId = (int) $request->get('inspector_id');
        $res = $this->service->searchPartsForInspector($plantCode, $inspectorId, $request->get('q'), 20);

        return response()->json([
            'success'       => true,
            'bagian'        => $res['bagian'],
            'default_table' => $res['default_table'] ?? null,
            'fallback_all'  => $res['fallback_all'],
            'data'         => $res['items']->map(fn($it) => [
                'id'          => $it->id,
                'name'        => $it->name,
                'part_number' => $it->part_number,
                'label'       => trim($it->name . ($it->part_number ? ' / ' . $it->part_number : '')),
            ])->values(),
        ]);
    }

    public function listNg(Request $request): JsonResponse
    {
        $plantCode = $this->resolvePlantCode($request);
        $this->authorizeSpvUp($request, $plantCode);

        $findings = $this->service->listNgFindings($plantCode, [
            'status'       => $request->get('status'),
            'date_from'    => $request->get('date_from'),
            'date_to'      => $request->get('date_to'),
            'inspector_id' => $request->get('inspector_id'),
        ]);

        $currentUser = $request->user();

        $rows = $findings->map(function ($r) use ($currentUser) {
            $canVerify = $r->ng_status === 'waiting_verify'
                && in_array($currentUser->role, OperatorComplianceItem::MASTER_MANAGER_ROLES, true);

            $canUnverify = $r->ng_status === 'closed'
                && (
                    in_array($currentUser->role, ['admin', 'superadmin'], true)
                    || ($r->audit && (int) $r->audit->auditor_id === (int) $currentUser->id)
                    || (int) $r->verified_by === (int) $currentUser->id
                );

            $evUrls = array_map(fn($p) => Storage::url($p), $r->getEvidencePathsList());
            $corrEvUrls = array_map(fn($p) => Storage::url($p), $r->getCorrectiveEvidencePathsList());

            return [
                'id'                        => $r->id,
                'audit_date'                => $r->audit?->audit_date?->format('d/m/Y') ?? '-',
                'inspector_name'            => $r->audit?->inspector?->name ?? '-',
                'bagian'                    => $r->audit?->proses_line_label ?? '-',
                'part_name'                 => $r->audit?->part_name ?? '-',
                'part_number'               => $r->audit?->part_number ?? '',
                'part_label'                => trim(($r->audit?->part_name ?? '-') . ($r->audit?->part_number ? ' / ' . $r->audit->part_number : '')),
                'auditor_name'              => $r->audit?->auditor_name ?? '-',
                'prinsip'                   => $r->prinsip,
                'level'                     => $r->level,
                'point_check'               => $r->point_check,
                'evidence_url'              => $evUrls[0] ?? null,
                'evidence_urls'             => $evUrls,
                'evidence_note'             => $r->evidence_note,
                'ng_status'                 => $r->ng_status,
                'why_1'                     => $r->why_1,
                'why_2'                     => $r->why_2,
                'why_3'                     => $r->why_3,
                'root_cause'                => $r->root_cause,
                'corrective_action'         => $r->corrective_action,
                'corrective_date'           => $r->corrective_date?->format('d/m/Y'),
                'corrective_date_iso'       => $r->corrective_date?->toDateString(),
                'corrective_evidence_url'   => $corrEvUrls[0] ?? null,
                'corrective_evidence_urls'  => $corrEvUrls,
                'corrector_name'            => $r->corrector?->name,
                'verifier_name'             => $r->verifier?->name,
                'verified_at'               => $r->verified_at?->format('d/m/Y H:i'),
                'reject_reason'             => $r->reject_reason,
                'can_verify'                => $canVerify,
                'can_unverify'              => $canUnverify,
            ];
        })->values();

        return response()->json([
            'success'       => true,
            'open_ng_count' => $this->service->countOpenNg($plantCode),
            'data'          => $rows,
        ]);
    }

    public function printNg(Request $request)
    {
        $plantCode = $this->resolvePlantCode($request);
        $this->authorizeSpvUp($request, $plantCode);

        $filters = [
            'status'       => $request->get('status'),
            'date_from'    => $request->get('date_from'),
            'date_to'      => $request->get('date_to'),
            'inspector_id' => $request->get('inspector_id'),
        ];

        $findings = $this->service->listNgFindings($plantCode, $filters);

        $inspectorName = null;
        if (!empty($filters['inspector_id'])) {
            $inspectorName = \App\Models\User::where('id', $filters['inspector_id'])->value('name');
        }

        $summary = [
            'total'          => $findings->count(),
            'open'           => $findings->where('ng_status', 'open')->count(),
            'waiting_verify' => $findings->where('ng_status', 'waiting_verify')->count(),
            'closed'         => $findings->where('ng_status', 'closed')->count(),
        ];

        return view('leveling_inspector.ng_print', [
            'plantCode'     => $plantCode,
            'findings'      => $findings,
            'filters'       => $filters,
            'inspectorName' => $inspectorName,
            'summary'       => $summary,
        ]);
    }

    public function correctNg(CorrectLevelingNgRequest $request, int $id): JsonResponse
    {
        $files = $request->file('corrective_evidences', []);
        $record = $this->service->submitNgCorrection(
            $id,
            $request->validated(),
            is_array($files) ? $files : [$files],
            $request->user()
        );

        return response()->json([
            'success'       => true,
            'message'       => 'Tindakan perbaikan NG berhasil disimpan dan menunggu verifikasi.',
            'ng_status'     => $record->ng_status,
            'open_ng_count' => $this->service->countOpenNg($record->audit->plant_code),
        ]);
    }

    public function updateNgCorrectionText(UpdateLevelingNgTextRequest $request, int $id): JsonResponse
    {
        $record = $this->service->updateNgCorrectionText($id, $request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Tindakan perbaikan berhasil diperbarui.',
            'data'    => [
                'why_1'               => $record->why_1,
                'why_2'               => $record->why_2,
                'why_3'               => $record->why_3,
                'root_cause'          => $record->root_cause,
                'corrective_action'   => $record->corrective_action,
                'corrective_date'     => $record->corrective_date?->format('d/m/Y'),
                'corrective_date_iso' => $record->corrective_date?->toDateString(),
                'corrector_name'      => $record->corrector?->name,
            ],
        ]);
    }

    public function updateNgCorrectionPhotos(UpdateLevelingNgPhotosRequest $request, int $id): JsonResponse
    {
        $keepIndices = $request->input('keep_indices', []);
        $newFiles = $request->file('new_evidences', []);

        $record = $this->service->updateNgCorrectionPhotos(
            $id,
            is_array($keepIndices) ? $keepIndices : [],
            is_array($newFiles) ? $newFiles : ($newFiles ? [$newFiles] : []),
            $request->user()
        );

        $corrEvUrls = array_map(fn($p) => Storage::url($p), $record->getCorrectiveEvidencePathsList());

        return response()->json([
            'success'                  => true,
            'message'                  => 'Foto evidence perbaikan berhasil diperbarui.',
            'corrective_evidence_urls' => $corrEvUrls,
        ]);
    }

    public function verifyNg(VerifyLevelingNgRequest $request, int $id): JsonResponse
    {
        $validated = $request->validated();
        $record = $this->service->verifyNgFinding(
            $id,
            $validated['action'],
            $validated['reject_reason'] ?? null,
            $request->user()
        );

        $msg = match ($validated['action']) {
            'approve'  => 'Verifikasi disetujui. Status temuan NG menjadi Closed.',
            'unverify' => 'Verifikasi dibatalkan. Status temuan NG kembali Waiting Verify.',
            default    => 'Perbaikan ditolak. Status temuan NG kembali Open.',
        };

        return response()->json([
            'success'       => true,
            'message'       => $msg,
            'ng_status'     => $record->ng_status,
            'open_ng_count' => $this->service->countOpenNg($record->audit->plant_code),
        ]);
    }

    public function listMasterItems(Request $request): JsonResponse
    {
        $this->authorizeSpvUp($request, $this->resolvePlantCode($request));

        return response()->json([
            'success' => true,
            'data'    => $this->service->listAllMasterItems(),
        ]);
    }

    public function storeMasterItem(StoreLevelingCheckItemRequest $request): JsonResponse
    {
        $item = $this->service->storeMasterItem($request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Master Point Check berhasil ditambahkan.',
            'data'    => $item,
        ]);
    }

    public function updateMasterItem(StoreLevelingCheckItemRequest $request, int $id): JsonResponse
    {
        $item = $this->service->updateMasterItem($id, $request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Master Point Check berhasil diperbarui.',
            'data'    => $item,
        ]);
    }

    public function toggleMasterItem(Request $request, int $id): JsonResponse
    {
        $this->authorizeSpvUp($request, $this->resolvePlantCode($request));
        $item = $this->service->toggleMasterItemStatus($id, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Status Master Point Check berhasil diubah.',
            'data'    => $item,
        ]);
    }
}
