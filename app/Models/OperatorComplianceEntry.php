<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperatorComplianceEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'checksheet_id',
        'item_id',
        'day',
        'status',
        'updated_by',
    ];

    public function checksheet()
    {
        return $this->belongsTo(OperatorComplianceChecksheet::class, 'checksheet_id');
    }

    public function item()
    {
        return $this->belongsTo(OperatorComplianceItem::class, 'item_id');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
