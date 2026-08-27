<?php

declare(strict_types=1);

namespace App\Http\Requests\Revit;

use Illuminate\Foundation\Http\FormRequest;

final class RevitHeartbeatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'revit_version' => ['nullable', 'string', 'max:40'],
            'client_version' => ['nullable', 'string', 'max:40'],
        ];
    }
}
