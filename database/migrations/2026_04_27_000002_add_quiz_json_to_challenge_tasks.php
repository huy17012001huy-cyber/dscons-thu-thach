<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Quiz definition lives on the task. JSONB array of question objects:
 * [{ q, options:{A,B,C,D}, correct, explanation, ai_prompt }, ...]
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('challenge_tasks', function (Blueprint $t) {
            $t->jsonb('quiz_json')->nullable()->after('admin_note');
        });
    }

    public function down(): void
    {
        Schema::table('challenge_tasks', function (Blueprint $t) {
            $t->dropColumn('quiz_json');
        });
    }
};
