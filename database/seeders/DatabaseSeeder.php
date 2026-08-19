<?php

namespace Database\Seeders;

use App\Models\Membership;
use App\Models\Brand;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /** Seeds only neutral data; commercial and member data is created in the admin UI. */
    public function run(): void
    {
        $this->call(BrandSeeder::class);
        app()->instance('brand', Brand::query()->findOrFail(1));
        $this->call([TopicSeeder::class, BadgeSeeder::class]);

        foreach ([
            ['membership_price_current', '0', 'Giá membership hiện tại (VNĐ)'],
            ['escalate_every_n_members', '50', 'Tăng giá mỗi N thành viên mới'],
            ['escalate_amount', '100000', 'Số tiền tăng mỗi bậc (VNĐ)'],
            ['free_questions_per_month', '3', 'Số câu hỏi miễn phí mỗi tháng'],
            ['deposit_chaos_aip', '300', 'AIP deposit cho Challenge Chaos'],
            ['burning_zone_threshold_pct', '15', 'Ngưỡng % kích hoạt Burning Zone'],
            ['burning_zone_bonus_pct', '50', 'Bonus XP Burning Zone (%)'],
            ['affiliate_default_rate', '0.20', 'Hoa hồng affiliate mặc định'],
            ['affiliate_elite_rate', '0.25', 'Hoa hồng affiliate elite'],
            ['weekly_challenge_reward_xp', '75', 'XP thưởng Community Challenge'],
        ] as [$key, $value, $description]) {
            Setting::updateOrCreate(['key' => $key], compact('value', 'description'));
        }

        $email = env('SEED_ADMIN_EMAIL', 'admin@example.test');
        $password = env('SEED_ADMIN_PASSWORD');
        if (blank($password)) {
            throw new \RuntimeException('Set SEED_ADMIN_PASSWORD before running the database seeder.');
        }

        $admin = User::firstOrNew(['email' => $email]);
        $admin->forceFill([
            'name' => 'Quản trị viên',
            'username' => 'admin',
            'password' => Hash::make($password),
            'class' => 'continuity_captain',
            'level' => 1,
            'xp' => 0,
            'aip' => 0,
            'streak' => 0,
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);
        $admin->save();

        Membership::updateOrCreate(['user_id' => $admin->id], [
            'status' => 'active',
            'starts_at' => now(),
            'expires_at' => now()->addYear(),
        ]);
    }
}
