<?php

declare(strict_types=1);

namespace CA\Http\Requests;

use CA\Models\KeyAlgorithm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateCaRequest extends FormRequest
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
        $keyAlgorithms = array_column(KeyAlgorithm::cases(), 'value');

        return [
            'parent_id' => ['sometimes', 'nullable', 'uuid', 'exists:certificate_authorities,id'],
            'tenant_id' => ['sometimes', 'nullable', 'string', 'max:255'],
            'subject_dn' => ['required', 'array'],
            'subject_dn.CN' => ['required', 'string', 'max:255'],
            'subject_dn.O' => ['sometimes', 'nullable', 'string', 'max:255'],
            'subject_dn.OU' => ['sometimes', 'nullable', 'string', 'max:255'],
            'subject_dn.C' => ['sometimes', 'nullable', 'string', 'size:2'],
            'subject_dn.ST' => ['sometimes', 'nullable', 'string', 'max:255'],
            'subject_dn.L' => ['sometimes', 'nullable', 'string', 'max:255'],
            'subject_dn.emailAddress' => ['sometimes', 'nullable', 'email', 'max:255'],
            'key_algorithm' => ['required', 'string', Rule::in($keyAlgorithms)],
            'validity_days' => ['sometimes', 'integer', 'min:1', 'max:36500'],
            'path_length' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:10'],
            'key_params' => ['sometimes', 'array'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'subject_dn.required' => 'A subject distinguished name is required.',
            'subject_dn.CN.required' => 'The Common Name (CN) is required.',
            'subject_dn.C.size' => 'The Country (C) must be a 2-letter ISO code.',
            'key_algorithm.required' => 'A key algorithm must be specified.',
            'key_algorithm.in' => 'The key algorithm is not supported.',
        ];
    }
}
