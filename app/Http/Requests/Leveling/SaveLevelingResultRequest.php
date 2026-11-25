<?php

namespace App\Http\Requests\Leveling;

use App\Models\OperatorComplianceItem;
use Illuminate\Foundation\Http\FormRequest;

class SaveLevelingResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, OperatorComplianceItem::MASTER_MANAGER_ROLES, true);
    }

    public function rules(): array
    {
        return [
            'audit_id'      => ['required', 'integer', 'min:1'],
            'check_item_id' => ['required', 'integer', 'min:1'],
            'result'        => ['nullable', 'in:OK,NG'],
        ];
    }
}
