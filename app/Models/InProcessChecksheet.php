<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InProcessChecksheet extends Model
{
    use HasFactory, \App\Traits\HasPlantFilter;

    protected $table = 'in_process_checksheets';

    protected $fillable = [
        'plant_id',
        'user_id',
        'item_id',
        'qrcode',
        'part_code',
        'supplier_id',
        'quantity',
        'unique_code_id',
        'sap_code',
        'scan_method',
        'created_at',
        'date',
        'shift',
        'code_machine', // New field
        'total_qty',
        'sampling_qty',
        'total_ok',
        'total_ng',
        'judgment',
        'operator_initials',
        'remarks',
        'next_proses',
        'tujuan',
        'dimension_check', // New field
        'part_weight', // Added for AHM
        'defects', // JSON or serialized
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted()
    {
        static::saved(fn() => \App\Services\DashboardService::clearDashboardCache());
        static::deleted(fn() => \App\Services\DashboardService::clearDashboardCache());
    }
}
