<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatingPasangRecord extends Model
{
    use HasFactory;

    protected $table = 'plating_pasang_records';

    protected $fillable = [
        'wip_qrcode',
        'customer_part',
        'no_po',
        'no_lot',
        'qty',
        'lot_id',
        'unique_code',
        'sap_code',
        'tanggal_pasang',
        'shift',
        'inisial_pasang',
        'generated_qrcode',
        'plant_id',
        'user_id',
    ];

    protected $casts = [
        'tanggal_pasang' => 'date',
    ];

    public function plant()
    {
        return $this->belongsTo(Plant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cabutRecord()
    {
        return $this->hasOne(PlatingCabutRecord::class, 'plating_pasang_record_id');
    }
}
