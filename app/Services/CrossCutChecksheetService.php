<?php

namespace App\Services;

use App\Models\CrossCutChecksheet;
use App\Models\Item;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CrossCutChecksheetService extends BaseService
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get filtered checksheets with pagination
     * 
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getFilteredChecksheets(array $filters)
    {
        return $this->buildFilteredQuery($filters)->paginate(10)->withQueryString();
    }

    /**
     * Build the filtered query
     * 
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function buildFilteredQuery(array $filters)
    {
        $query = CrossCutChecksheet::with('item')->orderBy('qc_datetime', 'desc')->orderBy('created_at', 'desc');

        // Apply plant filter if present
        if (isset($filters['plant'])) {
            $query->where($query->getModel()->getTable() . '.plant_id', $this->resolvePlantId($filters['plant']));
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('cross_cut_checksheets.qc_datetime', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('cross_cut_checksheets.qc_datetime', '<=', $filters['end_date']);
        }
        if (!empty($filters['item_id'])) {
            $query->where('item_id', $filters['item_id']);
        }

        if (!empty($filters['approval_status'])) {
            $this->applyApprovalStatusFilter($query, $filters['approval_status']);
        }

        if (!empty($filters['operator_initials'])) {
            $query->where('operator_initials', $filters['operator_initials']);
        }

        if (!empty($filters['customer'])) {
            $query->whereHas('item', function ($q) use ($filters) {
                $q->where('customer', $filters['customer']);
            });
        }

        if (!empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->where(function ($q) use ($searchTerm) {
                $q->whereHas('item', function ($itemQuery) use ($searchTerm) {
                    $itemQuery->where('name', 'like', "%{$searchTerm}%")
                        ->orWhere('customer', 'like', "%{$searchTerm}%")
                        ->orWhere('part_number', 'like', "%{$searchTerm}%");
                })->orWhere('operator_initials', 'like', "%{$searchTerm}%");
            });
        }

        // ID filter (for direct links from Sortir)
        if (!empty($filters['id'])) {
            $query->where($query->getModel()->getTable() . '.id', $filters['id']);
        }

        if (!empty($filters['check_type']) && is_array($filters['check_type'])) {
            $query->whereIn('check_type', $filters['check_type']);
        }

        if (!empty($filters['shift'])) {
            $query->where('qc_shift', $filters['shift']);
        }

        return $query;
    }

    /**
     * Create new cross cut checksheet
     * 
     * @param array $data
     * @return CrossCutChecksheet
     */
    public function createChecksheet(array $data): CrossCutChecksheet
    {
        DB::beginTransaction();
        try {
            $imagePath = null;
            if (isset($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
                $realPath = $data['image']->getRealPath();
                if (!$data['image']->isValid() || empty($realPath) || !file_exists($realPath)) {
                    $errMsg = $data['image']->getErrorMessage() ?: 'File temporary tidak ditemukan.';
                    \Log::error("Upload failed in CrossCutChecksheetService. Error: {$errMsg}");
                    throw new \Exception("Gambar gagal diunggah atau hilang dari server temporer ({$errMsg}). Silakan coba lagi.");
                }
                $imagePath = $data['image']->store('cross_cut_images', 'public');
            }

            $checksheet = CrossCutChecksheet::create([
                'plant_id' => $this->resolvePlantId($data['plant_id'] ?? $data['plant'] ?? auth()->user()->plant_id),
                'item_id' => $data['item_id'],
                'production_shift' => $data['production_shift'],
                'qc_shift' => $data['qc_shift'],
                'production_datetime' => $data['production_datetime'],
                'qc_datetime' => $data['qc_datetime'],
                'image_path' => (!empty($imagePath)) ? $imagePath : null,
                'chemical_catalyst' => $data['chemical_catalyst'] ?? null,
                'chemical_abu' => $data['chemical_abu'] ?? null,
                'position_remark_judgment' => $data['position_remark_judgment'],
                'position_remark_no_lot' => $data['position_remark_no_lot'],
                'result_remark' => $data['result_remark'] ?? null,
                'keterangan' => $data['keterangan'] ?? null,
                'next_proses' => $data['next_proses'] ?? null,
                'cycle_time' => $data['cycle_time'] ?? null,
                'operator_initials' => $data['operator_initials'] ?? null,
            ]);

            DB::commit();

            Log::info('Checksheet Cross Cut berhasil dibuat', [
                'user_id' => auth()->id(),
                'checksheet_id' => $checksheet->id,
                'plant_id' => $checksheet->plant_id
            ]);

            // Notifications

            return $checksheet;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal membuat checksheet Cross Cut', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Update cross cut checksheet
     * 
     * @param int $id
     * @param array $data
     * @return CrossCutChecksheet
     */
    public function updateChecksheet(int $id, array $data): CrossCutChecksheet
    {
        DB::beginTransaction();
        try {
            $checksheet = CrossCutChecksheet::findOrFail($id);

            $imagePath = $checksheet->image_path;
            if (isset($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
                $realPath = $data['image']->getRealPath();
                if (!$data['image']->isValid() || empty($realPath) || !file_exists($realPath)) {
                    $errMsg = $data['image']->getErrorMessage() ?: 'File temporary tidak ditemukan.';
                    \Log::error("Upload failed in CrossCutChecksheetService update. Error: {$errMsg}");
                    throw new \Exception("Gambar gagal diunggah atau hilang dari server temporer ({$errMsg}). Silakan coba lagi.");
                }

                if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                    Storage::disk('public')->delete($imagePath);
                }
                $imagePath = $data['image']->store('cross_cut_images', 'public');
            }

            $checksheet->update(array_merge($data, ['image_path' => $imagePath]));

            DB::commit();

            Log::info('Checksheet Cross Cut berhasil diperbarui', [
                'user_id' => auth()->id(),
                'checksheet_id' => $checksheet->id,
                'plant_id' => $checksheet->plant_id
            ]);

            return $checksheet;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal memperbarui checksheet Cross Cut', [
                'user_id' => auth()->id(),
                'checksheet_id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Delete cross cut checksheet
     * 
     * @param int $id
     * @return bool
     */
    public function deleteChecksheet(int $id): bool
    {
        DB::beginTransaction();
        try {
            $query = CrossCutChecksheet::query();
            if (auth()->user()->role === 'admin') {
                $query->withoutGlobalScope('plant');
            }
            $checksheet = $query->findOrFail($id);

            if ($checksheet->image_path) {
                Storage::disk('public')->delete($checksheet->image_path);
            }
            $checksheet->delete();

            DB::commit();

            Log::info('Checksheet Cross Cut berhasil dihapus', [
                'user_id' => auth()->id(),
                'checksheet_id' => $id
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menghapus checksheet Cross Cut', [
                'user_id' => auth()->id(),
                'checksheet_id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Update approval status (admin)
     * 
     * @param int $id
     * @param array $data
     * @return CrossCutChecksheet
     */
    public function updateApprovalStatus(int $id, array $data): CrossCutChecksheet
    {
        DB::beginTransaction();
        try {
            $checksheet = CrossCutChecksheet::findOrFail($id);
            $user = auth()->user();

            $this->updateLevel($checksheet, 'karu_qc', 'karu_qc_approved_at', $data['karu_qc'], $user);
            $this->updateLevel($checksheet, 'kashift_plating', 'kashift_plating_approved_at', $data['kashift_plating'], $user);
            $this->updateLevel($checksheet, 'supervisor_plating', 'supervisor_plating_approved_at', $data['supervisor_plating'], $user);
            $this->updateLevel($checksheet, 'supervisor_qc', 'supervisor_approved_at', $data['supervisor_qc'], $user);
            $this->updateLevel($checksheet, 'asst_manager_plating', 'asst_manager_plating_approved_at', $data['asst_manager_plating'], $user);
            $this->updateLevel($checksheet, 'asst_manager_qc', 'asst_manager_approved_at', $data['asst_manager_qc'], $user);

            if (
                $checksheet->asst_manager_qc === 'REJECTED' ||
                $checksheet->asst_manager_plating === 'REJECTED' ||
                $checksheet->supervisor_qc === 'REJECTED' ||
                $checksheet->supervisor_plating === 'REJECTED' ||
                $checksheet->kashift_plating === 'REJECTED' ||
                $checksheet->karu_qc === 'REJECTED'
            ) {
                $checksheet->approval_status = 'Rejected';
            } elseif ($checksheet->asst_manager_qc === 'Approved') {
                $checksheet->approval_status = 'Approved';
                $checksheet->rejection_remarks = null;
            } else {
                $checksheet->approval_status = 'Pending';
                $checksheet->rejection_remarks = null;
            }

            $checksheet->save();

            DB::commit();
            return $checksheet;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Apply approval status filter to query
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     * @return void
     */
    private function applyApprovalStatusFilter($query, string $status): void
    {
        if ($status == 'approved') {
            $query->whereNotNull('manager_qc')->where('manager_qc', '!=', 'REJECTED');
        } elseif ($status == 'rejected') {
            $query->where(function ($q) {
                $q->where('karu_qc', 'REJECTED')
                    ->orWhere('kashift_plating', 'REJECTED')
                    ->orWhere('supervisor_plating', 'REJECTED')
                    ->orWhere('supervisor_qc', 'REJECTED')
                    ->orWhere('manager_plating', 'REJECTED')
                    ->orWhere('manager_qc', 'REJECTED');
            });
        } elseif ($status == 'pending') {
            $query->whereNull('karu_qc');
        }
    }

    /**
     * Update single approval level
     * 
     * @param CrossCutChecksheet $checksheet
     * @param string $field
     * @param string $dateField
     * @param string $status
     * @param $user
     * @return void
     */
    private function updateLevel(CrossCutChecksheet $checksheet, $field, $dateField, $status, $user): void
    {
        if ($status === 'Approved') {
            if (is_null($checksheet->$field) || $checksheet->$field === 'REJECTED') {
                $checksheet->$field = $user->name;
                $checksheet->$dateField = now();
            }
        } elseif ($status === 'Rejected') {
            if ($checksheet->$field !== 'REJECTED') {
                $checksheet->$field = 'REJECTED';
                $checksheet->$dateField = now();
            }
        } else {
            $checksheet->$field = null;
            $checksheet->$dateField = null;
        }
    }

    /**
     * Handle single level approval
     * 
     * @param int $id
     * @param string $type
     * @param array $data
     * @return CrossCutChecksheet
     */
    public function singleApprove(int $id, string $type, array $data): CrossCutChecksheet
    {
        DB::beginTransaction();
        try {
            $query = CrossCutChecksheet::query();
            if (auth()->user()->role === 'admin') {
                $query->withoutGlobalScope('plant');
            }
            $checksheet = $query->findOrFail($id);
            $user = auth()->user();

            // Role validation
            if ($user->role !== 'admin') {
                if ($user->plant === 'jakarta') {
                    throw new \Exception('User Jakarta tidak memiliki akses approval untuk Cross Cut.', 403);
                }

                $allowedRole = $type;
                if ($type === 'supervisor')
                    $allowedRole = 'supervisor';
                if ($user->role !== $allowedRole) {
                    throw new \Exception('Unauthorized to approve at this level.', 403);
                }
            }

            // Check if rejected
            $wasRejected = ($checksheet->{$field = $this->getApprovalField($type)} === 'REJECTED');
            if ($wasRejected) {
                $checksheet->rejection_remarks = null;
            }

            // Check if already approved
            if ($checksheet->{$field} && $checksheet->{$field} !== 'REJECTED') {
                throw new \Exception("Checksheet sudah disetujui oleh " . $this->getApprovalLabel($type));
            }

            // Apply approval
            if ($type === 'kashift_plating') {
                $checksheet->kashift_plating = $data['approver_name'];
                $checksheet->kashift_plating_approved_at = now();
            } else {
                $checksheet->{$field} = $user->name;
                $dateField = $this->getApprovalDateField($type);
                $checksheet->{$dateField} = now();

                if ($type === 'asst_manager') {
                    $checksheet->approval_status = 'Approved';
                }
            }

            $checksheet->save();
            DB::commit();
            return $checksheet;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function getApprovalField($type)
    {
        $fields = [
            'karu_qc' => 'karu_qc',
            'kashift_plating' => 'kashift_plating',
            'supervisor_plating' => 'supervisor_plating',
            'asst_manager_plating' => 'asst_manager_plating',
            'supervisor' => 'supervisor_qc',
            'asst_manager' => 'asst_manager_qc',
        ];
        return $fields[$type] ?? $type;
    }

    private function getApprovalDateField($type)
    {
        $fields = [
            'karu_qc' => 'karu_qc_approved_at',
            'kashift_plating' => 'kashift_plating_approved_at',
            'supervisor_plating' => 'supervisor_plating_approved_at',
            'asst_manager_plating' => 'asst_manager_plating_approved_at',
            'supervisor' => 'supervisor_approved_at',
            'asst_manager' => 'asst_manager_approved_at',
        ];
        return $fields[$type] ?? "{$type}_approved_at";
    }

    private function getApprovalLabel($type)
    {
        $labels = [
            'karu_qc' => 'Karu QC',
            'kashift_plating' => 'Kashift Plating',
            'supervisor_plating' => 'SPV Plating',
            'asst_manager_plating' => 'Asst Manager Plating',
            'supervisor' => 'SPV Quality',
            'manager_plating' => 'Manager Plating',
            'manager' => 'Manager QC',
        ];
        return $labels[$type] ?? ucfirst($type);
    }
}
