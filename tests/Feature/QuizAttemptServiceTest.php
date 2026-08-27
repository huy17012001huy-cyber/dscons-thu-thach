<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ChallengeTask;
use App\Models\Expedition;
use App\Models\ExpeditionMember;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Learning\Application\QuizAttemptService;
use Tests\TestCase;

final class QuizAttemptServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_quiz_answer_is_recorded_once_and_awards_xp_once(): void
    {
        [$challenge, $task, $member] = $this->challengeWithQuiz();
        $service = app(QuizAttemptService::class);

        $first = $service->answer($task, $member, 0, 'B');
        $replay = $service->answer($task, $member, 0, 'A');

        self::assertTrue($first->isCorrect);
        self::assertTrue($first->wasRecorded);
        self::assertTrue($first->xpAwarded);
        self::assertSame('B', $replay->selectedLetter);
        self::assertFalse($replay->wasRecorded);
        $this->assertDatabaseCount('quiz_attempts', 1);
        $this->assertDatabaseCount('xp_transactions', 1);
        $this->assertDatabaseHas('quiz_attempts', ['challenge_task_id' => $task->id, 'user_id' => $member->id, 'selected_letter' => 'B', 'xp_awarded' => true]);
    }

    public function test_quiz_cannot_be_answered_before_the_task_is_available(): void
    {
        [$challenge, $task, $member] = $this->challengeWithQuiz();
        ExpeditionMember::query()->where('expedition_id', $challenge->id)->where('user_id', $member->id)->update(['personal_starts_at' => null]);

        $this->expectException(AuthorizationException::class);
        app(QuizAttemptService::class)->answer($task, $member, 0, 'B');
    }

    /** @return array{0: Expedition, 1: ChallengeTask, 2: User} */
    private function challengeWithQuiz(): array
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $member->brandRoles()->attach(brand()->id, ['role' => 'member']);
        $challenge = Expedition::create([
            'title' => 'Quiz Challenge',
            'slug' => 'quiz-challenge',
            'description' => 'Quiz test challenge.',
            'difficulty' => 'normal',
            'required_days' => 2,
            'max_members' => 50,
            'created_by' => $owner->id,
            'leader_id' => $owner->id,
            'status' => 'active',
        ]);
        $task = ChallengeTask::create([
            'expedition_id' => $challenge->id,
            'day_number' => 1,
            'title' => 'Quiz task',
            'quiz_json' => [[
                'q' => 'Which answer is correct?',
                'options' => ['A' => 'Wrong', 'B' => 'Correct'],
                'correct' => 'B',
            ]],
        ]);
        ExpeditionMember::create([
            'brand_id' => $challenge->brand_id,
            'expedition_id' => $challenge->id,
            'user_id' => $member->id,
            'status' => 'approved',
            'joined_at' => now()->subMinute(),
            'personal_starts_at' => now()->subMinute(),
        ]);

        return [$challenge, $task, $member];
    }
}
