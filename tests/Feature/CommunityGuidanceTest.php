<?php

namespace Tests\Feature;

use App\Livewire\AdminFeedbacks;
use App\Livewire\CommunityManage;
use App\Models\Feedback;
use App\Models\User;
use App\Support\CommunityContentDefaults;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CommunityGuidanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_open_the_community_guide_and_rules(): void
    {
        $brand = brand();
        $member = User::factory()->create();
        $member->brandRoles()->attach($brand->id, ['role' => 'member']);

        $html = $this->actingAs($member)
            ->get(route('community.guide', ['community' => $brand->slug]))
            ->assertOk()
            ->assertSee('Hướng dẫn sử dụng')
            ->assertSee('Bảng tin')
            ->assertSee('Mục đích')
            ->assertSee('Cách dùng')
            ->assertSee('Mẹo')
            ->getContent();

        $this->assertSame(15, substr_count($html, 'class="guide-detail-label">Mục đích</span>'));
        $this->assertStringNotContainsString('Ã', $html);
        $this->assertStringNotContainsString('á»', $html);
        $this->assertStringNotContainsString('Ä‘', $html);

        $rulesHtml = $this->get(route('community.rules', ['community' => $brand->slug]))
            ->assertOk()
            ->assertSee('Nội quy cộng đồng')
            ->assertSee('Tôn trọng người khác')
            ->assertSee('Tinh thần AI First')
            ->assertSee('Cam kết AI First');

        $rulesHtml = $rulesHtml->getContent();
        $this->assertSame(11, substr_count($rulesHtml, 'class="rule-number"'));
        $this->assertStringNotContainsString('Ã', $rulesHtml);
        $this->assertStringNotContainsString('á»', $rulesHtml);
        $this->assertStringNotContainsString('Ä‘', $rulesHtml);
    }

    public function test_every_guide_section_keeps_its_details_when_a_legacy_invalid_byte_is_present(): void
    {
        $sections = CommunityContentDefaults::sections(CommunityContentDefaults::guide());

        $this->assertCount(15, $sections);

        foreach ($sections as $section) {
            $body = CommunityContentDefaults::normalizeUtf8($section['body']);
            $lines = preg_split('/\R/', trim($body)) ?: [];

            $this->assertNotEmpty($lines, "Guide section [{$section['title']}] lost its details.");
            $this->assertStringContainsString(':', implode("\n", $lines));
        }
    }

    public function test_rules_show_the_number_only_in_the_badge(): void
    {
        $brand = brand();
        $member = User::factory()->create();
        $member->brandRoles()->attach($brand->id, ['role' => 'member']);

        $html = $this->actingAs($member)
            ->get(route('community.rules', ['community' => $brand->slug]))
            ->getContent();

        $this->assertStringContainsString('<div class="rule-number">06</div>', $html);
        $this->assertStringContainsString('<h2>Tôn trọng bản quyền và công sức</h2>', $html);
        $this->assertStringNotContainsString('<h2>6. Tôn trọng bản quyền và công sức</h2>', $html);
    }

    public function test_corrupted_custom_guidance_falls_back_to_clean_defaults(): void
    {
        $brand = brand();
        $brand->update(['rules_content' => 'Nội quy d� hi�u']);

        $this->assertSame(
            CommunityContentDefaults::rules(),
            CommunityContentDefaults::resolve($brand->rules_content, CommunityContentDefaults::rules())
        );
    }

    public function test_community_admin_can_customize_guide_and_rules(): void
    {
        $brand = brand();
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        Livewire::actingAs($admin)
            ->test(CommunityManage::class)
            ->set('guideContent', 'Hướng dẫn riêng của community')
            ->set('rulesContent', 'Nội quy riêng của community')
            ->call('save')
            ->assertHasNoErrors();

        $brand->refresh();
        $this->assertSame('Hướng dẫn riêng của community', $brand->guide_content);
        $this->assertSame('Nội quy riêng của community', $brand->rules_content);
    }

    public function test_community_admin_can_manage_feedback_for_its_community(): void
    {
        $brand = brand();
        $admin = User::factory()->create();
        $admin->brandRoles()->attach($brand->id, ['role' => 'admin']);
        $feedback = Feedback::create([
            'user_id' => $admin->id,
            'brand_id' => $brand->id,
            'type' => 'bao_loi',
            'subject' => 'Lỗi trong community',
            'content' => 'Nội dung báo lỗi để admin community xử lý.',
        ]);

        $this->actingAs($admin)
            ->get(route('community.manage.feedbacks', ['community' => $brand->slug]))
            ->assertOk()
            ->assertSee('Lỗi trong community');

        Livewire::actingAs($admin)
            ->test(AdminFeedbacks::class)
            ->call('markResolved', $feedback->id);

        $this->assertDatabaseHas('feedbacks', ['id' => $feedback->id, 'status' => 'resolved']);
    }
}
