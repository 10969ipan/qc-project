<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSortirChecksheetRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (!auth()->check()) {
            return false;
        }

        $user = auth()->user();

        // 1. Bar managers/asst_managers from making update requests (approvers only)
        if (in_array($user->role, ['manager', 'asst_manager']) && $user->name !== 'Marsiah') {
            return false;
        }

        // 2. Lock edits if already approved by Manager (unless admin)
        $id = $this->route('id');
        if ($id) {
            $checksheet = \App\Models\SortirChecksheet::find($id);
            if ($checksheet && $user->role !== 'admin') {
                // If Manager has approved and it's not rejected, lock it
                if ($checksheet->manager_qc && $checksheet->manager_qc !== 'REJECTED') {
                    return false;
                }
            }
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'item_id' => 'required|exists:items,id',
            'plant' => 'nullable|exists:plants,code',
            'date' => 'required|date',
            'shift' => 'required|string',
            'line' => 'nullable|string',
            'total_qty' => 'required|integer|min:0',
            'sampling_qty' => 'required|integer|min:0',
            'total_ok' => 'required|integer|min:0',
            'total_ng' => 'required|integer|min:0',
            'judgment' => [
                'required',
                'in:OK,NG',
                function ($attribute, $value, $fail) {
                    if (request('total_ng') > 0 && $value !== 'NG') {
                        $fail('Untuk hasil sortir dengan NG > 0, Judgment harus NG.');
                    }
                }
            ],
            'operator_initials' => 'nullable|string',
            'remarks' => 'nullable|string',
            'cycle_time' => 'nullable|integer',
            'next_proses' => 'required_if:judgment,NG|nullable|in:HOLD,REPAIR,CRUSHING,SORTIR,FINISHING,MARKING+FINISHING+PACKING',
            'defect_types' => 'nullable|array',
            'defect_quantities' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'next_proses.required_if' => 'Untuk hasil NG, Next Proses wajib dipilih.',
        ];
    }
}
