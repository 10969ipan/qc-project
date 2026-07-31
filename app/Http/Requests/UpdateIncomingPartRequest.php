<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIncomingPartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'item_id' => 'required|exists:items,id',
            'date' => 'required|date',
            'shift' => 'required|string',
            'total_check' => 'required|integer|min:0',
            'sampling_qty' => 'nullable|integer|min:0',
            'tanggal_datang' => 'required|date',
            'shift_datang' => 'required|string',
            'qty_datang' => 'required|integer|min:0',
            'judgment' => 'required|in:OK,NG',
            'total_ng' => 'nullable|integer',
            'remarks' => 'nullable|string',
            'operator_initials' => 'required|string',
            'defect_types.*' => 'nullable|string',
            'defect_quantities.*' => 'nullable|integer',
            'qrcode' => 'nullable|string',
            'part_code' => 'nullable|string',
            'supplier_id' => 'nullable|string',
            'quantity' => 'nullable|integer',
            'unique_code_id' => 'nullable|string',
            'sap_code' => 'nullable|string',
            'scan_method' => 'nullable|string|in:manual,hardware,camera',
            'cycle_time' => 'nullable|integer',
            'qty_balance_sisa' => 'nullable|integer|min:0',
        ];
    }
}
