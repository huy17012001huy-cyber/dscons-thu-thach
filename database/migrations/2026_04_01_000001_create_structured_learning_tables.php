<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['text', 'link', 'file', 'quiz'])->default('text');
            $table->unsignedTinyInteger('order_index')->default(0);
            $table->boolean('is_required')->default(true);
            $table->timestamps();
        });

        Schema::create('task_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_task_id')->constrained('lesson_tasks')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('content')->nullable();
            $table->string('file_url')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('submitted_at');
            $table->timestamps();
            $table->unique(['lesson_task_id', 'user_id']);
        });

        Schema::create('lesson_prerequisites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->foreignId('required_lesson_id')->constrained('lessons')->cascadeOnDelete();
            $table->unique(['lesson_id', 'required_lesson_id']);
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->string('lesson_type')->default('lecture')->after('title');
            $table->boolean('is_locked_by_default')->default(true)->after('order_index');
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn(['lesson_type', 'is_locked_by_default']);
        });

        Schema::dropIfExists('lesson_prerequisites');
        Schema::dropIfExists('task_submissions');
        Schema::dropIfExists('lesson_tasks');
    }
};
