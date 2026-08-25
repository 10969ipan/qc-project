<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFirstPieceApprovalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && !in_array(auth()->user()->role, ['manager', 'asst_manager']);
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
            'plant' => 'required',
            'date' => 'required|date',
            'shift' => 'required|string',
            'code_machine' => 'required|string',
            'category' => 'required|string',
            'total_qty' => 'required|integer|min:0',
            'sampling_qty' => 'required|integer|min:0',
            'total_ok' => 'required|integer|min:0',
            'total_ng' => 'required|integer|min:0',
            'judgment' => 'required|in:OK,NG',
            'operator_initials' => 'nullable|string',
            'part_weight' => 'nullable|array',
            'part_weight.*' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string',
            'dimensions' => 'nullable|array',
            'cycle_time' => 'nullable|integer',
            'defect_types' => 'nullable|array',
            'defect_quantities' => 'nullable|array',
            'next_proses' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if ($this->judgment !== 'NG') return;
                    $types = array_filter((array) $this->defect_types, fn($t) => !empty($t));
                    $quantities = (array) $this->defect_quantities;
                    $hasNonDimensiDefect = false;
                    $hasAnyDefect = false;
                    foreach ($types as $i => $type) {
                        $qty = (int) ($quantities[$i] ?? 0);
                        if ($qty > 0) {
                            $hasAnyDefect = true;
                            if (!preg_match('/dimensi|dimension/i', trim($type))) {
                                $hasNonDimensiDefect = true;
                            }
                        }
                    }
                    $isOnlyDimensi = $hasAnyDefect && !$hasNonDimensiDefect;
                    if (!$isOnlyDimensi && empty($value)) {
                        $fail('Untuk hasil NG, Next Proses wajib dipilih.');
                    }
                },
            ],
            'sap_code' => 'nullable|string',
            'user_id' => 'nullable|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'item_id.required' => 'Item wajib dipilih.',
            'date.required' => 'Tanggal wajib diisi.',
            'shift.required' => 'Shift wajib dipilih.',
            'code_machine.required' => 'Kode mesin wajib diisi.',
            'category.required' => 'Kategori wajib dipilih.',
            'judgment.in' => 'Judgment harus OK atau NG.',
        ];
    }
}
