<?php

declare(strict_types=1);

namespace App\Http\Requests\Commerce;

use App\Http\Responses\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

final class RegisterWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:50'],
            'email' => ['required', 'email'],
            'referral' => ['nullable', 'string', 'exists:users,username'],
            'source' => ['nullable', 'string', 'max:80'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(ApiResponse::error(
            'Dá»¯ liá»‡u Ä‘Äƒng kÃ½ khÃ´ng há»£p lá»‡.',
            422,
            $validator->errors()->toArray(),
        ));
    }
}
