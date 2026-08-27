<?php

declare(strict_types=1);

namespace App\Core\Integrations;

use Illuminate\Http\Request;

final class BotApiAuthenticator
{
    public function isValid(Request $request): bool
    {
        $token = config('services.bot.api_token');

        return is_string($token)
            && $token !== ''
            && hash_equals('Bearer '.$token, (string) $request->header('Authorization'));
    }
}
