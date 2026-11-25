<?php

namespace App\Http\Requests\Leveling;

use App\Models\OperatorComplianceItem;
use Illuminate\Foundation\Http\FormRequest;

class BulkSaveLevelingResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, OperatorComplianceItem::MASTER_MANAGER_ROLES, true);
    }

    public function rules(): array
    {
        return [
            'audit_id'         => ['required', 'integer', 'exists:leveling_audits,id'],
            'check_item_ids'   => ['required', 'array', 'min:1'],
            'check_item_ids.*' => ['integer', 'min:1'],
            'result'           => ['required', 'in:OK,NG'],
        ];
    }
}
