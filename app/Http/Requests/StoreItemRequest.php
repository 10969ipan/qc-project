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
            ],
            'category_id' => [
                'required',
                'exists:categories,id',
                function ($attribute, $value, $fail) {
                    $plantId = \App\Models\Plant::resolveId($this->input('plant')) ?? auth()->user()->plant_id;
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
            'part_number' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if (!empty($value)) {
                        $plantId = \App\Models\Plant::resolveId($this->input('plant')) ?? auth()->user()->plant_id;
                        $categoryId = $this->input('category_id');
                        if (Item::where('part_number', $value)
                            ->where('plant_id', $plantId)
                            ->where('category_id', $categoryId)
                            ->exists()) {
                            $fail('Nomor Part ini sudah terdaftar di kategori ini pada plant ini.');
                        }
                    }
                },
            ],
            'cavity' => 'nullable|integer|min:1',
            'weight_standard' => 'nullable|string|max:100',
            'sap_code' => [
                'nullable',
                'string',
                'max:100',
                function ($attribute, $value, $fail) {
                    if (!empty($value)) {
                        $plantId = \App\Models\Plant::resolveId($this->input('plant')) ?? auth()->user()->plant_id;
                        $categoryId = $this->input('category_id');
                        if (Item::where('sap_code', $value)
                            ->where('plant_id', $plantId)
                            ->where('category_id', $categoryId)
                            ->exists()) {
                            $fail('Kode SAP ini sudah terdaftar di kategori ini pada plant ini.');
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
