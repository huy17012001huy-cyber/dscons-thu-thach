<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\RecruiterPlan;
use Illuminate\Database\Seeder;

class RecruiterPlansSeeder extends Seeder
{
    public function run(): void
    {
        $brand = Brand::query()->where('slug', 'dscons')->firstOrFail();

        $plans = [
            [
                'name' => 'Starter',
                'description' => 'Phù hợp cho một đợt tuyển dụng nhỏ hoặc dùng thử Talent.',
                'contact_credits' => 5,
                'duration_days' => 30,
                'price' => 490000,
            ],
            [
                'name' => 'Growth',
                'description' => 'Tìm kiếm đều đặn cho đội BIM/MEP đang mở rộng.',
                'contact_credits' => 15,
                'duration_days' => 60,
                'price' => 1290000,
            ],
            [
                'name' => 'Professional',
                'description' => 'Dành cho doanh nghiệp có nhiều vị trí cần tuyển trong quý.',
                'contact_credits' => 40,
                'duration_days' => 90,
                'price' => 2990000,
            ],
            [
                'name' => 'Enterprise',
                'description' => 'Gói lớn cho đội tuyển dụng và nhiều dự án song song.',
                'contact_credits' => 100,
                'duration_days' => 180,
                'price' => 5990000,
            ],
        ];

        foreach ($plans as $plan) {
            RecruiterPlan::updateOrCreate(
                ['brand_id' => $brand->id, 'name' => $plan['name']],
                [...$plan, 'brand_id' => $brand->id, 'is_active' => true]
            );
        }
    }
}
