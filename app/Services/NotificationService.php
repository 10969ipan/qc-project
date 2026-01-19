<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Notify about a new NG finding
     */
    public function notifyNGFinding($checksheet, $type = 'Sub Assy')
    {
        try {
            $plant = $checksheet->plant;
            $item = $checksheet->item;
            $qty = $checksheet->total_ng;
            $title = "Temuan NG: {$item->name}";
            $locationLabel = $this->getLocationLabel($type);
            $locationValue = $this->getLineInfo($checksheet, $type);
            $message = "Ditemukan {$qty} Pcs NG pada {$type}" . ($locationValue ? " - {$locationLabel} {$locationValue}" : "") . ".";

            $url = $this->getChecksheetUrl($checksheet, $type);

            // Notify everyone in the plant who should know (Supervisor, Manager, Admin, Karu)
            // Admin role should receive from all plants
            $users = User::where(function ($q) use ($checksheet) {
                $q->where('plant_id', $checksheet->plant_id)
                    ->whereIn('role', ['supervisor', 'kashift', 'manager', 'asst_manager', 'karu_qc']);
            })->orWhere('role', 'admin')->get();

            foreach ($users as $user) {
                Notification::create([
                    'user_id' => $user->id,
                    'type' => 'ng_finding',
                    'title' => $title,
                    'message' => $message,
                    'data' => ['url' => $url],
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Notification Error (NG): ' . $e->getMessage());
        }
    }

    /**
     * Notify about a rejection
     */
    public function notifyRejection($checksheet, $type = 'Sub Assy', $rejector = null)
    {
        try {
            $item = $checksheet->item;
            $dateStr = ($checksheet->date instanceof \Carbon\Carbon)
                ? $checksheet->date->format('d-m-Y')
                : \Carbon\Carbon::parse($checksheet->date)->format('d-m-Y');

            $title = "Laporan Ditolak: {$item->name}";
            $message = "Laporan {$type} pada {$dateStr} (Shift {$checksheet->shift}) telah DITOLAK" . ($rejector ? " oleh {$rejector}" : "") . ".";

            $url = $this->getChecksheetUrl($checksheet, $type);

            // Notify all inspectors in the same plant
            $users = User::where('plant_id', $checksheet->plant_id)
                ->where('role', 'inspector')
                ->get();

            foreach ($users as $user) {
                Notification::create([
                    'user_id' => $user->id,
                    'type' => 'abnormal', // Using abnormal type for rejections (yellow icons)
                    'title' => $title,
                    'message' => $message,
                    'data' => ['url' => $url],
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Notification Error (Rejection): ' . $e->getMessage());
        }
    }

    /**
     * Notify about a request for approval (Karu, Kashift, Supervisor)
     */
    public function notifyApprovalRequest($checksheet, $type = 'Sub Assy')
    {
        try {
            $item = $checksheet->item;
            $dateStr = ($checksheet->date instanceof \Carbon\Carbon)
                ? $checksheet->date->format('d-m-Y')
                : \Carbon\Carbon::parse($checksheet->date)->format('d-m-Y');

            $title = "Permintaan Approval: {$item->name}";
            $locationLabel = $this->getLocationLabel($type);
            $locationValue = $this->getLineInfo($checksheet, $type);
            $message = "Laporan {$type} pada {$dateStr} (Shift {$checksheet->shift})" . ($locationValue ? " {$locationLabel} {$locationValue}" : "") . " membutuhkan approval.";

            $url = $this->getChecksheetUrl($checksheet, $type);

            // Specific roles as requested: Karu, Kashift, Supervisor
            // Admin role should receive from all plants
            $users = User::where(function ($q) use ($checksheet) {
                $q->where('plant_id', $checksheet->plant_id)
                    ->whereIn('role', ['karu_qc', 'kashift', 'supervisor']);
            })->orWhere('role', 'admin')->get();

            foreach ($users as $user) {
                Notification::create([
                    'user_id' => $user->id,
                    'type' => 'approval',
                    'title' => $title,
                    'message' => $message,
                    'data' => ['url' => $url],
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Notification Error (Approval): ' . $e->getMessage());
        }
    }

    /**
     * Helper to determine checksheet URL
     */
    private function getChecksheetUrl($checksheet, $type)
    {
        $dateStr = ($checksheet->date instanceof \Carbon\Carbon)
            ? $checksheet->date->format('Y-m-d')
            : \Carbon\Carbon::parse($checksheet->date)->format('Y-m-d');

        $params = [
            'plant' => $checksheet->plant->code,
            'search' => $checksheet->item->name,
            'start_date' => $dateStr,
            'end_date' => $dateStr,
        ];

        switch ($type) {
            case 'In Process':
                return route('in_process.index', $params);
            case 'Cross Cut':
                return route('cross_cut.index', $params);
            case 'Sortir':
                return route('sortir.index', $params);
            case 'Sub Assy':
            default:
                return route('admin.checksheets.index', $params);
        }
    }

    /**
     * Get the correct label (Line, Mesin, Meja) based on checksheet type
     */
    private function getLocationLabel($type)
    {
        switch ($type) {
            case 'In Process':
                return 'Mesin';
            case 'Cross Cut':
            case 'Sub Assy':
            case 'Sortir':
                return 'Meja';
            default:
                return 'Line';
        }
    }

    /**
     * Get the correct location value (line or code_machine) based on checksheet type
     */
    private function getLineInfo($checksheet, $type)
    {
        switch ($type) {
            case 'In Process':
                return $checksheet->code_machine ?? $checksheet->line ?? null;
            case 'Cross Cut':
                // Cross Cut doesn't seem to have a line/machine field in DB but we'll check it anyway
                return $checksheet->line ?? null;
            case 'Sub Assy':
            case 'Sortir':
            default:
                return $checksheet->line ?? null;
        }
    }
}
