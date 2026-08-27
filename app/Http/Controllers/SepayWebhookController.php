<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Commerce\Application\SepayWebhookProcessor;

final class SepayWebhookController extends Controller
{
    public function __construct(private readonly SepayWebhookProcessor $processor) {}

    public function __invoke(Request $request): JsonResponse
    {
        $token = (string) config('services.sepay.webhook_token');
        $authorization = (string) $request->header('Authorization');

        if ($token === '' || ! hash_equals('Apikey '.$token, $authorization)) {
            Log::warning('SePay webhook rejected: invalid authorization.');

            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $this->processor->process($request->validate([
            'id' => ['nullable', 'string', 'max:191'],
            'referenceCode' => ['nullable', 'string', 'max:191'],
            'transferType' => ['nullable', 'string', 'max:20'],
            'content' => ['nullable', 'string', 'max:500'],
            'transferAmount' => ['nullable', 'numeric', 'min:0'],
        ]));

        return response()->json(['success' => true]);
    }
}
