<?php

declare(strict_types=1);

namespace App\Core\Integrations;

use Illuminate\Http\Request;

final class SepayWebhookAuthenticator
{
    public function isValid(Request $request): bool
    {
        $token = config('services.sepay.webhook_token');

        return is_string($token)
            && $token !== ''
            && hash_equals('Apikey '.$token, (string) $request->header('Authorization'));
    }
}
