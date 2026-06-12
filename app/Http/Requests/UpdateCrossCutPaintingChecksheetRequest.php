<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCrossCutPaintingChecksheetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && !in_array(auth()->user()->role, ['manager', 'asst_manager']);
    }

    protected function prepareForValidation()
    {
        $now = now();

        // Merge date into datetime format only if the date fields are present in the request
        // and datetime fields are not yet passed from form data.
        if ($this->has('production_date') && !$this->has('production_datetime')) {
            $this->merge(['production_datetime' => $this->production_date . ' ' . $now->format('H:i:s')]);
        }
        if ($this->has('qc_date') && !$this->has('qc_datetime')) {
            $this->merge(['qc_datetime' => $this->qc_date . ' ' . $now->format('H:i:s')]);
        }
    }

    public function rules(): array
    {
        return [
            'item_id' => 'sometimes|exists:items,id',
            'plant' => 'sometimes',
            'production_shift' => 'sometimes|string|max:255',
            'qc_shift' => 'sometimes|string|max:255',
            'production_datetime' => 'sometimes|date',
            'qc_datetime' => 'sometimes|date',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10240',
            'pencil_scratch' => 'nullable|string|max:255',
            'tap_test' => 'nullable|string|max:255',
            'position_remark_judgment' => 'sometimes|in:OK,NG',
            'keterangan' => 'nullable|string',
            'cycle_time' => 'nullable|integer',
            'operator_initials' => 'nullable|string|max:255',
            'next_proses' => 'nullable|string',
            'defects' => 'nullable|array',
        ];
    }
}
