<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1;

use App\Core\Integrations\BotApiAuthenticator;
use App\Core\Integrations\BotQueryService;
use App\Http\Requests\V1\Bot\BotChallengeProgressRequest;
use App\Http\Requests\V1\Bot\BotMemberLookupRequest;
use App\Http\Requests\V1\Bot\BotPendingSubmissionsRequest;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

final class BotApiController
{
    public function __construct(
        private readonly BotApiAuthenticator $authenticator,
        private readonly BotQueryService $bot,
    ) {}

    public function member(BotMemberLookupRequest $request): JsonResponse
    {
        if (! $this->authenticator->isValid($request)) {
            return ApiResponse::error('Bot token không hợp lệ.', 401);
        }

        $user = $this->bot->findUser($request->string('q')->toString());
        if (! $user) {
            return ApiResponse::error('Không tìm thấy thành viên.', 404);
        }

        return ApiResponse::success($this->bot->member($user), 'Thông tin thành viên.');
    }

    public function challengeProgress(BotChallengeProgressRequest $request): JsonResponse
    {
        if (! $this->authenticator->isValid($request)) {
            return ApiResponse::error('Bot token không hợp lệ.', 401);
        }

        $user = $this->bot->findUser($request->string('q')->toString());
        $challenge = $this->bot->findChallenge($request->string('challenge')->toString());
        if (! $user || ! $challenge) {
            return ApiResponse::error('Không tìm thấy thành viên hoặc challenge.', 404);
        }

        return ApiResponse::success($this->bot->challengeProgress($user, $challenge), 'Tiến độ challenge.');
    }

    public function pendingSubmissions(BotPendingSubmissionsRequest $request): JsonResponse
    {
        if (! $this->authenticator->isValid($request)) {
            return ApiResponse::error('Bot token không hợp lệ.', 401);
        }

        $challenge = $this->bot->findChallenge($request->string('challenge')->toString());
        if (! $challenge) {
            return ApiResponse::error('Không tìm thấy challenge.', 404);
        }

        return ApiResponse::success($this->bot->pendingSubmissions($challenge), 'Bài nộp đang chờ duyệt.');
    }
}
