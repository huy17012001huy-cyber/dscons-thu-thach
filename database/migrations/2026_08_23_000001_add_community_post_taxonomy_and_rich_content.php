<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $this->createTaxonomyTables();
        $this->ensurePostColumns();
        $this->seedTaxonomy();
        $this->backfillPostSlugs();
        $this->backfillPostTaxonomy();
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            foreach (['post_type_id', 'subject_id'] as $column) {
                if (Schema::hasColumn('posts', $column)) {
                    $table->dropForeign([$column]);
                }
            }

            foreach (['brand_id', 'slug', 'content_html', 'content_format', 'subject_id', 'post_type_id'] as $column) {
                if (Schema::hasColumn('posts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::dropIfExists('community_post_types');
        Schema::dropIfExists('community_subjects');
    }

    private function ensurePostColumns(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            if (! Schema::hasColumn('posts', 'brand_id')) {
                $table->foreignId('brand_id')->nullable()->after('id')->constrained('brands')->nullOnDelete();
            }
            if (! Schema::hasColumn('posts', 'slug')) {
                $table->string('slug')->nullable()->after('title');
            }
            if (! Schema::hasColumn('posts', 'content_html')) {
                $table->longText('content_html')->nullable()->after('content');
            }
            if (! Schema::hasColumn('posts', 'content_format')) {
                $table->string('content_format', 20)->default('markdown')->after('content_html');
            }
            if (! Schema::hasColumn('posts', 'subject_id')) {
                $table->foreignId('subject_id')->nullable()->after('topic_id')->constrained('community_subjects')->nullOnDelete();
            }
            if (! Schema::hasColumn('posts', 'post_type_id')) {
                $table->foreignId('post_type_id')->nullable()->after('subject_id')->constrained('community_post_types')->nullOnDelete();
            }
        });

        DB::table('posts')->whereNull('brand_id')->update(['brand_id' => 1]);
        Schema::table('posts', function (Blueprint $table): void {
            $table->index(['brand_id', 'created_at']);
            $table->index(['brand_id', 'subject_id', 'post_type_id']);
        });
    }

    private function createTaxonomyTables(): void
    {
        if (! Schema::hasTable('community_subjects')) {
            Schema::create('community_subjects', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('brand_id')->constrained('brands')->cascadeOnDelete();
                $table->string('name', 100);
                $table->string('slug', 100);
                $table->string('icon', 40)->nullable();
                $table->string('color', 20)->nullable();
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->unique(['brand_id', 'slug']);
                $table->index(['brand_id', 'is_active', 'sort_order']);
            });
        }

        if (! Schema::hasTable('community_post_types')) {
            Schema::create('community_post_types', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('brand_id')->constrained('brands')->cascadeOnDelete();
                $table->string('name', 100);
                $table->string('slug', 100);
                $table->string('icon', 40)->nullable();
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->unique(['brand_id', 'slug']);
                $table->index(['brand_id', 'is_active', 'sort_order']);
            });
        }
    }

    private function seedTaxonomy(): void
    {
        $brands = DB::table('brands')->select(['id', 'slug'])->get();
        $postTypes = [
            ['name' => 'Hỏi đáp', 'slug' => 'hoi-dap', 'icon' => 'question'],
            ['name' => 'Chia sẻ', 'slug' => 'chia-se', 'icon' => 'chat'],
            ['name' => 'Case thực tế', 'slug' => 'case-thuc-te', 'icon' => 'briefcase'],
            ['name' => 'Workflow/Quy trình', 'slug' => 'workflow', 'icon' => 'layers'],
            ['name' => 'Tài nguyên', 'slug' => 'tai-nguyen', 'icon' => 'book'],
        ];

        foreach ($brands as $brand) {
            $subjects = $brand->slug === 'dscons'
                ? [
                    ['name' => 'Thiết kế', 'slug' => 'thiet-ke', 'icon' => 'compass', 'color' => '#147F96'],
                    ['name' => 'Dựng hình', 'slug' => 'dung-hinh', 'icon' => 'layers', 'color' => '#125A96'],
                    ['name' => 'Bóc tách', 'slug' => 'boc-tach', 'icon' => 'clipboard', 'color' => '#2563EB'],
                    ['name' => 'Phối hợp/Combine', 'slug' => 'phoi-hop-combine', 'icon' => 'users', 'color' => '#0E7490'],
                    ['name' => 'Family', 'slug' => 'family', 'icon' => 'grid', 'color' => '#1F77BE'],
                    ['name' => 'Tiêu chuẩn', 'slug' => 'tieu-chuan', 'icon' => 'check-circle', 'color' => '#18794E'],
                    ['name' => 'Mẹo hay', 'slug' => 'meo-hay', 'icon' => 'spark', 'color' => '#D97706'],
                ]
                : [
                    ['name' => 'Kinh nghiệm', 'slug' => 'kinh-nghiem', 'icon' => 'chat', 'color' => '#147F96'],
                    ['name' => 'Hỏi đáp', 'slug' => 'hoi-dap', 'icon' => 'question', 'color' => '#1F77BE'],
                    ['name' => 'Tài nguyên', 'slug' => 'tai-nguyen', 'icon' => 'book', 'color' => '#18794E'],
                ];

            foreach ($subjects as $sort => $subject) {
                DB::table('community_subjects')->updateOrInsert(
                    ['brand_id' => $brand->id, 'slug' => $subject['slug']],
                    [...$subject, 'brand_id' => $brand->id, 'sort_order' => $sort, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]
                );
            }

            foreach ($postTypes as $sort => $type) {
                DB::table('community_post_types')->updateOrInsert(
                    ['brand_id' => $brand->id, 'slug' => $type['slug']],
                    [...$type, 'brand_id' => $brand->id, 'sort_order' => $sort, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }

    private function backfillPostSlugs(): void
    {
        DB::table('posts')->whereNull('slug')->orWhere('slug', '')->orderBy('id')->eachById(function (object $post): void {
            $base = Str::slug($post->title ?: Str::limit(strip_tags($post->content), 60, '')) ?: 'bai-viet';
            $slug = $base.'-'.$post->id;
            DB::table('posts')->where('id', $post->id)->update(['slug' => $slug, 'updated_at' => now()]);
        });
    }

    private function backfillPostTaxonomy(): void
    {
        $typeIds = DB::table('community_post_types')->where('slug', 'chia-se')->pluck('id', 'brand_id');
        $subjectSlugs = [
            'offer' => 'phoi-hop-combine',
            'traffic' => 'thiet-ke',
            'conversion' => 'meo-hay',
            'delivery' => 'boc-tach',
            'continuity' => 'tieu-chuan',
        ];

        DB::table('posts')->whereNull('post_type_id')->orderBy('id')->eachById(function (object $post) use ($typeIds, $subjectSlugs): void {
            $subjectSlug = $subjectSlugs[$post->pillar] ?? 'kinh-nghiem';
            $subjectId = DB::table('community_subjects')
                ->where('brand_id', $post->brand_id)
                ->where('slug', $subjectSlug)
                ->value('id');

            DB::table('posts')->where('id', $post->id)->update([
                'subject_id' => $subjectId,
                'post_type_id' => $typeIds[$post->brand_id] ?? null,
                'updated_at' => now(),
            ]);
        });
    }
};
