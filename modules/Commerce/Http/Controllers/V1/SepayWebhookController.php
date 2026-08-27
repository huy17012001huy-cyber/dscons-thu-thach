<?php

declare(strict_types=1);

namespace Modules\Commerce\Http\Controllers\V1;

use App\Core\Integrations\SepayWebhookAuthenticator;
use App\Http\Requests\Commerce\SepayWebhookRequest;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Modules\Commerce\Application\SepayWebhookProcessor;

final class SepayWebhookController
{
    public function __construct(
        private readonly SepayWebhookAuthenticator $authenticator,
        private readonly SepayWebhookProcessor $processor,
    ) {}

    public function __invoke(SepayWebhookRequest $request): JsonResponse
    {
        if (! $this->authenticator->isValid($request)) {
            Log::warning('SePay webhook v1 rejected: invalid authorization.');

            return ApiResponse::error('Không được xác thực.', 401);
        }

        $this->processor->process($request->validated());

        return ApiResponse::success(['accepted' => true], 'Webhook đã được tiếp nhận.');
    }
}
