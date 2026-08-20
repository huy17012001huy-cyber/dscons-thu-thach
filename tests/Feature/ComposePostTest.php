<?php

namespace Tests\Feature;

use App\Livewire\ComposePost;
use App\Models\Membership;
use App\Models\Post;
use App\Support\PostContentRenderer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ComposePostTest extends TestCase
{
    use RefreshDatabase;

    private function member(array $attributes = []): User
    {
        $user = User::factory()->create(array_merge(['level' => 1, 'class' => 'offer_architect'], $attributes));
        Membership::factory()->active()->create(['user_id' => $user->id]);
        return $user;
    }

    public function test_level_one_member_can_publish_a_post(): void
    {
        $user = $this->member();

        Livewire::actingAs($user)
            ->test(ComposePost::class)
            ->set('title', 'Bài viết đầu tiên')
            ->set('content', '**Nội dung** chia sẻ với cộng đồng')
            ->set('pillar', 'offer')
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('posts', [
            'user_id' => $user->id,
            'title' => 'Bài viết đầu tiên',
            'pillar' => 'offer',
            'content' => '**Nội dung** chia sẻ với cộng đồng',
        ]);
    }

    public function test_pillar_is_required(): void
    {
        $user = $this->member();

        Livewire::actingAs($user)
            ->test(ComposePost::class)
            ->set('content', 'Nội dung hợp lệ nhưng chưa chọn pillar')
            ->call('submit')
            ->assertHasErrors(['pillar' => 'required']);
    }

    public function test_member_cannot_publish_a_sixth_post_in_one_vietnamese_day(): void
    {
        $user = $this->member();

        Post::factory()->count(5)->create([
            'user_id' => $user->id,
            'pillar' => 'offer',
            'created_at' => now('Asia/Ho_Chi_Minh')->setTimezone('UTC'),
        ]);

        Livewire::actingAs($user)
            ->test(ComposePost::class)
            ->set('content', 'Bài viết thứ sáu')
            ->set('pillar', 'offer')
            ->call('submit')
            ->assertHasErrors('content');

        $this->assertSame(5, Post::withTrashed()->where('user_id', $user->id)->count());
    }

    public function test_markdown_renderer_strips_html_and_keeps_safe_formatting(): void
    {
        $html = app(PostContentRenderer::class)->render('**Đậm** [Liên kết](https://example.com) <script>alert(1)</script>');

        $this->assertStringContainsString('<strong>Đậm</strong>', $html);
        $this->assertStringContainsString('target="_blank"', $html);
        $this->assertStringNotContainsString('<script>', $html);
    }
}
