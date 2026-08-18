<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Add vote_type to submission_votes so admin can vote both 'good' (Vote hay)
 * and 'excellent' (Vote xuất sắc) independently on the same submission.
 * Existing rows backfilled with 'good' (matches the original single-button behaviour).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submission_votes', function (Blueprint $t) {
            $t->string('vote_type', 16)->default('good')->after('user_id');
        });

        DB::statement("ALTER TABLE submission_votes DROP CONSTRAINT IF EXISTS submission_votes_completion_id_user_id_unique");

        Schema::table('submission_votes', function (Blueprint $t) {
            $t->unique(['completion_id', 'user_id', 'vote_type'], 'submission_votes_unique');
        });
    }

    public function down(): void
    {
        Schema::table('submission_votes', function (Blueprint $t) {
            $t->dropUnique('submission_votes_unique');
        });
        DB::statement("ALTER TABLE submission_votes ADD CONSTRAINT submission_votes_completion_id_user_id_unique UNIQUE (completion_id, user_id)");
        Schema::table('submission_votes', function (Blueprint $t) {
            $t->dropColumn('vote_type');
        });
    }
};
