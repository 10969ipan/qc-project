<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model buat master item audit kepatuhan operator (dikategoriin sesuai Prinsip Dasar)
 */
class OperatorComplianceItem extends Model
{
    use HasFactory;

    // Kolom-kolom yang bisa diisi masal
    protected $fillable = [
        'prinsip_dasar',
        'item_check',
        'standard',
        'order_no',
        'is_active',
    ];

    // Relasi ke entri-entri matriks harian
    public function entries()
    {
        return $this->hasMany(OperatorComplianceEntry::class, 'item_id');
    }
}
