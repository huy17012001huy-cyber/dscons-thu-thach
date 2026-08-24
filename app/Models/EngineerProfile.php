<?php

namespace App\Models;

use App\Models\Concerns\HasBrand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EngineerProfile extends Model
{
    use HasBrand;

    protected $fillable = ['brand_id', 'user_id', 'anonymized_code', 'headline', 'discipline', 'summary', 'years_experience', 'location', 'work_mode', 'availability', 'contact_email', 'contact_phone', 'contact_visibility', 'is_searchable'];

    protected $casts = ['contact_visibility' => 'array', 'is_searchable' => 'boolean'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function cv(): HasOne { return $this->hasOne(EngineerCv::class, 'user_id', 'user_id'); }
}
