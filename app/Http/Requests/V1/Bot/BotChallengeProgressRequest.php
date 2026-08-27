<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Bot;

final class BotChallengeProgressRequest extends BotApiRequest
{
    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'q' => ['required', 'string', 'max:191'],
            'challenge' => ['nullable', 'string', 'max:191'],
        ];
    }
}
