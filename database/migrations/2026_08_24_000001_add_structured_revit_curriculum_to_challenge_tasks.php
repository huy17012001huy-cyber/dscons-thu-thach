<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('challenge_tasks', function (Blueprint $table): void {
            $table->jsonb('instruction_payload')->nullable()->after('quiz_json');
        });

        Schema::table('challenge_task_completions', function (Blueprint $table): void {
            $table->jsonb('submission_payload')->nullable()->after('evidence');
            $table->unsignedSmallInteger('score')->nullable()->after('review_note');
            $table->string('auto_review_status')->nullable()->after('score');
            $table->jsonb('auto_review_payload')->nullable()->after('auto_review_status');
            $table->jsonb('rubric_payload')->nullable()->after('auto_review_payload');
        });
    }

    public function down(): void
    {
        Schema::table('challenge_task_completions', function (Blueprint $table): void {
            $table->dropColumn([
                'submission_payload', 'score', 'auto_review_status',
                'auto_review_payload', 'rubric_payload',
            ]);
        });

        Schema::table('challenge_tasks', function (Blueprint $table): void {
            $table->dropColumn('instruction_payload');
        });
    }
};
