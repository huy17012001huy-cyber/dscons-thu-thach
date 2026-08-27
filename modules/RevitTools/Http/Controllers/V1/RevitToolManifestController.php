<?php

declare(strict_types=1);

namespace Modules\RevitTools\Http\Controllers\V1;

use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\RevitTools\Application\RevitApiSessionResolver;
use Modules\RevitTools\Application\ToolLicenseService;
use Modules\RevitTools\Http\Resources\ToolManifestResource;

final class RevitToolManifestController
{
    public function __construct(
        private readonly ToolLicenseService $licenses,
        private readonly RevitApiSessionResolver $sessions,
    ) {}

    public function __invoke(Request $request, string $toolKey): JsonResponse
    {
        $session = $this->sessions->fromBearerToken($request->bearerToken());
        if (! $session) {
            return ApiResponse::error('Token Revit không hợp lệ hoặc đã hết hạn.', 401);
        }

        $product = $this->licenses->findPublishedTool($session->installation, $toolKey);
        if (! $product) {
            return ApiResponse::error('Không tìm thấy tool Revit.', 404);
        }
        $isEntitled = collect($this->licenses->getEntitlements($session->installation))
            ->contains('tool_key', $toolKey);
        if (! $isEntitled) {
            return ApiResponse::error('Tài khoản chưa có quyền dùng tool này.', 403);
        }

        return ApiResponse::success((new ToolManifestResource($product))->resolve($request), 'Manifest tool đã được tải.');
    }
}
