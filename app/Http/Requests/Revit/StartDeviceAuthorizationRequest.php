<?php

declare(strict_types=1);

namespace App\Http\Requests\Revit;

use Illuminate\Foundation\Http\FormRequest;

final class StartDeviceAuthorizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'installation_id' => ['required', 'string', 'min:24', 'max:255'],
            'device_fingerprint' => ['required', 'string', 'min:24', 'max:255'],
            'device_label' => ['nullable', 'string', 'max:120'],
            'revit_version' => ['nullable', 'string', 'max:40'],
            'client_version' => ['nullable', 'string', 'max:40'],
        ];
    }
}
