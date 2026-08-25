<?php

namespace Tests\Feature;

use App\Models\ChallengeTask;
use App\Models\Expedition;
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
        $admin = \App\Models\User::query()->where('is_admin', true)->firstOrFail();

        Livewire::actingAs($admin)
            ->test(\App\Livewire\ChallengeDetail::class, ['slug' => '21-ngay-lam-tool-revit-voi-ai-agent'])
            ->assertSee('Prompt copy vào AI Agent')
            ->assertSee('SOP duy nhất — làm theo từng bước')
            ->assertDontSee('SOP — Hướng dẫn')
            ->assertDontSee('Bạn đã yêu cầu AI làm gì?')
            ->assertSee('Minh chứng cần nộp')
            ->assertSee('Đạt từ 70/100');
    }
}
