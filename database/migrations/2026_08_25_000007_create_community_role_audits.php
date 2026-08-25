<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_role_audits', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('brand_id')->constrained('brands')->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('from_role', 20)->nullable();
            $table->string('to_role', 20)->nullable();
            $table->string('action', 40);
            $table->timestamps();
            $table->index(['brand_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });

        if (! Schema::hasTable('brand_user') || ! Schema::hasTable('brands')) {
            return;
        }

        $now = now();
        DB::table('brands')
            ->whereNotNull('owner_id')
            ->orderBy('id')
            ->each(function (object $brand) use ($now): void {
                $existing = DB::table('brand_user')
                    ->where('brand_id', $brand->id)
                    ->where('user_id', $brand->owner_id)
                    ->first();

                if ($existing?->role === 'owner') {
                    return;
                }

                DB::table('brand_user')->updateOrInsert(
                    ['brand_id' => $brand->id, 'user_id' => $brand->owner_id],
                    ['role' => 'owner', 'created_at' => $existing?->created_at ?: $now, 'updated_at' => $now]
                );

                DB::table('community_role_audits')->insert([
                    'brand_id' => $brand->id,
                    'actor_id' => null,
                    'user_id' => $brand->owner_id,
                    'from_role' => $existing?->role,
                    'to_role' => 'owner',
                    'action' => 'backfill_owner',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            });

        DB::table('brand_user')
            ->join('users', 'users.id', '=', 'brand_user.user_id')
            ->where('users.is_admin', false)
            ->where('users.is_moderator', true)
            ->where('brand_user.role', 'member')
            ->select(['brand_user.brand_id', 'brand_user.user_id'])
            ->orderBy('brand_user.id')
            ->each(function (object $membership) use ($now): void {
                DB::table('brand_user')
                    ->where('brand_id', $membership->brand_id)
                    ->where('user_id', $membership->user_id)
                    ->update(['role' => 'moderator', 'updated_at' => $now]);

                DB::table('community_role_audits')->insert([
                    'brand_id' => $membership->brand_id,
                    'actor_id' => null,
                    'user_id' => $membership->user_id,
                    'from_role' => 'member',
                    'to_role' => 'moderator',
                    'action' => 'backfill_legacy_moderator',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_role_audits');
    }
};
