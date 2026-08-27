<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\SearchResults;
use App\Models\Brand;
use App\Models\Post;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SearchResultsCommunityScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_does_not_return_results_from_another_community(): void
    {
        $currentBrand = brand();
        $otherBrand = Brand::create([
            'name' => 'Cộng đồng riêng tư',
            'slug' => 'cong-dong-rieng-tu',
            'domain' => 'private.test',
            'status' => 'active',
            'theme_primary' => '#1F77BE',
            'theme_accent' => '#F39402',
            'theme_bg' => '#F7FAFC',
        ]);
        $member = User::factory()->create();
        $member->brandRoles()->attach($currentBrand->id, ['role' => 'member']);
        $otherMember = User::factory()->create([
            'name' => 'Thành viên cộng đồng riêng tư',
            'username' => 'thanh-vien-rieng-tu',
        ]);
        $otherMember->brandRoles()->attach($otherBrand->id, ['role' => 'member']);

        Post::create([
            'brand_id' => $otherBrand->id,
            'user_id' => $otherMember->id,
            'title' => 'Bài viết cộng đồng riêng tư',
            'content' => 'Ma-tu-khoa-bao-mat-chi-o-noi-dung-bai-viet',
            'pillar' => 'delivery',
        ]);
        Question::create([
            'brand_id' => $otherBrand->id,
            'user_id' => $otherMember->id,
            'title' => 'Câu hỏi cộng đồng riêng tư',
            'body' => 'Ma-tu-khoa-bao-mat-chi-o-noi-dung-cau-hoi',
            'status' => 'open',
        ]);

        $this->actingAs($member);

        $search = Livewire::test(SearchResults::class)
            ->set('q', 'Ma-tu-khoa-bao-mat-chi-o-noi-dung')
            ->assertDontSee('Bài viết cộng đồng riêng tư')
            ->assertDontSee('Câu hỏi cộng đồng riêng tư');

        $search
            ->set('q', 'thanh-vien-rieng-tu')
            ->assertDontSee('Thành viên cộng đồng riêng tư');
    }
}
