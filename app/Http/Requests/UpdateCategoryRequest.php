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
        $id = $this->route('category') instanceof \App\Models\Category
            ? $this->route('category')->id
            : $this->route('category');

        return [
            'name' => 'required|string|max:255|unique:categories,name,' . $id,
        ];
    }
}
