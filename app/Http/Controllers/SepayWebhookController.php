<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Commerce\SepayWebhookRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Modules\Commerce\Application\SepayWebhookProcessor;

final class SepayWebhookController extends Controller
{
    public function __construct(private readonly SepayWebhookProcessor $processor) {}

    public function __invoke(SepayWebhookRequest $request): JsonResponse
    {
        $token = (string) config('services.sepay.webhook_token');
        $authorization = (string) $request->header('Authorization');

        if ($token === '' || ! hash_equals('Apikey '.$token, $authorization)) {
            Log::warning('SePay webhook rejected: invalid authorization.');

            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $this->processor->process($request->validated());

        return response()->json(['success' => true]);
    }
}
