<?php

namespace Tests\Feature;

use App\Models\Brand;
use Database\Seeders\QaSampleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class QaSampleSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_qa_sample_seeder_is_idempotent_and_uses_test_markers(): void
    {
        $seeder = app(QaSampleSeeder::class);
        $seeder->run();

        $firstCounts = $this->sampleCounts();

        $seeder->run();

        $this->assertSame($firstCounts, $this->sampleCounts());
        $this->assertSame(0, DB::table('users')->where('email', 'qa-member@example.test')->whereNotNull('password')->count());
    }

    public function test_qa_sample_seeder_bootstraps_dscons_when_no_community_exists(): void
    {
        app()->forgetInstance('brand');
        Brand::query()->delete();

        app(QaSampleSeeder::class)->run();

        $this->assertDatabaseHas('brands', ['slug' => 'dscons']);
        $this->assertDatabaseHas('users', ['email' => 'qa-member@example.test']);
    }

    private function sampleCounts(): array
    {
        return [
            'users' => DB::table('users')->where('source', 'qa-test')->count(),
            'posts' => DB::table('posts')->where('title', 'like', '[TEST]%')->count(),
            'questions' => DB::table('questions')->where('title', 'like', '[TEST]%')->count(),
            'courses' => DB::table('courses')->where('title', 'like', '[TEST]%')->count(),
            'events' => DB::table('events')->where('title', 'like', '[TEST]%')->count(),
            'expeditions' => DB::table('expeditions')->where('title', 'like', '[TEST]%')->count(),
            'products' => DB::table('digital_products')->where('title', 'like', '[TEST]%')->count(),
            'xp_transactions' => DB::table('xp_transactions')->where('description', '[TEST] QA leaderboard seed')->count(),
        ];
    }
}
