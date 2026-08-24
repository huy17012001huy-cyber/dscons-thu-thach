<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        Brand::query()->updateOrCreate(['id' => 1], [
            'name' => config('app.name', 'DSCons'),
            'slug' => 'dscons',
            'domain' => env('BRAND_DOMAIN', parse_url(config('app.url'), PHP_URL_HOST) ?: 'localhost'),
            'tagline' => 'Nền tảng học tập, thử thách và cộng đồng dành cho kỹ sư MEP',
            'theme_primary' => env('BRAND_PRIMARY') ?: '#1F77BE',
            'theme_accent' => env('BRAND_ACCENT') ?: '#E1F4F7',
            'theme_bg' => env('BRAND_BACKGROUND') ?: '#F7FAFC',
            'has_expeditions' => true,
            'has_academy' => true,
            'has_marketplace' => true,
            'has_qa' => true,
            'has_cv' => true,
            'has_recruitment' => true,
            'is_invite_only' => true,
            'registration_mode' => 'invite',
        ]);
    }
}
