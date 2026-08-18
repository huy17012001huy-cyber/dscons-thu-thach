<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('expedition_members', function (Blueprint $table) {
            $table->text('video_feedback_url')->nullable();
            $table->string('video_feedback_status')->nullable(); // pending, approved, rejected
            $table->text('video_feedback_note')->nullable();
            $table->timestamp('video_feedback_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('expedition_members', function (Blueprint $table) {
            $table->dropColumn(['video_feedback_url', 'video_feedback_status', 'video_feedback_note', 'video_feedback_at']);
        });
    }
};
