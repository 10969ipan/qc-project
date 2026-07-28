<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInProcessChecksheetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && !in_array(auth()->user()->role, ['manager', 'asst_manager']);
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'unique_code_id' => !empty($this->unique_code_id) ? $this->unique_code_id : null,
            'qrcode' => !empty($this->qrcode) ? $this->qrcode : null,
            'part_code' => !empty($this->part_code) ? $this->part_code : null,
            'supplier_id' => !empty($this->supplier_id) ? $this->supplier_id : null,
            'sap_code' => !empty($this->sap_code) ? $this->sap_code : null,
        ]);
    }

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
            'quantity' => 'nullable|integer',
            'unique_code_id' => [
                'nullable',
                'string',
                \Illuminate\Validation\Rule::unique('in_process_checksheets', 'unique_code_id')
                    ->where('quantity', $this->quantity),
            ],
            'sap_code' => 'nullable|string',
            'plant' => 'required', // Can be Name, Code, or UUID
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
            'part_weight' => 'nullable|array',
            'part_weight.*' => 'nullable|numeric|min:0',
            'cycle_time' => 'nullable|integer',
            'defect_types' => 'nullable|array',
            'defect_quantities' => 'nullable|array',
            'next_proses' => 'required_if:judgment,NG|nullable|string',
            'tujuan' => 'nullable|string',
            'scan_method' => 'nullable|string|in:manual,hardware,camera',
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
            'unique_code_id.unique' => 'QR Code / Label ini sudah pernah di-scan dan disimpan sebelumnya. Gunakan label yang berbeda.',
            'next_proses.required_if' => 'Untuk hasil NG, Next Proses wajib dipilih.',
        ];
    }
}
