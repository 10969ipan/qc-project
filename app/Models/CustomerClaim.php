<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerClaim extends Model
{
    use \App\Traits\HasPlantFilter;

    protected $fillable = [
        'plant_id',
        'year',
        'month',
        'ppm_value',
        'target_value',
        'total_claims',
        'total_claim_pcs',
        'total_delivery',
        'created_by',
    ];

    protected $casts = [
        'ppm_value' => 'decimal:2',
        'target_value' => 'decimal:2',
        'total_claims' => 'decimal:2',
        'total_claim_pcs' => 'decimal:2',
        'total_delivery' => 'decimal:2',
    ];

    /**
     * Relationship to Plant
     */
    public function plant()
    {
        return $this->belongsTo(Plant::class);
    }

    /**
     * Relationship to User (creator)
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get month name
     */
    public function getMonthNameAttribute()
    {
        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];
        if ($this->month == 0) {
            return 'Tahunan';
        }
        return $months[$this->month] ?? '';
    }
}
