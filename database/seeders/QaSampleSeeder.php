<?php

namespace Database\Seeders;

use App\Models\Answer;
use App\Models\ChallengeTask;
use App\Models\CommunityUserStat;
use App\Models\Course;
use App\Models\DigitalProduct;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Expedition;
use App\Models\ExpeditionMember;
use App\Models\LeaderboardSnapshot;
use App\Models\Membership;
use App\Models\Module;
use App\Models\Post;
use App\Models\ProductPurchase;
use App\Models\Question;
use App\Models\Topic;
use App\Models\User;
use App\Models\XpTransaction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class QaSampleSeeder extends Seeder
{
    public function run(): void
    {
        if (!app()->environment(['local', 'staging', 'testing'])) {
            throw new \RuntimeException('QaSampleSeeder chỉ được chạy ở local, staging hoặc testing.');
        }

        $brand = app()->bound('brand') ? brand() : \App\Models\Brand::query()->findOrFail(1);
        app()->instance('brand', $brand);

        $admin = $this->user('qa-admin@example.test', '[TEST] QA Admin', 'qa-admin', true);
        $member = $this->user('qa-member@example.test', '[TEST] QA Member', 'qa-member');
        $guest = $this->user('qa-guest@example.test', '[TEST] QA Guest', 'qa-guest');
        $unverified = $this->user('qa-unverified@example.test', '[TEST] QA Unverified', 'qa-unverified', false, false);
        $banned = $this->user('qa-banned@example.test', '[TEST] QA Banned', 'qa-banned');
        $this->membership($brand->id, $admin, 'active');
        $this->membership($brand->id, $member, 'active');
        $this->membership($brand->id, $guest, 'expired', now()->subDay());
        $this->membership($brand->id, $unverified, 'active');
        $this->membership($brand->id, $banned, 'banned');

        $topic = Topic::withoutGlobalScopes()->updateOrCreate(
            ['brand_id' => $brand->id, 'slug' => 'test-qa-topic'],
            ['name' => '[TEST] QA Topic', 'emoji' => '🧪', 'sort_order' => 999, 'is_active' => true]
        );

        Post::withoutGlobalScopes()->updateOrCreate(
            ['brand_id' => $brand->id, 'title' => '[TEST] Bài viết thường'],
            [
                'user_id' => $member->id,
                'content' => "[TEST] Nội dung bài viết thường với **định dạng** và [liên kết](https://example.com).",
                'pillar' => 'offer',
                'topic_id' => $topic->id,
            ]
        );
        Post::withoutGlobalScopes()->updateOrCreate(
            ['brand_id' => $brand->id, 'title' => '[TEST] Bài CỐT'],
            [
                'user_id' => $admin->id,
                'content' => '[TEST] Nội dung CỐT để kiểm tra tab tuyển chọn.',
                'pillar' => 'conversion',
                'is_cot' => true,
                'cot_at' => now(),
                'cot_by' => $admin->id,
            ]
        );
        Post::withoutGlobalScopes()->updateOrCreate(
            ['brand_id' => $brand->id, 'title' => '[TEST] Tín hiệu ngắn'],
            [
                'user_id' => $member->id,
                'content' => '[TEST] Tín hiệu ngắn để kiểm tra filter signal.',
                'pillar' => 'traffic',
                'is_signal' => true,
            ]
        );

        $question = Question::withoutGlobalScopes()->updateOrCreate(
            ['brand_id' => $brand->id, 'title' => '[TEST] Câu hỏi kỹ thuật'],
            [
                'user_id' => $member->id,
                'body' => '[TEST] Làm sao kiểm tra clash trong mô hình Revit?',
                'pillar' => 'delivery',
                'status' => 'open',
                'is_anonymous' => false,
                'is_paid' => false,
                'paid_aip_amount' => 0,
            ]
        );
        Answer::withoutGlobalScopes()->updateOrCreate(
            ['question_id' => $question->id, 'user_id' => $admin->id, 'brand_id' => $brand->id],
            ['body' => '[TEST] Câu trả lời kỹ thuật mẫu.', 'is_best' => true]
        );
        DB::table('notifications')->updateOrInsert(
            ['id' => '00000000-0000-0000-0000-000000000001'],
            [
                'type' => 'qa-test-notification',
                'notifiable_type' => User::class,
                'notifiable_id' => $member->id,
                'data' => json_encode(['title' => '[TEST] Thông báo mẫu', 'message' => '[TEST] Bạn có một thông báo để kiểm tra.']),
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $course = Course::withoutGlobalScopes()->updateOrCreate(
            ['brand_id' => $brand->id, 'title' => '[TEST] Khóa học Revit cơ bản'],
            [
                'description' => '[TEST] Khóa học published dùng cho QA catalog.',
                'pillar' => 'delivery',
                'difficulty' => 'basic',
                'min_level' => 1,
                'xp_reward' => 50,
                'aip_reward' => 10,
                'price' => 0,
                'is_published' => true,
                'access_tier' => 'free',
            ]
        );
        $module = Module::updateOrCreate(
            ['course_id' => $course->id, 'title' => '[TEST] Module mẫu'],
            ['order_index' => 0]
        );
        \App\Models\Lesson::updateOrCreate(
            ['module_id' => $module->id, 'title' => '[TEST] Bài học mẫu'],
            [
                'content' => '[TEST] Nội dung bài học mẫu.',
                'lesson_type' => 'article',
                'xp_reward' => 10,
                'aip_reward' => 2,
                'duration_minutes' => 15,
                'order_index' => 0,
            ]
        );

        $challenge = Expedition::withoutGlobalScopes()->updateOrCreate(
            ['brand_id' => $brand->id, 'slug' => 'test-challenge-revit'],
            [
                'title' => '[TEST] Challenge Revit 21 ngày',
                'description' => '[TEST] Challenge active để kiểm tra task unlock và trạng thái tham gia.',
                'boss_name' => '[TEST] Revit Boss',
                'difficulty' => 'hard',
                'required_days' => 21,
                'max_members' => 50,
                'created_by' => $admin->id,
                'leader_id' => $admin->id,
                'status' => 'active',
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addDays(20),
                'is_featured' => true,
            ]
        );
        ChallengeTask::updateOrCreate(
            ['expedition_id' => $challenge->id, 'day_number' => 1],
            [
                'label' => '[TEST] Day 1',
                'title' => '[TEST] Task Revit mẫu',
                'description' => '[TEST] Task mở để QA.',
                'sop_content' => '[TEST] SOP mẫu',
                'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'evidence_type' => 'text',
                'evidence_label' => '[TEST] Evidence',
                'duration_hours' => 24,
                'quiz_json' => [['q' => '[TEST] Câu hỏi quiz?', 'options' => ['A', 'B'], 'answer' => 0]],
            ]
        );
        ExpeditionMember::updateOrCreate(
            ['expedition_id' => $challenge->id, 'user_id' => $member->id],
            [
                'class_at_join' => 'offer_architect',
                'joined_at' => now()->subHours(2),
                'personal_starts_at' => now()->subHours(2),
                'status' => 'approved',
                'approved_at' => now(),
            ]
        );

        $event = Event::withoutGlobalScopes()->updateOrCreate(
            ['slug' => 'test-active-event'],
            [
                'brand_id' => $brand->id,
                'course_id' => $course->id,
                'created_by' => $admin->id,
                'title' => '[TEST] Sự kiện Revit live',
                'description' => '[TEST] Event active có đăng ký.',
                'event_type' => 'live',
                'format' => 'online',
                'starts_at' => now()->addDay(),
                'ends_at' => now()->addDay()->addHour(),
                'meeting_url' => 'https://meet.example.test/test',
                'capacity' => 50,
                'status' => 'published',
            ]
        );
        EventRegistration::withoutGlobalScopes()->updateOrCreate(
            ['event_id' => $event->id, 'user_id' => $member->id],
            ['brand_id' => $brand->id, 'status' => 'registered', 'registered_at' => now()]
        );

        $product = DigitalProduct::withoutGlobalScopes()->updateOrCreate(
            ['brand_id' => $brand->id, 'title' => '[TEST] Tài nguyên Revit miễn phí'],
            [
                'description' => '[TEST] Resource free cho marketplace QA.',
                'pillar' => 'delivery',
                'price' => 0,
                'delivery_type' => 'link',
                'access_url' => 'https://example.com/test-resource',
                'is_published' => true,
                'is_featured' => true,
            ]
        );
        ProductPurchase::withoutGlobalScopes()->updateOrCreate(
            ['user_id' => $member->id, 'digital_product_id' => $product->id],
            ['brand_id' => $brand->id, 'status' => 'active', 'amount_paid' => 0, 'paid_at' => now()]
        );

        foreach ([[$admin, 900], [$member, 450], [$guest, 100]] as [$user, $xp]) {
            CommunityUserStat::withoutGlobalScopes()->updateOrCreate(
                ['brand_id' => $brand->id, 'user_id' => $user->id],
                ['xp' => $xp, 'level' => max(1, (int) floor($xp / 100)), 'aip' => 20, 'streak' => 3, 'last_active_at' => now()]
            );
            LeaderboardSnapshot::withoutGlobalScopes()->updateOrCreate(
                ['brand_id' => $brand->id, 'user_id' => $user->id, 'period' => 'week', 'period_key' => 'test-week'],
                ['xp_earned' => $xp, 'rank' => $xp === 900 ? 1 : ($xp === 450 ? 2 : 3), 'rank_change' => 0, 'snapshot_date' => now()->toDateString()]
            );
            XpTransaction::withoutGlobalScopes()->updateOrCreate(
                ['brand_id' => $brand->id, 'user_id' => $user->id, 'type' => 'qa-seed', 'description' => '[TEST] QA leaderboard seed'],
                ['amount' => $xp, 'multiplier' => 1]
            );
        }

        foreach (['week', 'month', 'alltime', 'da'] as $period) {
            Cache::forget('leaderboard:'.$brand->id.':'.$period);
        }
    }

    private function user(string $email, string $name, string $username, bool $admin = false, bool $verified = true): User
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => $name, 'username' => $username, 'password' => Hash::make('test-only-not-for-login')]
        );
        $user->forceFill([
            'name' => $name,
            'username' => $username,
            'email_verified_at' => $verified ? now() : null,
            'password' => null,
            'is_admin' => $admin,
            'class' => 'offer_architect',
            'source' => 'qa-test',
        ])->save();

        return $user->fresh();
    }

    private function membership(int $brandId, User $user, string $status, $expiresAt = null): Membership
    {
        return Membership::withoutGlobalScopes()->updateOrCreate(
            ['brand_id' => $brandId, 'user_id' => $user->id],
            [
                'status' => $status,
                'plan' => 'qa-test',
                'starts_at' => now()->subDay(),
                'expires_at' => $expiresAt ?: now()->addYear(),
            ]
        );
    }
}
