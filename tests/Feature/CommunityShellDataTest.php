<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Course;
use App\Models\Membership;
use App\Models\User;
use App\Support\CommunityShellData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityShellDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_shell_data_keeps_learning_and_membership_context_per_community(): void
    {
        $firstBrand = brand();
        $secondBrand = Brand::create([
            'name' => 'Cộng đồng khác',
            'slug' => 'business',
            'domain' => 'business.localhost',
        ]);
        $user = User::factory()->create();

        $firstBrand->users()->attach($user->id, ['role' => 'member']);
        $secondBrand->users()->attach($user->id, ['role' => 'member']);

        Membership::create([
            'brand_id' => $firstBrand->id,
            'user_id' => $user->id,
            'status' => 'active',
            'plan' => 'free',
            'tier' => 'free',
        ]);
        Membership::create([
            'brand_id' => $secondBrand->id,
            'user_id' => $user->id,
            'status' => 'active',
            'plan' => 'free',
            'tier' => 'free',
        ]);

        Course::create([
            'brand_id' => $firstBrand->id,
            'title' => 'BIM coordination',
            'pillar' => 'offer',
            'is_published' => true,
        ]);
        Course::create([
            'brand_id' => $secondBrand->id,
            'title' => 'Business systems',
            'pillar' => 'offer',
            'is_published' => true,
        ]);

        $first = CommunityShellData::make($firstBrand, $user, false);
        $second = CommunityShellData::make($secondBrand, $user, false);

        $this->assertSame('BIM coordination', $first['featuredUpgrade']->title);
        $this->assertSame('Business systems', $second['featuredUpgrade']->title);
        $this->assertSame($firstBrand->id, $first['currentMembership']->brand_id);
        $this->assertSame($secondBrand->id, $second['currentMembership']->brand_id);
        $this->assertSame(1, $first['communityMemberCount']);
        $this->assertSame(1, $second['communityMemberCount']);
    }
}
