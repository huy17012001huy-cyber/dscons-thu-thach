<?php

namespace Database\Seeders;

use App\Models\CommunityChallenge;
use App\Models\DaKhongCuc;
use App\Models\Expedition;
use App\Models\ExpeditionMember;
use App\Models\Membership;
use App\Models\PillarStat;
use App\Models\Post;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Settings ────────────────────────────────────────────────
        $settings = [
            ['membership_price_current',     '1500000',  'Giá membership hiện tại (VNĐ)'],
            ['escalate_every_n_members',     '50',       'Tăng giá mỗi N member mới'],
            ['escalate_amount',              '100000',   'Số tiền tăng mỗi bậc (VNĐ)'],
            ['free_questions_per_month',     '3',        'Số câu hỏi miễn phí/tháng'],
            ['deposit_chaos_aip',            '300',      'AIP deposit cho Chaos challenge'],
            ['burning_zone_threshold_pct',   '15',       'Ngưỡng % để kích hoạt Burning Zone'],
            ['burning_zone_bonus_pct',       '50',       'Bonus XP khi Burning Zone (%)'],
            ['affiliate_default_rate',       '0.20',     'Hoa hồng mặc định affiliate'],
            ['affiliate_elite_rate',         '0.25',     'Hoa hồng All In Elite'],
            ['weekly_challenge_reward_xp',   '75',       'XP reward Community Challenge'],
        ];
        foreach ($settings as [$key, $value, $desc]) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value, 'description' => $desc]);
        }

        // ─── Admin user ───────────────────────────────────────────────
        // Email lấy theo domain trong APP_URL để mỗi bản deploy có admin riêng.
        // APP_URL còn là localhost (hoặc không có domain) → dùng example.com.
        $host = preg_replace('/^www\./', '', (string) parse_url((string) config('app.url'), PHP_URL_HOST));
        $adminEmail = 'admin@' . (str_contains($host, '.') ? $host : 'example.com');

        $admin = User::updateOrCreate(['email' => $adminEmail], [
            'name'       => 'Admin',
            'username'   => 'admin',
            'password'   => Hash::make('password'),
            'class'      => 'continuity_captain',
            'level'      => 100,
            'xp'         => 5000000,
            'aip'        => 10000,
            'streak'     => 90,
            'is_admin'   => true,
        ]);
        Membership::updateOrCreate(['user_id' => $admin->id], [
            'status'     => 'active',
            'starts_at'  => now()->subYear(),
            'expires_at' => now()->addYear(),
        ]);
        DaKhongCuc::updateOrCreate(['user_id' => $admin->id], ['total_count' => 12]);

        // ─── Demo members ─────────────────────────────────────────────
        $demoMembers = [
            ['Nguyễn Minh Khoa', 'khoa',    'offer_architect',    45, 150000,  2000],
            ['Trần Thị Lan',     'lan',     'traffic_mage',       32, 95000,   1500],
            ['Lê Văn Dũng',      'dung',    'conversion_ranger',  28, 80000,   800],
            ['Phạm Thu Hà',      'ha',      'delivery_assassin',  20, 45000,   500],
            ['Vũ Đức Thành',     'thanh',   'continuity_captain', 15, 25000,   300],
            ['Đặng Hải Yến',     'yen',     'offer_architect',    38, 120000,  1200],
            ['Bùi Quang Nam',    'nam',     'traffic_mage',       55, 280000,  3000],
            ['Hoàng Anh Tuấn',   'tuan',    'conversion_ranger',  12, 18000,   200],
            ['Ngô Thị Bích',     'bich',    'delivery_assassin',  7,  8000,    100],
            ['Lý Minh Phúc',     'phuc',    'continuity_captain', 65, 450000,  5000],
        ];

        $users = [];
        foreach ($demoMembers as [$name, $username, $class, $level, $xp, $aip]) {
            $user = User::updateOrCreate(['username' => $username], [
                'name'     => $name,
                'email'    => $username . '@demo.com',
                'password' => Hash::make('password'),
                'class'    => $class,
                'level'    => $level,
                'xp'       => $xp,
                'aip'      => $aip,
                'streak'   => rand(0, 45),
            ]);
            Membership::updateOrCreate(['user_id' => $user->id], [
                'status'     => 'active',
                'starts_at'  => now()->subMonths(rand(1, 11)),
                'expires_at' => now()->addMonths(rand(1, 12)),
            ]);
            $users[] = $user;
        }

        // Give some users Da Khong Cuc
        DaKhongCuc::updateOrCreate(['user_id' => $users[0]->id], ['total_count' => 5]);
        DaKhongCuc::updateOrCreate(['user_id' => $users[6]->id], ['total_count' => 3]);
        DaKhongCuc::updateOrCreate(['user_id' => $users[9]->id], ['total_count' => 7]);

        // ─── Sample posts ─────────────────────────────────────────────
        $samplePosts = [
            [$users[0], 'offer', true, false,
                "🔥 CASE STUDY: Tôi đã tăng AOV (Average Order Value) từ 350k lên 890k chỉ trong 3 tuần bằng cách bundle sản phẩm thông minh.\n\nCách làm:\n1. Phân tích top 20% sản phẩm bán chạy nhất\n2. Tạo bundle với tỷ lệ giá trị nhận được / giá trả tăng 3x\n3. Đặt bundle ở checkout step (upsell cuối cùng)\n4. Test 2 versions: Bundle A (3 sản phẩm) vs Bundle B (5 sản phẩm)\n\nKết quả: Bundle B thắng với AOV +154%\n\nTại sao hiệu quả? Vì khách hàng mua 1 rồi, họ đã vượt qua rào cản tâm lý đầu tiên. Chi phí thêm nhỏ nhưng value lớn."],
            [$users[1], 'traffic', false, true,
                "🧭 TÍN HIỆU: LinkedIn Creator Mode đang convert tốt hơn Facebook 3x cho B2B offer.\n\nVừa test với 2 posts tương tự:\n- Facebook: 1,200 reach → 2 leads\n- LinkedIn: 340 reach → 6 leads\n\nCost per lead LinkedIn rẻ hơn 70% với cùng budget content."],
            [$users[6], 'conversion', true, false,
                "🎯 Tôi đã rewrite landing page và tăng CVR từ 1.8% lên 4.3% chỉ bằng 1 thay đổi.\n\nThay đổi đó là: Di chuyển testimonial từ cuối trang lên ngay sau headline.\n\nLogic: Người dùng scan landing page theo hình F. Headline → subheadline → đọc tiếp hay không?\n\nNếu ngay sau subheadline là 'người thật nói tốt' → social proof ngay lập tức → tăng trust → tiếp tục đọc.\n\nBạn đang đặt testimonial ở đâu trên LP của mình?"],
            [$users[9], 'continuity', false, false,
                "Chia sẻ hệ thống onboarding khách hàng mới giúp tôi giảm churn từ 35% xuống 12% trong 6 tháng.\n\nTuần 1: Welcome email sequence (7 emails trong 14 ngày)\nTuần 2: Check-in call 30 phút với CS\nTháng 1: Report kết quả đầu tiên + roadmap\nTháng 3: Milestone celebration + upsell\n\nKey insight: 80% churn xảy ra trong 60 ngày đầu. Nếu bạn giữ được khách qua ngày 60, tỷ lệ họ ở lại 12 tháng tăng từ 40% lên 85%."],
            [$users[3], 'delivery', false, false,
                "⚙️ Chia sẻ stack automation tôi dùng để xử lý 200 đơn/ngày mà không cần thêm nhân sự:\n\n1. Tally.so → nhận form đặt hàng\n2. Make.com → tự động tạo invoice + gửi email xác nhận\n3. Notion DB → tracking đơn hàng\n4. Zapier → trigger WhatsApp khi đơn ready\n5. Google Sheets → báo cáo tự động mỗi sáng\n\nCost: ~$45/tháng. Tiết kiệm 4-5 tiếng/ngày."],
        ];

        foreach ($samplePosts as [$user, $pillar, $isCot, $isSignal, $content]) {
            Post::create([
                'user_id'   => $user->id,
                'content'   => $content,
                'pillar'    => $pillar,
                'is_cot'    => $isCot,
                'cot_at'    => $isCot ? now()->subDays(rand(1, 30)) : null,
                'cot_by'    => $isCot ? $admin->id : null,
                'is_signal' => $isSignal,
                'created_at'=> now()->subHours(rand(1, 200)),
            ]);
        }

        // ─── Pillar Stats ─────────────────────────────────────────────
        $pillars = ['offer' => 25, 'traffic' => 30, 'conversion' => 20, 'delivery' => 15, 'continuity' => 10];
        foreach ($pillars as $pillar => $pct) {
            PillarStat::updateOrCreate(['pillar' => $pillar], [
                'post_count_7d'     => (int)($pct * 2),
                'post_pct'          => $pct,
                'is_burning'        => $pillar === 'continuity',
                'burning_started_at'=> $pillar === 'continuity' ? now() : null,
                'last_calculated_at'=> now(),
            ]);
        }

        // ─── Community Challenge ──────────────────────────────────────
        CommunityChallenge::updateOrCreate(['week_start' => now()->startOfWeek()->toDateString()], [
            'title'        => 'Cả cộng đồng viết 50 bài tuần này',
            'target_type'  => 'post_count',
            'target_value' => 50,
            'current_value'=> Post::where('created_at', '>=', now()->startOfWeek())->count(),
            'reward_xp'    => 75,
            'week_start'   => now()->startOfWeek()->toDateString(),
            'week_end'     => now()->endOfWeek()->toDateString(),
        ]);

        // ─── Sample Expeditions ───────────────────────────────────────
        $expedition = Expedition::firstOrCreate(['title' => 'Chinh phục 100 khách hàng đầu tiên'], [
            'boss_name'    => 'Đạt 100 khách mua sản phẩm trong 30 ngày',
            'description'  => 'Cùng nhau triển khai chiến lược thu hút và convert khách hàng từ đầu.',
            'difficulty'   => 'hard',
            'required_days'=> 30,
            'max_members'  => 8,
            'created_by'   => $users[0]->id,
            'status'       => 'open',
            'deposit_aip'  => 0,
        ]);

        foreach (array_slice($users, 0, 3) as $u) {
            ExpeditionMember::firstOrCreate(
                ['expedition_id' => $expedition->id, 'user_id' => $u->id],
                ['class_at_join' => $u->class, 'joined_at' => now()]
            );
        }

        $this->command->info('✅ Seeder hoàn thành! Admin: ' . $adminEmail . ' / password');
        $this->command->info('✅ Demo members: khoa@demo.com, lan@demo.com, ... / password');
    }
}
