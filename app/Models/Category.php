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
    use \App\Traits\HasPlantFilter, \App\Traits\HasUuid;

    protected $fillable = ['plant_id', 'name'];

    /**
     * Get the plant that owns the category.
     */
    public function plant()
    {
        return $this->belongsTo(Plant::class);
    }

    /**
     * Get the items for the category.
     */
    public function items()
    {
        return $this->hasMany(Item::class);
    }
}
