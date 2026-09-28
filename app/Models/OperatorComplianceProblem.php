<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperatorComplianceProblem extends Model
{
    use HasFactory;

    protected $fillable = [
        'checksheet_id',
        'item_id',
        'problem_date',
        'problem_description',
        'corrective_action',
        'pic_name',
        'target_date',
        'status',
        'created_by',
    ];

    protected $casts = [
        'problem_date' => 'date',
        'target_date'  => 'date',
    ];

    public function checksheet()
    {
        return $this->belongsTo(OperatorComplianceChecksheet::class, 'checksheet_id');
    }

    public function item()
    {
        return $this->belongsTo(OperatorComplianceItem::class, 'item_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
