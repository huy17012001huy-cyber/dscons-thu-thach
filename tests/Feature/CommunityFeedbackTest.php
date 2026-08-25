<?php

namespace Tests\Feature;

use App\Livewire\CommunityFeedbacks;
use App\Livewire\CreateCommunityFeedback;
use App\Models\Brand;
use App\Models\Feedback;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class CommunityFeedbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_open_feedback_list_and_submit_a_community_scoped_ticket(): void
    {
        $brand = brand();
        $member = User::factory()->create();
        $member->brandRoles()->attach($brand->id, ['role' => 'member']);

        $this->actingAs($member)
            ->get(route('community.feedbacks', ['community' => $brand->slug]))
            ->assertOk()
            ->assertSee('Góp ý & Khiếu nại');

        $this->get(route('community.feedbacks.create', ['community' => $brand->slug]))
            ->assertOk()
            ->assertSee('Gửi góp ý / khiếu nại')
            ->assertSee('Nói ngắn gọn chuyện gì')
            ->assertSee('Báo lỗi')
            ->assertSee('Thanh toán')
            ->assertSee('Khác');

        Livewire::actingAs($member)
            ->test(CreateCommunityFeedback::class)
            ->set('subject', 'Không tải được ảnh')
            ->set('content', 'Tôi đã thử tải ảnh PNG nhưng biểu mẫu không hoàn tất.')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertRedirect(community_route('feedbacks'));

        $this->assertDatabaseHas('feedbacks', [
            'user_id' => $member->id,
            'brand_id' => $brand->id,
            'type' => 'gop_y',
            'subject' => 'Không tải được ảnh',
        ]);
    }

    public function test_feedback_uploads_are_stored_and_invalid_files_are_rejected(): void
    {
        Storage::fake('public');
        $brand = brand();
        $member = User::factory()->create();
        $member->brandRoles()->attach($brand->id, ['role' => 'member']);

        Livewire::actingAs($member)->test(CreateCommunityFeedback::class)
            ->set('subject', 'Tệp không hợp lệ')
            ->set('content', 'Đây là nội dung đủ dài để kiểm thử tệp không hợp lệ.')
            ->set('attachments', [UploadedFile::fake()->create('notes.txt', 10, 'text/plain')])
            ->call('submit')
            ->assertHasErrors(['attachments.0']);

        Livewire::actingAs($member)->test(CreateCommunityFeedback::class)
            ->set('subject', 'Gửi ảnh minh chứng')
            ->set('content', 'Đây là nội dung đủ dài để kiểm thử phần ảnh minh chứng.')
            ->set('attachments', [UploadedFile::fake()->createWithContent(
                'screen.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9WlS/cAAAAAASUVORK5CYII=')
            )])
            ->call('submit')
            ->assertHasNoErrors();

        $feedback = Feedback::query()->where('user_id', $member->id)->firstOrFail();
        $this->assertCount(1, $feedback->attachments);
        Storage::disk('public')->assertExists($feedback->attachments[0]);
    }

    public function test_member_cannot_see_feedback_from_another_community(): void
    {
        $first = brand();
        $second = Brand::create([
            'name' => 'Cộng đồng thứ hai',
            'slug' => 'cong-dong-thu-hai',
            'domain' => 'second.test',
            'status' => 'active',
            'theme_primary' => '#1F77BE',
            'theme_accent' => '#F39402',
            'theme_bg' => '#F7FAFC',
        ]);
        $member = User::factory()->create();
        $member->brandRoles()->attach([$first->id => ['role' => 'member'], $second->id => ['role' => 'member']]);

        Feedback::create([
            'user_id' => $member->id,
            'brand_id' => $second->id,
            'type' => 'gop_y',
            'subject' => 'Chỉ thuộc cộng đồng hai',
            'content' => 'Nội dung riêng của cộng đồng thứ hai.',
        ]);

        app()->instance('brand', $first);
        $this->actingAs($member);

        Livewire::test(CommunityFeedbacks::class)
            ->assertDontSee('Chỉ thuộc cộng đồng hai');
    }

    public function test_non_participant_is_sent_to_community_preview(): void
    {
        $brand = brand();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('community.feedbacks', ['community' => $brand->slug]))
            ->assertRedirectToRoute('community.preview', ['community' => $brand->slug]);
    }
}
