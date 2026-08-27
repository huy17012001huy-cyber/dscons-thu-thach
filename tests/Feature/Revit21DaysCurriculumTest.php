<?php

namespace Tests\Feature;

use App\Livewire\ChallengeDetail;
use App\Livewire\ChallengeLessonPage;
use App\Models\ChallengeTask;
use App\Models\Expedition;
use App\Models\User;
use Database\Seeders\Revit21DaysSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class Revit21DaysCurriculumTest extends TestCase
{
    use RefreshDatabase;

    public function test_revit_curriculum_seeds_twenty_one_structured_tasks_idempotently(): void
    {
        app(Revit21DaysSeeder::class)->run();
        app(Revit21DaysSeeder::class)->run();

        $challenge = Expedition::query()->where('slug', '21-ngay-lam-tool-revit-voi-ai-agent')->firstOrFail();
        $tasks = ChallengeTask::query()->where('expedition_id', $challenge->id)->orderBy('day_number')->get();

        $this->assertSame('21 Ngày Chinh Phục Tool Revit bằng AI Agent', $challenge->title);
        $this->assertCount(21, $tasks);
        $this->assertSame(21, $tasks->whereNotNull('instruction_payload')->count());
        $this->assertSame([1, 8, 15, 21], $tasks
            ->filter(fn ($task) => ($task->instruction_payload['modality'] ?? null) === 'live')
            ->pluck('day_number')->values()->all());
        $this->assertSame(21, $tasks->pluck('day_number')->unique()->count());
    }

    public function test_structured_payload_has_learning_workflow_and_rubric_keys(): void
    {
        app(Revit21DaysSeeder::class)->run();

        $payload = ChallengeTask::query()
            ->where('day_number', 18)
            ->whereHas('expedition', fn ($query) => $query->where('slug', '21-ngay-lam-tool-revit-voi-ai-agent'))
            ->firstOrFail()
            ->instruction_payload;

        foreach (['modality', 'learning_objectives', 'ai_actions', 'student_actions', 'sop_steps', 'verification_checklist', 'homework', 'evidence_requirements', 'rubric', 'common_errors', 'review_mode', 'pass_score', 'track', 'ai_prompt', 'safety_constraints'] as $key) {
            $this->assertArrayHasKey($key, $payload, "Missing curriculum key: {$key}");
        }

        $this->assertSame(70, $payload['pass_score']);
        $this->assertNotEmpty($payload['ai_prompt']);
        $this->assertNotEmpty($payload['safety_constraints']);
    }

    public function test_legacy_task_without_payload_remains_valid(): void
    {
        app(Revit21DaysSeeder::class)->run();

        $task = ChallengeTask::query()->where('day_number', 1)->firstOrFail();
        $task->update(['instruction_payload' => null]);

        $this->assertNull($task->fresh()->instruction_payload);
        $this->assertNotEmpty($task->fresh()->sop_content);
        $this->assertSame('text', $task->fresh()->evidence_type);
    }

    public function test_admin_can_render_structured_task_content(): void
    {
        app(Revit21DaysSeeder::class)->run();
        $admin = User::query()->where('is_admin', true)->firstOrFail();

        $component = Livewire::actingAs($admin)
            ->test(ChallengeDetail::class, ['slug' => '21-ngay-lam-tool-revit-voi-ai-agent'])
            ->assertSee('Prompt copy vào AI Agent')
            ->assertSee('Hướng dẫn thực hiện')
            ->assertDontSee('AI thực hiện')
            ->assertDontSee('Học viên kiểm tra')
            ->assertDontSee('Nền tảng chung')
            ->assertSee('Checklist trước khi nộp')
            ->assertSee('Đạt từ 70/100');

        $this->assertSame(21, substr_count($component->html(), 'SOP — CÁC BƯỚC'));
    }

    public function test_curriculum_has_ai_first_content_modes_and_only_requires_video_on_final_day(): void
    {
        app(Revit21DaysSeeder::class)->run();

        $tasks = ChallengeTask::query()
            ->whereHas('expedition', fn ($query) => $query->where('slug', '21-ngay-lam-tool-revit-voi-ai-agent'))
            ->orderBy('day_number')
            ->get();

        $this->assertSame('inline', $tasks->firstWhere('day_number', 1)->instruction_payload['content_mode']);
        $this->assertSame('inline', $tasks->firstWhere('day_number', 2)->instruction_payload['content_mode']);
        foreach ([7, 12, 15, 18, 20, 21] as $day) {
            $this->assertSame('landing', $tasks->firstWhere('day_number', $day)->instruction_payload['content_mode']);
        }
        $this->assertNotEmpty($tasks->firstWhere('day_number', 7)->instruction_payload['feed_activity']);
        $this->assertNotEmpty($tasks->firstWhere('day_number', 21)->instruction_payload['feed_activity']);

        foreach ($tasks->where('day_number', '<', 21) as $task) {
            $this->assertStringNotContainsString('Video tổng kết', implode(' ', $task->instruction_payload['evidence_requirements']));
        }
        $this->assertStringContainsString('Video tổng kết', implode(' ', $tasks->firstWhere('day_number', 21)->instruction_payload['evidence_requirements']));
    }

    public function test_seeding_preserves_admin_video_and_meeting_data_and_landing_renders_for_admin(): void
    {
        app(Revit21DaysSeeder::class)->run();
        $task = ChallengeTask::query()->where('day_number', 1)->firstOrFail();
        $task->update(['video_url' => 'https://example.test/recording', 'meeting_at' => now()->addDay()]);

        app(Revit21DaysSeeder::class)->run();
        $this->assertSame('https://example.test/recording', $task->fresh()->video_url);
        $this->assertNotNull($task->fresh()->meeting_at);

        $admin = User::query()->where('is_admin', true)->firstOrFail();
        Livewire::actingAs($admin)
            ->test(ChallengeLessonPage::class, ['slug' => '21-ngay-lam-tool-revit-voi-ai-agent', 'day' => 1])
            ->assertSee('Chọn và cài AI Agent để bắt đầu')
            ->assertSee('Prompt copy vào AI Agent')
            ->assertSee('Sao chép prompt');
    }
}
