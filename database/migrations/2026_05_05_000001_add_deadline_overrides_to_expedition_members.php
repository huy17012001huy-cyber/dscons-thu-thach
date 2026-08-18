<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expedition_members', function (Blueprint $table) {
            $table->jsonb('deadline_overrides')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('expedition_members', function (Blueprint $table) {
            $table->dropColumn('deadline_overrides');
        });
    }
};
