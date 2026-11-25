<?php

namespace App\Http\Requests\Leveling;

use App\Models\OperatorComplianceItem;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLevelingNgPhotosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, OperatorComplianceItem::MASTER_MANAGER_ROLES, true);
    }

    public function rules(): array
    {
        return [
            'keep_indices'    => ['nullable', 'array'],
            'keep_indices.*'  => ['integer', 'min:0'],
            'new_evidences'   => ['nullable', 'array', 'max:10'],
            'new_evidences.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'new_evidences.max'     => 'Maksimal 10 foto perbaikan.',
            'new_evidences.*.image' => 'Semua file evidence perbaikan harus berupa gambar.',
            'new_evidences.*.mimes' => 'Format foto harus JPG, JPEG, PNG, atau WEBP.',
            'new_evidences.*.max'   => 'Ukuran setiap foto maksimal 10 MB.',
        ];
    }
}
