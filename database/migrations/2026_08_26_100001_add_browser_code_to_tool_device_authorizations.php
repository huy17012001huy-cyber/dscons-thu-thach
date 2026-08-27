<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tool_device_authorizations', function (Blueprint $table): void {
            // Browser code is distinct from the add-in polling code so the
            // bearer token cannot be claimed from browser history or a shared URL.
            $table->string('browser_code_hash', 64)->nullable()->unique()->after('code_hash');
        });
    }

    public function down(): void
    {
        Schema::table('tool_device_authorizations', function (Blueprint $table): void {
            $table->dropUnique(['browser_code_hash']);
            $table->dropColumn('browser_code_hash');
        });
    }
};
