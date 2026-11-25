<?php

namespace App\Http\Requests\Leveling;

use App\Models\OperatorComplianceItem;
use Illuminate\Foundation\Http\FormRequest;

class CorrectLevelingNgRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, OperatorComplianceItem::MASTER_MANAGER_ROLES, true);
    }

    public function rules(): array
    {
        return [
            'why_1'                 => ['required', 'string', 'max:3000'],
            'why_2'                 => ['nullable', 'string', 'max:3000'],
            'why_3'                 => ['nullable', 'string', 'max:3000'],
            'root_cause'            => ['required', 'string', 'max:4000'],
            'corrective_action'     => ['required', 'string', 'max:5000'],
            'corrective_date'       => ['required', 'date'],
            'corrective_evidences'  => ['required', 'array', 'min:1', 'max:10'],
            'corrective_evidences.*'=> ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'why_1.required'                => 'Why 1 wajib diisi minimal satu data.',
            'root_cause.required'           => 'Root Cause wajib diisi.',
            'corrective_action.required'    => 'Tindakan perbaikan wajib diisi.',
            'corrective_date.required'      => 'Tanggal perbaikan wajib diisi.',
            'corrective_evidences.required' => 'Foto evidence perbaikan wajib diupload minimal 1 foto.',
            'corrective_evidences.max'      => 'Maksimal 10 foto perbaikan.',
            'corrective_evidences.*.image'  => 'Semua file evidence perbaikan harus berupa gambar.',
            'corrective_evidences.*.mimes'  => 'Format foto harus JPG, JPEG, PNG, atau WEBP.',
            'corrective_evidences.*.max'    => 'Ukuran setiap foto maksimal 10 MB.',
        ];
    }
}
