<?php

declare(strict_types=1);

namespace App\Http\Requests\Bot;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class BotApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'error' => $this->validationMessage(),
        ], 400));
    }

    protected function validationMessage(): string
    {
        return 'Invalid request parameters';
    }
}
