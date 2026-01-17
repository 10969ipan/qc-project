<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMachineStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'type' => 'required|in:line,machine',
            'number' => 'required|integer',
            'status' => 'required|in:normal,maintenance,stopped,trouble',
            'description' => 'nullable|string|max:255',
        ];
    }
}
