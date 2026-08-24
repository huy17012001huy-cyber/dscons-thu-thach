<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('da_khong_cuc') && Schema::hasColumn('da_khong_cuc', 'brand_id')) {
            Schema::table('da_khong_cuc', function (Blueprint $table): void {
                $table->dropUnique('da_khong_cuc_user_id_unique');
                $table->unique(['brand_id', 'user_id'], 'da_khong_cuc_brand_user_unique');
            });
        }

        if (Schema::hasTable('power_symbols') && Schema::hasColumn('power_symbols', 'brand_id')) {
            Schema::table('power_symbols', function (Blueprint $table): void {
                $table->dropUnique('power_symbols_user_id_pillar_unique');
                $table->unique(['brand_id', 'user_id', 'pillar'], 'power_symbols_brand_user_pillar_unique');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('da_khong_cuc')) {
            Schema::table('da_khong_cuc', function (Blueprint $table): void {
                $table->dropUnique('da_khong_cuc_brand_user_unique');
                $table->unique('user_id', 'da_khong_cuc_user_id_unique');
            });
        }

        if (Schema::hasTable('power_symbols')) {
            Schema::table('power_symbols', function (Blueprint $table): void {
                $table->dropUnique('power_symbols_brand_user_pillar_unique');
                $table->unique(['user_id', 'pillar'], 'power_symbols_user_id_pillar_unique');
            });
        }
    }
};
