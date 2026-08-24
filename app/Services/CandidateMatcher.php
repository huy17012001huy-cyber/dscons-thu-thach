<?php

namespace App\Services;

use App\Models\EngineerCv;

interface CandidateMatcher
{
    /** @return array{score:int, matched_skills:array<int,string>, reasons:array<int,string>} */
    public function score(array $jobCriteria, EngineerCv $cv): array;
}
