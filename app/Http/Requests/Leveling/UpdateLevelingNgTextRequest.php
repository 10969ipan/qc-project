<?php

namespace App\Http\Requests\Leveling;

use App\Models\OperatorComplianceItem;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLevelingNgTextRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, OperatorComplianceItem::MASTER_MANAGER_ROLES, true);
    }

    public function rules(): array
    {
        return [
            'why_1'             => ['required', 'string', 'max:3000'],
            'why_2'             => ['nullable', 'string', 'max:3000'],
            'why_3'             => ['nullable', 'string', 'max:3000'],
            'root_cause'        => ['required', 'string', 'max:4000'],
            'corrective_action' => ['required', 'string', 'max:5000'],
            'corrective_date'   => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'why_1.required'             => 'Why 1 wajib diisi.',
            'root_cause.required'        => 'Root Cause wajib diisi.',
            'corrective_action.required' => 'Tindakan perbaikan wajib diisi.',
            'corrective_date.required'   => 'Tanggal perbaikan wajib diisi.',
        ];
    }
}
