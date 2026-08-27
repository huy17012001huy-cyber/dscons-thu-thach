<?php

declare(strict_types=1);

namespace Modules\Recruitment\Application;

use App\Models\EngineerCv;
use Modules\Recruitment\Contracts\CandidateMatcher;

final class DeterministicCandidateMatcher implements CandidateMatcher
{
    /**
     * @param  array<string, mixed>  $jobCriteria
     * @return array{score:int,matched_skills:array<int,string>,reasons:array<int,string>}
     */
    public function score(array $jobCriteria, EngineerCv $cv): array
    {
        $skills = collect((array) $cv->skills())
            ->map(fn (mixed $skill): string => strtolower(trim(is_array($skill) ? (string) ($skill['name'] ?? '') : (string) $skill)))
            ->filter()
            ->values();
        $required = collect((array) ($jobCriteria['skills'] ?? []))
            ->map(fn (mixed $skill): string => strtolower((string) $skill))
            ->filter()
            ->values();
        $matched = $required
            ->filter(fn (string $skill): bool => $skills->contains(fn (string $candidate): bool => str_contains($candidate, $skill) || str_contains($skill, $candidate)))
            ->values();
        $score = $required->isEmpty() ? 50 : (int) round(($matched->count() / $required->count()) * 80);
        $years = (int) data_get($cv->data, 'years_experience', 0);
        if (($jobCriteria['years'] ?? 0) > 0 && $years >= $jobCriteria['years']) {
            $score += 20;
        }

        return [
            'score' => min(100, $score),
            'matched_skills' => $matched->all(),
            'reasons' => array_values(array_filter([
                $matched->isNotEmpty() ? "Khớp {$matched->count()}/{$required->count()} kỹ năng" : null,
                ($jobCriteria['years'] ?? 0) > 0 && $years >= $jobCriteria['years'] ? "Đủ {$years} năm kinh nghiệm" : null,
            ])),
        ];
    }
}
