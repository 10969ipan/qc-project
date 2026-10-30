<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DandoryOperatorConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'plant_code',
        'operator_id',
        'bagian',
        'default_table',
        'allowed_categories',
        'updated_by',
    ];

    protected $casts = [
        'allowed_categories' => 'array',
    ];

    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }
}

