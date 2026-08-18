<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('xp_transactions', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('created_at');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('pillar');
            $table->index('is_cot');
            $table->index('is_signal');
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->index('post_id');
            $table->index('user_id');
        });

        Schema::table('expedition_members', function (Blueprint $table) {
            $table->index('user_id');
        });

        Schema::table('expedition_checkins', function (Blueprint $table) {
            $table->index(['expedition_id', 'user_id', 'created_at']);
        });

        Schema::table('aip_transactions', function (Blueprint $table) {
            $table->index('user_id');
        });

        Schema::table('bookmarks', function (Blueprint $table) {
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('xp_transactions', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['pillar']);
            $table->dropIndex(['is_cot']);
            $table->dropIndex(['is_signal']);
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex(['post_id']);
            $table->dropIndex(['user_id']);
        });

        Schema::table('expedition_members', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });

        Schema::table('expedition_checkins', function (Blueprint $table) {
            $table->dropIndex(['expedition_id', 'user_id', 'created_at']);
        });

        Schema::table('aip_transactions', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });

        Schema::table('bookmarks', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });
    }
};
