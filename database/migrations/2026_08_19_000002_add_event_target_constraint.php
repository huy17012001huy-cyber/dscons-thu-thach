<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE events ADD CONSTRAINT events_exactly_one_target CHECK ((course_id IS NOT NULL AND expedition_id IS NULL) OR (course_id IS NULL AND expedition_id IS NOT NULL))");
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE events DROP CONSTRAINT IF EXISTS events_exactly_one_target');
        }
    }
};
