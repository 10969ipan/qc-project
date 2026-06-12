<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCrossCutPaintingChecksheetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && !in_array(auth()->user()->role, ['manager', 'asst_manager']);
    }

    protected function prepareForValidation()
    {
        // Combine date input with current time for full datetime
        $now = now();
        $this->merge([
            'production_datetime' => $this->production_date . ' ' . $now->format('H:i:s'),
            'qc_datetime' => $this->qc_date . ' ' . $now->format('H:i:s'),
        ]);
    }

    public function rules(): array
    {
        return [
            'item_id' => 'required|exists:items,id',
            'plant' => 'required',
            'production_shift' => 'required|string|max:255',
            'qc_shift' => 'required|string|max:255',
            'production_datetime' => 'required|date',
            'qc_datetime' => 'required|date',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:10240',
            'pencil_scratch' => 'nullable|string|max:255',
            'tap_test' => 'nullable|string|max:255',
            'position_remark_judgment' => 'required|in:OK,NG',
            'keterangan' => 'nullable|string',
            'cycle_time' => 'nullable|integer',
            'operator_initials' => 'nullable|string|max:255',
            'next_proses' => 'nullable|string',
            'defects' => 'nullable|array',
        ];
    }
}
