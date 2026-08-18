<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expeditions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('boss_name');
            $table->enum('difficulty', ['normal', 'hard', 'chaos'])->default('normal');
            $table->unsignedTinyInteger('required_days')->default(21);
            $table->unsignedTinyInteger('max_members')->default(10);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->enum('status', [
                'pending_approval', 'open', 'active', 'completed', 'failed', 'cancelled'
            ])->default('open');
            $table->unsignedInteger('deposit_aip')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('expedition_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expedition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('class_at_join')->nullable();
            $table->timestamp('joined_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('kicked_at')->nullable();
            $table->timestamp('last_checkin_at')->nullable();
            $table->unsignedTinyInteger('consecutive_missed_days')->default(0);
            $table->decimal('revenue_share_pct', 5, 2)->default(0);
            $table->unique(['expedition_id', 'user_id']);
        });

        Schema::create('expedition_checkins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expedition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('content');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expedition_checkins');
        Schema::dropIfExists('expedition_members');
        Schema::dropIfExists('expeditions');
    }
};
