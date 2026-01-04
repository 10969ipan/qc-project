<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $table = 'items';

    protected $fillable = [
        'name',
        'file_path',
        'customer',
        'part_number',
        'part_number',
        'dimension_standards',
        'defects',
    ];

    protected $casts = [
        'defects' => 'array',
        'dimension_standards' => 'array',
    ];
}
