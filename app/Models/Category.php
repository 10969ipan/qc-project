<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Item[] $items
 */
class Category extends Model
{
    protected $fillable = ['plant', 'name'];

    /**
     * Get the items for the category.
     */
    public function items()
    {
        return $this->hasMany(Item::class);
    }
}
