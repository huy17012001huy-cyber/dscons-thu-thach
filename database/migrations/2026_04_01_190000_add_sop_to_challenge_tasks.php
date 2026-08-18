<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('challenge_tasks', function (Blueprint $table) {
            $table->text('sop_content')->nullable()->after('description');
            $table->string('video_url')->nullable()->after('sop_content');
            $table->string('evidence_type')->default('text')->after('video_url'); // text, screenshot, link, api
            $table->string('evidence_label')->nullable()->after('evidence_type');
        });

        // Add evidence text to completions
        Schema::table('challenge_task_completions', function (Blueprint $table) {
            $table->text('evidence')->nullable()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('challenge_tasks', function (Blueprint $table) {
            $table->dropColumn(['sop_content', 'video_url', 'evidence_type', 'evidence_label']);
        });
        Schema::table('challenge_task_completions', function (Blueprint $table) {
            $table->dropColumn('evidence');
        });
    }
};
