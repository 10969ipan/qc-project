<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatingCabutRecord extends Model
{
    use HasFactory;

    protected $table = 'plating_cabut_records';

    protected $fillable = [
        'plating_pasang_record_id',
        'pasang_qrcode',
        'tanggal_cabut',
        'shift',
        'no_po',
        'no_lot_original',
        'qty_original',
        'inisial_cabut',
        'plant_id',
        'user_id',
    ];

    protected $casts = [
        'tanggal_cabut' => 'date',
    ];

    public function pasangRecord()
    {
        return $this->belongsTo(PlatingPasangRecord::class, 'plating_pasang_record_id');
    }

    public function plant()
    {
        return $this->belongsTo(Plant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function splits()
    {
        return $this->hasMany(PlatingCabutSplit::class, 'plating_cabut_record_id');
    }
}
