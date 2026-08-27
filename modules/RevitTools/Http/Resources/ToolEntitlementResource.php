<?php

declare(strict_types=1);

namespace Modules\RevitTools\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ToolEntitlementResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'tool_key' => $this['tool_key'],
            'title' => $this['title'],
            'manifest_version' => $this['manifest_version'],
            'supported_revit_versions' => $this['supported_revit_versions'],
            'active' => $this['active'],
        ];
    }
}
