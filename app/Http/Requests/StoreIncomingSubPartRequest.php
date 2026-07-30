<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIncomingSubPartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'item_id' => 'required|exists:items,id',
            'standard' => 'nullable|string',
            'tanggal_datang' => 'required|date',
            'date' => 'required|date',
            'lot_batch_number' => 'required|string',
            'quantity' => 'required|integer',
            'sampling_size_pcs' => 'required|integer',
            'check_dimensi' => 'nullable|string',
            'expired_date' => 'nullable|date',
            'judgment' => 'required|in:OK,NG',
            'total_ng' => 'nullable|integer',
            'remarks' => 'nullable|string',
            'operator_initials' => 'nullable|string',
            'defect_types.*' => 'nullable|string',
            'defect_quantities.*' => 'nullable|integer',
            'qrcode' => 'nullable|string',
            'part_code' => 'nullable|string',
            'supplier_id' => 'nullable|string',
            'unique_code_id' => [
                'nullable',
                'string',
                \Illuminate\Validation\Rule::unique('incoming_sub_parts', 'unique_code_id')->where(function ($query) {
                    if ($this->filled('sap_code')) {
                        $query->where('sap_code', $this->sap_code);
                    }
                    return $query;
                }),
            ],
            'sap_code' => 'nullable|string',
            'scan_method' => 'nullable|string|in:manual,hardware,camera',
            'cycle_time' => 'nullable|integer',
        ];
    }
}
