<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FirstPieceApproval extends Model
{
    use HasFactory, \App\Traits\HasPlantFilter;

    protected $table = 'first_piece_approvals';

    protected $fillable = [
        'plant_id',
        'item_id',
        'user_id',
        'sap_code',
        'created_at',
        'date',
        'shift',
        'code_machine',
        'category',
        'total_qty',
        'sampling_qty',
        'total_ok',
        'total_ng',
        'judgment',
        'operator_initials',
        'part_weight',
        'remarks',
        'next_proses',
        'dimension_check',
        'alat_ukur',
        'defects',
        'approval_status',
        'kashift_qc',
        'kashift_approved_at',
        'supervisor_qc',
        'supervisor_approved_at',
        'asst_manager_qc',
        'asst_manager_approved_at',
        'manager_qc',
        'manager_approved_at',
        'cycle_time',
        'rejection_remarks',
    ];

    protected $casts = [
        'defects' => 'array',
        'dimension_check' => 'array',
        'part_weight' => 'array',
        'date' => 'date',
    ];

    public function setCategoryAttribute($value)
    {
        $this->attributes['category'] = $value ? strtoupper(trim($value)) : null;
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the plant that owns the checksheet.
     */
    public function plant()
    {
        return $this->belongsTo(Plant::class);
    }

    public function getAlatUkurArrayAttribute()
    {
        if (empty($this->alat_ukur)) {
            return [];
        }
        if (is_array($this->alat_ukur)) {
            return $this->alat_ukur;
        }
        $decoded = json_decode($this->alat_ukur, true);
        if (is_array($decoded)) {
            return $decoded;
        }
        return ['1' => (string) $this->alat_ukur];
    }

    public function getAlatUkurFormattedAttribute()
    {
        $tools = $this->alat_ukur_array;
        if (empty($tools)) {
            return '-';
        }
        $filtered = array_filter($tools, function ($val) {
            return $val !== null && trim((string)$val) !== '';
        });
        if (empty($filtered)) {
            return '-';
        }
        $unique = array_unique(array_values($filtered));
        if (count($unique) === 1) {
            return reset($unique);
        }
        $parts = [];
        foreach ($filtered as $pt => $tool) {
            $parts[] = "P{$pt}: {$tool}";
        }
        return implode(', ', $parts);
    }
}
