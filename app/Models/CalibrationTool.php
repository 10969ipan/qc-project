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

    public function pendingLogs()
    {
        return $this->hasMany(CalibrationToolLog::class, 'calibration_tool_id')->whereNull('judgment_status');
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
     * Get the next/target calibration date
     */
    public function getNextCalibrationDateAttribute()
    {
        $latest = $this->latestVerification;

        // 1. Try to get from latest verification record
        if ($latest && $latest->tanggal_verifikasi) {
            if ($latest->next_kalibrasi) {
                return \Carbon\Carbon::parse((string) $latest->next_kalibrasi)->startOfDay();
            } elseif ($this->frekuensi_kalibrasi) {
                $vDate = \Carbon\Carbon::parse((string) $latest->tanggal_verifikasi)->startOfDay();
                $freq = strtolower($this->frekuensi_kalibrasi);
                $nVDate = clone $vDate;
                if (strpos($freq, 'bulan') !== false) {
                    $val = (int) filter_var($freq, FILTER_SANITIZE_NUMBER_INT);
                    $nVDate->addMonths($val ?: 6);
                } elseif (strpos($freq, 'tahun') !== false) {
                    $val = (int) filter_var($freq, FILTER_SANITIZE_NUMBER_INT);
                    $nVDate->addYears($val ?: 1);
                } else {
                    $nVDate->addMonths(12); // Default 1 year
                }
                return $nVDate;
            }
        }

        // 2. Fallback to schedules
        $today = now()->startOfDay();
        $nextSchedule = $this->schedules()
            ->where('schedule_date', '>=', $today)
            ->orderBy('schedule_date', 'asc')
            ->first();

        if ($nextSchedule) {
            return \Carbon\Carbon::parse((string) $nextSchedule->schedule_date)->startOfDay();
        }

        // 3. Fallback to past schedules if all are past
        $lastPastSchedule = $this->schedules()->orderBy('schedule_date', 'desc')->first();
        if ($lastPastSchedule) {
            return \Carbon\Carbon::parse((string) $lastPastSchedule->schedule_date)->startOfDay();
        }

        return $this->schedule_planning ? \Carbon\Carbon::parse((string) $this->schedule_planning)->startOfDay() : null;
    }

    /**
     * Get calibration status
     */
    public function getStatusKalibrasiAttribute()
    {
        // 0. Check if manually set to BROKEN
        if (($this->attributes['status'] ?? '') === 'BROKEN') {
            return 'broken';
        }

        // 0.1 Check if has pending problem reports
        if ($this->pendingLogs()->exists()) {
            return 'problem';
        }

        $today = now()->startOfDay();
        $next = $this->next_calibration_date;

        if (!$next) {
            return 'unknown';
        }

        // Check if already verified for this 'next' cycle
        // (e.g. happened in same month or after it)
        $hasValidVerif = $this->verifications()
            ->where('tanggal_verifikasi', '>=', $next->copy()->startOfMonth())
            ->exists();
        
        if ($hasValidVerif) {
            return 'calibrated';
        }

        $daysToNext = $today->diffInDays($next, false);
        $isOverdue = $today->gt($next);
        $isDueSoon = $daysToNext <= 30;

        if ($isOverdue) {
            if (strtoupper($this->jenis_kalibrasi) === 'EKSTERNAL') {
                // If overdue but PR is already entered, show PR Out (Grey date)
                $schedule = $this->schedules()->whereDate('schedule_date', $next)->first();
                if ($schedule && !empty($schedule->pr_number)) {
                    return 'pr_out';
                }
            }
            return 'overdue';
        }

        if ($isDueSoon) {
            if (strtoupper($this->jenis_kalibrasi) === 'EKSTERNAL') {
                $schedule = $this->schedules()->whereDate('schedule_date', $next)->first();
                if ($schedule && !empty($schedule->pr_number)) {
                    return 'pr_out';
                }
                return 'no_pr';
            } else {
                // For internal: < 7 days is 'due_soon' (warning), otherwise 'waiting_internal' (info)
                if ($daysToNext < 7) {
                    return 'due_soon';
                }
                return 'waiting_internal';
            }
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

        $schedulesQuery = $this->schedules()->orderBy('schedule_date', 'asc');
        
        if ($year !== 'all') {
            $schedulesQuery->whereYear('schedule_date', $year);
        }

        $schedules = $schedulesQuery->get();

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
            $vEarly = $verifications->first(function ($v) use ($sDate) {
                if (!$v->tanggal_verifikasi || !$v->next_kalibrasi)
                    return false;
                $vDate = \Carbon\Carbon::parse((string) $v->tanggal_verifikasi)->startOfDay();
                $nextDate = \Carbon\Carbon::parse((string) $v->next_kalibrasi)->startOfDay();

                return $vDate->lte($sDate) && $nextDate->gt($sDate);
            });

            return $vEarly;
        };

        // If no schedules in the new table, try fallback to legacy schedule_planning
        if ($schedules->isEmpty()) {
            if ($this->schedule_planning && ($year === 'all' || \Carbon\Carbon::parse((string) $this->schedule_planning)->format('Y') == $year)) {
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
