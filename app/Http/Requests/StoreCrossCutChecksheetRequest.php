<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCrossCutChecksheetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && !in_array(auth()->user()->role, ['manager', 'asst_manager']);
    }

    protected function prepareForValidation()
    {
        $now = now();
        $this->merge([
            'date' => $this->qc_date ?? now()->toDateString(),
            'production_datetime' => $this->production_date ? $this->production_date . ' ' . $now->format('H:i:s') : null,
            'qc_datetime' => $this->qc_date ? $this->qc_date . ' ' . $now->format('H:i:s') : null,
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
            'chemical_catalyst' => 'nullable|string|max:255',
            'chemical_abu' => 'nullable|string|max:255',
            'position_remark_judgment' => 'required|in:OK,NG',
            'position_remark_no_lot' => 'required|string|max:255',
            'visual_ok' => 'nullable|boolean',
            'result_remark' => 'nullable|string',
            'keterangan' => 'nullable|string',
            'cycle_time' => 'nullable|integer',
            'operator_initials' => 'nullable|string|max:255',
            'next_proses' => 'nullable|string',
        ];
    }
}
