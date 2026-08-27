<?php

declare(strict_types=1);

namespace App\Core\Audit;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

final class AuditLogger
{
    /** @param array<string, scalar|null> $metadata */
    public function record(string $domain, string $action, ?User $actor = null, ?Model $subject = null, ?int $brandId = null, array $metadata = []): void
    {
        AuditLog::query()->create([
            'brand_id' => $brandId,
            'actor_id' => $actor?->id,
            'domain' => $domain,
            'action' => $action,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'metadata' => $metadata ?: null,
        ]);
    }
}
