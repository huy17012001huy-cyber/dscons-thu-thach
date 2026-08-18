<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('challenge_task_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('completion_id')->constrained('challenge_task_completions')->cascadeOnDelete();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status'); // approved | rejected
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['completion_id', 'created_at']);
        });

        // Backfill: seed one review row for every completion that already has a reviewed_at
        DB::statement("
            INSERT INTO challenge_task_reviews (completion_id, reviewer_id, status, note, created_at)
            SELECT id, reviewed_by, status, review_note, reviewed_at
            FROM challenge_task_completions
            WHERE reviewed_at IS NOT NULL AND status IN ('approved','rejected')
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('challenge_task_reviews');
    }
};
