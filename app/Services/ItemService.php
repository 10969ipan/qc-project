<?php

namespace App\Services;

use App\Models\Item;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ItemService extends BaseService
{
    /**
     * Get filtered items with pagination
     * 
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getFilteredItems(array $filters)
    {
        $query = Item::with(['category', 'requester', 'approver', 'spvApprover', 'asstMgrApprover', 'mgrApprover']);

        // Apply plant filter if present
        if (!empty($filters['plant']) && $filters['plant'] !== 'all' && $filters['plant'] !== 'total') {
            $resolvedPlant = $this->resolvePlantId($filters['plant']);
            if ($resolvedPlant) {
                $query->where($query->getModel()->getTable() . '.plant_id', $resolvedPlant);
            }
        }

        // Apply approval status filter
        if (!empty($filters['approval_status'])) {
            $query->where('approval_status', $filters['approval_status']);
        }

        // Apply specific ID filter (Highest Priority)
        if (!empty($filters['item_id'])) {
            $query->where('id', $filters['item_id']);
            return $query->paginate(10)->withQueryString();
        }

        // Apply global search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('customer', 'like', '%' . $search . '%')
                    ->orWhere('part_number', 'like', '%' . $search . '%')
                    ->orWhere('sap_code', 'like', '%' . $search . '%')
                    ->orWhereHas('category', function ($cq) use ($search) {
                        $cq->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        // Apply specific search filters (fallback if needed)
        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        if (!empty($filters['customer'])) {
            $query->where('customer', 'like', '%' . $filters['customer'] . '%');
        }

        if (!empty($filters['part_number'])) {
            $query->where('part_number', 'like', '%' . $filters['part_number'] . '%');
        }

        if (!empty($filters['sap_code'])) {
            $query->where('sap_code', 'like', '%' . $filters['sap_code'] . '%');
        }

        if (!empty($filters['category'])) {
            $query->where('category_id', $filters['category']);
        }

        return $query->orderBy('name', 'asc')->paginate(10)->withQueryString();
    }

    /**
     * Create new item
     * 
     * @param array $data
     * @return Item
     */
    public function createItem(array $data): Item
    {
        DB::beginTransaction();
        try {
            // Pre-validate uniqueness in DB to avoid moving files if SQL insert will fail
            if (!empty($data['sap_code']) && $data['sap_code'] !== '-') {
                $plantId = $this->resolvePlantId($data['plant_id'] ?? $data['plant'] ?? auth()->user()->plant_id);
                $partNumber = $data['part_number'] ?? null;
                $query = Item::where('sap_code', $data['sap_code'])
                    ->where('plant_id', $plantId)
                    ->where('category_id', $data['category_id']);
                if (!empty($partNumber) && $partNumber !== '-') {
                    $query->where('part_number', $partNumber);
                } else {
                    $query->where(function ($q) {
                        $q->whereNull('part_number')->orWhere('part_number', '')->orWhere('part_number', '-');
                    });
                }
                if ($query->exists()) {
                    throw new \Exception("Duplicate entry '{$data['sap_code']}' for part number '{$partNumber}'");
                }
            }

            // Handle multi-file upload
            $filePaths = [];
            if (isset($data['files'])) {
                foreach ($data['files'] as $file) {
                    $filePaths[] = $this->handleItemFileUpload($file, $data['customer'] ?? null);
                }
            }

            // Process defects
            $defects = $this->processDefects($data['defects'] ?? null);

            // Process dimension standards
            $dimensionStandards = $this->processDimensionStandards($data);

            // Handle similar part file upload from data
            if (isset($data['similar_part_file']) && $data['similar_part_file'] instanceof \Illuminate\Http\UploadedFile) {
                $data['similar_part_file_path'] = $this->handleItemFileUpload($data['similar_part_file'], $data['customer'] ?? null);
            }

            $creatorRole = auth()->user()->role ?? '';
            $isLeadershipCreator = in_array($creatorRole, ['supervisor', 'supervisor_qc', 'asst_manager', 'asst_manager_qc', 'admin']);
            $currentUserId = auth()->id();
            $now = now();

            $resolvedPlantId = $this->resolvePlantId($data['plant_id'] ?? $data['plant'] ?? auth()->user()->plant_id);

            // Jika dibuat oleh SPV QC, asst_mgr_approved_by menggunakan user Asst Manager dari plant tersebut
            $spvApprovedBy = null;
            $asstMgrApprovedBy = null;
            if ($isLeadershipCreator) {
                if (in_array($creatorRole, ['asst_manager', 'asst_manager_qc'])) {
                    $spvApprovedBy = $currentUserId;
                    $asstMgrApprovedBy = $currentUserId;
                } elseif (in_array($creatorRole, ['supervisor', 'supervisor_qc'])) {
                    $spvApprovedBy = $currentUserId;
                    // Cari user Asst Manager QC (prioritaskan plant terkait, atau Asst Manager QC lintas plant)
                    $asstMgrUser = \App\Models\User::whereIn('role', ['asst_manager', 'asst_manager_qc'])
                        ->where('plant_id', $resolvedPlantId)
                        ->first() 
                        ?: \App\Models\User::whereIn('role', ['asst_manager', 'asst_manager_qc'])->first();
                    $asstMgrApprovedBy = $asstMgrUser ? $asstMgrUser->id : $currentUserId;
                } else {
                    // Admin
                    $spvApprovedBy = $currentUserId;
                    $asstMgrApprovedBy = $currentUserId;
                }
            }

            // Create item (jika dibuat oleh SPV QC / Asst Mgr / Admin, langsung approved tanpa perlu approval lagi)
            $item = Item::create([
                'plant_id' => $resolvedPlantId,
                'name' => $data['name'],
                'category_id' => $data['category_id'],
                'approval_status' => $isLeadershipCreator ? 'approved' : 'pending',
                'approved_by' => $isLeadershipCreator ? ($asstMgrApprovedBy ?? $currentUserId) : null,
                'approved_at' => $isLeadershipCreator ? $now : null,
                'spv_approved_by' => $spvApprovedBy,
                'spv_approved_at' => $isLeadershipCreator ? $now : null,
                'asst_mgr_approved_by' => $asstMgrApprovedBy,
                'asst_mgr_approved_at' => $isLeadershipCreator ? $now : null,
                'mgr_approved_by' => null,
                'mgr_approved_at' => null,
                'rejection_reason' => null,
                'requested_by' => $currentUserId ?? ($data['requested_by'] ?? null),
                'file_path' => $filePaths[0] ?? null, // Fallback for legacy
                'file_paths' => $filePaths,
                'similar_part_file_path' => $data['similar_part_file_path'] ?? null,
                'customer' => $data['customer'] ?? null,
                'part_number' => $data['part_number'] ?? null,
                'sap_code' => $data['sap_code'] ?? null,
                'cavity' => $data['cavity'] ?? 1,
                'weight_standard' => $data['weight_standard'] ?? null,
                'standard_cycle_time' => $data['standard_cycle_time'] ?? null,
                'defects' => $defects,
                'dimension_standards' => $dimensionStandards,
            ]);

            DB::commit();
            return $item;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update existing item
     * 
     * @param int $id
     * @param array $data
     * @return Item
     */
    public function updateItem(string $id, array $data): Item
    {
        DB::beginTransaction();
        try {
            // Pre-validate uniqueness in DB to avoid moving files if SQL update will fail
            if (!empty($data['sap_code']) && $data['sap_code'] !== '-') {
                $itemCurrent = Item::find($id);
                $plantId = $this->resolvePlantId($data['plant_id'] ?? $data['plant'] ?? ($itemCurrent ? $itemCurrent->plant_id : auth()->user()->plant_id));
                $partNumber = $data['part_number'] ?? ($itemCurrent ? $itemCurrent->part_number : null);
                $query = Item::where('sap_code', $data['sap_code'])
                    ->where('plant_id', $plantId)
                    ->where('category_id', $data['category_id'])
                    ->where('id', '!=', $id);
                if (!empty($partNumber) && $partNumber !== '-') {
                    $query->where('part_number', $partNumber);
                } else {
                    $query->where(function ($q) {
                        $q->whereNull('part_number')->orWhere('part_number', '')->orWhere('part_number', '-');
                    });
                }
                if ($query->exists()) {
                    throw new \Exception("Duplicate entry '{$data['sap_code']}' for part number '{$partNumber}'");
                }
            }

            // Get item (with or without global scope based on role)
            if (auth()->user()->role === 'admin') {
                $item = Item::withoutGlobalScope('plant')->findOrFail($id);
            } else {
                $item = Item::findOrFail($id);
            }

            $customerChanged = $item->customer !== ($data['customer'] ?? null);
            $filePaths = $item->file_paths ?? [];

            // Handle similar part file upload from data
            if (isset($data['similar_part_file']) && $data['similar_part_file'] instanceof \Illuminate\Http\UploadedFile) {
                // Delete old file if exists
                if ($item->similar_part_file_path) {
                    $this->deleteFile($item->similar_part_file_path);
                }
                $data['similar_part_file_path'] = $this->handleItemFileUpload($data['similar_part_file'], $data['customer'] ?? null);
            } else {
                $data['similar_part_file_path'] = $item->similar_part_file_path;
            }

            // If customer changed, move all existing files
            if ($customerChanged) {
                if (!empty($filePaths)) {
                    $newFilePaths = [];
                    foreach ($filePaths as $path) {
                        $newFilePaths[] = $this->moveFilePathToCustomerFolder($path, $data['customer'] ?? null);
                    }
                    $filePaths = $newFilePaths;
                }
                if ($data['similar_part_file_path']) {
                    $data['similar_part_file_path'] = $this->moveFilePathToCustomerFolder($data['similar_part_file_path'], $data['customer'] ?? null);
                }
            }

            // Handle new standard file uploads
            if (isset($data['files'])) {
                foreach ($data['files'] as $file) {
                    $filePaths[] = $this->handleItemFileUpload($file, $data['customer'] ?? null);
                }
            }

            // Process defects
            $defects = $this->processDefects($data['defects'] ?? null);

            // Process dimension standards
            $dimensionStandards = $this->processDimensionStandards($data);

            // Track pending changes (Before vs After) for approval modal
            $resolvedPlantId = $this->resolvePlantId($data['plant_id'] ?? $data['plant'] ?? $item->plant_id);
            $trackedFields = [
                'name' => ['label' => 'Nama Item', 'old' => $item->name, 'new' => $data['name']],
                'part_number' => ['label' => 'No Part', 'old' => $item->part_number, 'new' => $data['part_number'] ?? null],
                'customer' => ['label' => 'Customer', 'old' => $item->customer, 'new' => $data['customer'] ?? null],
                'sap_code' => ['label' => 'Kode SAP', 'old' => $item->sap_code, 'new' => $data['sap_code'] ?? null],
                'cavity' => ['label' => 'Cavity', 'old' => $item->cavity, 'new' => $data['cavity'] ?? 1],
                'weight_standard' => ['label' => 'Standar Berat', 'old' => $item->weight_standard, 'new' => $data['weight_standard'] ?? null],
                'standard_cycle_time' => ['label' => 'SCT (Plating)', 'old' => $item->standard_cycle_time, 'new' => $data['standard_cycle_time'] ?? null],
                'defects' => [
                    'label' => 'List Defect',
                    'old' => is_array($item->defects) ? implode(', ', $item->defects) : '',
                    'new' => is_array($defects) ? implode(', ', $defects) : '',
                ],
            ];

            // Category change
            if ($item->category_id != $data['category_id']) {
                $oldCatName = optional($item->category)->name ?? '-';
                $newCatName = \App\Models\Category::withoutGlobalScope('plant')->where('id', $data['category_id'])->value('name') ?? '-';
                $trackedFields['category'] = ['label' => 'Kategori', 'old' => $oldCatName, 'new' => $newCatName];
            }

            // Plant change
            if ($item->plant_id != $resolvedPlantId) {
                $oldPlantName = optional($item->plant)->name ?? '-';
                $newPlantName = \App\Models\Plant::where('id', $resolvedPlantId)->value('name') ?? '-';
                $trackedFields['plant'] = ['label' => 'Plant', 'old' => $oldPlantName, 'new' => $newPlantName];
            }

            $diffChanges = [];
            foreach ($trackedFields as $key => $info) {
                $oldVal = trim((string)($info['old'] ?? ''));
                $newVal = trim((string)($info['new'] ?? ''));
                if ($oldVal !== $newVal) {
                    $beforeText = ($oldVal !== '' && $oldVal !== '-') ? $info['old'] : 'Tidak Ada Data';
                    $afterText = ($newVal !== '' && $newVal !== '-') ? $info['new'] : 'Tidak Ada Data';
                    $diffChanges[$key] = [
                        'label' => $info['label'],
                        'before' => $beforeText,
                        'after' => $afterText,
                    ];
                }
            }

            // Track dimension standards changes (cek isi detail nilai & toleransi, bukan hanya jumlah point)
            $oldDimJson = !empty($item->dimension_standards) ? json_encode($item->dimension_standards) : '';
            $newDimJson = !empty($dimensionStandards) ? json_encode($dimensionStandards) : '';
            if ($oldDimJson !== $newDimJson) {
                $oldCount = !empty($item->dimension_standards) ? count($item->dimension_standards) : 0;
                $newCount = !empty($dimensionStandards) ? count($dimensionStandards) : 0;
                $beforeDimText = $oldCount > 0 ? "{$oldCount} point dimensi" : 'Tidak Ada Data';
                $afterDimText = $newCount > 0 ? ($oldCount === $newCount ? "{$newCount} point dimensi (nilai diubah)" : "{$newCount} point dimensi") : 'Tidak Ada Data';
                $diffChanges['dimension_standards'] = [
                    'label' => 'Standar Dimensi In-Process',
                    'before' => $beforeDimText,
                    'after' => $afterDimText,
                ];
            }

            // Track standard file changes
            $oldFilesCount = count($item->file_paths ?? ($item->file_path ? [$item->file_path] : []));
            $newFilesCount = count($filePaths);
            if ($oldFilesCount !== $newFilesCount || (isset($data['files']) && !empty($data['files']))) {
                $diffChanges['files'] = [
                    'label' => 'Dokumen Standar',
                    'before' => $oldFilesCount > 0 ? "{$oldFilesCount} file terlampir" : 'Tidak Ada File',
                    'after' => $newFilesCount > 0 ? "{$newFilesCount} file terlampir" : 'Tidak Ada File',
                ];
            }

            // Track similar part file change
            if ($item->similar_part_file_path !== $data['similar_part_file_path']) {
                $diffChanges['similar_part_file'] = [
                    'label' => 'Dokumen Standar 2',
                    'before' => $item->similar_part_file_path ? basename($item->similar_part_file_path) : 'Tidak Ada File',
                    'after' => $data['similar_part_file_path'] ? basename($data['similar_part_file_path']) : 'Tidak Ada File',
                ];
            }

            $updaterRole = auth()->user()->role ?? '';
            $isLeadershipUpdater = in_array($updaterRole, ['supervisor', 'supervisor_qc', 'asst_manager', 'asst_manager_qc', 'admin']);
            $currentUserId = auth()->id();
            $now = now();

            // Jika diedit oleh SPV QC, asst_mgr_approved_by menggunakan user Asst Manager dari plant tersebut
            $spvApprovedBy = null;
            $asstMgrApprovedBy = null;
            if ($isLeadershipUpdater) {
                if (in_array($updaterRole, ['asst_manager', 'asst_manager_qc'])) {
                    $spvApprovedBy = $item->spv_approved_by ?: $currentUserId;
                    $asstMgrApprovedBy = $currentUserId;
                } elseif (in_array($updaterRole, ['supervisor', 'supervisor_qc'])) {
                    $spvApprovedBy = $currentUserId;
                    // Cari user Asst Manager QC (prioritaskan plant terkait, atau Asst Manager QC lintas plant)
                    $asstMgrUser = \App\Models\User::whereIn('role', ['asst_manager', 'asst_manager_qc'])
                        ->where('plant_id', $resolvedPlantId)
                        ->first() 
                        ?: \App\Models\User::whereIn('role', ['asst_manager', 'asst_manager_qc'])->first();
                    $asstMgrApprovedBy = $asstMgrUser ? $asstMgrUser->id : $currentUserId;
                } else {
                    // Admin
                    $spvApprovedBy = $item->spv_approved_by ?: $currentUserId;
                    $asstMgrApprovedBy = $item->asst_mgr_approved_by ?: $currentUserId;
                }
            }

            // Update item: jika diedit oleh SPV QC / Asst Mgr / Admin, langsung fully approved tanpa perlu approval lagi
            $item->update([
                'name' => $data['name'],
                'category_id' => $data['category_id'],
                'approval_status' => $isLeadershipUpdater ? 'approved' : 'pending',
                'approved_by' => $isLeadershipUpdater ? ($asstMgrApprovedBy ?? $currentUserId) : null,
                'approved_at' => $isLeadershipUpdater ? $now : null,
                'spv_approved_by' => $spvApprovedBy,
                'spv_approved_at' => $isLeadershipUpdater ? $now : null,
                'asst_mgr_approved_by' => $asstMgrApprovedBy,
                'asst_mgr_approved_at' => $isLeadershipUpdater ? $now : null,
                'mgr_approved_by' => null,
                'mgr_approved_at' => null,
                'rejection_reason' => null,
                'requested_by' => $currentUserId ?? ($data['requested_by'] ?? $item->requested_by),
                'pending_changes' => $isLeadershipUpdater ? null : (!empty($diffChanges) ? $diffChanges : null),
                'plant_id' => $resolvedPlantId,
                'customer' => $data['customer'] ?? null,
                'part_number' => $data['part_number'] ?? null,
                'sap_code' => $data['sap_code'] ?? null,
                'file_path' => $filePaths[0] ?? null,
                'file_paths' => $filePaths,
                'similar_part_file_path' => $data['similar_part_file_path'],
                'cavity' => $data['cavity'] ?? 1,
                'weight_standard' => $data['weight_standard'] ?? null,
                'standard_cycle_time' => $data['standard_cycle_time'] ?? null,
                'defects' => $defects,
                'dimension_standards' => $dimensionStandards,
            ]);

            // Jika diedit oleh leadership (langsung approved), simpan riwayat perubahan sekarang
            if ($isLeadershipUpdater && !empty($diffChanges)) {
                foreach ($diffChanges as $fieldName => $diff) {
                    \App\Models\ItemChangeHistory::create([
                        'item_id' => $item->id,
                        'plant_id' => $resolvedPlantId,
                        'user_id' => $currentUserId,
                        'field_name' => $fieldName,
                        'field_label' => $diff['label'],
                        'before_value' => $diff['before'],
                        'after_value' => $diff['after'],
                    ]);
                }
            }

            DB::commit();
            return $item;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Approve master item by tier (spv, asst_mgr, mgr)
     * 
     * @param string $id
     * @param int $userId
     * @param string $tier 'spv', 'asst_mgr', 'mgr'
     * @return Item
     */
    public function approveItemTier(string $id, int $userId, string $tier): Item
    {
        $query = Item::query();
        if (in_array(auth()->user()->role ?? '', ['admin', 'asst_manager', 'asst_manager_qc', 'manager', 'manager_qc'])) {
            $query->withoutGlobalScope('plant');
        }
        $item = $query->findOrFail($id);

        $now = now();
        $updates = [
            'rejection_reason' => null,
        ];

        if ($tier === 'spv') {
            $updates['spv_approved_by'] = $userId;
            $updates['spv_approved_at'] = $now;
        } elseif ($tier === 'asst_mgr') {
            // Wajib Supervisor QC menyetujui terlebih dahulu
            if (empty($item->spv_approved_at)) {
                throw new \InvalidArgumentException('Item ini belum disetujui oleh Supervisor QC. Persetujuan harus dilakukan oleh Supervisor QC terlebih dahulu.');
            }

            $updates['asst_mgr_approved_by'] = $userId;
            $updates['asst_mgr_approved_at'] = $now;
        }

        // Cek apakah seluruh jenjang (SPV & Asst Mgr) telah menyetujui
        $spvApproved = !empty($item->spv_approved_at) || ($tier === 'spv');
        $asstMgrApproved = !empty($item->asst_mgr_approved_at) || ($tier === 'asst_mgr');

        if ($spvApproved && $asstMgrApproved) {
            $updates['approval_status'] = 'approved';
            $updates['approved_by'] = $userId;
            $updates['approved_at'] = $now;

            // Bersihkan notifikasi approval yang sudah selesai
            \App\Models\Notification::where('type', 'master_item_approval')
                ->whereRaw("JSON_EXTRACT(data, '$.item_id') = ?", [(string) $item->id])
                ->delete();

            // Simpan riwayat perubahan saat sudah di-approve
            if (!empty($item->pending_changes)) {
                foreach ($item->pending_changes as $fieldName => $diff) {
                    \App\Models\ItemChangeHistory::create([
                        'item_id' => $item->id,
                        'plant_id' => $item->plant_id,
                        'user_id' => $item->requested_by ?? $userId,
                        'field_name' => $fieldName,
                        'field_label' => $diff['label'],
                        'before_value' => $diff['before'],
                        'after_value' => $diff['after'],
                    ]);
                }
                $updates['pending_changes'] = null;
            }
        } else {
            $updates['approval_status'] = 'pending';
        }

        $item->update($updates);
        return $item;
    }

    /**
     * Approve master item by supervisor or above (Legacy / Admin one-shot)
     */
    public function approveItem(string $id, int $userId): Item
    {
        $query = Item::query();
        if (in_array(auth()->user()->role ?? '', ['admin', 'asst_manager', 'asst_manager_qc', 'manager', 'manager_qc'])) {
            $query->withoutGlobalScope('plant');
        }
        $item = $query->findOrFail($id);
        $now = now();

        // Simpan riwayat perubahan saat sudah di-approve
        if (!empty($item->pending_changes)) {
            foreach ($item->pending_changes as $fieldName => $diff) {
                \App\Models\ItemChangeHistory::create([
                    'item_id' => $item->id,
                    'plant_id' => $item->plant_id,
                    'user_id' => $item->requested_by ?? $userId,
                    'field_name' => $fieldName,
                    'field_label' => $diff['label'],
                    'before_value' => $diff['before'],
                    'after_value' => $diff['after'],
                ]);
            }
        }

        $item->update([
            'approval_status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => $now,
            'spv_approved_by' => $item->spv_approved_by ?: $userId,
            'spv_approved_at' => $item->spv_approved_at ?: $now,
            'asst_mgr_approved_by' => $item->asst_mgr_approved_by ?: $userId,
            'asst_mgr_approved_at' => $item->asst_mgr_approved_at ?: $now,
            'mgr_approved_by' => $item->mgr_approved_by ?: $userId,
            'mgr_approved_at' => $item->mgr_approved_at ?: $now,
            'pending_changes' => null,
            'rejection_reason' => null,
        ]);

        \App\Models\Notification::where('type', 'master_item_approval')
            ->whereRaw("JSON_EXTRACT(data, '$.item_id') = ?", [(string) $item->id])
            ->delete();

        return $item;
    }

    /**
     * Reject master item
     * 
     * @param string $id
     * @param int $userId
     * @param string|null $reason
     * @return Item
     */
    public function rejectItem(string $id, int $userId, ?string $reason = null): Item
    {
        $query = Item::query();
        if (in_array(auth()->user()->role ?? '', ['admin', 'asst_manager', 'asst_manager_qc', 'manager', 'manager_qc'])) {
            $query->withoutGlobalScope('plant');
        }
        $item = $query->findOrFail($id);

        $item->update([
            'approval_status' => 'rejected',
            'approved_by' => $userId,
            'approved_at' => now(),
            'spv_approved_by' => null,
            'spv_approved_at' => null,
            'asst_mgr_approved_by' => null,
            'asst_mgr_approved_at' => null,
            'mgr_approved_by' => null,
            'mgr_approved_at' => null,
            'rejection_reason' => $reason,
        ]);

        // Bersihkan notifikasi pending approval dari approver
        \App\Models\Notification::where('type', 'master_item_approval')
            ->whereRaw("JSON_EXTRACT(data, '$.item_id') = ?", [(string) $item->id])
            ->delete();

        // Buat notifikasi untuk user yang mengajukan (requester)
        if (!empty($item->requested_by)) {
            $rejecter = \App\Models\User::find($userId);
            $rejecterName = $rejecter ? $rejecter->name : 'Atasan';
            $plantCode = optional($item->plant)->code ?: 'total';
            $itemUrl = route('admin.items.index', [
                'plant' => $plantCode,
                'f_item_id' => $item->id,
                'item_id' => $item->id
            ]);

            \App\Models\Notification::create([
                'user_id' => $item->requested_by,
                'type' => 'master_item_rejected',
                'title' => 'Master Item Ditolak: ' . $item->name,
                'message' => "Pengajuan ditolak oleh {$rejecterName}. Alasan: \"{$reason}\". Silakan update/perbaiki data item.",
                'data' => [
                    'item_id' => (string) $item->id,
                    'url' => $itemUrl,
                    'rejection_reason' => $reason,
                    'rejected_by' => $rejecterName,
                ],
                'is_read' => false,
            ]);
        }

        return $item;
    }

    /**
     * Delete item
     * 
     * @param int $id
     * @return bool
     */
    public function deleteItem(string $id): bool
    {
        DB::beginTransaction();
        try {
            $query = Item::query();
            if (auth()->user()->role === 'admin') {
                $query->withoutGlobalScope('plant');
            }
            $item = $query->findOrFail($id);

            // Delete all associated files
            if (!empty($item->file_paths)) {
                foreach ($item->file_paths as $path) {
                    $this->deleteFile($path);
                }
            } elseif ($item->file_path) {
                $this->deleteFile($item->file_path);
            }

            if ($item->similar_part_file_path) {
                $this->deleteFile($item->similar_part_file_path);
            }

            $item->delete();

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Handle item file upload
     * 
     * @param \Illuminate\Http\UploadedFile $file
     * @param string|null $customer
     * @return string
     */
    private function handleItemFileUpload($file, ?string $customer): string
    {
        $filename = time() . '_' . $file->getClientOriginalName();
        $customerFolder = $this->getCustomerFolder($customer);
        $path = 'master item/' . $customerFolder;

        $uploadPath = public_path($path);
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $file->move($uploadPath, $filename);

        return $path . '/' . $filename;
    }

    /**
     * Move a single file path to new customer folder
     * 
     * @param string $path
     * @param string|null $newCustomer
     * @return string
     */
    private function moveFilePathToCustomerFolder(string $path, ?string $newCustomer): string
    {
        $oldPath = public_path($path);

        if (file_exists($oldPath)) {
            $filename = basename($path);
            $customerFolder = $this->getCustomerFolder($newCustomer);
            $newPath = public_path('master item/' . $customerFolder);

            if (!file_exists($newPath)) {
                mkdir($newPath, 0755, true);
            }

            $newFilePath = $newPath . '/' . $filename;
            rename($oldPath, $newFilePath);

            return 'master item/' . $customerFolder . '/' . $filename;
        }

        return $path;
    }

    /**
     * Delete a single PDF from an item
     * 
     * @param string $id
     * @param int $index
     * @return void
     */
    public function deleteItemPdf(string $id, int $index): void
    {
        $item = Item::withoutGlobalScope('plant')->findOrFail($id);
        $filePaths = $item->file_paths;

        // Handle legacy file_path if file_paths is empty
        if (empty($filePaths) && $index == 0 && $item->file_path) {
            $this->deleteFile($item->file_path);
            $item->update([
                'file_path' => null,
                'file_paths' => []
            ]);
            return;
        }

        if (isset($filePaths[$index])) {
            $pathToDelete = $filePaths[$index];
            $this->deleteFile($pathToDelete);

            array_splice($filePaths, $index, 1);
            $filePaths = array_values($filePaths); // Ensure clean indexing

            $item->update([
                'file_paths' => $filePaths,
                'file_path' => $filePaths[0] ?? null
            ]);
        }
    }

    /**
     * Delete similar part PDF from an item
     * 
     * @param string $id
     * @return void
     */
    public function deleteItemSimilarPdf(string $id): void
    {
        // Use withoutGlobalScope if needed, similar to deleteItemPdf logic
        if (auth()->user()->role === 'admin') {
            $item = Item::withoutGlobalScope('plant')->findOrFail($id);
        } else {
            $item = Item::findOrFail($id);
        }

        if ($item->similar_part_file_path) {
            $this->deleteFile($item->similar_part_file_path);

            $item->update([
                'similar_part_file_path' => null
            ]);
        }
    }

    /**
     * Determine customer folder
     * 
     * @param string|null $customer
     * @return string
     */
    private function getCustomerFolder(?string $customer): string
    {
        if (!$customer) {
            return 'others';
        }

        $customer = strtolower(trim($customer));

        if (strpos($customer, 'astra honda') !== false || strpos($customer, 'ahm') !== false) {
            return 'ahm';
        } elseif (strpos($customer, 'yamaha') !== false || strpos($customer, 'yimm') !== false) {
            return 'yimm';
        }

        return 'others';
    }

    /**
     * Resolve file path by checking subdirectories
     * 
     * @param Item $item
     * @return void
     */
    private function resolveFilePath(Item $item): void
    {
        if (!$item->file_path) {
            return;
        }

        $currentPath = public_path($item->file_path);
        if (file_exists($currentPath)) {
            return;
        }

        // File not found, check subdirectories
        $filename = basename($item->file_path);
        $subfolders = ['ahm', 'yimm', 'others'];

        foreach ($subfolders as $folder) {
            $relativePath = 'master item/' . $folder . '/' . $filename;
            $candidatePath = public_path($relativePath);

            if (file_exists($candidatePath)) {
                $item->file_path = $relativePath;
                $item->save();
                return;
            }
        }

        // Check root folder
        $rootRelative = 'master item/' . $filename;
        $rootPath = public_path($rootRelative);
        if (file_exists($rootPath)) {
            $item->file_path = $rootRelative;
            $item->save();
        }
    }

    /**
     * Process defects from text input
     * 
     * @param string|null $defectsText
     * @return array|null
     */
    private function processDefects(?string $defectsText): ?array
    {
        if (empty($defectsText)) {
            return null;
        }

        return array_values(array_filter(array_map('trim', explode("\n", $defectsText))));
    }

    /**
     * Process dimension standards
     * 
     * @param array $data
     * @return array|null
     */
    private function processDimensionStandards(array $data): ?array
    {
        if (empty($data['dimension_points'])) {
            return null;
        }

        $dimensionStandards = [];
        foreach ($data['dimension_points'] as $key => $point) {
            if (!empty($point)) {
                $alatUkur = !empty($data['dimension_alat_ukur'][$key]) ? trim((string)$data['dimension_alat_ukur'][$key]) : null;
                $dimensionStandards[] = [
                    'point' => $point,
                    'size' => $data['dimension_sizes'][$key] ?? null,
                    'tolerance' => $data['dimension_tolerances'][$key] ?? null,
                    'min' => $data['dimension_mins'][$key] ?? null,
                    'max' => $data['dimension_maxs'][$key] ?? null,
                    'alat_ukur' => $alatUkur,
                ];
            }
        }

        return empty($dimensionStandards) ? null : $dimensionStandards;
    }
}
