<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('challenge_task_reviews', function (Blueprint $table): void {
            $table->unsignedSmallInteger('score')->nullable()->after('note');
            $table->jsonb('rubric_payload')->nullable()->after('score');
        });
    }

    public function down(): void
    {
        Schema::table('challenge_task_reviews', function (Blueprint $table): void {
            $table->dropColumn(['score', 'rubric_payload']);
        });
    }
};
