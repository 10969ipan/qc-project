<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperatorComplianceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'prinsip_dasar',
        'item_check',
        'standard',
        'order_no',
        'is_active',
    ];

    public function entries()
    {
        return $this->hasMany(OperatorComplianceEntry::class, 'item_id');
    }
}
