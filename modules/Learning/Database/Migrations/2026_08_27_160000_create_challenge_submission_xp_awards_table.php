<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('challenge_submission_xp_awards', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('challenge_task_completion_id')->constrained('challenge_task_completions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique('challenge_task_completion_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('challenge_submission_xp_awards');
    }
};
