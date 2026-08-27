<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Feedback;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Community\Application\CommunityFeedbackService;
use Tests\TestCase;

final class CommunityFeedbackServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_community_member_can_submit_a_scoped_feedback_ticket(): void
    {
        $member = $this->userWithRole('member');
        $feedback = app(CommunityFeedbackService::class)->submit(
            $member,
            'bao_loi',
            'Không tải được ảnh',
            'Tôi đã thử tải ảnh PNG nhưng không thể hoàn tất.',
            ['feedback/dscons/'.$member->id.'/screen.png'],
        );

        self::assertSame(brand()->id, $feedback->brand_id);
        $this->assertDatabaseHas('feedbacks', ['id' => $feedback->id, 'user_id' => $member->id, 'status' => 'pending']);
    }

    public function test_only_community_admin_can_manage_feedback(): void
    {
        $admin = $this->userWithRole('admin');
        $member = $this->userWithRole('member');
        $feedback = Feedback::create([
            'brand_id' => brand()->id,
            'user_id' => $member->id,
            'type' => 'gop_y',
            'subject' => 'Góp ý',
            'content' => 'Nội dung góp ý đủ dài để xem xét.',
        ]);
        $service = app(CommunityFeedbackService::class);

        try {
            $service->updateStatus($feedback->id, $member, 'resolved');
            self::fail('A member must not manage feedback.');
        } catch (AuthorizationException) {
        }

        self::assertTrue($service->updateStatus($feedback->id, $admin, 'resolved'));
        self::assertTrue($service->saveNotes($feedback->id, $admin, 'Đã kiểm tra và xử lý.'));
        $this->assertDatabaseHas('feedbacks', [
            'id' => $feedback->id,
            'status' => 'resolved',
            'admin_notes' => 'Đã kiểm tra và xử lý.',
        ]);
    }

    public function test_feedback_management_cannot_cross_community(): void
    {
        $admin = $this->userWithRole('admin');
        $otherBrand = $this->otherBrand();
        $feedback = Feedback::create([
            'brand_id' => $otherBrand->id,
            'user_id' => User::factory()->create()->id,
            'type' => 'khieu_nai',
            'subject' => 'Ngoài community',
            'content' => 'Nội dung khiếu nại của community khác.',
        ]);

        $this->expectException(ModelNotFoundException::class);
        app(CommunityFeedbackService::class)->delete($feedback->id, $admin);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->brandRoles()->attach(brand()->id, ['role' => $role]);

        return $user;
    }

    private function otherBrand(): Brand
    {
        return Brand::create([
            'name' => 'Other Feedback Community',
            'slug' => 'other-feedback-community',
            'domain' => 'other-feedback-community.test',
            'theme_primary' => '#1F77BE',
            'theme_accent' => '#DCECF7',
            'theme_bg' => '#F8FAFC',
            'registration_mode' => 'invite',
            'is_invite_only' => true,
        ]);
    }
}
