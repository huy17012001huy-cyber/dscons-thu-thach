<?php

namespace App\Models;

use App\Models\Concerns\HasBrand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EngineerCv extends Model
{
    use HasBrand;

    protected $table = 'engineer_cvs';
    protected $fillable = ['brand_id', 'user_id', 'title', 'template', 'accent_color', 'status', 'data', 'published_at'];
    protected $casts = ['data' => 'array', 'published_at' => 'datetime'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function brand(): BelongsTo { return $this->belongsTo(Brand::class); }

    public function skills(): array { return $this->data['skills'] ?? []; }
    public function experiences(): array { return $this->data['experiences'] ?? []; }
}
