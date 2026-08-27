<?php

declare(strict_types=1);

namespace Modules\RevitTools\Application;

use App\Models\ToolSession;
use App\Services\ToolLicenseService;

final readonly class RevitApiSessionResolver
{
    public function __construct(private ToolLicenseService $licenses) {}

    public function fromBearerToken(?string $token): ?ToolSession
    {
        return $this->licenses->sessionFromToken($token);
    }
}
