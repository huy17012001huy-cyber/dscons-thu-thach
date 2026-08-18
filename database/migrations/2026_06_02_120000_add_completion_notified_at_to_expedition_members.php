<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expedition_members', function (Blueprint $table) {
            // Đã bắn thông báo "hoàn thành sạch 21 ngày" về Telegram chưa (chống gửi trùng).
            $table->timestamp('completion_notified_at')->nullable()->after('completed_at');
        });
    }

    public function down(): void
    {
        Schema::table('expedition_members', function (Blueprint $table) {
            $table->dropColumn('completion_notified_at');
        });
    }
};
