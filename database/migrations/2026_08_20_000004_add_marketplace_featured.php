<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['expeditions', 'courses', 'digital_products'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->boolean('is_featured')->default(false)->index();
            });
        }
    }

    public function down(): void
    {
        foreach (['expeditions', 'courses', 'digital_products'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropColumn('is_featured');
            });
        }
    }
};
