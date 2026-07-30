<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlatingChecksheetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && !in_array(auth()->user()->role, ['manager', 'asst_manager']);
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
            'qrcode_verifikasi' => 'nullable|string',
            'part_code' => 'nullable|string',
            'supplier_id' => 'nullable|string',
            'quantity' => 'nullable|string',
            'unique_code_id' => [
                'nullable',
                'string',
                \Illuminate\Validation\Rule::unique('plating_checksheets', 'unique_code_id')
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
            'injection_date' => 'required|date',
            'injection_shift' => 'required|string',
            'plating_date' => 'required|date',
            'plating_shift' => 'required|string',
            'no_lot' => 'nullable|string',
            'line' => 'required|string',
            'total_qty' => 'required|integer|min:0',
            'sampling_qty' => 'nullable|integer|min:0',
            'total_ok' => 'required|integer|min:0',
            'total_ng' => 'required|integer|min:0',
            'judgment' => 'required|in:OK,NG',
            'operator_initials' => 'nullable|string',
            'remarks' => 'nullable|string',
            'cycle_time' => 'nullable|integer',
            'next_proses' => [
                Rule::requiredIf(function () {
                    return $this->judgment === 'NG';
                }),
                'nullable',
                'string'
            ],
            'is_scanned' => 'nullable|boolean',
            'jam_before' => 'nullable|string',
            'jam_after' => 'nullable|string',
            'defect_types' => 'nullable|array',
            'defect_quantities' => 'nullable|array',
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
            // 'sampling_qty.required' => 'Check Qty wajib diisi.',
            'total_ok.required' => 'Total OK wajib diisi.',
            'total_ng.required' => 'Total NG wajib diisi.',
            'judgment.required' => 'Judgment wajib dipilih.',
            'judgment.in' => 'Judgment harus OK atau NG.',
            'next_proses.required_if' => 'Untuk hasil NG, Next Proses wajib dipilih.',
        ];
    }
}
