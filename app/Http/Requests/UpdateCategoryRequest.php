<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->role, ['admin', 'supervisor', 'kashift']);
    }

    public function rules(): array
    {
        $category = $this->route('category');
        $id = $category instanceof \App\Models\Category ? $category->id : $category;

        $user = auth()->user();
        $plantId = $category instanceof \App\Models\Category ? $category->plant_id : $user->plant_id;

        if ($this->has('plant')) {
            $resolvedId = \App\Models\Plant::resolveId($this->input('plant'));
            if ($resolvedId) {
                $plantId = $resolvedId;
            }
        }

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('categories')->ignore($id)->where(function ($query) use ($plantId) {
                    return $query->where('plant_id', $plantId);
                }),
            ],
            'plant' => 'nullable|string',
        ];
    }
}
