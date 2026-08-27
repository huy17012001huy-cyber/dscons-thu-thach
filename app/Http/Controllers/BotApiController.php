<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Integrations\BotApiAuthenticator;
use App\Http\Requests\Bot\BotChallengeProgressRequest;
use App\Http\Requests\Bot\BotMemberLookupRequest;
use App\Http\Requests\Bot\BotPendingSubmissionsRequest;
use App\Services\BotQueryService;
use Illuminate\Http\JsonResponse;

final class BotApiController extends Controller
{
    public function __construct(
        private readonly BotApiAuthenticator $authenticator,
        private readonly BotQueryService $bot,
    ) {}

    public function lookupMember(BotMemberLookupRequest $request): JsonResponse
    {
        if (! $this->authenticator->isValid($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = $this->bot->findUser($request->string('q')->toString());
        if (! $user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json($this->bot->member($user));
    }

    public function challengeProgress(BotChallengeProgressRequest $request): JsonResponse
    {
        if (! $this->authenticator->isValid($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = $this->bot->findUser($request->string('q')->toString());
        if (! $user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        $challenge = $this->bot->findChallenge($request->string('challenge')->toString());
        if (! $challenge) {
            return response()->json(['error' => 'Challenge not found'], 404);
        }

        return response()->json($this->bot->challengeProgress($user, $challenge));
    }

    public function pendingSubmissions(BotPendingSubmissionsRequest $request): JsonResponse
    {
        if (! $this->authenticator->isValid($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $challenge = $this->bot->findChallenge($request->string('challenge')->toString());
        if (! $challenge) {
            return response()->json(['error' => 'Challenge not found'], 404);
        }

        return response()->json($this->bot->pendingSubmissions($challenge));
    }
}
