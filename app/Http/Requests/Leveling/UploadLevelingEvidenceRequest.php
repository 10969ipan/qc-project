<?php

namespace App\Http\Requests\Leveling;

use App\Models\OperatorComplianceItem;
use Illuminate\Foundation\Http\FormRequest;

class UploadLevelingEvidenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, OperatorComplianceItem::MASTER_MANAGER_ROLES, true);
    }

    public function rules(): array
    {
        return [
            'audit_id'      => ['required', 'integer', 'exists:leveling_audits,id'],
            'check_item_id' => ['required', 'integer', 'exists:leveling_check_items,id'],
            'evidence_note' => ['nullable', 'string', 'max:1000'],
            'evidences'     => ['required_without:evidence', 'array', 'min:1', 'max:10'],
            'evidences.*'   => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'evidence'      => ['required_without:evidences', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'evidences.required_without' => 'File foto evidence wajib dipilih.',
            'evidences.max'              => 'Maksimal 10 foto sekaligus dalam sekali upload.',
            'evidences.*.image'          => 'Semua file evidence harus berupa gambar.',
            'evidences.*.mimes'          => 'Format foto harus JPG, JPEG, PNG, atau WEBP.',
            'evidences.*.max'            => 'Ukuran setiap foto maksimal 10 MB.',
            'evidence.image'             => 'File evidence harus berupa gambar.',
            'evidence.mimes'             => 'Format foto harus JPG, JPEG, PNG, atau WEBP.',
            'evidence.max'               => 'Ukuran foto maksimal 10 MB.',
        ];
    }
}
