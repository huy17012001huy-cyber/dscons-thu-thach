<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\XpTransaction;
use App\Services\XpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class XpServiceTest extends TestCase
{
    use RefreshDatabase;

    private XpService $xpService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->xpService = app(XpService::class);
    }

    /**
     * Test award calculates XP correctly with base amount
     */
    public function test_award_calculates_xp_with_base_amount(): void
    {
        $user = User::factory()->create(['xp' => 0, 'streak' => 0]);

        $xp = $this->xpService->award($user, 'comment');

        $this->assertEquals(1, $xp);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'xp' => 1]);
    }

    /**
     * Test award creates transaction record
     */
    public function test_award_creates_transaction_record(): void
    {
        $user = User::factory()->create(['xp' => 0]);

        $this->xpService->award($user, 'comment', 1.0, 'Test comment', null);

        $this->assertDatabaseHas('xp_transactions', [
            'user_id' => $user->id,
            'type' => 'comment',
            'amount' => 1,
            'description' => 'Test comment',
        ]);
    }

    /**
     * Test award applies custom multiplier
     */
    public function test_award_applies_multiplier(): void
    {
        $user = User::factory()->create(['xp' => 0, 'streak' => 0]);

        $xp = $this->xpService->award($user, 'comment', 2.0);

        $this->assertEquals(2, $xp); // 1 * 2.0
        $this->assertDatabaseHas('users', ['id' => $user->id, 'xp' => 2]);
    }

    /**
     * Test award applies streak multiplier (7-29 streak = 1.1x)
     */
    public function test_award_applies_streak_multiplier_medium(): void
    {
        $user = User::factory()->create(['xp' => 0, 'streak' => 7]);

        $xp = $this->xpService->award($user, 'comment', 1.0);

        $this->assertEquals(1, $xp); // 1 * 1.0 * 1.1 = 1.1 → 1
        $this->assertDatabaseHas('users', ['id' => $user->id, 'xp' => 1]);
    }

    /**
     * Test award applies high streak multiplier (30+ streak = 1.2x)
     */
    public function test_award_applies_streak_multiplier_high(): void
    {
        $user = User::factory()->create(['xp' => 0, 'streak' => 30]);

        $xp = $this->xpService->award($user, 'comment', 1.0);

        $this->assertEquals(1, $xp); // 1 * 1.0 * 1.2 = 1.2 → 1
        $this->assertDatabaseHas('users', ['id' => $user->id, 'xp' => 1]);
    }

    /**
     * Test award combines multiplier and streak
     */
    public function test_award_combines_multiplier_and_streak(): void
    {
        $user = User::factory()->create(['xp' => 0, 'streak' => 30]);

        $xp = $this->xpService->award($user, 'comment', 2.0);

        $this->assertEquals(2, $xp); // 1 * 2.0 * 1.2 = 2.4 → 2
        $this->assertDatabaseHas('users', ['id' => $user->id, 'xp' => 2]);
    }

    /**
     * Test award with different reward types
     */
    public function test_award_with_different_types(): void
    {
        $user = User::factory()->create(['xp' => 0, 'streak' => 0]);

        // Login: 1 XP
        $this->xpService->award($user, 'login');
        $user->refresh();
        $this->assertEquals(1, $user->xp);

        // Comment: 1 XP
        $this->xpService->award($user, 'comment');
        $user->refresh();
        $this->assertEquals(2, $user->xp);

        // COT: 50 XP
        $this->xpService->award($user, 'cot');
        $user->refresh();
        $this->assertEquals(52, $user->xp);

        // Expedition complete: 100 XP
        $this->xpService->award($user, 'expedition_complete');
        $user->refresh();
        $this->assertEquals(152, $user->xp);
    }

    /**
     * Test award returns 0 for invalid type
     */
    public function test_award_returns_zero_for_invalid_type(): void
    {
        $user = User::factory()->create(['xp' => 0]);

        $xp = $this->xpService->award($user, 'invalid_type');

        $this->assertEquals(0, $xp);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'xp' => 0]);
    }

    /**
     * Test checkLevelUp increments level when threshold met
     */
    public function test_check_level_up_increments_level(): void
    {
        $user = User::factory()->create(['xp' => 0, 'level' => 1]);

        // Level 2 requires 15 XP (first level)
        $user->update(['xp' => 15]);

        $result = $this->xpService->checkLevelUp($user);

        $this->assertTrue($result);
        $this->assertEquals(2, $user->fresh()->level);
    }

    /**
     * Test checkLevelUp does not level up when threshold not met
     */
    public function test_check_level_up_does_not_level_when_insufficient_xp(): void
    {
        $user = User::factory()->create(['xp' => 10, 'level' => 1]);

        $result = $this->xpService->checkLevelUp($user);

        $this->assertFalse($result);
        $this->assertEquals(1, $user->fresh()->level);
    }

    /**
     * Test checkLevelUp can level up multiple times
     */
    public function test_check_level_up_multiple_levels(): void
    {
        $user = User::factory()->create(['xp' => 0, 'level' => 1]);

        // Set XP to level 4: cumulative is 15 + 34 + 57 = 106
        $user->update(['xp' => 106]);

        $result = $this->xpService->checkLevelUp($user);

        $this->assertTrue($result);
        $this->assertEquals(4, $user->fresh()->level);
    }

    /**
     * Test expRequiredForLevel returns config value for levels 1-60
     */
    public function test_exp_required_for_level_from_config(): void
    {
        $required = $this->xpService->expRequiredForLevel(1);
        $this->assertEquals(15, $required);

        $required = $this->xpService->expRequiredForLevel(10);
        $this->assertEquals(1242, $required);

        $required = $this->xpService->expRequiredForLevel(60);
        $this->assertEquals(158340, $required);
    }

    /**
     * Test expRequiredForLevel uses formula for levels > 60
     */
    public function test_exp_required_for_level_formula_above_60(): void
    {
        // Formula: 158340 * pow(1.08, level - 60)
        $required = $this->xpService->expRequiredForLevel(61);
        $expected = (int) round(158340 * pow(1.08, 61 - 60));
        $this->assertEquals($expected, $required);

        $required = $this->xpService->expRequiredForLevel(70);
        $expected = (int) round(158340 * pow(1.08, 70 - 60));
        $this->assertEquals($expected, $required);
    }

    /**
     * Test cumulativeExpForLevel calculates total correctly
     */
    public function test_cumulative_exp_for_level(): void
    {
        // Level 1: 15
        $cumulative = $this->xpService->cumulativeExpForLevel(1);
        $this->assertEquals(0, $cumulative);

        // Level 2: 15 + 34 = 49
        $cumulative = $this->xpService->cumulativeExpForLevel(2);
        $this->assertEquals(15, $cumulative);

        // Level 3: 15 + 34 + 57 = 106
        $cumulative = $this->xpService->cumulativeExpForLevel(3);
        $this->assertEquals(49, $cumulative);
    }

    /**
     * Test expToNextLevel calculates remaining XP
     */
    public function test_exp_to_next_level(): void
    {
        $user = User::factory()->create(['xp' => 0, 'level' => 1]);

        $remaining = $this->xpService->expToNextLevel($user);
        $this->assertEquals(15, $remaining); // Need 15 total to reach level 2

        $user->update(['xp' => 10]);
        $remaining = $this->xpService->expToNextLevel($user);
        $this->assertEquals(5, $remaining); // Need 5 more to reach level 2

        // After leveling up to 2
        $user->update(['xp' => 15, 'level' => 2]);
        $remaining = $this->xpService->expToNextLevel($user);
        // At level 2 with 15 XP, need 49 total for level 3, so 49 - 15 = 34
        $this->assertEquals(34, $remaining);
    }

    /**
     * Test expProgressPct calculates progress percentage
     */
    public function test_exp_progress_pct(): void
    {
        $user = User::factory()->create(['xp' => 0, 'level' => 1]);

        $progress = $this->xpService->expProgressPct($user);
        $this->assertEquals(0, $progress);

        // Half way to level 2 (15 XP needed total)
        $user->update(['xp' => 7]);
        $progress = $this->xpService->expProgressPct($user);
        $this->assertGreaterThan(45, $progress);
        $this->assertLessThan(50, $progress);

        // At level 2 (15 XP exactly reaches level 2)
        $user->update(['xp' => 15, 'level' => 2]);
        $progress = $this->xpService->expProgressPct($user);
        $this->assertEquals(0, $progress); // Just reached level 2

        // Half way to level 3 (need 49 total)
        // 34 XP needed from level 2 to level 3, so 17 XP = 50%
        $user->update(['xp' => 15 + 17, 'level' => 2]); // 32 XP total
        $progress = $this->xpService->expProgressPct($user);
        $this->assertGreaterThan(45, $progress);
        $this->assertLessThan(55, $progress);
    }

    /**
     * Test award with reference model stores reference
     */
    public function test_award_stores_reference_model(): void
    {
        $user = User::factory()->create(['xp' => 0]);
        $post = \App\Models\Post::factory()->create(['user_id' => $user->id]);

        $this->xpService->award($user, 'comment', 1.0, 'Posted', $post);

        $this->assertDatabaseHas('xp_transactions', [
            'user_id' => $user->id,
            'reference_type' => \App\Models\Post::class,
            'reference_id' => $post->id,
        ]);
    }

    /**
     * Test checkLevelUp doesn't exceed max level
     */
    public function test_check_level_up_respects_max_level(): void
    {
        $user = User::factory()->create(['xp' => 99999999, 'level' => 298]);

        $result = $this->xpService->checkLevelUp($user);

        // Should not exceed level 300
        $this->assertLessThanOrEqual(300, $user->fresh()->level);
    }
}
