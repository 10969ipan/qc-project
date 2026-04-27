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

        // 1. Check if the latest verification is still valid (covers TODAY)
        $latest = $this->latestVerification;
        if ($latest && $latest->next_kalibrasi && $latest->tanggal_verifikasi) {
            $vDate = \Carbon\Carbon::parse((string) $latest->tanggal_verifikasi)->startOfDay();
            $nVDate = \Carbon\Carbon::parse((string) $latest->next_kalibrasi)->startOfDay();
            
            // If we are past the next calibration date, it's overdue regardless of the current "calibrated" status
            if ($today->gte($nVDate)) {
                return 'overdue';
            }

            if ($vDate->lte($today) && $nVDate->gt($today)) {
                // Check if approaching next calibration (within 30 days)
                if ($today->diffInDays($nVDate, false) <= 30) {
                    return 'due_soon';
                }
                return 'calibrated';
            }
        }

        // 2. Fallback to schedules
        $nextSchedule = $this->schedules()
            ->where('schedule_date', '>=', $today)
            ->orderBy('schedule_date', 'asc')
            ->first();

        $nextDate = $nextSchedule ? $nextSchedule->schedule_date : $this->schedule_planning;

        if (!$nextDate) {
            $lastPastSchedule = $this->schedules()->orderBy('schedule_date', 'desc')->first();
            if ($lastPastSchedule) {
                return 'overdue';
            }
            return 'unknown';
        }

        $next = \Carbon\Carbon::parse((string) $nextDate)->startOfDay();

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
