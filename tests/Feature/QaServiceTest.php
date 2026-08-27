<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\Brand;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Community\Application\QaAnswerOutcome;
use Modules\Community\Application\QaService;
use Tests\TestCase;

final class QaServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_question_and_answer_are_scoped_to_the_current_community(): void
    {
        $author = $this->member();
        $answerer = $this->member();
        $service = app(QaService::class);

        $question = $service->submitQuestion($author, [
            'title' => 'How do I review a Revit model?',
            'body' => 'I need a dependable model-review workflow.',
            'pillar' => 'delivery',
            'isAnonymous' => false,
        ]);

        self::assertSame(brand()->id, $question->brand_id);
        self::assertSame(QaAnswerOutcome::Answered, $service->submitAnswer($question->id, $answerer, 'Start with coordination views and clash checks.'));
        self::assertSame(QaAnswerOutcome::AlreadyAnswered, $service->submitAnswer($question->id, $answerer, 'A duplicate answer.'));
        self::assertSame('answered', $question->fresh()->status);
        $this->assertDatabaseHas('answers', ['brand_id' => brand()->id, 'question_id' => $question->id, 'user_id' => $answerer->id]);
    }

    public function test_answers_and_questions_cannot_be_managed_across_communities(): void
    {
        $member = $this->member();
        $other = Brand::create([
            'name' => 'Other QA Community',
            'slug' => 'other-qa-community',
            'domain' => 'other-qa-community.test',
            'theme_primary' => '#1F77BE',
            'theme_accent' => '#DCECF7',
            'theme_bg' => '#F8FAFC',
            'registration_mode' => 'invite',
            'is_invite_only' => true,
        ]);
        $question = Question::withoutGlobalScopes()->create([
            'brand_id' => $other->id,
            'user_id' => $member->id,
            'title' => 'Other question',
        ]);

        $this->expectException(ModelNotFoundException::class);
        app(QaService::class)->submitAnswer($question->id, $member, 'This must not cross communities.');
    }

    public function test_community_moderator_can_manage_qa_content_and_reopen_after_deleting_last_answer(): void
    {
        $author = $this->member();
        $moderator = $this->member('moderator');
        $question = Question::create(['user_id' => $author->id, 'title' => 'Question to moderate', 'status' => 'answered']);
        $answer = Answer::create(['question_id' => $question->id, 'user_id' => $author->id, 'body' => 'Answer']);
        $service = app(QaService::class);

        $service->updateQuestion($question->id, $moderator, 'Updated question title', 'Updated body');
        $service->deleteAnswer($answer->id, $moderator);

        self::assertSame('Updated question title', $question->fresh()->title);
        self::assertSame('open', $question->fresh()->status);
        $this->assertDatabaseMissing('answers', ['id' => $answer->id]);
    }

    private function member(string $role = 'member'): User
    {
        $user = User::factory()->create();
        $user->brandRoles()->attach(brand()->id, ['role' => $role]);

        return $user;
    }
}
