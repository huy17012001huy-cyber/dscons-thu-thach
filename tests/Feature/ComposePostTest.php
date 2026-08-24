<?php

namespace Tests\Feature;

use App\Livewire\ComposePost;
use App\Models\Membership;
use App\Models\Post;
use App\Support\PostContentRenderer;
use App\Support\PostHtmlSanitizer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ComposePostTest extends TestCase
{
    use RefreshDatabase;

    private function member(array $attributes = []): User
    {
        $user = User::factory()->create(array_merge(['level' => 1, 'class' => 'offer_architect'], $attributes));
        Membership::factory()->active()->create(['user_id' => $user->id]);
        $user->brandRoles()->attach(brand()->id, ['role' => 'member']);
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

    public function test_community_member_without_membership_can_publish_a_post(): void
    {
        $user = User::factory()->create(['level' => 1, 'class' => 'offer_architect']);
        $user->brandRoles()->attach(brand()->id, ['role' => 'member']);

        Livewire::actingAs($user)
            ->test(ComposePost::class)
            ->set('content', 'Bài viết của thành viên cộng đồng không có Membership')
            ->set('pillar', 'offer')
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('posts', [
            'user_id' => $user->id,
            'content' => 'Bài viết của thành viên cộng đồng không có Membership',
        ]);
    }

    public function test_community_participant_without_premium_can_publish_a_post(): void
    {
        $user = User::factory()->create(['level' => 1, 'class' => 'offer_architect']);
        $user->brandRoles()->attach(brand()->id, ['role' => 'member']);

        Livewire::actingAs($user)
            ->test(ComposePost::class)
            ->set('title', 'Bài viết của thành viên cộng đồng')
            ->set('content', 'Nội dung chia sẻ không cần Premium.')
            ->set('pillar', 'offer')
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('posts', [
            'user_id' => $user->id,
            'title' => 'Bài viết của thành viên cộng đồng',
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

    public function test_member_can_upload_an_image_before_publishing(): void
    {
        Storage::fake('public');
        $user = $this->member();
        $image = UploadedFile::fake()->create('coordination-plan.png', 100, 'image/png');

        Livewire::actingAs($user)
            ->test(ComposePost::class)
            ->set('imageUploads', [$image])
            ->assertHasNoErrors()
            ->assertCount('uploadedImages', 1);

        Storage::disk('public')->assertExists('post-images/'.$image->hashName());
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
        $this->assertStringNotContainsString('javascript:', $html);
    }

    public function test_markdown_preview_supports_lists_quotes_youtube_and_rejects_unsafe_urls(): void
    {
        $content = "- Một\n- Hai\n\n> Trích dẫn\n\n[Unsafe](javascript:alert(1))\n\nhttps://www.youtube.com/watch?v=dQw4w9WgXcQ";
        $html = app(PostContentRenderer::class)->render($content);

        $this->assertStringContainsString('<ul>', $html);
        $this->assertStringContainsString('<blockquote>', $html);
        $this->assertStringContainsString('youtube.com/embed/dQw4w9WgXcQ', $html);
        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringNotContainsString('onclick=', strtolower($html));
    }

    public function test_rich_post_html_sanitizer_keeps_formatting_and_removes_unsafe_attributes(): void
    {
        $html = app(PostHtmlSanitizer::class)->sanitize('<p><strong>Đậm</strong></p><script>alert(1)</script><a href="https://example.com" onclick="alert(1)">Link</a>');

        $this->assertStringContainsString('<strong>Đậm</strong>', $html);
        $this->assertStringContainsString('href="https://example.com"', $html);
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('onclick', $html);
    }

    public function test_editor_preview_uses_the_same_safe_renderer_as_published_posts(): void
    {
        $user = $this->member();

        Livewire::actingAs($user)
            ->test(ComposePost::class)
            ->set('expanded', true)
            ->set('content', "**Đậm**\n\n<script>alert(1)</script>\n\n[Link](javascript:alert(1))")
            ->set('editorMode', 'preview')
            ->assertSeeHtml('<strong>Đậm</strong>')
            ->assertDontSee('<script>')
            ->assertSee('Soạn thảo')
            ->assertSee('Xem trước');
    }

    public function test_switching_back_to_edit_mode_keeps_the_markdown_content(): void
    {
        $user = $this->member();

        Livewire::actingAs($user)
            ->test(ComposePost::class)
            ->set('expanded', true)
            ->set('content', '**Nội dung không mất**')
            ->set('editorMode', 'preview')
            ->set('editorMode', 'write')
            ->assertSet('content', '**Nội dung không mất**')
            ->assertSee('Nội dung bài viết');
    }

    public function test_validation_still_runs_while_preview_mode_is_active(): void
    {
        $user = $this->member();

        Livewire::actingAs($user)
            ->test(ComposePost::class)
            ->set('expanded', true)
            ->set('content', 'Nội dung đủ dài để kiểm tra validation trong preview.')
            ->set('editorMode', 'preview')
            ->call('submit')
            ->assertHasErrors(['pillar' => 'required']);
    }
}
