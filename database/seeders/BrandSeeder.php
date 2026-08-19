<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        Brand::query()->updateOrCreate(['id' => 1], [
            'name' => config('app.name', 'DSCons AI-BIM Lab'),
            'slug' => 'dscons-ai-bim-lab',
            'domain' => env('BRAND_DOMAIN', parse_url(config('app.url'), PHP_URL_HOST) ?: 'localhost'),
            'tagline' => 'Chuyển hóa kỹ sư MEP thành AI-BIM Engineer & BIM Developer',
            'theme_primary' => env('BRAND_PRIMARY', '#0F3D5E'),
            'theme_accent' => env('BRAND_ACCENT', '#DCECF7'),
            'theme_bg' => env('BRAND_BACKGROUND', '#F4F7F9'),
            'has_expeditions' => true,
            'has_academy' => true,
            'has_marketplace' => true,
            'has_qa' => true,
            'is_invite_only' => true,
            'registration_mode' => 'invite',
        ]);
    }
}
