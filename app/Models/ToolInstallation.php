<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasBrand;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property int $brand_id
 * @property string $platform
 * @property string $installation_id_hash
 * @property string $device_fingerprint_hash
 * @property string|null $device_label
 * @property string $status
 * @property CarbonInterface|null $blocked_until
 * @property CarbonInterface|null $first_seen_at
 * @property CarbonInterface|null $last_seen_at
 * @property string|null $last_revit_version
 * @property string|null $last_client_version
 * @property CarbonInterface|null $revoked_at
 * @property User $user
 * @property Collection<int, ToolSession> $sessions
 */
class ToolInstallation extends Model
{
    use HasBrand;

    protected $fillable = ['user_id', 'brand_id', 'platform', 'installation_id_hash', 'device_fingerprint_hash', 'device_label', 'status', 'blocked_until', 'first_seen_at', 'last_seen_at', 'last_revit_version', 'last_client_version', 'revoked_at'];

    protected $casts = ['blocked_until' => 'datetime', 'first_seen_at' => 'datetime', 'last_seen_at' => 'datetime', 'revoked_at' => 'datetime'];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<ToolSession, $this> */
    public function sessions(): HasMany
    {
        return $this->hasMany(ToolSession::class);
    }

    /** @return HasMany<ToolSecurityEvent, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(ToolSecurityEvent::class);
    }
}
