<?php

declare(strict_types=1);

namespace Modules\Community\Application;

use App\Core\CommunityContext;
use App\Core\Gamification\XpService;
use App\Models\Answer;
use App\Models\Brand;
use App\Models\Question;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

final class QaService
{
    public function __construct(
        private readonly CommunityContext $context,
        private readonly XpService $xp,
    ) {}

    /** @param array{title:string,body:?string,pillar:?string,isAnonymous:bool} $data */
    public function submitQuestion(User $user, array $data): Question
    {
        $brand = $this->participantBrand($user);

        return DB::transaction(function () use ($user, $data, $brand): Question {
            $question = Question::create([
                'brand_id' => $brand->id,
                'user_id' => $user->id,
                'title' => trim($data['title']),
                'body' => blank($data['body']) ? null : trim((string) $data['body']),
                'pillar' => $data['pillar'],
                'is_anonymous' => $data['isAnonymous'],
            ]);
            DB::afterCommit(fn () => $this->xp->award($user, 'post', 0.33, 'Đặt câu hỏi', $question));

            return $question;
        });
    }

    public function submitAnswer(int $questionId, User $user, string $body): QaAnswerOutcome
    {
        $brand = $this->participantBrand($user);

        return DB::transaction(function () use ($questionId, $user, $body, $brand): QaAnswerOutcome {
            $question = Question::query()->where('brand_id', $brand->id)->whereKey($questionId)->lockForUpdate()->firstOrFail();
            if (Answer::query()->where('brand_id', $brand->id)->where('question_id', $question->id)->where('user_id', $user->id)->exists()) {
                return QaAnswerOutcome::AlreadyAnswered;
            }
            if (Answer::query()->where('brand_id', $brand->id)->where('user_id', $user->id)->where('created_at', '>=', now()->subHour())->count() >= 10) {
                return QaAnswerOutcome::RateLimited;
            }

            Answer::create([
                'brand_id' => $brand->id,
                'question_id' => $question->id,
                'user_id' => $user->id,
                'body' => trim($body),
            ]);
            if ($question->status === 'open') {
                $question->update(['status' => 'answered']);
            }
            DB::afterCommit(fn () => $this->xp->award($user, 'comment', 1.0, 'Trả lời câu hỏi', $question));

            return QaAnswerOutcome::Answered;
        });
    }

    public function updateAnswer(int $answerId, User $actor, string $body): void
    {
        DB::transaction(function () use ($answerId, $actor, $body): void {
            $answer = $this->managedAnswer($answerId, $actor);
            $answer->update(['body' => trim($body)]);
        });
    }

    public function deleteAnswer(int $answerId, User $actor): void
    {
        DB::transaction(function () use ($answerId, $actor): void {
            $answer = $this->managedAnswer($answerId, $actor);
            $question = $answer->question()->lockForUpdate()->firstOrFail();
            $answer->delete();
            if (! Answer::query()->where('brand_id', $question->brand_id)->where('question_id', $question->id)->exists()) {
                $question->update(['status' => 'open']);
            }
        });
    }

    public function updateQuestion(int $questionId, User $actor, string $title, ?string $body): void
    {
        DB::transaction(function () use ($questionId, $actor, $title, $body): void {
            $question = $this->moderatedQuestion($questionId, $actor);
            $question->update(['title' => trim($title), 'body' => blank($body) ? null : trim((string) $body)]);
        });
    }

    public function deleteQuestion(int $questionId, User $actor): void
    {
        DB::transaction(function () use ($questionId, $actor): void {
            $this->moderatedQuestion($questionId, $actor)->delete();
        });
    }

    private function participantBrand(User $user): Brand
    {
        $brand = $this->context->require();
        if (! $user->isCommunityParticipant($brand->id)) {
            throw new AuthorizationException('Community participation is required.');
        }

        return $brand;
    }

    private function managedAnswer(int $answerId, User $actor): Answer
    {
        $brand = $this->context->require();
        $answer = Answer::query()->where('brand_id', $brand->id)->whereKey($answerId)->lockForUpdate()->firstOrFail();
        if ($answer->user_id !== $actor->id && ! $actor->isCommunityModerator($brand->id)) {
            throw new AuthorizationException('Answer management is not allowed.');
        }

        return $answer;
    }

    private function moderatedQuestion(int $questionId, User $actor): Question
    {
        $brand = $this->context->require();
        if (! $actor->isCommunityModerator($brand->id)) {
            throw new AuthorizationException('Community moderation is required.');
        }

        return Question::query()->where('brand_id', $brand->id)->whereKey($questionId)->lockForUpdate()->firstOrFail();
    }
}
