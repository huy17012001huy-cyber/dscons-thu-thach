<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'location')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('location', 160)->nullable()->after('bio');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'location')) {
            Schema::table('users', fn (Blueprint $table) => $table->dropColumn('location'));
        }
    }
};
