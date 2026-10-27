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
     * Notify about a new NG finding (Disabled: notifikasi temuan NG telah dinonaktifkan)
     */
    public function notifyNGFinding($checksheet, $type = 'Sub Assy')
    {
        return;
    }

    /**
     * Notify about a rejection
     */
    public function notifyRejection($checksheet, $type = 'Sub Assy', $rejector = null)
    {
        try {
            $item = $checksheet->item;
            $rawDate = $checksheet->date ?? $checksheet->qc_datetime ?? $checksheet->production_datetime ?? clone $checksheet->created_at;
            $dateStr = ($rawDate instanceof \Carbon\Carbon)
                ? $rawDate->format('d-m-Y')
                : \Carbon\Carbon::parse($rawDate)->format('d-m-Y');

            $title = "Laporan Ditolak: " . ($item ? $item->name : 'N/A');
            $shift = $checksheet->shift ?? $checksheet->qc_shift ?? $checksheet->production_shift ?? '-';
            $message = "Laporan {$type} pada {$dateStr} (Shift {$shift}) telah DITOLAK" . ($rejector ? " oleh {$rejector}" : "") . ".";

            if (!empty($checksheet->rejection_remarks)) {
                $message .= "<br><br><strong>Keterangan:</strong><br>" . nl2br(htmlspecialchars($checksheet->rejection_remarks));
            }

            $url = $this->getChecksheetUrl($checksheet, $type);

            // Notify the specific inspector who created the checksheet
            // Identify by initials within the same plant
            if ($checksheet->operator_initials) {
                $user = User::where('plant_id', $checksheet->plant_id)
                    ->whereRaw('UPPER(initials) = ?', [strtoupper(trim($checksheet->operator_initials))])
                    ->first();

                if ($user) {
                    Notification::create([
                        'user_id' => $user->id,
                        'type' => 'rejection_alert',
                        'title' => $title,
                        'message' => $message,
                        'is_read' => false,
                        'data' => [
                            'url' => $url,
                            'checksheet_id' => $checksheet->id,
                            'checksheet_type' => $type,
                            'plant_id' => $checksheet->plant_id,
                            'rejected_by' => $rejector,
                            'show_popup' => true,
                        ],
                    ]);
                } else {
                    Log::warning("Rejection alert failed: No inspector found with initials '{$checksheet->operator_initials}' in plant '{$checksheet->plant_id}'");
                }
            }
        } catch (\Exception $e) {
            Log::error('Notification Error (Rejection): ' . $e->getMessage());
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
            case 'Cross Cut Painting':
                return route('cross_cut_painting.index', $params);
            case 'Sortir':
                return route('sortir.index', $params);
            case 'Plating':
                return route('plating.index', $params);
            case 'Painting':
                return route('painting.index', $params);
            case 'Double Tape':
                return route('double_tape.index', $params);
            case 'First Piece Approval':
                return route('first_piece_approval.index', $params);
            case 'Incoming Part':
                return route('incoming.parts.index', $params);
            case 'Incoming Material':
                return route('incoming.materials.index', $params);
            case 'Incoming Sub-Part':
                return route('incoming.sub_parts.index', $params);
            case 'Outgoing Export':
            case 'Incoming Export':
                return route('incoming.exports.index', $params);
            case 'Incoming Chemical':
                return route('incoming.chemicals.index', $params);
            case 'Sub Assy':
            default:
                return route('admin.checksheets.index', $params);
        }
    }
}
