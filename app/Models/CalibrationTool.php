<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalibrationTool extends Model
{
    use HasFactory, \App\Traits\HasPlantFilter, \App\Traits\HasUuid, \Illuminate\Database\Eloquent\SoftDeletes;

    protected $fillable = [
        'bagian',
        'name_alat',
        'merk',
        'serial_number',
        'range',
        'resolusi',
        'tanggal_beli',
        'frekuensi_kalibrasi',
        'riwayat_kalibrasi',
        'jenis_kalibrasi',
        'schedule_planning',
        'certification_path',
        'plant_id',
        'status',
    ];

    protected $casts = [
        'tanggal_beli' => 'date',
        'schedule_planning' => 'date',
    ];

    public function plant()
    {
        return $this->belongsTo(Plant::class);
    }

    public function logs()
    {
        return $this->hasMany(CalibrationToolLog::class, 'calibration_tool_id');
    }

    public function verifications()
    {
        return $this->hasMany(CalibrationVerification::class, 'tool_id');
    }

    public function schedules()
    {
        return $this->hasMany(CalibrationToolSchedule::class, 'tool_id');
    }

    public function latestVerification()
    {
        return $this->hasOne(CalibrationVerification::class, 'tool_id')->latestOfMany('tanggal_verifikasi');
    }

    /**
     * Get calibration status
     */
    public function getStatusAttribute()
    {
        $today = now()->startOfDay();

        // 1. Check if the latest verification is still valid (covers TODAY)
        $latest = $this->latestVerification;
        if ($latest && $latest->next_kalibrasi && $latest->tanggal_verifikasi) {
            $vDate = \Carbon\Carbon::parse((string) $latest->tanggal_verifikasi)->startOfDay();
            $nVDate = \Carbon\Carbon::parse((string) $latest->next_kalibrasi)->startOfDay();
            if ($vDate->lte($today) && $nVDate->gt($today)) {
                return 'calibrated';
            }
        }

        // Get the next upcoming schedule date from the new schedules table
        $nextSchedule = $this->schedules()
            ->where('schedule_date', '>=', $today)
            ->orderBy('schedule_date', 'asc')
            ->first();

        // Fallback to legacy field if no upcoming schedule is found in the new table
        // This is to maintain compatibility while migrating
        $nextDate = $nextSchedule ? $nextSchedule->schedule_date : $this->schedule_planning;

        // If even the legacy field is empty and no schedules exist at all, check if there's any past schedule
        if (!$nextDate) {
            $lastPastSchedule = $this->schedules()->orderBy('schedule_date', 'desc')->first();
            if ($lastPastSchedule) {
                // If we only have past schedules, it's definitely overdue
                return 'overdue';
            }
            return 'unknown';
        }

        $next = \Carbon\Carbon::parse((string) $nextDate)->startOfDay();

        // Check if there is a verification that covers the next schedule
        $verifications = $this->verifications()->orderBy('tanggal_verifikasi', 'desc')->get();
        foreach ($verifications as $v) {
            if (!$v->tanggal_verifikasi || !$v->next_kalibrasi)
                continue;
            $vDate = \Carbon\Carbon::parse((string) $v->tanggal_verifikasi)->startOfDay();
            $nextV = \Carbon\Carbon::parse((string) $v->next_kalibrasi)->startOfDay();

            // If verification covers the next schedule date
            if ($vDate->lte($next) && \Carbon\Carbon::parse((string) $v->next_kalibrasi)->startOfDay()->gt($next)) {
                return 'calibrated';
            }

            // Also accept same month as fallback/legacy
            if (\Carbon\Carbon::parse((string) $v->tanggal_verifikasi)->format('Y-m') === $next->format('Y-m')) {
                return 'calibrated';
            }
        }

        if ($today->gt($next)) {
            return 'overdue';
        }

        if ($today->diffInDays($next, false) <= 30) {
            return 'due_soon';
        }

        return 'calibrated';
    }

    /**
     * Get detailed statuses for all schedules in a year
     */
    public function getScheduledStatuses($year = null)
    {
        if (!$year) {
            $year = date('Y');
        }

        $schedules = $this->schedules()
            ->whereYear('schedule_date', $year)
            ->orderBy('schedule_date', 'asc')
            ->get();

        $verifications = $this->verifications()
            ->orderBy('tanggal_verifikasi', 'desc')
            ->get();

        $results = [];

        // Helper to find valid verification for a schedule
        $findVerification = function ($scheduleDate) use ($verifications) {
            $sDate = \Carbon\Carbon::parse((string) $scheduleDate)->startOfDay();
            $month = $sDate->format('Y-m');

            // 1. Check for verification in the same month
            $v = $verifications->first(function ($v) use ($month) {
                return $v->tanggal_verifikasi && \Carbon\Carbon::parse((string) $v->tanggal_verifikasi)->format('Y-m') === $month;
            });

            if ($v)
                return $v;

            // 2. Check for "Early Verification": 
            // Any verification done BEFORE the schedule date whose NEXT calibration is AFTER the schedule date.
            $vEarly = $verifications->first(function ($v) use ($sDate) {
                if (!$v->tanggal_verifikasi || !$v->next_kalibrasi)
                    return false;
                $vDate = \Carbon\Carbon::parse((string) $v->tanggal_verifikasi)->startOfDay();
                $nextDate = \Carbon\Carbon::parse((string) $v->next_kalibrasi)->startOfDay();

                // If verification was done before or on schedule, and it covers the schedule
                return $vDate->lte($sDate) && $nextDate->gt($sDate);
            });

            return $vEarly;
        };

        // If no schedules in the new table, try fallback to legacy schedule_planning
        if ($schedules->isEmpty()) {
            if ($this->schedule_planning && \Carbon\Carbon::parse((string) $this->schedule_planning)->format('Y') == $year) {
                $v = $findVerification($this->schedule_planning);

                $results[] = (object) [
                    'schedule_date' => $this->schedule_planning,
                    'status' => $v ? 'OK' : 'Belum Verifikasi',
                    'is_ok' => (bool) $v,
                    'verification' => $v
                ];
            }
        } else {
            foreach ($schedules as $s) {
                $v = $findVerification($s->schedule_date);

                $results[] = (object) [
                    'id' => $s->id,
                    'schedule_date' => $s->schedule_date,
                    'actual_date' => $v ? $v->tanggal_verifikasi : null,
                    'status' => $v ? 'OK' : 'Belum Verifikasi',
                    'is_ok' => (bool) $v,
                    'verification' => $v,
                    'pr_number' => $s->pr_number,
                    'pr_date' => $s->pr_date,
                ];
            }
        }

        return $results;
    }
}
