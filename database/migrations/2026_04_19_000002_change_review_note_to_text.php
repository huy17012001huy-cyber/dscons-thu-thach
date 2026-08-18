<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE challenge_task_completions ALTER COLUMN review_note TYPE text');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE challenge_task_completions ALTER COLUMN review_note TYPE varchar(255)');
    }
};
