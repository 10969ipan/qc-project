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
        return MachineStatus::updateOrCreate(
            [
                'plant' => auth()->user()->plant,
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
