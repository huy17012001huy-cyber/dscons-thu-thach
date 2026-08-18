<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('challenge_task_completions', function (Blueprint $table) {
            $table->unsignedTinyInteger('reject_count')->default(0)->after('is_late');
        });
    }

    public function down(): void
    {
        Schema::table('challenge_task_completions', function (Blueprint $table) {
            $table->dropColumn('reject_count');
        });
    }
};
