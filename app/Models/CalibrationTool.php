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
        'tanggal_beli' => 'date',
        'schedule_planning' => 'date',
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

        if ($today->gt($next)) {
            return 'overdue';
        }

        if ($today->diffInDays($next, false) <= 30) {
            return 'due_soon';
        }

        return 'calibrated';
    }
}
