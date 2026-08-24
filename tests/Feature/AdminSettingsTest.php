<?php

namespace Tests\Feature;

use App\Livewire\AdminSettings;
use App\Models\Setting;
use App\Models\User;
use App\Support\CommunityBrandSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_save_community_level_membership_and_avatar_settings(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        Livewire::actingAs($admin)
            ->test(AdminSettings::class)
            ->set('membershipLabel', 'Kỹ sư VIP')
            ->set('stageLabels.newcomer', 'Kỹ sư tập sự')
            ->set('badgeColors.newcomer', '#123456')
            ->set('memberAvatarSize', 44)
            ->call('saveCommunityBranding')
            ->assertHasNoErrors();

        $brand = brand();
        $this->assertSame('Kỹ sư VIP', CommunityBrandSettings::membershipLabel($brand));
        $this->assertSame('Kỹ sư tập sự', CommunityBrandSettings::stageLabels($brand)['newcomer']);
        $this->assertSame('#123456', CommunityBrandSettings::badgeColors($brand)['newcomer']);
        $this->assertSame(44, CommunityBrandSettings::memberAvatarSize($brand));
        $this->assertDatabaseHas('settings', [
            'key' => 'community.' . $brand->id . '.membership_label',
            'value' => 'Kỹ sư VIP',
        ]);
    }
}
