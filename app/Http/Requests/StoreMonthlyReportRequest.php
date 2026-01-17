<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMonthlyReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2100',
            'title' => 'required|string|max:255',
            'pdf_file' => 'required|file|mimes:pdf|max:10240',
            'is_active' => 'nullable|boolean',
        ];
    }
}
