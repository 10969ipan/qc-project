<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubAssyChecksheetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = auth()->user();
        if (!$user)
            return false;

        // Admin can edit anything
        if ($user->role === 'admin')
            return true;

        // Managers cannot edit, they only approve
        if (in_array($user->role, ['manager', 'asst_manager']))
            return false;

        // Lock if already approved by Manager (integrity check)
        $checksheet = $this->route('checksheet');
        if (is_string($checksheet)) {
            $checksheet = \App\Models\SubAssyChecksheet::find($checksheet);
        }

        if ($checksheet && $checksheet->manager_qc && $checksheet->manager_qc !== 'REJECTED') {
            return false;
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'item_id' => 'required|exists:items,id',
            'qrcode' => 'nullable|string',
            'part_code' => 'nullable|string',
            'supplier_id' => 'nullable|string',
            'quantity' => 'nullable|string',
            'unique_code_id' => [
                'nullable',
                'string',
                \Illuminate\Validation\Rule::unique('sub_assy_checksheets', 'unique_code_id')
                    ->ignore($this->route('id') ?? $this->route('checksheet'))
                    ->where(function ($query) {
                        if ($this->filled('sap_code')) {
                            $query->where('sap_code', $this->sap_code);
                        }
                        return $query->whereNotNull('unique_code_id');
                    }),
            ],
            'sap_code' => 'nullable|string',
            'date' => 'required|date',
            'shift' => 'required|string',
            'line' => 'required|string',
            'total_qty' => 'required|integer|min:0',
            'sampling_qty' => 'required|integer|min:0',
            'total_ok' => 'required|integer|min:0',
            'total_ng' => 'required|integer|min:0',
            'judgment' => 'required|in:OK,NG',
            'operator_initials' => 'nullable|string',
            'remarks' => 'nullable|string',
            'cycle_time' => 'nullable|integer',
            'jam_before' => 'nullable|date_format:H:i',
            'jam_after' => 'nullable|date_format:H:i',
            'next_proses' => 'required_if:judgment,NG|nullable|string',
            'defect_types' => 'nullable|array',
            'defect_types.*' => 'nullable|string',
            'defect_quantities' => 'nullable|array',
            'defect_quantities.*' => 'nullable|numeric|min:1',
            'plant' => 'nullable|string', // Support for admin to move reports between plants
            'user_id' => 'nullable|exists:users,id', // Allow manual correction of inspector
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'item_id.required' => 'Item wajib dipilih.',
            'item_id.exists' => 'Item yang dipilih tidak valid.',
            'date.required' => 'Tanggal wajib diisi.',
            'shift.required' => 'Shift wajib dipilih.',
            'line.required' => 'Line wajib diisi.',
            'total_qty.required' => 'Total Qty wajib diisi.',
            'sampling_qty.required' => 'Sampling Qty wajib diisi.',
            'total_ok.required' => 'Total OK wajib diisi.',
            'total_ng.required' => 'Total NG wajib diisi.',
            'judgment.required' => 'Judgment wajib dipilih.',
            'judgment.in' => 'Judgment harus OK atau NG.',
            'next_proses.required_if' => 'Untuk hasil NG, Next Proses wajib dipilih.',
        ];
    }
}
