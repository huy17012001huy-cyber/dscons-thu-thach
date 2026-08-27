<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Core\Messaging\ConversationMessageService;
use App\Models\Brand;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ConversationMessageServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_community_messages_cannot_cross_community_boundaries(): void
    {
        $sender = User::factory()->create();
        $sender->brandRoles()->attach(brand()->id, ['role' => 'member']);
        $otherBrand = Brand::create(['name' => 'Other community', 'slug' => 'other-messages', 'domain' => 'other-messages.test', 'status' => 'active', 'theme_primary' => '#1F77BE', 'theme_accent' => '#E1F4F7', 'theme_bg' => '#F7FAFC']);
        $recipient = User::factory()->create();
        $recipient->brandRoles()->attach($otherBrand->id, ['role' => 'member']);

        $service = app(ConversationMessageService::class);
        self::assertNull($service->communityConversation($sender, $recipient));

        $foreignConversation = Conversation::withoutGlobalScopes()->create(['brand_id' => $otherBrand->id, 'user_one_id' => $sender->id, 'user_two_id' => $recipient->id, 'conversation_type' => 'community']);
        self::assertFalse($service->send($sender, $foreignConversation->id, 'Xin chào', 'Thông báo', '/messages/'.$foreignConversation->id));
        $this->assertDatabaseMissing('direct_messages', ['conversation_id' => $foreignConversation->id]);
    }

    public function test_community_message_is_stored_and_notified_after_commit(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        $sender->brandRoles()->attach(brand()->id, ['role' => 'member']);
        $recipient->brandRoles()->attach(brand()->id, ['role' => 'member']);
        $service = app(ConversationMessageService::class);
        $conversation = $service->communityConversation($sender, $recipient);

        self::assertNotNull($conversation);
        self::assertTrue($service->send($sender, $conversation->id, 'Xin chào', 'Bạn có tin nhắn mới', '/messages/'.$conversation->id));
        $this->assertDatabaseHas('direct_messages', ['brand_id' => brand()->id, 'conversation_id' => $conversation->id, 'sender_id' => $sender->id]);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $recipient->id]);
    }
}
