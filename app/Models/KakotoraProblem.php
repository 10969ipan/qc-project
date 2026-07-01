<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KakotoraProblem extends Model
{
    use HasFactory;

    protected $fillable = ['plant', 'name'];
}
