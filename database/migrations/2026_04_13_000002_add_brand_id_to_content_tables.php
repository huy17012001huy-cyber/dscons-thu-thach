<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tables that get a direct brand_id column.
     * Excludes child tables accessed only through their parent
     * (modules, lessons, lesson_tasks, lesson_prerequisites, challenge_tasks,
     *  post_attachments, post_images, challenge_task_completions, submission_votes).
     */
    private array $tables = [
        'posts',
        'comments',
        'topics',
        'likes',
        'bookmarks',
        'reports',
        'questions',
        'answers',
        'courses',
        'course_enrollments',
        'lesson_progress',
        'task_submissions',
        'expeditions',
        'expedition_members',
        'expedition_checkins',
        'digital_products',
        'product_purchases',
        'conversations',
        'direct_messages',
        'pillar_stats',
        'leaderboard_snapshots',
        'community_challenges',
        'affiliate_earnings',
        'memberships',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->foreignId('brand_id')->default(1)->constrained('brands');
                $blueprint->index('brand_id');
            });
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->tables) as $table) {
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                $blueprint->dropForeign([$table.'_brand_id_foreign']);
                $blueprint->dropColumn('brand_id');
            });
        }
    }
};
