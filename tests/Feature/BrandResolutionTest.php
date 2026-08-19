<?php

namespace Tests\Feature;

use App\Models\Brand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandResolutionTest extends TestCase
{
    use RefreshDatabase;

    public function test_local_brand_is_available_on_the_login_page(): void
    {
        $this->get('/login', ['Host' => 'localhost'])
            ->assertOk()
            ->assertSee('Website Thử Thách');
    }

    public function test_request_host_selects_its_brand_configuration(): void
    {
        Brand::create([
            'name' => 'Staging Challenge',
            'slug' => 'staging-challenge',
            'domain' => 'staging.challenge.test',
            'tagline' => 'Staging',
            'theme_primary' => '#2563EB',
            'theme_accent' => '#DBEAFE',
            'theme_bg' => '#F8FAFC',
            'registration_mode' => 'invite',
            'is_invite_only' => true,
        ]);

        $this->get('/login', ['Host' => 'staging.challenge.test'])
            ->assertOk()
            ->assertSee('Staging Challenge');
    }

    public function test_public_registration_remains_closed(): void
    {
        $this->get('/register', ['Host' => 'localhost'])
            ->assertRedirect(route('login'));
    }
}
