<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 50)->unique();
            $table->string('domain', 255)->unique();
            $table->string('logo_path', 255)->nullable();
            $table->string('tagline', 255)->nullable();

            // Theme
            $table->string('theme_primary', 7)->default('#1d9e75');
            $table->string('theme_accent', 7)->default('#1d9e75');
            $table->string('theme_bg', 7)->default('#f0ede8');

            // Feature flags
            $table->boolean('has_expeditions')->default(true);
            $table->boolean('has_academy')->default(true);
            $table->boolean('has_marketplace')->default(true);
            $table->boolean('has_qa')->default(true);
            $table->boolean('is_invite_only')->default(false);
            $table->string('registration_mode', 20)->default('open'); // open, invite, closed

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};
