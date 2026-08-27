<?php

declare(strict_types=1);

namespace Modules\Recruitment\Contracts;

interface JobDescriptionParser
{
    /** @return array{skills:list<string>,years:int,discipline:?string} */
    public function parse(string $description): array;
}
