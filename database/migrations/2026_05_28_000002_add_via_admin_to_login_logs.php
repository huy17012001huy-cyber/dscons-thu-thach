<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('login_logs', function (Blueprint $table) {
            // true khi login phát sinh do admin "đóng vai" (impersonate) — dùng để
            // loại trừ khỏi báo cáo IP nghi vấn và cron cảnh báo.
            $table->boolean('via_admin')->default(false)->index()->after('user_agent');
        });
    }

    public function down(): void
    {
        Schema::table('login_logs', function (Blueprint $table) {
            $table->dropIndex(['via_admin']);
            $table->dropColumn('via_admin');
        });
    }
};
