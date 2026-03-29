<?php

declare(strict_types=1);

namespace CA\Csr\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportCsrRequest extends FormRequest
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
            'csr_pem' => ['required', 'string', 'starts_with:-----BEGIN CERTIFICATE REQUEST-----'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'csr_pem.starts_with' => 'The CSR must be a valid PEM-encoded certificate request.',
        ];
    }
}
