<?php

declare(strict_types=1);

namespace CA\Crt\Http\Requests;

use CA\Models\CertificateType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class IssueCertificateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'ca_id' => ['required', 'string', 'exists:certificate_authorities,id'],
            'type' => ['required', 'string', Rule::in(array_column(CertificateType::cases(), 'value'))],
            'subject' => ['required', 'array'],
            'subject.CN' => ['required', 'string', 'max:255'],
            'subject.O' => ['nullable', 'string', 'max:255'],
            'subject.OU' => ['nullable', 'string', 'max:255'],
            'subject.C' => ['nullable', 'string', 'size:2'],
            'subject.ST' => ['nullable', 'string', 'max:255'],
            'subject.L' => ['nullable', 'string', 'max:255'],
            'subject.emailAddress' => ['nullable', 'email', 'max:255'],
            'san' => ['nullable', 'array'],
            'san.*' => ['array'],
            'key_id' => ['required_without:csr_id', 'nullable', 'string', 'exists:ca_keys,id'],
            'csr_id' => ['required_without:key_id', 'nullable', 'string', 'exists:ca_csrs,id'],
            'template_id' => ['nullable', 'string', 'exists:ca_certificate_templates,id'],
            'validity_days' => ['nullable', 'integer', 'min:1', 'max:7300'],
            'key_usage' => ['nullable', 'array'],
            'key_usage.*' => ['string'],
            'extended_key_usage' => ['nullable', 'array'],
            'extended_key_usage.*' => ['string'],
            'hash_algorithm' => ['nullable', 'string', Rule::in(['sha256', 'sha384', 'sha512'])],
        ];
    }
}
