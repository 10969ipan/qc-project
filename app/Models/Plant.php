<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Plant extends Model
{
    use \App\Traits\HasUuid;

    protected $fillable = [
        'name',
        'code',
        'address',
        'is_active',
    ];

    /**
     * Resolve a plant identifier to a UUID.
     * 
     * @param mixed $identifier
     * @return string|null
     */
    public static function resolveId($identifier)
    {
        if (empty($identifier))
            return null;

        // If it's already a UUID string
        if (is_string($identifier) && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $identifier)) {
            return $identifier;
        }

        // If it's a model instance
        if ($identifier instanceof self) {
            return $identifier->id;
        }

        // If it's a name or code (case insensitive)
        return self::where('name', 'like', $identifier)
            ->orWhere('code', 'like', $identifier)
            ->value('id');
    }

    protected $casts = [
        'is_active' => 'boolean',
    ];

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
