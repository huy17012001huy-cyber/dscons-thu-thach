<?php

declare(strict_types=1);

namespace Modules\Learning\Application;

use App\Core\CommunityContext;
use App\Models\ChallengeTaskCompletion;
use App\Models\Expedition;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class SubmissionVoteService
{
    private const TYPES = ['good', 'excellent'];

    public function __construct(private readonly CommunityContext $context) {}

    public function toggle(Expedition $challenge, int $completionId, User $voter, string $type): bool
    {
        $this->assertCurrentCommunity($challenge);
        if (! $voter->isCommunityAdmin($challenge->brand_id) || ! in_array($type, self::TYPES, true)) {
            return false;
        }

        return DB::transaction(function () use ($challenge, $completionId, $voter, $type): bool {
            $completionExists = ChallengeTaskCompletion::query()
                ->whereKey($completionId)
                ->whereHas('task', function (Builder $query) use ($challenge): void {
                    $query->where('expedition_id', $challenge->id);
                })
                ->lockForUpdate()
                ->exists();
            if (! $completionExists) {
                return false;
            }

            $vote = DB::table('submission_votes')
                ->where('completion_id', $completionId)
                ->where('user_id', $voter->id)
                ->where('vote_type', $type)
                ->lockForUpdate();
            if ($vote->exists()) {
                $vote->delete();
            } else {
                DB::table('submission_votes')->insert([
                    'completion_id' => $completionId,
                    'user_id' => $voter->id,
                    'vote_type' => $type,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return true;
        });
    }

    private function assertCurrentCommunity(Expedition $challenge): void
    {
        $brand = $this->context->current();

        if ($brand && $challenge->brand_id !== $brand->id) {
            throw new AuthorizationException('Challenge does not belong to the current community.');
        }
    }
}
