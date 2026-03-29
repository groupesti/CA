<?php

declare(strict_types=1);

namespace CA\Policy\Http\Requests;

use CA\Models\PolicyAction;
use CA\Models\PolicySeverity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateIssuanceRuleRequest extends FormRequest
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
            'policy_id' => ['nullable', 'uuid', 'exists:ca_certificate_policies,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:65535'],
            'rule_class' => ['required', 'string', 'max:500'],
            'parameters' => ['nullable', 'array'],
            'priority' => ['integer', 'min:0'],
            'severity' => ['required', 'string', Rule::in([PolicySeverity::ERROR, PolicySeverity::WARNING, PolicySeverity::INFO])],
            'action_on_failure' => ['required', 'string', Rule::in([PolicyAction::DENY, PolicyAction::ALLOW, PolicyAction::WARN, PolicyAction::REQUIRE_APPROVAL])],
            'enabled' => ['boolean'],
            'applies_to_types' => ['nullable', 'array'],
            'applies_to_types.*' => ['string'],
        ];
    }
}
