<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-user attempt at one quiz question on a challenge task.
 * UNIQUE(user, task, question_index) → upsert latest answer when user retries.
 * `xp_awarded` flag prevents double-awarding XP if a user retries a previously-correct answer.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_attempts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('challenge_task_id')->constrained()->cascadeOnDelete();
            $t->smallInteger('question_index');
            $t->char('selected_letter', 1);
            $t->boolean('is_correct');
            $t->boolean('xp_awarded')->default(false);
            $t->timestamp('answered_at')->useCurrent();

            $t->unique(['user_id', 'challenge_task_id', 'question_index'], 'quiz_attempts_unique');
            $t->index(['challenge_task_id', 'user_id'], 'quiz_attempts_task_user_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
    }
};
