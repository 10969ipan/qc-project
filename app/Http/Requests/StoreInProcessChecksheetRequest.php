<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInProcessChecksheetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
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
            'defect_types' => 'nullable|array',
            'defect_quantities' => 'nullable|array',
            'next_proses' => 'nullable|string',
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
        ];
    }
}
