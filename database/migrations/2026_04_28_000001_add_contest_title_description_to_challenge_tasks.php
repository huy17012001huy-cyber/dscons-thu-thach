<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('challenge_tasks', function (Blueprint $table) {
            $table->string('contest_title')->nullable()->after('is_contest');
            $table->text('contest_description')->nullable()->after('contest_title');
        });
    }

    public function down(): void
    {
        Schema::table('challenge_tasks', function (Blueprint $table) {
            $table->dropColumn(['contest_title', 'contest_description']);
        });
    }
};
