<?php

declare(strict_types=1);

namespace App\Http\Requests\Revit;

use Illuminate\Foundation\Http\FormRequest;

final class PollDeviceAuthorizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return ['authorization_code' => ['required', 'string', 'size:64']];
    }
}
