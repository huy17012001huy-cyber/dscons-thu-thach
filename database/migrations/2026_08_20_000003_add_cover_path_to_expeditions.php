<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('expeditions', 'cover_path')) {
            Schema::table('expeditions', function (Blueprint $table) {
                $table->string('cover_path')->nullable()->after('description');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('expeditions', 'cover_path')) {
            Schema::table('expeditions', function (Blueprint $table) {
                $table->dropColumn('cover_path');
            });
        }
    }
};
