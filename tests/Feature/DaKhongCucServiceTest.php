<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Core\Gamification\DaKhongCucService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DaKhongCucServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_award_is_scoped_to_the_current_community_and_notifies_the_member(): void
    {
        $user = User::factory()->create();

        app(DaKhongCucService::class)->award($user, 3, 'Hoàn thành thử thách');

        $this->assertDatabaseHas('da_khong_cuc', ['brand_id' => brand()->id, 'user_id' => $user->id, 'total_count' => 3]);
        $this->assertDatabaseHas('da_khong_cuc_log', ['brand_id' => brand()->id, 'user_id' => $user->id, 'delta' => 3]);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $user->id]);
    }
}
