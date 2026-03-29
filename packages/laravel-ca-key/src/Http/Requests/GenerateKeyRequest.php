<?php

declare(strict_types=1);

namespace CA\Key\Http\Requests;

use CA\Models\KeyAlgorithm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GenerateKeyRequest extends FormRequest
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
        $validAlgorithms = array_map(
            static fn (KeyAlgorithm $algo): string => $algo->slug,
            KeyAlgorithm::cases(),
        );

        return [
            'algorithm' => ['required', 'string', Rule::in($validAlgorithms)],
            'parameters' => ['sometimes', 'array'],
            'tenant_id' => ['sometimes', 'nullable', 'string', 'max:255'],
            'ca_id' => ['sometimes', 'nullable', 'exists:certificate_authorities,id'],
            'usage' => ['sometimes', 'nullable', 'string', 'max:50'],
        ];
    }
}
