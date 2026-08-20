<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('brands')) {
            Schema::table('brands', function (Blueprint $table) {
                if (! Schema::hasColumn('brands', 'owner_id')) {
                    $table->foreignId('owner_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn('brands', 'status')) {
                    $table->string('status', 20)->default('active')->after('domain');
                }
                if (! Schema::hasColumn('brands', 'description')) {
                    $table->text('description')->nullable()->after('tagline');
                }
                if (! Schema::hasColumn('brands', 'banner_path')) {
                    $table->string('banner_path')->nullable()->after('logo_path');
                }
                if (! Schema::hasColumn('brands', 'verified_at')) {
                    $table->timestamp('verified_at')->nullable()->after('status');
                }
            });
        }

        if (! Schema::hasTable('community_applications')) {
            Schema::create('community_applications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('applicant_id')->constrained('users')->cascadeOnDelete();
                $table->string('name', 100);
                $table->string('slug', 50);
                $table->string('tagline')->nullable();
                $table->text('description')->nullable();
                $table->string('logo_path')->nullable();
                $table->string('banner_path')->nullable();
                $table->string('teaching_topic')->nullable();
                $table->text('program_description')->nullable();
                $table->unsignedInteger('proposed_premium_price')->nullable();
                $table->string('proposed_sepay_account')->nullable();
                $table->string('proposed_sepay_bank')->nullable();
                $table->string('status', 24)->default('pending');
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('review_note')->nullable();
                $table->timestamps();
                $table->index(['status', 'created_at']);
            });
        }

        if (! Schema::hasTable('membership_plans')) {
            Schema::create('membership_plans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('brand_id')->constrained('brands')->cascadeOnDelete();
                $table->string('tier', 20)->default('free');
                $table->string('name', 100);
                $table->unsignedInteger('duration_days')->nullable();
                $table->unsignedInteger('price')->default(0);
                $table->text('benefits')->nullable();
                $table->string('status', 24)->default('published');
                $table->string('sepay_account')->nullable();
                $table->string('sepay_bank')->nullable();
                $table->timestamps();
                $table->index(['brand_id', 'tier', 'status']);
            });
        }

        if (! Schema::hasTable('community_user_stats')) {
            Schema::create('community_user_stats', function (Blueprint $table) {
                $table->id();
                $table->foreignId('brand_id')->constrained('brands')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->unsignedInteger('xp')->default(0);
                $table->unsignedSmallInteger('level')->default(1);
                $table->unsignedInteger('aip')->default(0);
                $table->unsignedInteger('streak')->default(0);
                $table->timestamp('last_active_at')->nullable();
                $table->timestamps();
                $table->unique(['brand_id', 'user_id']);
                $table->index(['brand_id', 'xp']);
            });
        }

        $this->addBrandColumn('xp_transactions');
        $this->addBrandColumn('aip_transactions');
        $this->addBrandColumn('power_symbols');
        $this->addBrandColumn('da_khong_cuc');
        $this->addBrandColumn('da_khong_cuc_log');
        $this->addBrandColumn('user_badges');
        $this->addBrandColumn('notifications', false);

        $this->addAccessTier('courses');
        $this->addAccessTier('expeditions');
        $this->addAccessTier('events');

        if (Schema::hasTable('memberships') && ! Schema::hasColumn('memberships', 'tier')) {
            Schema::table('memberships', function (Blueprint $table) {
                $table->string('tier', 20)->default('free')->after('user_id');
                $table->index(['brand_id', 'user_id', 'tier']);
            });

            // Existing lifetime/active memberships belong to the original DSCons
            // community and should retain full access after the migration.
            DB::table('memberships')->whereIn('status', ['active', 'trial'])->update(['tier' => 'premium']);
        }

        if (Schema::hasTable('brand_user') && DB::getDriverName() !== 'sqlite') {
            // The role column is intentionally string-based; owner is a
            // community-scoped role and requires no enum migration.
        }

        $this->backfillStats();
        $this->seedDefaultPlans();
    }

    public function down(): void
    {
        Schema::dropIfExists('community_user_stats');
        Schema::dropIfExists('membership_plans');
        Schema::dropIfExists('community_applications');
    }

    private function addBrandColumn(string $table, bool $withDefault = true): void
    {
        if (! Schema::hasTable($table) || Schema::hasColumn($table, 'brand_id')) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($withDefault) {
            $column = $blueprint->unsignedBigInteger('brand_id')->nullable();
            if ($withDefault) {
                $column->default(1);
            }
            $blueprint->index('brand_id');
        });

        DB::table($table)->whereNull('brand_id')->update(['brand_id' => 1]);
    }

    private function addAccessTier(string $table): void
    {
        if (! Schema::hasTable($table) || Schema::hasColumn($table, 'access_tier')) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->string('access_tier', 20)->default('premium')->after('status');
            $blueprint->index(['brand_id', 'access_tier']);
        });
    }

    private function backfillStats(): void
    {
        if (! Schema::hasTable('community_user_stats') || ! Schema::hasTable('users')) {
            return;
        }

        DB::table('users')->select(['id', 'xp', 'level', 'aip', 'streak', 'last_active_at'])->orderBy('id')->each(function ($user) {
            DB::table('community_user_stats')->updateOrInsert(
                ['brand_id' => 1, 'user_id' => $user->id],
                [
                    'xp' => (int) ($user->xp ?? 0),
                    'level' => max(1, (int) ($user->level ?? 1)),
                    'aip' => (int) ($user->aip ?? 0),
                    'streak' => (int) ($user->streak ?? 0),
                    'last_active_at' => $user->last_active_at,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        });
    }

    private function seedDefaultPlans(): void
    {
        if (! Schema::hasTable('membership_plans') || ! Schema::hasTable('brands')) {
            return;
        }

        $brandIds = DB::table('brands')->pluck('id');
        foreach ($brandIds as $brandId) {
            DB::table('membership_plans')->updateOrInsert(
                ['brand_id' => $brandId, 'tier' => 'free', 'name' => 'Free'],
                [
                    'duration_days' => null,
                    'price' => 0,
                    'benefits' => json_encode(['Xem nội dung công khai', 'Tham gia cộng đồng cơ bản'], JSON_UNESCAPED_UNICODE),
                    'status' => 'published',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            DB::table('membership_plans')->updateOrInsert(
                ['brand_id' => $brandId, 'tier' => 'premium', 'name' => 'Premium'],
                [
                    'duration_days' => 365,
                    'price' => $brandId === 1 ? 13000000 : 0,
                    'benefits' => json_encode(['Toàn bộ khóa học', 'Toàn bộ Challenge', 'Sự kiện premium'], JSON_UNESCAPED_UNICODE),
                    'status' => $brandId === 1 ? 'published' : 'draft',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
};
