<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('digital_products', function (Blueprint $table): void {
            $table->string('product_kind')->default('resource')->after('pillar');
            $table->string('tool_key')->nullable()->after('product_kind');
            $table->jsonb('supported_revit_versions')->nullable()->after('tool_key');
            $table->string('tool_manifest_version')->nullable()->after('supported_revit_versions');
            $table->string('package_path')->nullable()->after('file_path');
            $table->boolean('is_license_required')->default(false)->after('package_path');
            $table->unique(['brand_id', 'tool_key']);
        });

        Schema::create('tool_installations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
            $table->string('platform')->default('revit');
            $table->string('installation_id_hash', 64)->unique();
            $table->string('device_fingerprint_hash', 64);
            $table->string('device_label')->nullable();
            $table->string('status')->default('active');
            $table->timestamp('blocked_until')->nullable();
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->string('last_revit_version')->nullable();
            $table->string('last_client_version')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
            $table->unique(['brand_id', 'user_id', 'platform']);
            $table->index(['brand_id', 'status']);
        });

        Schema::create('tool_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tool_installation_id')->constrained()->cascadeOnDelete();
            $table->string('token_hash', 64)->unique();
            $table->timestamp('expires_at');
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
            $table->index(['tool_installation_id', 'expires_at']);
        });

        Schema::create('tool_device_authorizations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
            $table->string('platform')->default('revit');
            $table->string('code_hash', 64)->unique();
            $table->string('installation_id_hash', 64);
            $table->string('device_fingerprint_hash', 64);
            $table->string('device_label')->nullable();
            $table->string('revit_version')->nullable();
            $table->string('client_version')->nullable();
            $table->string('status')->default('pending');
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('tool_session_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('expires_at');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('consumed_at')->nullable();
            $table->timestamps();
            $table->index(['brand_id', 'status', 'expires_at']);
        });

        Schema::create('tool_security_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('tool_installation_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event_type');
            $table->string('severity')->default('info');
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
            $table->index(['brand_id', 'user_id', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tool_security_events');
        Schema::dropIfExists('tool_device_authorizations');
        Schema::dropIfExists('tool_sessions');
        Schema::dropIfExists('tool_installations');

        Schema::table('digital_products', function (Blueprint $table): void {
            $table->dropUnique(['brand_id', 'tool_key']);
            $table->dropColumn(['product_kind', 'tool_key', 'supported_revit_versions', 'tool_manifest_version', 'package_path', 'is_license_required']);
        });
    }
};
