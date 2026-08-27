<?php

declare(strict_types=1);

namespace Modules\RevitTools\Http\Controllers\V1;

use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\RevitTools\Application\RevitApiSessionResolver;
use Modules\RevitTools\Application\ToolLicenseService;
use Modules\RevitTools\Http\Resources\ToolEntitlementResource;

final class RevitEntitlementController
{
    public function __invoke(
        Request $request,
        ToolLicenseService $licenses,
        RevitApiSessionResolver $sessions,
    ): JsonResponse {
        $session = $sessions->fromBearerToken($request->bearerToken());

        if (! $session) {
            return ApiResponse::error('Token Revit không hợp lệ hoặc đã hết hạn.', 401);
        }

        return ApiResponse::success([
            'tools' => ToolEntitlementResource::collection($licenses->getEntitlements($session->installation))->resolve($request),
        ], 'Danh sách tool đã được tải.');
    }
}
