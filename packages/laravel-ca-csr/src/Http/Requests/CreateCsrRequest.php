<?php

declare(strict_types=1);

namespace CA\Csr\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateCsrRequest extends FormRequest
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
            'key_id' => ['required', 'integer', 'exists:ca_keys,id'],
            'subject' => ['required', 'array'],
            'subject.CN' => ['required', 'string', 'max:255'],
            'subject.O' => ['nullable', 'string', 'max:255'],
            'subject.OU' => ['nullable', 'string', 'max:255'],
            'subject.C' => ['nullable', 'string', 'size:2'],
            'subject.ST' => ['nullable', 'string', 'max:255'],
            'subject.L' => ['nullable', 'string', 'max:255'],
            'subject.emailAddress' => ['nullable', 'email', 'max:255'],
            'san' => ['nullable', 'array'],
            'san.*.type' => ['required_with:san', 'string', 'in:dns,ip,email,uri'],
            'san.*.value' => ['required_with:san', 'string', 'max:255'],
            'template_id' => ['nullable', 'string', 'exists:ca_certificate_templates,id'],
        ];
    }
}
