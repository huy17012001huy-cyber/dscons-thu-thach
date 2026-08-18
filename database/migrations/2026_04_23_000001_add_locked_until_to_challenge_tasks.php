<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('challenge_tasks', function (Blueprint $table) {
            $table->timestamp('locked_until')->nullable()->after('deadline_override_at');
        });
    }

    public function down(): void
    {
        Schema::table('challenge_tasks', function (Blueprint $table) {
            $table->dropColumn('locked_until');
        });
    }
};
