<?php

namespace App\Http\Requests\Leveling;

use App\Models\OperatorComplianceItem;
use Illuminate\Foundation\Http\FormRequest;

class StoreLevelingAuditRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, OperatorComplianceItem::MASTER_MANAGER_ROLES, true);
    }

    public function rules(): array
    {
        return [
            'plant'        => ['required', 'string', 'max:30'],
            'inspector_id' => ['required', 'integer', 'exists:users,id'],
            'bagian'       => ['required', 'string', 'max:120'],
            'meja_mesin'   => ['nullable', 'string', 'max:80'],
            'item_id'      => ['required', 'string', 'exists:items,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'inspector_id.required' => 'Nama Inspector wajib dipilih.',
            'bagian.required'       => 'Proses / Line (Bagian) wajib diisi.',
            'item_id.required'      => 'Nama Part / No. Part wajib dipilih.',
        ];
    }
}
