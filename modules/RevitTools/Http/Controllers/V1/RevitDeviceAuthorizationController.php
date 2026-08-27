<?php

declare(strict_types=1);

namespace Modules\RevitTools\Http\Controllers\V1;

use App\Http\Requests\Revit\PollDeviceAuthorizationRequest;
use App\Http\Requests\Revit\StartDeviceAuthorizationRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Brand;
use App\Models\ToolDeviceAuthorization;
use App\Models\ToolSession;
use App\Services\ToolLicenseService;
use Illuminate\Http\JsonResponse;

final class RevitDeviceAuthorizationController
{
    public function __construct(private readonly ToolLicenseService $licenses) {}

    public function start(StartDeviceAuthorizationRequest $request): JsonResponse
    {
        $started = $this->licenses->startDeviceAuthorization($this->dscons(), $request->validated());

        return ApiResponse::success([
            'authorization_code' => $started['code'],
            'verification_url' => route('revit.authorization.show', ['code' => $started['browserCode']]),
            'expires_at' => $started['authorization']->expires_at->toIso8601String(),
            'poll_after_seconds' => 4,
        ], 'Mã kích hoạt Revit đã được tạo.', 201);
    }

    public function poll(PollDeviceAuthorizationRequest $request): JsonResponse
    {
        $authorization = $this->authorization($request->string('authorization_code')->toString());
        if (! $authorization || $authorization->expires_at->isPast()) {
            return ApiResponse::error('Mã kích hoạt đã hết hạn.', 410, ['authorization' => ['expired']]);
        }
        if ($authorization->status === 'pending') {
            return ApiResponse::success(['status' => 'pending'], 'Đang chờ người dùng xác nhận.');
        }
        if ($authorization->status !== 'approved' || ! $authorization->tool_session_id || $authorization->consumed_at) {
            return ApiResponse::error('Yêu cầu kích hoạt chưa được chấp thuận.', 403, ['authorization' => ['denied']]);
        }

        $credential = cache()->pull('revit:authorization-credential:'.$authorization->id);
        if (! is_string($credential) || $credential === '') {
            return ApiResponse::error('Phiên kích hoạt đã hết hạn. Hãy tạo mã mới trong Revit.', 410, ['authorization' => ['expired']]);
        }

        $authorization->update(['consumed_at' => now()]);
        $session = $authorization->session;
        if (! $session instanceof ToolSession) {
            return ApiResponse::error('Không thể hoàn tất phiên kích hoạt.', 403, ['authorization' => ['denied']]);
        }

        return ApiResponse::success([
            'status' => 'approved',
            'access_token' => $credential,
            'expires_at' => $session->expires_at->toIso8601String(),
        ], 'Revit đã được kết nối.');
    }

    private function authorization(string $code): ?ToolDeviceAuthorization
    {
        return ToolDeviceAuthorization::withoutGlobalScopes()
            ->where('brand_id', $this->dscons()->id)
            ->where('code_hash', hash('sha256', $code))
            ->first();
    }

    private function dscons(): Brand
    {
        return Brand::query()->where('slug', 'dscons')->firstOrFail();
    }
}
