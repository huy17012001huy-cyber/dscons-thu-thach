<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('challenge_task_completions', function (Blueprint $table) {
            $table->dropUnique(['challenge_task_id', 'user_id']);
            $table->index(['challenge_task_id', 'user_id'], 'cttc_task_user_idx');
        });
    }

    public function down(): void
    {
        Schema::table('challenge_task_completions', function (Blueprint $table) {
            $table->dropIndex('cttc_task_user_idx');
            $table->unique(['challenge_task_id', 'user_id']);
        });
    }
};
