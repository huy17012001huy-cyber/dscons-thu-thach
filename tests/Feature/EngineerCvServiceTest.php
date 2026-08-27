<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Core\CommunityContext;
use App\Models\Brand;
use App\Models\EngineerCv;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Recruitment\Application\EngineerCvService;
use Tests\TestCase;

final class EngineerCvServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_engineer_cv_workspace_is_scoped_and_publish_updates_search_visibility(): void
    {
        $engineer = User::factory()->create(['account_type' => 'engineer']);
        $service = app(EngineerCvService::class);
        $workspace = $service->ensureWorkspace($engineer);
        $cv = $service->save($engineer, true, [
            'headline' => 'BIM Engineer',
            'discipline' => 'BIM',
            'summary' => null,
            'years_experience' => 5,
            'location' => 'Hà Nội',
            'work_mode' => 'Hybrid',
            'availability' => 'Sẵn sàng',
            'contact_phone' => '0900000000',
            'contact_visibility' => ['email' => true, 'phone' => false],
        ], [
            'title' => 'CV BIM Engineer',
            'template' => 'technical-clean',
            'accent_color' => '#1F77BE',
            'data' => ['skills' => [['name' => 'Revit']]],
        ]);

        self::assertSame('KYS-'.str_pad((string) $engineer->id, 5, '0', STR_PAD_LEFT), $workspace['profile']->anonymized_code);
        self::assertSame('published', $cv->status);
        self::assertTrue($workspace['profile']->fresh()->is_searchable);
        $this->assertDatabaseHas('engineer_cvs', ['brand_id' => brand()->id, 'user_id' => $engineer->id, 'status' => 'published']);
        $this->assertDatabaseHas('audit_logs', [
            'brand_id' => brand()->id,
            'actor_id' => $engineer->id,
            'domain' => 'recruitment',
            'action' => 'engineer_cv_published',
            'subject_type' => EngineerCv::class,
            'subject_id' => $cv->id,
        ]);
    }

    public function test_cv_workspace_is_unavailable_when_community_cv_feature_is_disabled(): void
    {
        $engineer = User::factory()->create(['account_type' => 'engineer']);
        $disabledBrand = Brand::create([
            'name' => 'Disabled CV Community',
            'slug' => 'disabled-cv-community',
            'domain' => 'disabled-cv-community.test',
            'theme_primary' => '#1F77BE',
            'theme_accent' => '#DCECF7',
            'theme_bg' => '#F8FAFC',
            'registration_mode' => 'invite',
            'is_invite_only' => true,
            'has_cv' => false,
        ]);

        app(CommunityContext::class)->run($disabledBrand, function () use ($engineer): void {
            $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
            app(EngineerCvService::class)->ensureWorkspace($engineer);
        });
    }
}
