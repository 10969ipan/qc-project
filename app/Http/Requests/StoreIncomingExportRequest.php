<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIncomingExportRequest extends FormRequest
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
            'date' => 'required|date',
            'tanggal_delivery' => 'required|date',
            'lot_qty' => 'required|integer|min:0',
            'total_check' => 'required|integer|min:0',
            'judgment' => 'required|in:OK,NG',
            'total_ng' => 'nullable|integer',
            'remarks' => 'nullable|string',
            'operator_initials' => 'nullable|string',
            'defect_types.*' => 'nullable|string',
            'defect_quantities.*' => 'nullable|integer',
        ];
    }
}
