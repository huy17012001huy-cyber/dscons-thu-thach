<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_challenges', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('target_type', ['post_count', 'comment_count', 'expedition_checkin']);
            $table->unsignedInteger('target_value');
            $table->unsignedInteger('current_value')->default(0);
            $table->unsignedInteger('reward_xp')->default(75);
            $table->unsignedInteger('reward_aip')->default(0);
            $table->date('week_start');
            $table->date('week_end');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('pillar_stats', function (Blueprint $table) {
            $table->id();
            $table->enum('pillar', ['offer', 'traffic', 'conversion', 'delivery', 'continuity']);
            $table->unsignedInteger('post_count_7d')->default(0);
            $table->decimal('post_pct', 5, 2)->default(0);
            $table->boolean('is_burning')->default(false);
            $table->timestamp('burning_started_at')->nullable();
            $table->timestamp('last_calculated_at')->nullable();
            $table->timestamps();
            $table->unique('pillar');
        });

        Schema::create('leaderboard_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('period', ['week', 'month', 'alltime']);
            $table->string('period_key'); // '2025-W12' or '2025-03' or 'all'
            $table->unsignedBigInteger('xp_earned')->default(0);
            $table->unsignedInteger('rank')->nullable();
            $table->integer('rank_change')->default(0);
            $table->date('snapshot_date');
            $table->unique(['user_id', 'period', 'period_key']);
        });

        Schema::create('affiliate_earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('referred_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('membership_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('amount');
            $table->decimal('commission_rate', 4, 2)->default(0.20);
            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('affiliate_earnings');
        Schema::dropIfExists('leaderboard_snapshots');
        Schema::dropIfExists('pillar_stats');
        Schema::dropIfExists('community_challenges');
    }
};
