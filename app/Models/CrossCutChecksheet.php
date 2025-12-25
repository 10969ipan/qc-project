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
        'shift',
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
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
