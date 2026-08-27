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
                    $qty = $this->defect_quantities[$index] ?? 0;
                    if ((int)$qty > 0) {
                        $types[] = $type;
                        $quantities[] = (int)$qty;
                    }
                }
            }

            $this->merge([
                'defect_types' => $types,
                'defect_quantities' => $quantities,
            ]);
        }

        if ($this->has('part_weight') && is_array($this->part_weight)) {
            $weights = array_map(function ($w) {
                return ($w === '' || $w === null) ? null : (is_numeric($w) ? (float)$w : null);
            }, $this->part_weight);
            $this->merge(['part_weight' => array_values(array_filter($weights, fn($v) => $v !== null))]);
        }
    }

    public function rules(): array
    {
        return [
            'item_id' => 'required|exists:items,id',
            'date' => 'required|date',
            'shift' => 'required|string',
            'code_machine' => 'required|string',
            'category' => 'nullable|string',
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
            'jam_before' => 'nullable|date_format:H:i',
            'jam_after' => 'nullable|date_format:H:i',
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
            'defect_types' => 'nullable|array',
            'defect_types.*' => 'nullable|string',
            'defect_quantities' => 'nullable|array',
            'defect_quantities.*' => 'nullable|numeric|min:1',
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
            'judgment.in' => 'Judgment harus OK atau NG.',
        ];
    }
}
