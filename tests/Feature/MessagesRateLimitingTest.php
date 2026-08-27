<?php

namespace Tests\Feature;

use App\Livewire\MessagesPage;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MessagesRateLimitingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that sending messages is rate limited.
     */
    public function test_sending_messages_is_rate_limited(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $conversation = Conversation::findOrCreateBetween($user1->id, $user2->id);

        $component = Livewire::actingAs($user1)
            ->test(MessagesPage::class, ['conversation' => $conversation->id]);

        // Send 10 messages (the limit)
        for ($i = 0; $i < 10; $i++) {
            $component->set('newMessage', 'Message '.$i)
                ->call('sendMessage')
                ->assertHasNoErrors('newMessage');
        }

        // The 11th message should fail
        $component->set('newMessage', 'Message 11')
            ->call('sendMessage')
            ->assertHasErrors(['newMessage']);

        $this->assertEquals(10, $conversation->messages()->count());
    }
}
