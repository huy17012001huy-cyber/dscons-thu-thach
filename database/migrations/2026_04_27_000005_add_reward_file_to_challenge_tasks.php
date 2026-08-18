<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('challenge_tasks', function (Blueprint $table) {
            $table->string('reward_file_path')->nullable()->after('contest_duration_hours');
            $table->string('reward_file_label')->nullable()->after('reward_file_path');
        });
    }

    public function down(): void
    {
        Schema::table('challenge_tasks', function (Blueprint $table) {
            $table->dropColumn(['reward_file_path', 'reward_file_label']);
        });
    }
};
