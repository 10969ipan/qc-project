<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name'];

    /**
     * Get the items for the category.
     */
    public function items()
    {
        return $this->hasMany(Item::class);
    }
}
