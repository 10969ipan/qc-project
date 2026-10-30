<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DandoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'phase',
        'order_no',
        'item_check',
        'standard',
        'is_active',
    ];

    protected $casts = [
        'order_no'  => 'integer',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAwal($query)
    {
        return $query->where('phase', 'awal')->orderBy('order_no');
    }

    public function scopeAkhir($query)
    {
        return $query->where('phase', 'akhir')->orderBy('order_no');
    }
}

