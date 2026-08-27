<?php

declare(strict_types=1);

namespace Modules\Recruitment\Application;

use Modules\Recruitment\Contracts\JobDescriptionParser;

final class DeterministicJobDescriptionParser implements JobDescriptionParser
{
    private const SKILLS = ['revit', 'navisworks', 'autocad', 'bim', 'mep', 'dynamo', 'python', 'family', 'bóc tách', 'combine', 'hvac', 'plumbing', 'electrical'];

    /** @return array{skills:list<string>,years:int,discipline:?string} */
    public function parse(string $description): array
    {
        $normalized = strtolower($description);
        $skills = array_values(array_filter(self::SKILLS, fn (string $skill): bool => str_contains($normalized, $skill)));
        preg_match('/(\d+)\s*(?:năm|years?)/i', $description, $yearsMatch);

        $discipline = match (true) {
            str_contains($normalized, 'hvac') || str_contains($normalized, 'cơ điện') => 'MEP',
            str_contains($normalized, 'bim') || str_contains($normalized, 'revit') => 'BIM',
            default => null,
        };

        return ['skills' => $skills, 'years' => (int) ($yearsMatch[1] ?? 0), 'discipline' => $discipline];
    }
}
