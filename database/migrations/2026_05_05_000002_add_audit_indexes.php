<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'posts_user_created_idx');
            $table->index(['pillar', 'created_at'], 'posts_pillar_created_idx');
        });

        Schema::table('challenge_task_completions', function (Blueprint $table) {
            $table->index('status', 'cttc_status_idx');
            $table->index(['user_id', 'status'], 'cttc_user_status_idx');
        });

        Schema::table('expedition_members', function (Blueprint $table) {
            $table->index('status', 'expedition_members_status_idx');
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->index('status', 'questions_status_idx');
            $table->index(['user_id', 'created_at'], 'questions_user_created_idx');
        });

        Schema::table('xp_transactions', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'xp_transactions_user_created_idx');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex('posts_user_created_idx');
            $table->dropIndex('posts_pillar_created_idx');
        });

        Schema::table('challenge_task_completions', function (Blueprint $table) {
            $table->dropIndex('cttc_status_idx');
            $table->dropIndex('cttc_user_status_idx');
        });

        Schema::table('expedition_members', function (Blueprint $table) {
            $table->dropIndex('expedition_members_status_idx');
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropIndex('questions_status_idx');
            $table->dropIndex('questions_user_created_idx');
        });

        Schema::table('xp_transactions', function (Blueprint $table) {
            $table->dropIndex('xp_transactions_user_created_idx');
        });
    }
};
