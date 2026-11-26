<?php

namespace App\Http\Requests\Leveling;

use App\Models\OperatorComplianceItem;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLevelingMejaMesinRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user && in_array($user->role, OperatorComplianceItem::MASTER_MANAGER_ROLES, true);
    }

    public function rules(): array
    {
        return [
            'audit_id'   => ['required', 'integer', 'exists:leveling_audits,id'],
            'meja_mesin' => ['nullable', 'string', 'max:80'],
        ];
    }
}
