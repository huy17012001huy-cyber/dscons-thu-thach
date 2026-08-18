<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expedition_members', function (Blueprint $table) {
            $table->string('status')->default('approved'); // pending, approved, paid, rejected
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('payment_amount', 15, 2)->nullable();
            $table->string('payment_ref')->nullable();
            $table->timestamp('personal_starts_at')->nullable();
        });

        Schema::table('expeditions', function (Blueprint $table) {
            $table->decimal('price', 15, 2)->default(0);
        });

        // Backfill: existing approved members get personal_starts_at = expedition starts_at
        DB::table('expedition_members')
            ->whereNull('personal_starts_at')
            ->whereNull('kicked_at')
            ->update([
                'status' => 'approved',
                'personal_starts_at' => DB::raw('(SELECT starts_at FROM expeditions WHERE expeditions.id = expedition_members.expedition_id)'),
            ]);
    }

    public function down(): void
    {
        Schema::table('expedition_members', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['status', 'approved_at', 'approved_by', 'payment_amount', 'payment_ref', 'personal_starts_at']);
        });

        Schema::table('expeditions', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
};
