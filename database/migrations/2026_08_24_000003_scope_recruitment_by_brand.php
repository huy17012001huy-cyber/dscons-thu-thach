<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('brands', 'has_cv')) {
            Schema::table('brands', function (Blueprint $table): void {
                $table->boolean('has_cv')->default(false)->after('has_qa');
                $table->boolean('has_recruitment')->default(false)->after('has_cv');
            });
        }

        $tables = [
            'recruiter_profiles', 'recruiter_plans', 'recruiter_orders',
            'recruiter_entitlements', 'recruiter_credit_ledger', 'recruitment_contact_requests',
        ];

        foreach ($tables as $tableName) {
            if (! Schema::hasColumn($tableName, 'brand_id')) {
                Schema::table($tableName, function (Blueprint $table): void {
                    $table->foreignId('brand_id')->nullable()->after('id')->constrained('brands')->nullOnDelete();
                });
            }
        }

        // Existing recruitment records belong to the original DSCons marketplace.
        $dsconsId = DB::table('brands')->where('slug', 'dscons')->value('id');
        if ($dsconsId) {
            foreach ($tables as $tableName) {
                DB::table($tableName)->whereNull('brand_id')->update(['brand_id' => $dsconsId]);
            }
        }

        // A recruiter may operate one company profile in each community.
        if (Schema::hasTable('recruiter_profiles')) {
            try {
                Schema::table('recruiter_profiles', fn (Blueprint $table) => $table->dropUnique('recruiter_profiles_user_id_unique'));
            } catch (Throwable) {
            }
            try {
                Schema::table('recruiter_profiles', fn (Blueprint $table) => $table->dropUnique('recruiter_profiles_company_slug_unique'));
            } catch (Throwable) {
            }
            Schema::table('recruiter_profiles', function (Blueprint $table): void {
                $table->unique(['brand_id', 'user_id']);
                $table->unique(['brand_id', 'company_slug']);
            });
        }

        if ($dsconsId) {
            DB::table('brands')->where('slug', 'dscons')->update(['has_cv' => true, 'has_recruitment' => true]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('recruiter_profiles')) {
            try {
                Schema::table('recruiter_profiles', fn (Blueprint $table) => $table->dropUnique(['brand_id', 'user_id']));
            } catch (Throwable) {
            }
            try {
                Schema::table('recruiter_profiles', fn (Blueprint $table) => $table->dropUnique(['brand_id', 'company_slug']));
            } catch (Throwable) {
            }
        }

        foreach (['recruitment_contact_requests', 'recruiter_credit_ledger', 'recruiter_entitlements', 'recruiter_orders', 'recruiter_plans', 'recruiter_profiles'] as $tableName) {
            if (Schema::hasColumn($tableName, 'brand_id')) {
                Schema::table($tableName, function (Blueprint $table): void {
                    $table->dropForeign(['brand_id']);
                    $table->dropColumn('brand_id');
                });
            }
        }
        if (Schema::hasColumn('brands', 'has_recruitment')) {
            Schema::table('brands', fn (Blueprint $table) => $table->dropColumn(['has_recruitment', 'has_cv']));
        }
    }
};
