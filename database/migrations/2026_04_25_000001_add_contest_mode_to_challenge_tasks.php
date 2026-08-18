<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('challenge_tasks', function (Blueprint $table) {
            $table->boolean('is_contest')->default(false)->after('locked_until');
            $table->unsignedSmallInteger('duration_hours')->default(24)->after('is_contest');
        });
    }

    public function down(): void
    {
        Schema::table('challenge_tasks', function (Blueprint $table) {
            $table->dropColumn(['is_contest', 'duration_hours']);
        });
    }
};
