<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;

class CategoryService extends BaseService
{
    /**
     * Get filtered categories
     */
    public function getFilteredCategories(array $filters)
    {
        $query = Category::withCount('items')->orderBy('name');

        if (!empty($filters['plant'])) {
            $query->where($query->getModel()->getTable() . '.plant_id', $this->resolvePlantId($filters['plant']));
        }

        return $query->paginate(10)->withQueryString();
    }

    /**
     * Create category
     */
    public function createCategory(array $data): Category
    {
        return Category::create($data);
    }

    /**
     * Update category
     */
    public function updateCategory(int $id, array $data): Category
    {
        $category = $this->findCategory($id);
        $category->update($data);
        return $category;
    }

    /**
     * Delete category
     */
    public function deleteCategory(int $id): bool
    {
        $category = $this->findCategory($id);
        return $category->delete();
    }

    /**
     * Helper to find category with admin override
     */
    public function findCategory(int $id): Category
    {
        $query = Category::query();
        if (auth()->user()->role === 'admin') {
            $query->withoutGlobalScope('plant');
        }
        return $query->findOrFail($id);
    }
}
