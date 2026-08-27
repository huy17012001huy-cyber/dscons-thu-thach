<?php

declare(strict_types=1);

namespace App\Http\Requests\Bot;

final class BotPendingSubmissionsRequest extends BotApiRequest
{
    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return ['challenge' => ['nullable', 'string', 'max:191']];
    }
}
