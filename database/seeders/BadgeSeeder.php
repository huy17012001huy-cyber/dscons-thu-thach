<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            ['name' => 'Tân binh', 'description' => 'Chào mừng đến Website Thử Thách', 'icon' => '🌱', 'rarity' => 'common', 'condition_type' => 'level_gte', 'condition_value' => '1'],
            ['name' => 'Cây bút', 'description' => 'Đăng bài viết đầu tiên', 'icon' => '✍️', 'rarity' => 'common', 'condition_type' => 'post_count_gte', 'condition_value' => '1'],
            ['name' => 'Nhà văn', 'description' => 'Đăng 10 bài viết', 'icon' => '📝', 'rarity' => 'rare', 'condition_type' => 'post_count_gte', 'condition_value' => '10'],
            ['name' => 'Bình luận gia', 'description' => 'Viết 10 bình luận', 'icon' => '💬', 'rarity' => 'common', 'condition_type' => 'comment_count_gte', 'condition_value' => '10'],
            ['name' => 'Thảo luận viên', 'description' => 'Viết 50 bình luận', 'icon' => '🗣️', 'rarity' => 'rare', 'condition_type' => 'comment_count_gte', 'condition_value' => '50'],
            ['name' => 'Thuyền trưởng', 'description' => 'Tạo một Challenge', 'icon' => '⚔️', 'rarity' => 'rare', 'condition_type' => 'expedition_created', 'condition_value' => '1'],
            ['name' => 'Streak 7', 'description' => 'Duy trì streak 7 ngày', 'icon' => '🔥', 'rarity' => 'common', 'condition_type' => 'streak_gte', 'condition_value' => '7'],
            ['name' => 'Streak 30', 'description' => 'Duy trì streak 30 ngày', 'icon' => '🔥🔥', 'rarity' => 'rare', 'condition_type' => 'streak_gte', 'condition_value' => '30'],
            ['name' => 'Streak 90', 'description' => 'Duy trì streak 90 ngày', 'icon' => '🔥🔥🔥', 'rarity' => 'epic', 'condition_type' => 'streak_gte', 'condition_value' => '90'],
            ['name' => 'GD1 — Freelancer', 'description' => 'Đạt Level 10', 'icon' => '⭐', 'rarity' => 'common', 'condition_type' => 'level_gte', 'condition_value' => '10'],
            ['name' => 'GD2 — Growing', 'description' => 'Đạt Level 30', 'icon' => '⭐⭐', 'rarity' => 'rare', 'condition_type' => 'level_gte', 'condition_value' => '30'],
            ['name' => 'GD3 — Chuyên gia', 'description' => 'Đạt Level 60', 'icon' => '⭐⭐⭐', 'rarity' => 'epic', 'condition_type' => 'level_gte', 'condition_value' => '60'],
            ['name' => 'GD4 — Business Owner', 'description' => 'Đạt Level 100', 'icon' => '👑', 'rarity' => 'legendary', 'condition_type' => 'level_gte', 'condition_value' => '100'],
            ['name' => 'Collector', 'description' => 'Lưu 50 bookmark', 'icon' => '📚', 'rarity' => 'rare', 'condition_type' => 'bookmark_count_gte', 'condition_value' => '50'],
            ['name' => 'Đá Không Cực', 'description' => 'Sở hữu viên đá đầu tiên', 'icon' => '💎', 'rarity' => 'legendary', 'condition_type' => 'da_count_gte', 'condition_value' => '1'],
        ];

        foreach ($badges as $data) {
            Badge::updateOrCreate(['name' => $data['name']], $data);
        }
    }
}
