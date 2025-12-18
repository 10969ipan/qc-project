<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionReport extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'report_date',
        'product_name',
        'batch_no',
        'total_produced',
        'accepted_qty',
        'rejected_qty',
        'inspector_name',
        'notes',
    ];
}