<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->unsignedInteger('price')->default(0)->after('xp_reward'); // 0 = free
        });

        // Course enrollment: track payment status
        Schema::table('course_enrollments', function (Blueprint $table) {
            $table->string('status')->default('active')->after('course_id'); // pending_payment, active
            $table->string('payment_ref')->nullable()->after('status'); // SePay transaction ref
            $table->unsignedInteger('amount_paid')->default(0)->after('payment_ref');
            $table->timestamp('paid_at')->nullable()->after('amount_paid');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('price');
        });
        Schema::table('course_enrollments', function (Blueprint $table) {
            $table->dropColumn(['status', 'payment_ref', 'amount_paid', 'paid_at']);
        });
    }
};
