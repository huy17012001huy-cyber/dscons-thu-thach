<?php

namespace App\Support;

use App\Models\Brand;
use App\Models\Setting;
use Illuminate\Support\Facades\Schema;

final class CommunityBrandSettings
{
    private const DEFAULT_BADGE_COLORS = [
        'newcomer' => '#8B5CF6',
        'practitioner' => '#0284C7',
        'core' => '#0D9488',
        'expert' => '#D97706',
        'mentor' => '#1D4ED8',
    ];

    public static function membershipLabel(Brand $brand): string
    {
        return (string) (self::stored(self::key($brand, 'membership_label'))
            ?: config('communities.membership_labels.'.$brand->slug)
            ?: config('communities.membership_labels.default', 'Premium'));
    }

    /** @return array<string, string> */
    public static function stageLabels(Brand $brand): array
    {
        $defaults = config('communities.stages.'.$brand->slug)
            ?: config('communities.stages.default', []);
        $configured = self::decodeArray(self::stored(self::key($brand, 'stage_labels')));

        return [
            'newcomer' => (string) ($configured['newcomer'] ?? $defaults[10] ?? 'Người mới vào nghề'),
            'practitioner' => (string) ($configured['practitioner'] ?? $defaults[30] ?? 'Kỹ sư thực hành'),
            'core' => (string) ($configured['core'] ?? $defaults[60] ?? 'Kỹ sư nòng cốt'),
            'expert' => (string) ($configured['expert'] ?? $defaults[100] ?? 'Chuyên gia BIM/MEP'),
            'mentor' => (string) ($configured['mentor'] ?? $defaults[200] ?? $defaults['default'] ?? 'Mentor DSCons'),
        ];
    }

    /** @return array<string, string> */
    public static function badgeColors(Brand $brand): array
    {
        return array_merge(
            self::DEFAULT_BADGE_COLORS,
            array_intersect_key(
                self::decodeArray(self::stored(self::key($brand, 'badge_colors'))),
                self::DEFAULT_BADGE_COLORS,
            ),
        );
    }

    public static function memberAvatarSize(Brand $brand): int
    {
        return max(28, min(56, (int) (self::stored(self::key($brand, 'member_avatar_size')) ?: 30)));
    }

    /** @param array<string, string> $stageLabels
     * @param  array<string, string>  $badgeColors
     */
    public static function save(
        Brand $brand,
        string $membershipLabel,
        array $stageLabels,
        array $badgeColors,
        int $avatarSize,
    ): void {
        Setting::set(self::key($brand, 'membership_label'), trim($membershipLabel));
        Setting::set(self::key($brand, 'stage_labels'), json_encode($stageLabels, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        Setting::set(self::key($brand, 'badge_colors'), json_encode($badgeColors, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        Setting::set(self::key($brand, 'member_avatar_size'), (string) max(28, min(56, $avatarSize)));
    }

    private static function key(Brand $brand, string $name): string
    {
        return 'community.'.$brand->id.'.'.$name;
    }

    private static function stored(string $key): mixed
    {
        return Schema::hasTable('settings') ? Setting::get($key) : null;
    }

    /** @return array<string, mixed> */
    private static function decodeArray(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value) || blank($value)) {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }
}
