<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('challenge_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expedition_id')->constrained('expeditions')->cascadeOnDelete();
            $table->integer('day_number');
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['expedition_id', 'day_number']);
        });

        Schema::create('challenge_task_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('challenge_task_id')->constrained('challenge_tasks')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['challenge_task_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('challenge_task_completions');
        Schema::dropIfExists('challenge_tasks');
    }
};
