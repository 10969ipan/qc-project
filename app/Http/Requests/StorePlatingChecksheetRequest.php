<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePlatingChecksheetRequest extends FormRequest
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
            'plant' => 'required',
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
