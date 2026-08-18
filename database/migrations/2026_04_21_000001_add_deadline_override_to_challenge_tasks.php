<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-task absolute deadline override. When set, this datetime replaces the
 * computed `personal_starts_at + day*24h (+ freeze)` deadline — used when a
 * specific day needs a non-standard window (e.g. 48h instead of 24h).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('challenge_tasks', function (Blueprint $t) {
            $t->timestamp('deadline_override_at')->nullable()->after('meeting_at');
        });
    }

    public function down(): void
    {
        Schema::table('challenge_tasks', function (Blueprint $t) {
            $t->dropColumn('deadline_override_at');
        });
    }
};
