<?php

namespace App\Services;

use App\Models\MachineStatus;

class MachineStatusService extends BaseService
{
    /**
     * Update or create machine status
     */
    public function updateStatus(array $data): MachineStatus
    {
        // Use plant from request data if provided, otherwise use auth user's plant
        $plantIdentifier = !empty($data['plant']) ? $data['plant'] : auth()->user()->plant_id;
        $plantId = $this->resolvePlantId($plantIdentifier);

        $machineStatus = MachineStatus::withoutGlobalScope('plant')->updateOrCreate(
            [
                'plant_id' => $plantId,
                'type' => $data['type'],
                'number' => $data['number'],
            ],
            [
                'status' => $data['status'],
                'description' => $data['description'] ?? null,
                'created_by' => auth()->user()?->name ?? 'System'
            ]
        );

        // Langsung bersihkan cache monitoring dashboard agar perubahan status langsung tampil real-time
        DashboardService::clearDashboardCache();

        return $machineStatus;
    }
}
