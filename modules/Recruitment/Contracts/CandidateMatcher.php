<?php

declare(strict_types=1);

namespace Modules\Recruitment\Contracts;

use App\Models\EngineerCv;

interface CandidateMatcher
{
    /**
     * @param  array<string, mixed>  $jobCriteria
     * @return array{score:int,matched_skills:array<int,string>,reasons:array<int,string>}
     */
    public function score(array $jobCriteria, EngineerCv $cv): array;
}
