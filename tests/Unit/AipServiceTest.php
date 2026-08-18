<?php

namespace Tests\Unit;

use App\Models\AipTransaction;
use App\Models\User;
use App\Services\AipService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AipServiceTest extends TestCase
{
    use RefreshDatabase;

    private AipService $aipService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->aipService = app(AipService::class);
    }

    /**
     * Test earn increments AIP
     */
    public function test_earn_increments_aip(): void
    {
        $user = User::factory()->create(['aip' => 0]);

        $this->aipService->earn($user, 100, 'Test earn');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'aip' => 100,
        ]);
    }

    /**
     * Test earn creates transaction record
     */
    public function test_earn_creates_transaction(): void
    {
        $user = User::factory()->create(['aip' => 0]);

        $this->aipService->earn($user, 100, 'Test earn');

        $this->assertDatabaseHas('aip_transactions', [
            'user_id' => $user->id,
            'amount' => 100,
            'type' => 'earn',
            'reason' => 'Test earn',
        ]);
    }

    /**
     * Test earn accumulates multiple times
     */
    public function test_earn_accumulates(): void
    {
        $user = User::factory()->create(['aip' => 0]);

        $this->aipService->earn($user, 100, 'First earn');
        $user->refresh();
        $this->assertEquals(100, $user->aip);

        $this->aipService->earn($user, 50, 'Second earn');
        $user->refresh();
        $this->assertEquals(150, $user->aip);

        $this->aipService->earn($user, 25, 'Third earn');
        $user->refresh();
        $this->assertEquals(175, $user->aip);
    }

    /**
     * Test earn with reference model
     */
    public function test_earn_with_reference_model(): void
    {
        $user = User::factory()->create(['aip' => 0]);
        $post = \App\Models\Post::factory()->create();

        $this->aipService->earn($user, 50, 'Post reward', $post);

        $this->assertDatabaseHas('aip_transactions', [
            'user_id' => $user->id,
            'amount' => 50,
            'type' => 'earn',
            'reason' => 'Post reward',
            'reference_type' => \App\Models\Post::class,
            'reference_id' => $post->id,
        ]);
    }

    /**
     * Test spend decrements AIP
     */
    public function test_spend_decrements_aip(): void
    {
        $user = User::factory()->create(['aip' => 100]);

        $this->aipService->spend($user, 30, 'Test spend');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'aip' => 70,
        ]);
    }

    /**
     * Test spend creates transaction record
     */
    public function test_spend_creates_transaction(): void
    {
        $user = User::factory()->create(['aip' => 100]);

        $this->aipService->spend($user, 30, 'Test spend');

        $this->assertDatabaseHas('aip_transactions', [
            'user_id' => $user->id,
            'amount' => -30,
            'type' => 'spend',
            'reason' => 'Test spend',
        ]);
    }

    /**
     * Test spend multiple times
     */
    public function test_spend_accumulates(): void
    {
        $user = User::factory()->create(['aip' => 200]);

        $this->aipService->spend($user, 50, 'First spend');
        $user->refresh();
        $this->assertEquals(150, $user->aip);

        $this->aipService->spend($user, 30, 'Second spend');
        $user->refresh();
        $this->assertEquals(120, $user->aip);

        $this->aipService->spend($user, 40, 'Third spend');
        $user->refresh();
        $this->assertEquals(80, $user->aip);
    }

    /**
     * Test spend with reference model
     */
    public function test_spend_with_reference_model(): void
    {
        $user = User::factory()->create(['aip' => 100]);
        $post = \App\Models\Post::factory()->create();

        $this->aipService->spend($user, 25, 'Boost post', $post);

        $this->assertDatabaseHas('aip_transactions', [
            'user_id' => $user->id,
            'amount' => -25,
            'type' => 'spend',
            'reason' => 'Boost post',
            'reference_type' => \App\Models\Post::class,
            'reference_id' => $post->id,
        ]);
    }

    /**
     * Test spend throws exception when insufficient AIP
     */
    public function test_spend_throws_exception_when_insufficient(): void
    {
        $user = User::factory()->create(['aip' => 50]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Không đủ AIP');

        $this->aipService->spend($user, 100, 'Cannot spend');
    }

    /**
     * Test spend at exact balance
     */
    public function test_spend_exact_balance(): void
    {
        $user = User::factory()->create(['aip' => 100]);

        $this->aipService->spend($user, 100, 'Spend all');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'aip' => 0,
        ]);
    }

    /**
     * Test spend with zero balance
     */
    public function test_spend_with_zero_balance(): void
    {
        $user = User::factory()->create(['aip' => 0]);

        $this->expectException(\RuntimeException::class);

        $this->aipService->spend($user, 1, 'Cannot spend');
    }

    /**
     * Test spend message includes required and current AIP
     */
    public function test_spend_exception_message_format(): void
    {
        $user = User::factory()->create(['aip' => 30]);

        try {
            $this->aipService->spend($user, 100, 'Test');
            $this->fail('Expected RuntimeException');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('100', $e->getMessage());
            $this->assertStringContainsString('30', $e->getMessage());
        }
    }

    /**
     * Test spend and earn together
     */
    public function test_spend_and_earn_together(): void
    {
        $user = User::factory()->create(['aip' => 100]);

        $this->aipService->spend($user, 30, 'Spend');
        $user->refresh();
        $this->assertEquals(70, $user->aip);

        $this->aipService->earn($user, 50, 'Earn');
        $user->refresh();
        $this->assertEquals(120, $user->aip);

        $this->aipService->spend($user, 20, 'Spend again');
        $user->refresh();
        $this->assertEquals(100, $user->aip);
    }

    /**
     * Test transaction records maintain history
     */
    public function test_transaction_records_maintain_history(): void
    {
        $user = User::factory()->create(['aip' => 100]);

        $this->aipService->earn($user, 50, 'Earn 1');
        $this->aipService->spend($user, 30, 'Spend 1');
        $this->aipService->earn($user, 25, 'Earn 2');

        $transactions = AipTransaction::where('user_id', $user->id)->get();

        $this->assertEquals(3, $transactions->count());
        $this->assertEquals('earn', $transactions[0]->type);
        $this->assertEquals('spend', $transactions[1]->type);
        $this->assertEquals('earn', $transactions[2]->type);
    }

    /**
     * Test negative spend attempts don't affect balance negatively
     */
    public function test_spend_with_large_amount(): void
    {
        $user = User::factory()->create(['aip' => 100]);

        $this->expectException(\RuntimeException::class);

        $this->aipService->spend($user, 999999, 'Large amount');

        $user->refresh();
        $this->assertEquals(100, $user->aip); // Unchanged
    }
}
