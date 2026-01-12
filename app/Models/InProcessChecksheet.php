<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InProcessChecksheet extends Model
{
    use HasFactory;

    protected $table = 'in_process_checksheets';

    protected $fillable = [
        'item_id',
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
        'dimension_check', // New field
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

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
