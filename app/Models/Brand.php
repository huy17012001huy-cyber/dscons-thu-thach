<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Brand extends Model
{
    protected $fillable = [
        'name', 'slug', 'domain', 'logo_path', 'tagline',
        'theme_primary', 'theme_accent', 'theme_bg',
        'has_expeditions', 'has_academy', 'has_marketplace', 'has_qa',
        'is_invite_only', 'registration_mode',
    ];

    protected $casts = [
        'has_expeditions' => 'boolean',
        'has_academy'     => 'boolean',
        'has_marketplace' => 'boolean',
        'has_qa'          => 'boolean',
        'is_invite_only'  => 'boolean',
    ];

    public static function findByDomain(string $domain): ?self
    {
        return Cache::remember("brand:domain:{$domain}", 60, function () use ($domain) {
            return static::where('domain', $domain)->first();
        });
    }

    public function getThemeVarsAttribute(): array
    {
        return [
            '--green'  => $this->theme_primary,
            '--accent' => $this->theme_accent,
            '--bg-app' => $this->theme_bg,
        ];
    }
}
