<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Bot;

use App\Http\Responses\ApiResponse;
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
        throw new HttpResponseException(ApiResponse::error(
            'Tham số Bot API chưa hợp lệ.',
            422,
            $validator->errors()->toArray(),
        ));
    }
}
