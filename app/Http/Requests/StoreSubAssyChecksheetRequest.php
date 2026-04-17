<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class StoreSubAssyChecksheetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
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
            'quantity' => 'nullable|integer',
            'unique_code_id' => [
                'nullable',
                'string',
                \Illuminate\Validation\Rule::unique('sub_assy_checksheets', 'unique_code_id')
                    ->where('part_code', $this->part_code)
                    ->whereNotNull('unique_code_id')
            ],
            'sap_code' => 'nullable|string',
            'plant' => 'required',
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
            'defect_types' => 'nullable|array',
            'defect_quantities' => 'nullable|array',
            'next_proses' => 'required_if:judgment,NG|nullable|string',
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
            'line.required' => 'Meja/Line wajib diisi.',
            'judgment.required' => 'Judgment (OK/NG) harus terisi.',
            'judgment.in' => 'Judgment harus OK atau NG.',
            'unique_code_id.unique' => 'QR Code / Label ini sudah pernah di-scan dan disimpan sebelumnya. Gunakan label yang berbeda.',
            'next_proses.required_if' => 'Untuk hasil NG, Next Proses wajib dipilih.',
            'total_ok.required' => 'Total OK harus terisi.',
            'total_ng.required' => 'Total NG harus terisi.',
            'sampling_qty.required' => 'Sampling Qty harus terisi.',
            'total_qty.required' => 'Total Qty harus terisi.',
            'plant.required' => 'Informasi Plant tidak ditemukan. Pastikan Anda memiliki akses ke plant yang benar.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        if ($this->ajax() || $this->wantsJson()) {
            Log::warning('Validation failed for Sub Assy checksheet', [
                'user_id' => auth()->id(),
                'errors' => $validator->errors()->toArray(),
                'input' => $this->except(['_token', 'qrcode'])
            ]);

            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', $validator->errors()->all()),
                'errors' => $validator->errors()
            ], 422));
        }

        parent::failedValidation($validator);
    }
}
