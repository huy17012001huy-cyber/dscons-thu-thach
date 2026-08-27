<?php

namespace Tests\Feature;

use App\Livewire\AccountSettings;
use App\Livewire\ProfileEditPage;
use App\Models\Brand;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AccountSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_menu_and_member_pages_are_available(): void
    {
        $user = User::factory()->create(['username' => 'thanhvien']);
        $user->brandRoles()->attach(brand()->id, ['role' => 'member']);
        Membership::factory()->active()->create([
            'user_id' => $user->id,
            'brand_id' => brand()->id,
            'plan' => 'Hàng Xóm Ruột',
        ]);

        $this->actingAs($user)
            ->get(route('profile', 'thanhvien'))
            ->assertOk()
            ->assertSee('account-trigger')
            ->assertSee('Hồ sơ của bạn')
            ->assertSee('Cài đặt tài khoản')
            ->assertSee('Hàng Xóm Ruột')
            ->assertDontSee('Beginner');

        $this->actingAs($user)->get(route('account.settings'))->assertOk()->assertSee('Lịch sử đã mua');
        $this->actingAs($user)->get(route('profile.edit'))->assertOk()->assertSee('Sửa hồ sơ');
    }

    public function test_notification_preferences_are_scoped_per_community(): void
    {
        $other = Brand::create([
            'name' => 'Community khác', 'slug' => 'community-khac', 'domain' => 'other.localhost',
            'status' => 'active', 'theme_primary' => '#1F77BE', 'theme_accent' => '#DCECF7', 'theme_bg' => '#F7FBFD',
        ]);
        $user = User::factory()->create();
        $user->brandRoles()->attach([
            brand()->id => ['role' => 'member'],
            $other->id => ['role' => 'member'],
        ]);

        Livewire::actingAs($user)->test(AccountSettings::class)->call('toggleNotifications', $other->id);

        $this->assertDatabaseHas('user_community_preferences', [
            'user_id' => $user->id, 'brand_id' => $other->id, 'notifications_enabled' => false,
        ]);
        $this->assertDatabaseMissing('user_community_preferences', ['user_id' => $user->id, 'brand_id' => brand()->id]);
    }

    public function test_billing_profiles_are_saved_without_becoming_public_profile_data(): void
    {
        $user = User::factory()->create(['username' => 'billing-user']);
        $user->brandRoles()->attach(brand()->id, ['role' => 'member']);

        Livewire::actingAs($user)->test(AccountSettings::class)
            ->set('fullName', 'Nguyễn Văn Thành')
            ->set('invoiceEmail', 'billing@example.com')
            ->set('identityNumber', '012345678901')
            ->set('address', 'Hà Nội')
            ->call('saveBilling')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('user_billing_profiles', [
            'user_id' => $user->id, 'type' => 'personal', 'full_name' => 'Nguyễn Văn Thành',
            'invoice_email' => 'billing@example.com', 'identity_number' => '012345678901',
        ]);

        $this->actingAs($user)->get(route('profile', 'billing-user'))->assertDontSee('012345678901');
    }

    public function test_profile_edit_does_not_allow_email_changes_and_validates_avatar(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['username' => 'edit-user', 'email' => 'original@example.com']);

        Livewire::actingAs($user)->test(ProfileEditPage::class)
            ->set('editName', 'Tên mới')
            ->set('editUsername', 'ten-moi')
            ->set('location', 'Hà Nội')
            ->set('avatarUpload', UploadedFile::fake()->createWithContent(
                'avatar.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9WlS/cAAAAAASUVORK5CYII=')
            ))
            ->call('save')
            ->assertRedirect(route('profile', 'ten-moi'));

        $user->refresh();
        $this->assertSame('original@example.com', $user->email);
        $this->assertSame('Tên mới', $user->name);
        $this->assertSame('Hà Nội', $user->location);
        Storage::disk('public')->assertExists($user->avatar);
    }

    public function test_admin_does_not_receive_engineer_cv_navigation(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get(route('account.settings'))
            ->assertOk()
            ->assertDontSee('CV của tôi');
    }
}
