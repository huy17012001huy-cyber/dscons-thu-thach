<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\DigitalProduct;
use Illuminate\Database\Seeder;

class DsconsRevitToolSeeder extends Seeder
{
    public function run(): void
    {
        $brand = Brand::query()->where('slug', 'dscons')->firstOrFail();
        foreach ([
            ['tool_key' => 'dscons-test-1', 'title' => 'DSCons Tool Test 1', 'description' => 'Tool demo kiểm tra Ribbon, đăng nhập và license DSCons. Bấm nút sẽ xác nhận Tool Test 1 đã load thành công.'],
            ['tool_key' => 'dscons-test-2', 'title' => 'DSCons Tool Test 2', 'description' => 'Tool demo thứ hai để kiểm tra quyền mua riêng theo từng tool trên cùng Ribbon DSCons.'],
        ] as $index => $tool) {
            DigitalProduct::withoutGlobalScopes()->updateOrCreate(
                ['brand_id' => $brand->id, 'tool_key' => $tool['tool_key']],
                [
                    ...$tool,
                    'brand_id' => $brand->id,
                    'product_kind' => 'revit_tool',
                    'price' => 0,
                    'delivery_type' => 'link',
                    'is_license_required' => true,
                    // Do not advertise a version merely because the project builds.
                    // Populate this after a clean install and in-Revit runtime test.
                    'supported_revit_versions' => [],
                    'tool_manifest_version' => '1.0.0',
                    'is_published' => true,
                    'is_featured' => $index === 0,
                    'sort_order' => $index,
                ]
            );
        }
    }
}
