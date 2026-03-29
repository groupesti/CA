<?php

declare(strict_types=1);

namespace CA\Policy\Http\Requests;

use CA\Models\NameType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateNameConstraintRequest extends FormRequest
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
            'policy_id' => ['required', 'uuid', 'exists:ca_certificate_policies,id'],
            'ca_id' => ['required', 'uuid', 'exists:certificate_authorities,id'],
            'type' => ['required', 'string', Rule::in(['permitted', 'excluded'])],
            'name_type' => ['required', 'string', Rule::in([NameType::DNS, NameType::EMAIL, NameType::IP, NameType::URI, NameType::DIRECTORY])],
            'value' => ['required', 'string', 'max:255'],
            'min_subtree' => ['integer', 'min:0'],
            'max_subtree' => ['nullable', 'integer', 'min:0'],
            'enabled' => ['boolean'],
        ];
    }
}
