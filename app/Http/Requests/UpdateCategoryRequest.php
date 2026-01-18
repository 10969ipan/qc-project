<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $category = $this->route('category');
        $id = $category instanceof \App\Models\Category ? $category->id : $category;
        // Since we are updating, we should check against the plant of the CATEGORY itself, 
        // in case Admin (Karawang) is editing Jakarta Category.
        // If $category is just ID (rare if route model binding used), we might need to fetch it.
        // Assuming Route Model Binding is active.
        $plantId = $category instanceof \App\Models\Category ? $category->plant_id : auth()->user()->plant_id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('categories')->ignore($id)->where(function ($query) use ($plantId) {
                    return $query->where('plant_id', $plantId);
                }),
            ],
        ];
    }
}
