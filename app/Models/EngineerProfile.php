<?php

namespace App\Models;

use App\Models\Concerns\HasBrand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $brand_id
 * @property int $user_id
 * @property string $anonymized_code
 * @property string|null $headline
 * @property string|null $discipline
 * @property string|null $summary
 * @property int|null $years_experience
 * @property string|null $location
 * @property string|null $work_mode
 * @property string|null $availability
 * @property string|null $contact_email
 * @property string|null $contact_phone
 * @property array<string, bool>|null $contact_visibility
 * @property bool $is_searchable
 * @property-read EngineerCv|null $cv
 */
class EngineerProfile extends Model
{
    use HasBrand;

    protected $fillable = ['brand_id', 'user_id', 'anonymized_code', 'headline', 'discipline', 'summary', 'years_experience', 'location', 'work_mode', 'availability', 'contact_email', 'contact_phone', 'contact_visibility', 'is_searchable'];

    protected $casts = ['contact_visibility' => 'array', 'is_searchable' => 'boolean'];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasOne<EngineerCv, $this> */
    public function cv(): HasOne
    {
        return $this->hasOne(EngineerCv::class, 'user_id', 'user_id');
    }
}
