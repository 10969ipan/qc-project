<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\CalibrationToolSchedule;
use App\Models\CalibrationVerification;
use Carbon\Carbon;
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
                    'data' => [
                        'url' => $url,
                        'checksheet_id' => $checksheet->id,  // Tambahkan ID untuk auto-hide
                        'checksheet_type' => $type
                    ],
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
            $dateStr = ($checksheet->date instanceof Carbon)
                ? $checksheet->date->format('d-m-Y')
                : Carbon::parse($checksheet->date)->format('d-m-Y');

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
                    'data' => [
                        'url' => $url,
                        'checksheet_id' => $checksheet->id,  // Tambahkan ID untuk auto-hide
                        'checksheet_type' => $type
                    ],
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
            $dateStr = ($checksheet->date instanceof Carbon)
                ? $checksheet->date->format('d-m-Y')
                : Carbon::parse($checksheet->date)->format('d-m-Y');

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
                    'data' => [
                        'url' => $url,
                        'checksheet_id' => $checksheet->id,  // Tambahkan ID untuk auto-hide
                        'checksheet_type' => $type
                    ],
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Notification Error (Approval): ' . $e->getMessage());
        }
    }

    /**
     * Notify about calibration schedules that need verification
     */
    public function notifyCalibrationReminder()
    {
        try {
            $today = Carbon::today();

            // Get all upcoming schedules
            $schedules = CalibrationToolSchedule::with('tool.plant')
                ->where('schedule_date', '>', $today)
                ->get();

            // Target users: admins + Mida Herdiyani
            $targetUsers = User::where('role', 'admin')
                ->orWhere('name', 'Mida Herdiyani')
                ->get();

            foreach ($schedules as $schedule) {
                $tool = $schedule->tool;
                if (!$tool)
                    continue;

                $leadTime = ($tool->jenis_kalibrasi === 'Eksternal') ? 30 : 7;
                $diffInDays = $today->diffInDays($schedule->schedule_date, false);

                // Check if we are within the notification window
                if ($diffInDays <= $leadTime) {
                    $scheduleDate = Carbon::parse($schedule->schedule_date);

                    // Check if verification already exists for this tool in the same month/year
                    $alreadyVerified = CalibrationVerification::where('tool_id', $tool->id)
                        ->whereMonth('tanggal_verifikasi', $scheduleDate->month)
                        ->whereYear('tanggal_verifikasi', $scheduleDate->year)
                        ->exists();

                    if (!$alreadyVerified) {
                        $title = "Reminder Kalibrasi: {$tool->name_alat}";
                        $message = "Alat {$tool->name_alat} ({$tool->serial_number}) dijadwalkan kalibrasi {$tool->jenis_kalibrasi} pada {$scheduleDate->format('d-m-Y')}. Silakan lakukan verifikasi.";

                        $url = route('calibration.verifications.index', ['plant' => $tool->plant->code ?? 'jakarta']);

                        foreach ($targetUsers as $user) {
                            // Avoid duplicate notifications for the same day/tool/user
                            $exists = Notification::where('user_id', $user->id)
                                ->where('type', 'calibration_reminder')
                                ->where('title', $title)
                                ->whereDate('created_at', $today)
                                ->exists();

                            if (!$exists) {
                                Notification::create([
                                    'user_id' => $user->id,
                                    'type' => 'calibration_reminder',
                                    'title' => $title,
                                    'message' => $message,
                                    'data' => ['url' => $url],
                                ]);
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Notification Error (Calibration Reminder): ' . $e->getMessage());
        }
    }

    /**
     * Helper to determine checksheet URL
     */
    private function getChecksheetUrl($checksheet, $type)
    {
        // Only use ID parameter to filter directly to the specific checksheet
        // Remove search, plant, and date parameters to avoid showing multiple results
        $params = [
            'id' => $checksheet->id,  // Filter langsung ke checksheet spesifik
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

    /**
     * Delete all notifications related to a specific checksheet
     * This is called after approval to remove the notification from list
     */
    public function markChecksheetNotificationsAsRead($checksheet, $type = 'In Process')
    {
        try {
            // Delete all notifications related to this checksheet
            // Using whereRaw for better compatibility across database drivers
            $deleted = Notification::whereRaw("JSON_EXTRACT(data, '$.checksheet_id') = ?", [$checksheet->id])
                ->whereRaw("JSON_EXTRACT(data, '$.checksheet_type') = ?", [$type])
                ->delete();

            Log::info("Deleted {$deleted} notifications for checksheet ID: {$checksheet->id}, Type: {$type}");

            // If no notifications were deleted, try fallback for old notifications without checksheet_id
            if ($deleted === 0) {
                Log::warning("No notifications found with checksheet_id. Trying fallback for old notifications...");

                // Fallback: Delete old notifications by parsing URL (for notifications created before checksheet_id was added)
                $url = $this->getChecksheetUrl($checksheet, $type);

                // Delete notifications where URL contains id parameter
                $deletedOld = Notification::where('data->url', 'LIKE', "%id={$checksheet->id}%")
                    ->orWhere('data->url', 'LIKE', "%id%3D{$checksheet->id}%") // URL encoded =
                    ->delete();

                Log::info("Fallback: Deleted {$deletedOld} old notifications by URL for checksheet ID: {$checksheet->id}");

                if ($deletedOld === 0) {
                    // Last resort: check total notifications
                    $count = Notification::where('is_read', false)->count();
                    Log::warning("No notifications deleted at all. Total unread notifications: {$count}");
                }
            }
        } catch (\Exception $e) {
            Log::error('Error deleting notifications: ' . $e->getMessage());

            // Final fallback: Try to mark as read
            try {
                // Try with checksheet_id first
                $updated = Notification::whereRaw("JSON_EXTRACT(data, '$.checksheet_id') = ?", [$checksheet->id])
                    ->where('is_read', false)
                    ->update(['is_read' => true]);

                // If nothing updated, try URL fallback
                if ($updated === 0) {
                    $updated = Notification::where('data->url', 'LIKE', "%id={$checksheet->id}%")
                        ->orWhere('data->url', 'LIKE', "%id%3D{$checksheet->id}%")
                        ->where('is_read', false)
                        ->update(['is_read' => true]);
                }

                Log::info("Fallback: Marked {$updated} notifications as read for checksheet ID: {$checksheet->id}");
            } catch (\Exception $e2) {
                Log::error('Fallback also failed: ' . $e2->getMessage());
            }
        }
    }
}
