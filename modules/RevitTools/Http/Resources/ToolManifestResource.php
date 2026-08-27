<?php

declare(strict_types=1);

namespace Modules\RevitTools\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ToolManifestResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $product = $this->resource;

        return [
            'tool_key' => data_get($product, 'tool_key'),
            'title' => data_get($product, 'title'),
            'manifest_version' => data_get($product, 'tool_manifest_version'),
            'supported_revit_versions' => data_get($product, 'supported_revit_versions') ?: [],
            'embedded' => blank(data_get($product, 'package_path')),
            'package_url' => null,
        ];
    }
}
