<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('feedbacks', function (Blueprint $table): void {
            $table->foreignId('brand_id')->nullable()->after('user_id')->constrained('brands')->nullOnDelete();
            $table->json('attachments')->nullable()->after('content');
            $table->index(['brand_id', 'user_id', 'created_at']);
        });

        // Existing feedback was created before community scoping existed. Keep it
        // visible by assigning it to the primary DSCons community when available.
        $dsconsId = DB::table('brands')->where('slug', 'dscons')->value('id');
        if ($dsconsId) {
            DB::table('feedbacks')->whereNull('brand_id')->update(['brand_id' => $dsconsId]);
        }
    }

    public function down(): void
    {
        Schema::table('feedbacks', function (Blueprint $table): void {
            $table->dropIndex(['brand_id', 'user_id', 'created_at']);
            $table->dropForeign(['brand_id']);
            $table->dropColumn(['brand_id', 'attachments']);
        });
    }
};
