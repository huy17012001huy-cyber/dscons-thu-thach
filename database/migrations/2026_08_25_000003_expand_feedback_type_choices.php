<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite test databases get the expanded check from the original
        // migration. Existing PostgreSQL installations need the constraint
        // replaced because that migration has already run there.
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE feedbacks DROP CONSTRAINT IF EXISTS feedbacks_type_check');
            DB::statement("ALTER TABLE feedbacks ADD CONSTRAINT feedbacks_type_check CHECK (type IN ('khieu_nai', 'gop_y', 'bao_loi', 'thanh_toan', 'khac'))");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::table('feedbacks')->whereNotIn('type', ['khieu_nai', 'gop_y'])->update(['type' => 'gop_y']);
            DB::statement('ALTER TABLE feedbacks DROP CONSTRAINT IF EXISTS feedbacks_type_check');
            DB::statement("ALTER TABLE feedbacks ADD CONSTRAINT feedbacks_type_check CHECK (type IN ('khieu_nai', 'gop_y'))");
        }
    }
};
