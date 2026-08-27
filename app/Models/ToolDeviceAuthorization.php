<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasBrand;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $brand_id
 * @property string $code_hash
 * @property string $browser_code_hash
 * @property string $installation_id_hash
 * @property string $device_fingerprint_hash
 * @property string $status
 * @property int|null $approved_by_user_id
 * @property int|null $tool_session_id
 * @property CarbonInterface $expires_at
 * @property CarbonInterface|null $approved_at
 * @property CarbonInterface|null $consumed_at
 * @property ToolSession|null $session
 */
class ToolDeviceAuthorization extends Model
{
    use HasBrand;

    protected $fillable = ['brand_id', 'platform', 'code_hash', 'browser_code_hash', 'installation_id_hash', 'device_fingerprint_hash', 'device_label', 'revit_version', 'client_version', 'status', 'approved_by_user_id', 'tool_session_id', 'expires_at', 'approved_at', 'consumed_at'];

    protected $casts = ['expires_at' => 'datetime', 'approved_at' => 'datetime', 'consumed_at' => 'datetime'];

    /** @return BelongsTo<User, $this> */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    /** @return BelongsTo<ToolSession, $this> */
    public function session(): BelongsTo
    {
        return $this->belongsTo(ToolSession::class, 'tool_session_id');
    }
}
