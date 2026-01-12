<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MachineStatus;

class MachineStatusController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'type' => 'required|in:line,machine',
            'number' => 'required|integer',
            'status' => 'required|in:normal,maintenance,stopped,trouble',
            'description' => 'nullable|string|max:255',
        ]);

        MachineStatus::updateOrCreate(
            [
                'type' => $request->type,
                'number' => $request->number,
            ],
            [
                'status' => $request->status,
                'description' => $request->description,
                'created_by' => auth()->user()->name
            ]
        );

        return back()->with('success', 'Status updated successfully!');
    }
}
