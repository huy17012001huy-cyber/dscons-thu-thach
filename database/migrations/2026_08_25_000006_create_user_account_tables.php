<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_community_preferences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
            $table->boolean('notifications_enabled')->default(true);
            $table->timestamps();
            $table->unique(['user_id', 'brand_id']);
        });

        Schema::create('user_billing_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20)->default('personal');
            $table->string('full_name', 160)->nullable();
            $table->string('company_name', 200)->nullable();
            $table->string('invoice_email', 255)->nullable();
            $table->string('identity_number', 40)->nullable();
            $table->string('tax_code', 40)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('phone', 40)->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'type']);
            $table->index(['user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_billing_profiles');
        Schema::dropIfExists('user_community_preferences');
    }
};
