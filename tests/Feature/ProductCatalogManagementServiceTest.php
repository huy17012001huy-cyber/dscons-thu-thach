<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\DigitalProduct;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Commerce\Application\ProductCatalogManagementService;
use Tests\TestCase;

final class ProductCatalogManagementServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_community_admin_can_create_publish_and_delete_a_revit_tool_product(): void
    {
        $admin = $this->admin();
        $service = app(ProductCatalogManagementService::class);
        $product = $service->save(null, $admin, $this->attributes('DSCons Tool Test 1'));

        self::assertNotNull($product);
        self::assertSame(brand()->id, $product->brand_id);
        self::assertSame('revit_tool', $product->product_kind);
        self::assertFalse($product->is_published);
        self::assertTrue($service->togglePublished($product->id, $admin)->is_published);
        self::assertSame($product->id, $service->delete($product->id, $admin)->id);
        $this->assertDatabaseMissing('digital_products', ['id' => $product->id]);
    }

    public function test_product_management_cannot_target_another_community(): void
    {
        $admin = $this->admin();
        $other = Brand::create([
            'name' => 'Other product catalog community',
            'slug' => 'other-product-catalog-community',
            'domain' => 'other-product-catalog-community.test',
            'theme_primary' => '#1F77BE',
            'theme_accent' => '#DCECF7',
            'theme_bg' => '#F8FAFC',
            'registration_mode' => 'invite',
            'is_invite_only' => true,
        ]);
        $product = DigitalProduct::withoutGlobalScopes()->create([
            ...$this->attributes('Other tool'),
            'brand_id' => $other->id,
        ]);

        $this->expectException(ModelNotFoundException::class);
        app(ProductCatalogManagementService::class)->togglePublished($product->id, $admin);
    }

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->brandRoles()->attach(brand()->id, ['role' => 'admin']);

        return $admin;
    }

    /** @return array<string, mixed> */
    private function attributes(string $title): array
    {
        return [
            'title' => $title,
            'description' => null,
            'price' => 0,
            'delivery_type' => 'file',
            'is_published' => false,
            'is_featured' => false,
            'product_kind' => 'revit_tool',
            'tool_key' => 'dscons-test-1',
            'supported_revit_versions' => ['2024'],
            'tool_manifest_version' => '1.0.0',
            'is_license_required' => true,
        ];
    }
}
