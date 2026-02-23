<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFirstPieceApprovalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && !in_array(auth()->user()->role, ['manager', 'asst_manager']);
    }

    protected function prepareForValidation()
    {
        if ($this->has('defect_types') && is_array($this->defect_types)) {
            $types = [];
            $quantities = [];

            foreach ($this->defect_types as $index => $type) {
                if (!empty($type)) {
                    $types[] = $type;
                    $quantities[] = $this->defect_quantities[$index] ?? 1;
                }
            }

            $this->merge([
                'defect_types' => $types,
                'defect_quantities' => $quantities,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'item_id' => 'required|exists:items,id',
            'date' => 'required|date',
            'shift' => 'required|string',
            'code_machine' => 'required|string',
            'total_qty' => 'required|integer|min:0',
            'sampling_qty' => 'required|integer|min:0',
            'total_ok' => 'required|integer|min:0',
            'total_ng' => 'required|integer|min:0',
            'judgment' => 'required|in:OK,NG',
            'operator_initials' => 'nullable|string',
            'remarks' => 'nullable|string',
            'dimensions' => 'nullable|array',
            'cycle_time' => 'nullable|integer',
            'jam_before' => 'nullable|date_format:H:i',
            'jam_after' => 'nullable|date_format:H:i',
            'next_proses' => 'required_if:judgment,NG|nullable|string',
            'defect_types' => 'nullable|array',
            'defect_types.*' => 'nullable|string',
            'defect_quantities' => 'nullable|array',
            'defect_quantities.*' => 'nullable|numeric|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'item_id.required' => 'Item wajib dipilih.',
            'date.required' => 'Tanggal wajib diisi.',
            'shift.required' => 'Shift wajib dipilih.',
            'code_machine.required' => 'Kode mesin wajib diisi.',
            'judgment.in' => 'Judgment harus OK atau NG.',
            'next_proses.required_if' => 'Untuk hasil NG, Next Proses wajib dipilih.',
        ];
    }
}
