<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInProcessChecksheetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && !in_array(auth()->user()->role, ['manager', 'asst_manager']);
    }

    protected function prepareForValidation()
    {
        // Clean up defects arrays
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
    }

    public function rules(): array
    {
        return [
            'item_id' => 'required|exists:items,id',
            'qrcode' => 'nullable|string',
            'part_code' => 'nullable|string',
            'supplier_id' => 'nullable|string',
            'quantity' => 'nullable|integer',
            'unique_code_id' => [
                'nullable',
                'string',
                \Illuminate\Validation\Rule::unique('in_process_checksheets', 'unique_code_id')
                    ->ignore($this->route('id'))
                    ->where(function ($query) {
                        if ($this->filled('sap_code')) {
                            $query->where('sap_code', $this->sap_code);
                        }
                        return $query;
                    }),
            ],
            'sap_code' => 'nullable|string',
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
            'user_id' => 'nullable|exists:users,id', // Allow manual correction of inspector
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
        ];
    }
}
