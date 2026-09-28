<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model buat nyimpan entri matriks harian (OK / NG / NA) per tanggal dan per item
 */
class OperatorComplianceEntry extends Model
{
    use HasFactory;

    // Kolom-kolom yang bisa diisi masal
    protected $fillable = [
        'checksheet_id',
        'item_id',
        'day',
        'status',
        'updated_by',
    ];

    // Relasi balik ke header checksheet
    public function checksheet()
    {
        return $this->belongsTo(OperatorComplianceChecksheet::class, 'checksheet_id');
    }

    // Relasi ke item audit master
    public function item()
    {
        return $this->belongsTo(OperatorComplianceItem::class, 'item_id');
    }

    // Relasi ke user yang mengupdate status ceklis ini
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
