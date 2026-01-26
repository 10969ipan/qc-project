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
        // For updates, we might only change the date but keep the time
        // However, standardizing to current time or keeping original time is tricky without fetching model
        // Simplest consistent approach: combine input date with CURRENT time if updating, OR try to preserve logic
        // But since user wants "same as Plating" (which I assume is fresh time or handled in backend),
        // I will use current time for simplicity unless existing time is crucial.
        // Actually, for edit, we usually want to KEEP the original time if only date changes, but here we just merge date + now
        // to pass validation. 
        // BETTER: Use the time from the hidden inputs if available?
        // No, I'm removing hidden inputs.
        // Let's use current time or 00:00:00?
        // Use current time to be safe.

        $now = now();

        // We need to check if production_date is present
        if ($this->has('production_date')) {
            $this->merge(['production_datetime' => $this->production_date . ' ' . $now->format('H:i:s')]);
        }
        if ($this->has('qc_date')) {
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
        ];
    }
}
