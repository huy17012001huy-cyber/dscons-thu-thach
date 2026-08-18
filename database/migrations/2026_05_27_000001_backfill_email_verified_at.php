<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Grandfather: mọi user đã tồn tại trước khi bật email verification
        // được coi là đã xác minh, tránh khóa toàn bộ thành viên hiện hữu.
        // Từ giờ chỉ đăng ký công khai mới phải verify email.
        DB::table('users')
            ->whereNull('email_verified_at')
            ->update(['email_verified_at' => now()]);
    }

    public function down(): void
    {
        // Không revert — không thể phân biệt user nào vốn dĩ đã verify trước đó.
    }
};
