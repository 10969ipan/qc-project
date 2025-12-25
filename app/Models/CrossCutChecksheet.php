<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrossCutChecksheet extends Model
{
    use HasFactory;

    protected $table = 'cross_cut_checksheets';

    protected $fillable = [
        'item_id',
        'production_shift',
        'qc_shift',
        'production_datetime',
        'qc_datetime',
        'image_path',
        'chemical_copper',
        'chemical_nikel',
        'chemical_eching',
        'chemical_abu',
        'position_remark_judgment',
        'position_remark_no_lot',
        'result_remark',
        'keterangan',
        'cycle_time',
        'operator_initials',
        'approval_status',
        'kashift_qc',
        'kashift_approved_at',
        'supervisor_qc',
        'supervisor_approved_at',
        'asst_manager_qc',
        'asst_manager_approved_at',
        'manager_qc',
        'manager_approved_at',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
