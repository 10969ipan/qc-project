<?php

namespace App\Services;

use App\Models\DurabilityPlatingChecksheet;
use App\Models\Plant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Helpers\ShiftHelper;

class DurabilityPlatingChecksheetService extends BaseService
{
    use \App\Traits\ChecksheetServiceTrait;

    public function getQuery($filters = [])
    {
        $query = DurabilityPlatingChecksheet::query()->with(['standard']);

        if (!empty($filters['plant'])) {
            $plantId = Plant::resolveId($filters['plant']);
            if ($plantId) {
                $query->where('plant_id', $plantId);
            }
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('date', '<=', $filters['end_date']);
        }

        if (!empty($filters['approval_status'])) {
            if ($filters['approval_status'] == 'completed') {
                $query->where('approval_status', 'completed');
            } else {
                $query->where('approval_status', '!=', 'completed');
            }
        }

        if (!empty($filters['shift'])) {
            $query->where('shift', $filters['shift']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('standard', function ($q) use ($search) {
                $q->where('part_name', 'like', "%{$search}%")
                  ->orWhere('customer', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    public function getFilteredChecksheets($filters = [], $perPage = 10)
    {
        return $this->getQuery($filters)->latest()->paginate($perPage)->withQueryString();
    }

    public function createChecksheet(array $data, callable $exportMapper = null)
    {
        return DB::transaction(function () use ($data) {
            $checksheet = DurabilityPlatingChecksheet::create($data);
            return ['checksheet' => $checksheet];
        });
    }

    public function updateChecksheet($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $checksheet = DurabilityPlatingChecksheet::findOrFail($id);
            $checksheet->update($data);
            return $checksheet;
        });
    }

    public function deleteChecksheet($id)
    {
        return DB::transaction(function () use ($id) {
            $checksheet = DurabilityPlatingChecksheet::findOrFail($id);
            $checksheet->delete();
            return true;
        });
    }

    public function updateApprovalStatus(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $checksheet = DurabilityPlatingChecksheet::findOrFail($id);
            $this->processFullApprovalUpdate($checksheet, $data);
            $checksheet->save();
            return $checksheet;
        });
    }
}
