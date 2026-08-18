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
        Schema::create('digital_products', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('pillar')->nullable(); // offer/traffic/conversion/delivery/continuity
            $table->unsignedInteger('price'); // VND, 0 = free
            $table->string('delivery_type')->default('file'); // file, link, both
            $table->string('file_path')->nullable(); // storage path for downloadable file
            $table->string('file_name')->nullable(); // original filename for display
            $table->text('access_url')->nullable(); // link to Notion/Drive/tool
            $table->boolean('is_published')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('digital_products');
    }
};
