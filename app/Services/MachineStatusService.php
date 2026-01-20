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
        $plantId = isset($data['plant'])
            ? $this->resolvePlantId($data['plant'])
            : auth()->user()->plant_id;

        return MachineStatus::updateOrCreate(
            [
                'plant_id' => $plantId,
                'type' => $data['type'],
                'number' => $data['number'],
            ],
            [
                'status' => $data['status'],
                'description' => $data['description'] ?? null,
                'created_by' => auth()->user()->name
            ]
        );
    }
}
