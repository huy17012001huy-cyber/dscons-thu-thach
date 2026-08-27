<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\ChallengeTask;
use App\Models\Expedition;
use Database\Seeders\AutoCAD21DaysSeeder;
use Database\Seeders\Navisworks21DaysSeeder;
use Database\Seeders\Revit21DaysSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AutoCadAndNavisworks21DaysCurriculumTest extends TestCase
{
    use RefreshDatabase;

    public function test_both_curricula_seed_42_structured_tasks_idempotently(): void
    {
        app(Revit21DaysSeeder::class)->run();
        app(AutoCAD21DaysSeeder::class)->run();
        app(Navisworks21DaysSeeder::class)->run();
        app(AutoCAD21DaysSeeder::class)->run();
        app(Navisworks21DaysSeeder::class)->run();

        $this->assertChallenge('21-ngay-chinh-phuc-tool-autocad-bang-ai-agent', '21 Ngày Chinh Phục Tool AutoCAD bằng AI Agent');
        $this->assertChallenge('21-ngay-chinh-phuc-tool-navisworks-bang-ai-agent', '21 Ngày Chinh Phục Tool Navisworks bằng AI Agent');
        $this->assertSame(3, Expedition::query()->count());
        $this->assertSame(42, ChallengeTask::query()->whereNotNull('instruction_payload')->whereHas('expedition', fn ($query) => $query->whereIn('slug', [
            '21-ngay-chinh-phuc-tool-autocad-bang-ai-agent',
            '21-ngay-chinh-phuc-tool-navisworks-bang-ai-agent',
        ]))->count());
    }

    public function test_autocad_and_navisworks_have_common_payload_and_four_live_days(): void
    {
        app(Revit21DaysSeeder::class)->run();
        app(AutoCAD21DaysSeeder::class)->run();
        app(Navisworks21DaysSeeder::class)->run();

        foreach ([
            '21-ngay-chinh-phuc-tool-autocad-bang-ai-agent',
            '21-ngay-chinh-phuc-tool-navisworks-bang-ai-agent',
        ] as $slug) {
            $tasks = ChallengeTask::query()
                ->whereHas('expedition', fn ($query) => $query->where('slug', $slug))
                ->orderBy('day_number')
                ->get();

            $this->assertCount(21, $tasks);
            $this->assertSame([1, 8, 15, 21], $tasks
                ->filter(fn ($task) => ($task->instruction_payload['modality'] ?? null) === 'live')
                ->pluck('day_number')->values()->all());

            foreach ($tasks as $task) {
                $payload = $task->instruction_payload;
                foreach ([
                    'modality', 'estimated_minutes', 'learning_objectives', 'prerequisites',
                    'ai_actions', 'student_actions', 'sop_steps', 'verification_checklist',
                    'homework', 'evidence_requirements', 'rubric', 'common_errors',
                    'review_mode', 'pass_score', 'track', 'ai_prompt', 'safety_constraints',
                ] as $key) {
                    $this->assertArrayHasKey($key, $payload, "{$slug} day {$task->day_number} missing {$key}");
                }
                $this->assertSame(70, $payload['pass_score']);
                $this->assertNotEmpty($task->sop_content);
            }
        }
    }

    public function test_navisworks_payload_preserves_runtime_safety_boundary(): void
    {
        app(Revit21DaysSeeder::class)->run();
        app(Navisworks21DaysSeeder::class)->run();

        $tasks = ChallengeTask::query()
            ->whereHas('expedition', fn ($query) => $query->where('slug', '21-ngay-chinh-phuc-tool-navisworks-bang-ai-agent'))
            ->get();

        $content = $tasks->pluck('sop_content')->implode("\n");
        $this->assertStringContainsString('Simulate', $content);
        $this->assertStringContainsString('Manage', $content);
        $this->assertStringContainsString('runtime', strtolower($content));
        $this->assertStringContainsString('không tạo nút giả', strtolower($content));
    }

    public function test_evidence_is_image_first_and_video_is_reserved_for_key_days(): void
    {
        app(Revit21DaysSeeder::class)->run();
        app(AutoCAD21DaysSeeder::class)->run();
        app(Navisworks21DaysSeeder::class)->run();

        foreach (Expedition::query()->whereIn('slug', [
            '21-ngay-lam-tool-revit-voi-ai-agent',
            '21-ngay-chinh-phuc-tool-autocad-bang-ai-agent',
            '21-ngay-chinh-phuc-tool-navisworks-bang-ai-agent',
        ])->with('tasks')->get() as $challenge) {
            foreach ($challenge->tasks as $task) {
                $evidence = $task->instruction_payload['evidence_requirements'] ?? [];
                $hasVideo = collect($evidence)->contains(fn ($item) => str_contains(strtolower($item), 'video'));

                $videoDays = $challenge->slug === '21-ngay-lam-tool-revit-voi-ai-agent'
                    ? [21]
                    : [7, 14, 21];
                $this->assertSame(
                    in_array($task->day_number, $videoDays, true),
                    $hasVideo,
                    "Unexpected video evidence on {$challenge->slug} day {$task->day_number}"
                );
                $this->assertNotEmpty($evidence);
            }
        }
    }

    public function test_challenge_detail_renders_both_new_curricula(): void
    {
        app(Revit21DaysSeeder::class)->run();
        app(AutoCAD21DaysSeeder::class)->run();
        app(Navisworks21DaysSeeder::class)->run();
        $admin = \App\Models\User::query()->where('is_admin', true)->firstOrFail();

        foreach ([
            '21-ngay-chinh-phuc-tool-autocad-bang-ai-agent' => 'AutoCAD',
            '21-ngay-chinh-phuc-tool-navisworks-bang-ai-agent' => 'Navisworks',
        ] as $slug => $appName) {
            $component = Livewire::actingAs($admin)
                ->test(\App\Livewire\ChallengeDetail::class, ['slug' => $slug])
                ->assertSee($appName)
                ->assertSee('Prompt copy vào AI Agent')
                ->assertSee('Hướng dẫn thực hiện')
                ->assertDontSee('Bạn đã yêu cầu AI làm gì?')
                ->assertSee('Checklist trước khi nộp')
                ->assertSee('Đạt từ 70/100');

            $this->assertSame(21, substr_count($component->html(), 'SOP — CÁC BƯỚC'));
        }
    }

    private function assertChallenge(string $slug, string $title): void
    {
        $challenge = Expedition::query()->where('slug', $slug)->firstOrFail();
        $revit = Expedition::query()->where('slug', '21-ngay-lam-tool-revit-voi-ai-agent')->firstOrFail();

        $this->assertSame($title, $challenge->title);
        $this->assertSame(21, $challenge->required_days);
        $this->assertSame((string) $revit->price, (string) $challenge->price);
        $this->assertSame($revit->access_tier, $challenge->access_tier);
        $this->assertSame($revit->max_members, $challenge->max_members);
    }
}
