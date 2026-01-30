<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIncomingChemicalRequest extends FormRequest
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
            'quantity_kg' => 'required|numeric',
            'komper_jirigen_kg' => 'required|numeric',
            'sampling_size_jirigen_kg' => 'required|numeric',
            'expired_date' => 'required|date',
            'judgment' => 'required|in:OK,NG',
            'total_ng' => 'nullable|integer',
            'remarks' => 'nullable|string',
            'operator_initials' => 'nullable|string',
            'defect_types.*' => 'nullable|string',
            'defect_quantities.*' => 'nullable|integer',
        ];
    }
}
