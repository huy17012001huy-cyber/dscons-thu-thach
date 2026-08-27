<?php

declare(strict_types=1);

namespace App\Http\Requests\Bot;

final class BotMemberLookupRequest extends BotApiRequest
{
    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return ['q' => ['required', 'string', 'max:191']];
    }

    protected function validationMessage(): string
    {
        return 'Missing q parameter';
    }
}
