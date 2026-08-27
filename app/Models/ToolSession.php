<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $tool_installation_id
 * @property string $token_hash
 * @property CarbonInterface $expires_at
 * @property CarbonInterface|null $last_seen_at
 * @property CarbonInterface|null $revoked_at
 * @property ToolInstallation $installation
 */
class ToolSession extends Model
{
    protected $fillable = ['tool_installation_id', 'token_hash', 'expires_at', 'last_seen_at', 'revoked_at'];

    protected $casts = ['expires_at' => 'datetime', 'last_seen_at' => 'datetime', 'revoked_at' => 'datetime'];

    /** @return BelongsTo<ToolInstallation, $this> */
    public function installation(): BelongsTo
    {
        return $this->belongsTo(ToolInstallation::class, 'tool_installation_id');
    }
}
