<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemCustomer extends Model
{
    use HasFactory;

    protected $fillable = ['plant', 'name'];
}
