<?php

namespace App\Http\Requests;

use App\Models\Item;
use Illuminate\Foundation\Http\FormRequest;

class StoreItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->role, ['admin', 'supervisor', 'kashift']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    $plantId = \App\Models\Plant::resolveId(request('plant')) ?? auth()->user()->plant_id;
                    $categoryId = request('category_id');
                    $partNumber = request('part_number');

                    $exists = Item::where('name', $value)
                        ->where('part_number', $partNumber)
                        ->where('plant_id', $plantId)
                        ->where('category_id', $categoryId)
                        ->exists();

                    if ($exists) {
                        $fail('Item dengan nama dan part number yang sama sudah ada di kategori ini.');
                    }
                },
            ],
            'category_id' => [
                'required',
                'exists:categories,id',
                function ($attribute, $value, $fail) {
                    $plantId = \App\Models\Plant::resolveId(request('plant')) ?? auth()->user()->plant_id;
                    $category = \App\Models\Category::find($value);
                    if ($category && $category->plant_id != $plantId) {
                        $fail('Kategori yang dipilih tidak terdaftar untuk plant ini.');
                    }
                },
            ],
            'plant' => 'required',
            'files' => 'required|array|min:1',
            'files.*' => 'mimes:pdf|max:10240', // Max 10MB per file
            'similar_part_file' => 'nullable|mimes:pdf|max:10240',
            'customer' => 'nullable|string',
            'part_number' => 'nullable|string',
            'sap_code' => [
                'nullable',
                'string',
                'max:100',
                function ($attribute, $value, $fail) {
                    if (!empty($value)) {
                        $plantId = \App\Models\Plant::resolveId(request('plant')) ?? auth()->user()->plant_id;
                        $exists = Item::where('sap_code', $value)
                            ->where('plant_id', $plantId)
                            ->exists();
                        if ($exists) {
                            $fail('Kode SAP sudah digunakan oleh item lain di plant ini.');
                        }
                    }
                },
            ],
            'defects' => 'nullable|string',
            'dimension_points' => 'nullable|array',
            'dimension_sizes' => 'nullable|array',
            'dimension_tolerances' => 'nullable|array',
            'dimension_mins' => 'nullable|array',
            'dimension_maxs' => 'nullable|array',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama item wajib diisi.',
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid.',
            'file.required' => 'File PDF wajib diunggah.',
            'file.mimes' => 'File harus berformat PDF.',
            'file.max' => 'Ukuran file maksimal 5MB.',
        ];
    }
}
