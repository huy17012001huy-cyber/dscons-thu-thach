<?php

declare(strict_types=1);

namespace Modules\RevitTools\Http\Controllers\V1;

use App\Http\Requests\Revit\PollDeviceAuthorizationRequest;
use App\Http\Requests\Revit\StartDeviceAuthorizationRequest;
use App\Http\Responses\ApiResponse;
use App\Models\ToolSession;
use Illuminate\Http\JsonResponse;
use Modules\RevitTools\Application\ToolLicenseService;

final class RevitDeviceAuthorizationController
{
    public function __construct(private readonly ToolLicenseService $licenses) {}

    public function start(StartDeviceAuthorizationRequest $request): JsonResponse
    {
        $started = $this->licenses->startDeviceAuthorization($this->licenses->revitBrand(), $request->validated());

        return ApiResponse::success([
            'authorization_code' => $started['code'],
            'verification_url' => route('revit.authorization.show', ['code' => $started['browserCode']]),
            'expires_at' => $started['authorization']->expires_at->toIso8601String(),
            'poll_after_seconds' => 4,
        ], 'Mã kích hoạt Revit đã được tạo.', 201);
    }

    public function poll(PollDeviceAuthorizationRequest $request): JsonResponse
    {
        $result = $this->licenses->pollDeviceAuthorization(
            $this->licenses->revitBrand(),
            $request->string('authorization_code')->toString(),
        );
        if ($result->status === 'expired') {
            return ApiResponse::error('Mã kích hoạt đã hết hạn.', 410, ['authorization' => ['expired']]);
        }
        if ($result->status === 'pending') {
            return ApiResponse::success(['status' => 'pending'], 'Đang chờ người dùng xác nhận.');
        }
        $approval = $result->approval();
        if ($approval === null) {
            return ApiResponse::error('Yêu cầu kích hoạt chưa được chấp thuận.', 403, ['authorization' => ['denied']]);
        }

        /** @var mixed $credential */
        $credential = $approval['credential'];
        if (! is_string($credential) || $credential === '') {
            return ApiResponse::error('Phiên kích hoạt đã hết hạn. Hãy tạo mã mới trong Revit.', 410, ['authorization' => ['expired']]);
        }

        /** @var mixed $session */
        $session = $approval['session'];
        if (! $session instanceof ToolSession) {
            return ApiResponse::error('Không thể hoàn tất phiên kích hoạt.', 403, ['authorization' => ['denied']]);
        }

        return ApiResponse::success([
            'status' => 'approved',
            'access_token' => $credential,
            'expires_at' => $session->expires_at->toIso8601String(),
        ], 'Revit đã được kết nối.');
    }
}
