<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('xp_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('amount');
            $table->string('type'); // post|comment|cot|expedition|challenge|login|affiliate|...
            $table->nullableMorphs('reference');
            $table->decimal('multiplier', 4, 2)->default(1.00);
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('aip_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('amount');
            $table->enum('type', ['earn', 'spend']);
            $table->string('reason')->nullable();
            $table->nullableMorphs('reference');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('da_khong_cuc', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('total_count')->default(0);
            $table->timestamps();
        });

        Schema::create('da_khong_cuc_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->smallInteger('delta');
            $table->string('reason')->nullable();
            $table->foreignId('awarded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('power_symbols', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('pillar', ['offer', 'traffic', 'conversion', 'delivery', 'continuity']);
            $table->unsignedTinyInteger('level')->default(0);
            $table->unsignedInteger('fragments')->default(0);
            $table->timestamps();
            $table->unique(['user_id', 'pillar']);
        });

        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('icon')->nullable();
            $table->enum('rarity', ['common', 'rare', 'epic', 'unique', 'legendary'])->default('common');
            $table->string('condition_type')->nullable();
            $table->string('condition_value')->nullable();
            $table->timestamps();
        });

        Schema::create('user_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('badge_id')->constrained()->cascadeOnDelete();
            $table->timestamp('earned_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_badges');
        Schema::dropIfExists('badges');
        Schema::dropIfExists('power_symbols');
        Schema::dropIfExists('da_khong_cuc_log');
        Schema::dropIfExists('da_khong_cuc');
        Schema::dropIfExists('aip_transactions');
        Schema::dropIfExists('xp_transactions');
    }
};
