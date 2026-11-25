<?php

namespace App\Http\Requests\Leveling;

use App\Models\LevelingRecapApproval;
use Illuminate\Foundation\Http\FormRequest;

class ApproveLevelingRecapRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, LevelingRecapApproval::REKAP_ACCESS_ROLES, true);
    }

    public function rules(): array
    {
        return [
            'plant_code'   => ['required', 'string', 'max:32'],
            'year'         => ['required', 'integer', 'min:2020', 'max:2100'],
            'inspector_id' => ['required', 'integer', 'exists:users,id'],
            'stage'        => ['required', 'in:auditor,leader,spv,asst_mgr'],
            'action'       => ['required', 'in:approve,cancel'],
        ];
    }
}
