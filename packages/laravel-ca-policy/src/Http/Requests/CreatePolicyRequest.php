<?php

declare(strict_types=1);

namespace CA\Policy\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePolicyRequest extends FormRequest
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
            'ca_id' => ['required', 'uuid', 'exists:certificate_authorities,id'],
            'name' => ['required', 'string', 'max:255'],
            'policy_oid' => ['required', 'string', 'max:255'],
            'cps_uri' => ['nullable', 'string', 'url', 'max:2048'],
            'description' => ['nullable', 'string', 'max:65535'],
            'is_default' => ['boolean'],
            'enabled' => ['boolean'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
