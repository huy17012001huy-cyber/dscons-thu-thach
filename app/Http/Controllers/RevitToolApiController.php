<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Revit\PollDeviceAuthorizationRequest;
use App\Http\Requests\Revit\RevitHeartbeatRequest;
use App\Http\Requests\Revit\StartDeviceAuthorizationRequest;
use App\Models\ToolSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\RevitTools\Application\ToolLicenseService;

final class RevitToolApiController extends Controller
{
    public function __construct(private readonly ToolLicenseService $licenses) {}

    public function start(StartDeviceAuthorizationRequest $request): JsonResponse
    {
        $input = $request->validated();
        $brand = $this->licenses->revitBrand();
        $started = $this->licenses->startDeviceAuthorization($brand, $input);

        return response()->json([
            'authorization_code' => $started['code'],
            'verification_url' => route('revit.authorization.show', ['code' => $started['browserCode']]),
            'expires_at' => $started['authorization']->expires_at->toIso8601String(),
            'poll_after_seconds' => 4,
        ], 201);
    }

    public function poll(PollDeviceAuthorizationRequest $request): JsonResponse
    {
        $result = $this->licenses->pollDeviceAuthorization(
            $this->licenses->revitBrand(),
            $request->string('authorization_code')->toString(),
        );
        if ($result->status === 'expired') {
            return response()->json(['status' => 'expired', 'message' => 'Mã kích hoạt đã hết hạn.'], 410);
        }
        if ($result->status === 'pending') {
            return response()->json(['status' => 'pending']);
        }
        $approval = $result->approval();
        if ($approval === null) {
            return response()->json(['status' => 'denied', 'message' => 'Yêu cầu kích hoạt chưa được chấp thuận.'], 403);
        }

        $sessionCredential = $approval['credential'];
        if (! $sessionCredential) {
            return response()->json(['status' => 'expired', 'message' => 'Phiên kích hoạt đã hết hạn. Hãy tạo mã mới trong Revit.'], 410);
        }
        $session = $approval['session'];

        return response()->json(['status' => 'approved', 'access_token' => $sessionCredential, 'expires_at' => $session->expires_at->toIso8601String()]);
    }

    public function entitlements(Request $request): JsonResponse
    {
        $session = $this->authenticatedSession($request);
        if (! $session) {
            return $this->unauthorized();
        }

        return response()->json(['tools' => $this->licenses->getEntitlements($session->installation)]);
    }

    public function manifest(Request $request, string $toolKey): JsonResponse
    {
        $session = $this->authenticatedSession($request);
        if (! $session) {
            return $this->unauthorized();
        }
        $product = $this->licenses->findPublishedTool($session->installation, $toolKey);
        if ($product === null) {
            abort(404);
        }
        $allowed = collect($this->licenses->getEntitlements($session->installation))->contains('tool_key', $toolKey);
        abort_unless($allowed, 403);

        return response()->json([
            'tool_key' => $product->tool_key,
            'title' => $product->title,
            'manifest_version' => $product->tool_manifest_version,
            'supported_revit_versions' => $product->supported_revit_versions ?: [],
            // Test 1/2 live inside the launcher. A real paid package will only
            // return a time-limited signed delivery URL after package signing.
            'embedded' => blank($product->package_path),
            'package_url' => null,
        ]);
    }

    public function heartbeat(RevitHeartbeatRequest $request): JsonResponse
    {
        $session = $this->authenticatedSession($request);
        if (! $session) {
            return $this->unauthorized();
        }
        $input = $request->validated();
        $result = $this->licenses->heartbeat($session, $input);
        if (! $result) {
            return response()->json(['message' => 'License đã bị thu hồi hoặc tạm khóa.'], 403);
        }

        return response()->json([
            'status' => 'active',
            'entitlements' => $result['entitlements'],
            'expires_at' => $result['session']->expires_at->toIso8601String(),
            'replacement_token' => $result['replacement_token'],
            'next_check_after_hours' => 24,
            'offline_grace_hours' => 48,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $session = $this->authenticatedSession($request);
        if (! $session) {
            return $this->unauthorized();
        }
        $this->licenses->revokeInstallation($session->installation, 'user_logout');

        return response()->json(['status' => 'logged_out']);
    }

    private function authenticatedSession(Request $request): ?ToolSession
    {
        $token = $request->bearerToken();

        return $this->licenses->sessionFromToken($token);
    }

    private function unauthorized(): JsonResponse
    {
        return response()->json(['message' => 'Token Revit không hợp lệ hoặc đã hết hạn.'], 401);
    }
}
