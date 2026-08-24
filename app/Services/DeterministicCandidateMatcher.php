<?php

namespace App\Services;

use App\Models\EngineerCv;

class DeterministicCandidateMatcher implements CandidateMatcher
{
    public function score(array $jobCriteria, EngineerCv $cv): array
    {
        $skills = collect($cv->skills())->map(fn ($skill) => strtolower(trim(is_array($skill) ? ($skill['name'] ?? '') : $skill)))->filter()->values();
        $required = collect($jobCriteria['skills'] ?? [])->map(fn ($skill) => strtolower($skill))->filter()->values();
        $matched = $required->filter(fn ($skill) => $skills->contains(fn ($candidate) => str_contains($candidate, $skill) || str_contains($skill, $candidate)))->values();
        $score = $required->isEmpty() ? 50 : (int) round(($matched->count() / $required->count()) * 80);
        $years = (int) data_get($cv->data, 'years_experience', 0);
        if (($jobCriteria['years'] ?? 0) > 0 && $years >= $jobCriteria['years']) {
            $score += 20;
        }

        return [
            'score' => min(100, $score),
            'matched_skills' => $matched->all(),
            'reasons' => array_filter([
                $matched->isNotEmpty() ? "Khớp {$matched->count()}/{$required->count()} kỹ năng" : null,
                ($jobCriteria['years'] ?? 0) > 0 && $years >= $jobCriteria['years'] ? "Đủ {$years} năm kinh nghiệm" : null,
            ]),
        ];
    }
}
