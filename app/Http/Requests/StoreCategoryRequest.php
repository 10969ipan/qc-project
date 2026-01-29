<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->role, ['admin', 'supervisor', 'kashift']);
    }

    public function rules(): array
    {
        $user = auth()->user();
        $plantId = $user->plant_id;

        // Give priority to the 'plant' input if present and valid
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
                \Illuminate\Validation\Rule::unique('categories')->where(function ($query) use ($plantId) {
                    return $query->where('plant_id', $plantId);
                }),
            ],
            'plant' => 'nullable|string',
        ];
    }
}
