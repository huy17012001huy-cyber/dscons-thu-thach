<?php

namespace App\Models;

use App\Models\Concerns\HasBrand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $brand_id
 * @property int $user_id
 * @property string|null $title
 * @property string|null $template
 * @property string|null $accent_color
 * @property string|null $status
 * @property array<string, mixed> $data
 */
class EngineerCv extends Model
{
    use HasBrand;

    protected $table = 'engineer_cvs';

    protected $fillable = ['brand_id', 'user_id', 'title', 'template', 'accent_color', 'status', 'data', 'published_at'];

    protected $casts = ['data' => 'array', 'published_at' => 'datetime'];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Brand, $this> */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /** @return array<int, mixed> */
    public function skills(): array
    {
        return is_array($this->data['skills'] ?? null) ? $this->data['skills'] : [];
    }

    /** @return array<int, mixed> */
    public function experiences(): array
    {
        return is_array($this->data['experiences'] ?? null) ? $this->data['experiences'] : [];
    }
}
