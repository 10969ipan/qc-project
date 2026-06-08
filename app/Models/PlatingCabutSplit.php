<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatingCabutSplit extends Model
{
    use HasFactory;

    protected $table = 'plating_cabut_splits';

    protected $fillable = [
        'plating_cabut_record_id',
        'no_lot_split',
        'qty_split',
        'generated_qrcode',
    ];

    public function cabutRecord()
    {
        return $this->belongsTo(PlatingCabutRecord::class, 'plating_cabut_record_id');
    }

    public function checksheet()
    {
        return $this->hasOne(PlatingChecksheet::class, 'qrcode', 'generated_qrcode');
    }
}
