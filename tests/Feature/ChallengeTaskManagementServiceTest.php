<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Core\CommunityContext;
use App\Livewire\AdminChallenges;
use App\Models\Brand;
use App\Models\ChallengeTask;
use App\Models\Expedition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Learning\Application\ChallengeTaskManagementService;
use Tests\TestCase;

final class ChallengeTaskManagementServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_community_admin_can_manage_tasks_in_its_challenge(): void
    {
        $admin = $this->admin();
        $challenge = $this->challenge($admin);
        $service = app(ChallengeTaskManagementService::class);

        $task = $service->save($challenge->id, null, $admin, $this->attributes('Created task'));

        self::assertNotNull($task);
        self::assertSame($challenge->id, $task->expedition_id);
        $updated = $service->save($challenge->id, $task->id, $admin, $this->attributes('Updated task'));
        self::assertSame('Updated task', $updated?->title);
        self::assertSame($task->id, $service->delete($task->id, $admin)?->id);
        $this->assertDatabaseMissing('challenge_tasks', ['id' => $task->id]);
    }

    public function test_task_management_cannot_write_to_a_different_community(): void
    {
        $admin = $this->admin();
        $challenge = $this->challenge($admin);
        $task = ChallengeTask::create([...$this->attributes('Scoped task'), 'expedition_id' => $challenge->id]);
        $other = Brand::create([
            'name' => 'Other community',
            'slug' => 'other-community',
            'domain' => 'other-community.test',
            'status' => 'active',
            'theme_primary' => '#1F77BE',
            'theme_accent' => '#E1F4F7',
            'theme_bg' => '#F7FAFC',
        ]);

        app(CommunityContext::class)->run($other, function () use ($admin, $challenge, $task): void {
            self::assertNull(
                app(ChallengeTaskManagementService::class)->save(
                    $challenge->id,
                    $task->id,
                    $admin,
                    $this->attributes('Cross community task'),
                ),
            );
        });
        self::assertSame('Scoped task', $task->fresh()->title);
    }

    public function test_admin_challenges_saves_a_task_through_the_management_service(): void
    {
        $admin = $this->admin();
        $challenge = $this->challenge($admin);

        Livewire::actingAs($admin)
            ->test(AdminChallenges::class)
            ->set('managingExpeditionId', $challenge->id)
            ->set('taskDayNumber', 1)
            ->set('taskTitle', 'Livewire managed task')
            ->set('taskEvidenceType', 'text')
            ->set('taskDurationHours', 24)
            ->call('saveTask')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('challenge_tasks', [
            'expedition_id' => $challenge->id,
            'title' => 'Livewire managed task',
        ]);
    }

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->brandRoles()->attach(brand()->id, ['role' => 'admin']);

        return $admin;
    }

    private function challenge(User $admin): Expedition
    {
        return Expedition::create([
            'title' => 'Task management challenge',
            'slug' => 'task-management-challenge',
            'description' => 'Challenge task management test.',
            'difficulty' => 'normal',
            'required_days' => 7,
            'max_members' => 50,
            'created_by' => $admin->id,
            'leader_id' => $admin->id,
            'status' => 'active',
        ]);
    }

    /** @return array<string, mixed> */
    private function attributes(string $title): array
    {
        return [
            'day_number' => 1,
            'title' => $title,
            'evidence_type' => 'text',
            'duration_hours' => 24,
            'is_contest' => false,
        ];
    }
}
