<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIncomingPartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
            'date' => 'required|date',
            'shift' => 'required|string',
            'lot_qty' => 'required|integer|min:0',
            'total_check' => 'required|integer|min:0',
            'tanggal_datang' => 'required|date',
            'judgment' => 'required|in:OK,NG',
            'total_ng' => 'nullable|integer',
            'remarks' => 'nullable|string',
            'operator_initials' => 'nullable|string',
            'defect_types.*' => 'nullable|string',
            'defect_quantities.*' => 'nullable|integer',
        ];
    }
}
