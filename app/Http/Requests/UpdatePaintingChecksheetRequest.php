<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaintingChecksheetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && !in_array(auth()->user()->role, ['manager', 'asst_manager']);
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'unique_code_id' => !empty($this->unique_code_id) ? $this->unique_code_id : null,
            'qrcode' => !empty($this->qrcode) ? $this->qrcode : null,
            'part_code' => !empty($this->part_code) ? $this->part_code : null,
            'supplier_id' => !empty($this->supplier_id) ? $this->supplier_id : null,
            'sap_code' => !empty($this->sap_code) ? $this->sap_code : null,
            'qrcode_verifikasi' => !empty($this->qrcode_verifikasi) ? $this->qrcode_verifikasi : null,
        ]);
    }

    public function rules(): array
    {
        $id = $this->route('id') ?? $this->route('painting');

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
            'qrcode_verifikasi' => 'nullable|string',
            'part_code' => 'nullable|string',
            'supplier_id' => 'nullable|string',
            'quantity' => 'nullable|string',
            'unique_code_id' => [
                'nullable',
                'string',
                \Illuminate\Validation\Rule::unique('painting_checksheets', 'unique_code_id')
                    ->ignore($id)
                    ->where(function ($query) {
                        if ($this->filled('sap_code')) {
                            $query->where('sap_code', $this->sap_code);
                        }
                        return $query->whereNotNull('unique_code_id');
                    }),
            ],
            'sap_code' => 'nullable|string',
            'plant' => 'required',
            'date' => 'required|date',
            'shift' => 'required|string',
            'injection_date' => 'nullable|date',
            'injection_shift' => 'nullable|string',
            'injection_initials' => 'nullable|string',
            'painting_date' => 'nullable|date',
            'painting_shift' => 'nullable|string',
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
            'defect_types' => 'nullable|array',
            'defect_quantities' => 'nullable|array',
            'next_proses' => [
                Rule::requiredIf(function () {
                    return $this->judgment === 'NG';
                }),
                'nullable',
                'string'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'item_id.required' => 'Item wajib dipilih.',
            'item_id.exists' => 'Item yang dipilih tidak valid.',
            'date.required' => 'Tanggal wajib diisi.',
            'shift.required' => 'Shift wajib dipilih.',
            'injection_initials.required' => 'Inisial Lot ID wajib diisi.',
            'line.required' => 'Line wajib diisi.',
            'total_qty.required' => 'Total Qty wajib diisi.',
            'total_ok.required' => 'Total OK wajib diisi.',
            'total_ng.required' => 'Total NG wajib diisi.',
            'judgment.required' => 'Judgment wajib dipilih.',
            'judgment.in' => 'Judgment harus OK atau NG.',
            'next_proses.required_if' => 'Untuk hasil NG, Next Proses wajib dipilih.',
        ];
    }
}
