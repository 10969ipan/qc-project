<?php

namespace App\Http\Requests\Leveling;

use App\Models\OperatorComplianceItem;
use Illuminate\Foundation\Http\FormRequest;

class StoreLevelingCheckItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, OperatorComplianceItem::MASTER_MANAGER_ROLES, true);
    }

    public function rules(): array
    {
        return [
            'prinsip'     => ['required', 'string', 'max:30'],
            'level'       => ['required', 'integer', 'in:1,2,3'],
            'order_no'    => ['nullable', 'integer', 'min:1'],
            'point_check' => ['required', 'string', 'max:2000'],
            'guidance'    => ['nullable', 'string', 'max:15000'],
            'is_active'   => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'prinsip.required'     => 'Prinsip wajib diisi.',
            'level.required'       => 'Level wajib dipilih (1, 2, atau 3).',
            'point_check.required' => 'Point Check wajib diisi.',
        ];
    }
}
