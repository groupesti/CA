<?php

declare(strict_types=1);

namespace CA\Crt\Http\Requests;

use CA\Models\RevocationReason;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class RevokeCertificateRequest extends FormRequest
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
            'reason' => [
                'required',
                'integer',
                Rule::in(array_column(RevocationReason::cases(), 'value')),
            ],
        ];
    }
}
