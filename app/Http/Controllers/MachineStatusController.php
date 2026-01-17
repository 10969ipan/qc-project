<?php

namespace App\Http\Controllers;

use App\Services\MachineStatusService;
use App\Http\Requests\UpdateMachineStatusRequest;

class MachineStatusController extends Controller
{
    protected $machineStatusService;

    public function __construct(MachineStatusService $machineStatusService)
    {
        $this->machineStatusService = $machineStatusService;
    }

    public function update(UpdateMachineStatusRequest $request)
    {
        $this->machineStatusService->updateStatus($request->validated());
        return back()->with('success', 'Status updated successfully!');
    }
}
