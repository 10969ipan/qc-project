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
        'category_id',
        'file_path',
        'customer',
        'part_number',
        'sap_code',
        'dimension_standards',
        'defects',
    ];

    protected $casts = [
        'defects' => 'array',
        'dimension_standards' => 'array',
    ];

    /**
     * Get the category that owns the item.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Scope a query to filter items by category.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string|array|int  $category
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCategory($query, $category)
    {
        if (is_array($category)) {
            // If array of category names, get IDs first
            if (is_string($category[0] ?? null)) {
                $categoryIds = Category::whereIn('name', $category)->pluck('id');
                return $query->whereIn('category_id', $categoryIds);
            }
            // If array of IDs
            return $query->whereIn('category_id', $category);
        }

        // If category name (string)
        if (is_string($category)) {
            $categoryId = Category::where('name', $category)->value('id');
            return $query->where('category_id', $categoryId);
        }

        // If category ID (int)
        return $query->where('category_id', $category);
    }
}
