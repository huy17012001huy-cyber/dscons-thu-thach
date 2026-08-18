<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('challenge_tasks', function (Blueprint $table) {
            $table->text('evidence_label')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('challenge_tasks', function (Blueprint $table) {
            $table->string('evidence_label', 255)->nullable()->change();
        });
    }
};
