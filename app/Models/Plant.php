<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Plant extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'code',
        'address',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Boot function to auto-generate UUID
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = Str::uuid()->toString();
            }
        });
    }

    /**
     * Get all users for this plant
     */
    public function users()
    {
        return $this->hasMany(User::class, 'plant_id');
    }

    /**
     * Get all items for this plant
     */
    public function items()
    {
        return $this->hasMany(Item::class, 'plant_id');
    }

    /**
     * Get all categories for this plant
     */
    public function categories()
    {
        return $this->hasMany(Category::class, 'plant_id');
    }
}
