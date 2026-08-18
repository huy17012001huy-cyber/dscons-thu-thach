<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('login_logs', function (Blueprint $table) {
            // UUID dài cố định set bởi middleware vào cookie taip_device_id (5 năm).
            // Cùng máy/browser → cùng cookie kể cả qua nhiều account → catch nick ảo
            // mạnh hơn IP (IP có thể đổi do dynamic ISP).
            $table->string('device_cookie_id', 36)->nullable()->index()->after('via_admin');

            // Hash truncated SHA-256 (16 hex) của canvas+webgl+screen+navigator…
            // Cùng device dù clear cookie vẫn cho hash giống (~95% case).
            $table->string('fingerprint_hash', 16)->nullable()->index()->after('device_cookie_id');
        });
    }

    public function down(): void
    {
        Schema::table('login_logs', function (Blueprint $table) {
            $table->dropIndex(['device_cookie_id']);
            $table->dropIndex(['fingerprint_hash']);
            $table->dropColumn(['device_cookie_id', 'fingerprint_hash']);
        });
    }
};
