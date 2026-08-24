<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'account_type')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('account_type', 20)->default('engineer')->after('email');
            });
        }

        Schema::create('recruiter_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('company_name', 160);
            $table->string('company_slug', 180)->unique();
            $table->string('business_email')->nullable();
            $table->string('website')->nullable();
            $table->string('industry', 120)->nullable();
            $table->text('description')->nullable();
            $table->string('verification_status', 20)->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamps();
            $table->index(['verification_status', 'created_at']);
        });

        Schema::create('engineer_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('brand_id')->constrained('brands')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('anonymized_code', 40);
            $table->string('headline', 180)->nullable();
            $table->string('discipline', 120)->nullable();
            $table->text('summary')->nullable();
            $table->unsignedSmallInteger('years_experience')->default(0);
            $table->string('location', 120)->nullable();
            $table->string('work_mode', 40)->nullable();
            $table->string('availability', 40)->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone', 40)->nullable();
            $table->json('contact_visibility')->nullable();
            $table->boolean('is_searchable')->default(false);
            $table->timestamps();
            $table->unique(['brand_id', 'user_id']);
            $table->unique(['brand_id', 'anonymized_code']);
            $table->index(['brand_id', 'is_searchable', 'discipline']);
        });

        Schema::create('engineer_cvs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('brand_id')->constrained('brands')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 160)->default('CV kỹ sư');
            $table->string('template', 40)->default('technical-clean');
            $table->string('accent_color', 20)->default('#1F77BE');
            $table->string('status', 20)->default('draft');
            $table->json('data')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->index(['brand_id', 'status', 'updated_at']);
            $table->unique(['brand_id', 'user_id']);
        });

        Schema::create('recruiter_plans', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->unsignedInteger('contact_credits')->default(0);
            $table->unsignedInteger('duration_days')->nullable();
            $table->unsignedInteger('price')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['is_active', 'price']);
        });

        Schema::create('recruiter_orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('recruiter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('recruiter_plans')->restrictOnDelete();
            $table->string('status', 24)->default('pending_payment');
            $table->string('payment_ref')->unique();
            $table->unsignedInteger('amount');
            $table->unsignedInteger('amount_paid')->default(0);
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->index(['recruiter_id', 'status', 'created_at']);
        });

        Schema::create('recruiter_entitlements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('recruiter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('recruiter_orders')->cascadeOnDelete();
            $table->unsignedInteger('credits_total')->default(0);
            $table->unsignedInteger('credits_reserved')->default(0);
            $table->unsignedInteger('credits_used')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->index(['recruiter_id', 'expires_at']);
        });

        Schema::create('recruiter_credit_ledger', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('entitlement_id')->constrained('recruiter_entitlements')->cascadeOnDelete();
            $table->foreignId('recruiter_id')->constrained('users')->cascadeOnDelete();
            $table->integer('amount');
            $table->string('type', 24);
            $table->string('reference')->nullable();
            $table->timestamps();
            $table->index(['recruiter_id', 'created_at']);
        });

        Schema::create('recruitment_contact_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('recruiter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('engineer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('cv_id')->nullable()->constrained('engineer_cvs')->nullOnDelete();
            $table->foreignId('entitlement_id')->nullable()->constrained('recruiter_entitlements')->nullOnDelete();
            $table->string('status', 24)->default('pending');
            $table->text('message')->nullable();
            $table->timestamp('reserved_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('contact_revealed_at')->nullable();
            $table->timestamps();
            $table->index(['engineer_id', 'status', 'created_at']);
            $table->index(['recruiter_id', 'status', 'created_at']);
        });

        if (Schema::hasTable('conversations') && ! Schema::hasColumn('conversations', 'conversation_type')) {
            Schema::table('conversations', function (Blueprint $table): void {
                $table->string('conversation_type', 20)->default('community')->after('brand_id');
                $table->foreignId('contact_request_id')->nullable()->after('conversation_type')->constrained('recruitment_contact_requests')->nullOnDelete();
                $table->index(['conversation_type', 'contact_request_id']);
            });
        }

        DB::table('recruiter_plans')->insertOrIgnore([
            ['name' => 'Starter', 'description' => 'Gói thử nghiệm cho một đợt tuyển dụng nhỏ.', 'contact_credits' => 5, 'duration_days' => 30, 'price' => 0, 'is_active' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Team', 'description' => 'Tìm kiếm ứng viên BIM/MEP theo nhu cầu của đội ngũ.', 'contact_credits' => 20, 'duration_days' => 60, 'price' => 0, 'is_active' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('conversations', 'contact_request_id')) {
            Schema::table('conversations', function (Blueprint $table): void {
                $table->dropForeign(['contact_request_id']);
                $table->dropColumn(['contact_request_id', 'conversation_type']);
            });
        }

        Schema::dropIfExists('recruitment_contact_requests');
        Schema::dropIfExists('recruiter_credit_ledger');
        Schema::dropIfExists('recruiter_entitlements');
        Schema::dropIfExists('recruiter_orders');
        Schema::dropIfExists('recruiter_plans');
        Schema::dropIfExists('engineer_cvs');
        Schema::dropIfExists('engineer_profiles');
        Schema::dropIfExists('recruiter_profiles');
        if (Schema::hasColumn('users', 'account_type')) {
            Schema::table('users', fn (Blueprint $table) => $table->dropColumn('account_type'));
        }
    }
};
