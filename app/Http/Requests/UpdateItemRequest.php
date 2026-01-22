<?php

namespace App\Http\Requests;

use App\Models\Item;
use Illuminate\Foundation\Http\FormRequest;

class UpdateItemRequest extends FormRequest
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
        $item = $this->route('item');
        $itemId = $item instanceof Item ? $item->id : $item;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($itemId) {
                    $exists = Item::where('name', $value)
                        ->where('part_number', $this->part_number)
                        ->where('id', '!=', $itemId)
                        ->exists();

                    if ($exists) {
                        $fail('Item dengan nama dan part number yang sama sudah ada.');
                    }
                },
            ],
            'category_id' => 'required|exists:categories,id',
            'plant' => 'required|string',
            'files' => 'nullable|array',
            'files.*' => 'mimes:pdf|max:10240', // Max 10MB per file
            'customer' => 'nullable|string',
            'part_number' => 'nullable|string',
            'sap_code' => [
                'nullable',
                'string',
                'max:100',
                function ($attribute, $value, $fail) use ($itemId) {
                    if (!empty($value)) {
                        $exists = Item::where('sap_code', $value)
                            ->where('id', '!=', $itemId)
                            ->exists();
                        if ($exists) {
                            $fail('Kode SAP sudah digunakan oleh item lain.');
                        }
                    }
                },
            ],
            'defects' => 'nullable|string',
            'dimension_points' => 'nullable|array',
            'dimension_sizes' => 'nullable|array',
            'dimension_tolerances' => 'nullable|array',
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
            'file.mimes' => 'File harus berformat PDF.',
            'file.max' => 'Ukuran file maksimal 5MB.',
        ];
    }
}
