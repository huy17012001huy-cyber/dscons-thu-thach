<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('topics', function (Blueprint $table): void {
            $table->dropUnique('topics_slug_unique');
            $table->unique(['brand_id', 'slug'], 'topics_brand_slug_unique');
        });
    }

    public function down(): void
    {
        Schema::table('topics', function (Blueprint $table): void {
            $table->dropUnique('topics_brand_slug_unique');
            $table->unique('slug');
        });
    }
};
