<?php

declare(strict_types=1);

namespace Modules\RevitTools\Http\Controllers\V1;

use App\Http\Requests\Revit\RevitHeartbeatRequest;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\RevitTools\Application\RevitApiSessionResolver;
use Modules\RevitTools\Application\ToolLicenseService;

final class RevitRuntimeController
{
    public function __construct(
        private readonly ToolLicenseService $licenses,
        private readonly RevitApiSessionResolver $sessions,
    ) {}

    public function heartbeat(RevitHeartbeatRequest $request): JsonResponse
    {
        $session = $this->sessions->fromBearerToken($request->bearerToken());
        if (! $session) {
            return ApiResponse::error('Token Revit không hợp lệ hoặc đã hết hạn.', 401);
        }

        $result = $this->licenses->heartbeat($session, $request->validated());
        if (! $result) {
            return ApiResponse::error('License đã bị thu hồi hoặc tạm khóa.', 403);
        }

        return ApiResponse::success([
            'status' => 'active',
            'entitlements' => $result['entitlements'],
            'expires_at' => $result['session']->expires_at->toIso8601String(),
            'replacement_token' => $result['replacement_token'],
            'next_check_after_hours' => 24,
            'offline_grace_hours' => 48,
        ], 'License Revit đang hoạt động.');
    }

    public function logout(Request $request): JsonResponse
    {
        $session = $this->sessions->fromBearerToken($request->bearerToken());
        if (! $session) {
            return ApiResponse::error('Token Revit không hợp lệ hoặc đã hết hạn.', 401);
        }

        $this->licenses->revokeInstallation($session->installation, 'user_logout');

        return ApiResponse::success(['status' => 'logged_out'], 'Thiết bị Revit đã được đăng xuất.');
    }
}
