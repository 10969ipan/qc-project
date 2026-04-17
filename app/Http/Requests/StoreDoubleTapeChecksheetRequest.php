<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDoubleTapeChecksheetRequest extends FormRequest
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
            'item_id' => [
                'required',
                'exists:items,id',
                function ($attribute, $value, $fail) {
                    $plantId = \App\Models\Plant::resolveId(request('plant')) ?? auth()->user()->plant_id;
                    $item = \App\Models\Item::find($value);

                    if ($item && $item->plant_id != $plantId) {
                        $fail('Item yang dipilih tidak terdaftar untuk plant ini.');
                    }
                },
            ],
            'qrcode' => 'nullable|string',
            'part_code' => 'nullable|string',
            'supplier_id' => 'nullable|string',
            'quantity' => 'nullable|string',
            'unique_code_id' => [
                'nullable',
                'string',
                \Illuminate\Validation\Rule::unique('double_tape_checksheets', 'unique_code_id')
                    ->where('part_code', $this->part_code)
                    ->whereNotNull('unique_code_id'),
            ],
            'sap_code' => 'nullable|string',
            'plant' => 'required',
            'date' => 'required|date',
            'shift' => 'required|string',
            'check_type' => 'nullable|in:sampling,fullcheck',
            // 'line' is omitted for Double Tape
            'total_qty' => 'required|integer|min:0',
            'sampling_qty' => 'required|integer|min:0',
            'total_ok' => 'required|integer|min:0',
            'total_ng' => 'required|integer|min:0',
            'judgment' => 'required|in:OK,NG',
            'operator_initials' => 'nullable|string',
            'remarks' => 'nullable|string',
            'cycle_time' => 'nullable|integer',
            'defect_types' => 'nullable|array',
            'defect_quantities' => 'nullable|array',
            'next_proses' => 'required_if:judgment,NG|nullable|string',
        ];
    }

    /**
     * Prepare data before validation — pastikan judgment dan numerik punya nilai default.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'judgment'     => $this->judgment ?: 'OK',
            'total_qty'    => $this->total_qty !== null && $this->total_qty !== '' ? $this->total_qty : 0,
            'sampling_qty' => $this->sampling_qty !== null && $this->sampling_qty !== '' ? $this->sampling_qty : 0,
            'total_ok'     => $this->total_ok !== null && $this->total_ok !== '' ? $this->total_ok : 0,
            'total_ng'     => $this->total_ng !== null && $this->total_ng !== '' ? $this->total_ng : 0,
            'unique_code_id' => !empty($this->unique_code_id) ? $this->unique_code_id : null,
            'qrcode' => !empty($this->qrcode) ? $this->qrcode : null,
            'part_code' => !empty($this->part_code) ? $this->part_code : null,
            'supplier_id' => !empty($this->supplier_id) ? $this->supplier_id : null,
            'sap_code' => !empty($this->sap_code) ? $this->sap_code : null,
        ]);

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
