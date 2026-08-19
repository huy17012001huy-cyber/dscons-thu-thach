<?php

namespace Tests;

use App\Models\Brand;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!\Schema::hasTable('brands')) {
            return;
        }

        $brand = Brand::query()->firstOrCreate(['id' => 1], [
            'name' => 'Website Thử Thách',
            'slug' => 'website-thu-thach',
            'domain' => 'localhost',
            'tagline' => 'Học tập · Thử thách · Cộng đồng',
            'theme_primary' => '#2563EB',
            'theme_accent' => '#DBEAFE',
            'theme_bg' => '#F8FAFC',
            'registration_mode' => 'invite',
            'is_invite_only' => true,
        ]);

        app()->instance('brand', $brand);
    }
}
