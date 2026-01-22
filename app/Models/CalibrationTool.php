<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalibrationTool extends Model
{
    use HasFactory, \App\Traits\HasPlantFilter, \App\Traits\HasUuid;

    protected $fillable = [
        'bagian',
        'name_alat',
        'serial_number',
        'range',
        'resolusi',
        'lokasi_pakai',
        'tanggal_beli',
        'frekuensi_kalibrasi',
        'riwayat_kalibrasi',
        'jenis_kalibrasi',
        'schedule_planning',
        'certification_path',
        'plant_id',
    ];

    protected $casts = [
        'tanggal_beli' => 'date:Y-m-d',
        'schedule_planning' => 'date:Y-m-d',
    ];

    public function plant()
    {
        return $this->belongsTo(Plant::class);
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

        $next = \Carbon\Carbon::parse($nextDate)->startOfDay();

        // Check if there is a verification in the same month and year as the next schedule
        $latestVerification = $this->latestVerification;
        if ($latestVerification) {
            $verificationDate = \Carbon\Carbon::parse($latestVerification->tanggal_verifikasi)->startOfDay();
            if ($verificationDate->format('Y-m') === $next->format('Y-m')) {
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
            ->whereYear('tanggal_verifikasi', $year)
            ->get();

        $results = [];

        // If no schedules in the new table, try fallback to legacy schedule_planning
        if ($schedules->isEmpty()) {
            if ($this->schedule_planning && $this->schedule_planning->format('Y') == $year) {
                $month = $this->schedule_planning->format('Y-m');
                $v = $verifications->first(function ($v) use ($month) {
                    return $v->tanggal_verifikasi->format('Y-m') === $month;
                });

                $results[] = (object) [
                    'schedule_date' => $this->schedule_planning,
                    'status' => $v ? 'OK' : 'Belum Verifikasi',
                    'is_ok' => (bool) $v,
                    'verification' => $v
                ];
            }
        } else {
            foreach ($schedules as $s) {
                $month = $s->schedule_date->format('Y-m');
                $v = $verifications->first(function ($v) use ($month) {
                    return $v->tanggal_verifikasi->format('Y-m') === $month;
                });

                $displayDate = $v ? $v->tanggal_verifikasi : $s->schedule_date;

                $results[] = (object) [
                    'schedule_date' => $displayDate,
                    'status' => $v ? 'OK' : 'Belum Verifikasi',
                    'is_ok' => (bool) $v,
                    'verification' => $v
                ];
            }
        }

        return $results;
    }
}
