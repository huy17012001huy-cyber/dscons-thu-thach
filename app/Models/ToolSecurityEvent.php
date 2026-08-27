<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasBrand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $brand_id
 * @property int|null $user_id
 * @property int|null $tool_installation_id
 * @property string $event_type
 * @property string $severity
 * @property array<string, mixed>|null $metadata
 * @property User|null $user
 * @property ToolInstallation|null $installation
 */
class ToolSecurityEvent extends Model
{
    use HasBrand;

    protected $fillable = ['brand_id', 'user_id', 'tool_installation_id', 'event_type', 'severity', 'metadata'];

    protected $casts = ['metadata' => 'array'];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<ToolInstallation, $this> */
    public function installation(): BelongsTo
    {
        return $this->belongsTo(ToolInstallation::class, 'tool_installation_id');
    }
}
