<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Auth\UserProvisioningService;
use App\Core\CommunityContext;
use App\Http\Requests\Commerce\RegisterWebhookRequest;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

final class RegisterWebhookController extends Controller
{
    public function __construct(
        private readonly CommunityContext $context,
        private readonly UserProvisioningService $provisioning,
    ) {}

    public function __invoke(RegisterWebhookRequest $request): JsonResponse
    {
        $secret = Setting::get('register_webhook_secret') ?: config('services.register_webhook.secret');
        $provided = $request->bearerToken() ?: (string) $request->input('secret', '');
        if (! $secret || ! hash_equals($secret, $provided)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validated();
        $existing = User::query()->where('email', $validated['email'])->first();
        if ($existing) {
            return response()->json([
                'status' => 'existing',
                'user_id' => $existing->id,
                'username' => $existing->username,
                'email' => $existing->email,
            ]);
        }

        $referrer = filled($validated['referral'] ?? null)
            ? User::query()->where('username', $validated['referral'])->first()
            : null;
        $user = $this->provisioning->provisionWebhookMember(
            $this->context->require(),
            $validated['name'],
            $validated['email'],
            $referrer,
            filled($validated['source'] ?? null) ? $validated['source'] : 'webhook',
        );

        Log::info('Webhook user registered.', ['user_id' => $user->id, 'username' => $user->username]);

        return response()->json([
            'status' => 'created',
            'user_id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
        ], 201);
    }
}
