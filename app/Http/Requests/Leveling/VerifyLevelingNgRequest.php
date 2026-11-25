<?php

namespace App\Http\Requests\Leveling;

use App\Models\OperatorComplianceItem;
use Illuminate\Foundation\Http\FormRequest;

class VerifyLevelingNgRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, OperatorComplianceItem::MASTER_MANAGER_ROLES, true);
    }

    public function rules(): array
    {
        return [
            'action'        => ['required', 'in:approve,reject,unverify'],
            'reject_reason' => ['required_if:action,reject', 'nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'reject_reason.required_if' => 'Alasan penolakan wajib diisi saat me-reject perbaikan.',
        ];
    }
}
