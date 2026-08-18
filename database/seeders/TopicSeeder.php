<?php

namespace Database\Seeders;

use App\Models\Topic;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TopicSeeder extends Seeder
{
    public function run(): void
    {
        $topics = [
            ['name' => 'Case Study',    'emoji' => '🔥', 'sort_order' => 1],
            ['name' => 'Win',           'emoji' => '🏆', 'sort_order' => 2],
            ['name' => 'Hỏi đáp',      'emoji' => '❓', 'sort_order' => 3],
            ['name' => 'Resource',      'emoji' => '📚', 'sort_order' => 4],
            ['name' => 'Giới thiệu',    'emoji' => '👋', 'sort_order' => 5],
            ['name' => 'Insight',       'emoji' => '💡', 'sort_order' => 6],
            ['name' => 'Chiến lược',    'emoji' => '♟️', 'sort_order' => 7],
            ['name' => 'Khác',          'emoji' => '📌', 'sort_order' => 8],
        ];

        foreach ($topics as $topic) {
            Topic::firstOrCreate(
                ['slug' => Str::slug($topic['name'])],
                $topic + ['slug' => Str::slug($topic['name'])]
            );
        }
    }
}
