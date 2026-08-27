<?php

declare(strict_types=1);

namespace App\Http\Requests\Commerce;

use App\Http\Responses\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

final class SepayWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'id' => ['nullable', 'string', 'max:191'],
            'referenceCode' => ['nullable', 'string', 'max:191'],
            'transferType' => ['nullable', 'string', 'max:20'],
            'content' => ['nullable', 'string', 'max:500'],
            'transferAmount' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(ApiResponse::error(
            'Dá»¯ liá»‡u webhook khÃ´ng há»£p lá»‡.',
            422,
            $validator->errors()->toArray(),
        ));
    }
}
