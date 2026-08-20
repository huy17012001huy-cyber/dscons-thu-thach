<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite stores this column as text already and does not support
        // PostgreSQL's ALTER COLUMN ... TYPE syntax used in production.
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE challenge_task_completions ALTER COLUMN review_note TYPE text');
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE challenge_task_completions ALTER COLUMN review_note TYPE varchar(255)');
    }
};
