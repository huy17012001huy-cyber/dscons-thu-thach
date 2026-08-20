<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->default(1)->constrained('brands')->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained('courses')->cascadeOnDelete();
            $table->foreignId('expedition_id')->nullable()->constrained('expeditions')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('title', 255);
            $table->string('slug', 255)->unique();
            $table->text('description')->nullable();
            $table->string('event_type', 30)->default('workshop');
            $table->string('format', 20)->default('online');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->text('meeting_url')->nullable();
            $table->string('location', 500)->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamps();

            $table->index(['brand_id', 'status', 'starts_at']);
            $table->index(['course_id', 'starts_at']);
            $table->index(['expedition_id', 'starts_at']);
        });

        Schema::create('event_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->default(1)->constrained('brands')->cascadeOnDelete();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 20)->default('registered');
            $table->timestamp('registered_at');
            $table->timestamp('attended_at')->nullable();
            $table->foreignId('marked_attended_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['event_id', 'user_id']);
            $table->index(['event_id', 'status']);
            $table->index(['brand_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_registrations');
        Schema::dropIfExists('events');
    }
};
