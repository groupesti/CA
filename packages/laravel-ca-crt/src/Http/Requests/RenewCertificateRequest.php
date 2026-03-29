<?php

declare(strict_types=1);

namespace CA\Crt\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class RenewCertificateRequest extends FormRequest
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
            'validity_days' => ['nullable', 'integer', 'min:1', 'max:7300'],
        ];
    }
}
