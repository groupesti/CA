<?php

declare(strict_types=1);

namespace CA\Oid\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterOidRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'oid' => [
                'required',
                'string',
                'regex:/^\d+(\.\d+)+$/',
                'unique:ca_oids,oid',
            ],
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'category' => [
                'nullable',
                'string',
                'max:50',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'oid.regex' => 'The OID must be a valid dot-separated numeric identifier (e.g. 1.2.3.4).',
            'oid.unique' => 'This OID is already registered.',
        ];
    }
}
