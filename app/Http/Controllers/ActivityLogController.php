<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Item;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ActivityLogController extends Controller
{
    public function index()
    {
        $logs = ActivityLog::with('user')
            ->latest()
            ->paginate(50);

        return response()->json($logs);
    }

    public function getItemLogs($itemId)
    {
        $logs = ActivityLog::with('user:id,name')
            ->where('model_type', Item::class)
            ->where('model_id', $itemId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'description' => $log->description,
                    'user' => $log->user ? $log->user->name : 'System',
                    'date' => Carbon::parse($log->created_at)->translatedFormat('d M Y, H:i'),
                ];
            });

        return response()->json([
            'success' => true,
            'logs' => $logs
        ]);
    }
}
