<?php

namespace Tests\Feature;

use App\Livewire\PostCard;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PostXssTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_content_linkification_is_secure_against_xss(): void
    {
        $user = User::factory()->create();

        // Payload without space to ensure the regex matches the escaped quote
        $payload = 'https://example.com"onmouseover=alert(1)';

        $post = Post::create([
            'user_id' => $user->id,
            'content' => $payload,
        ]);

        $component = Livewire::test(PostCard::class, ['post' => $post]);
        $html = $component->html();

        // Vulnerable output would look like: <a href="https://example.com&quot;onmouseover=alert(1)" ...
        // Secure output should stop before &quot;
        $this->assertStringNotContainsString('href="https://example.com&quot;onmouseover=alert(1)"', $html);
        $this->assertStringContainsString('href="https://example.com"', $html);
    }

    public function test_post_content_linkification_allows_ampersands(): void
    {
        $user = User::factory()->create();

        $payload = 'https://example.com/search?q=query&limit=10';

        $post = Post::create([
            'user_id' => $user->id,
            'content' => $payload,
        ]);

        $component = Livewire::test(PostCard::class, ['post' => $post]);
        $html = $component->html();

        // After e(), & becomes &amp;
        // The regex should match the whole URL including &amp;
        $this->assertStringContainsString('href="https://example.com/search?q=query&amp;limit=10"', $html);
    }
}
