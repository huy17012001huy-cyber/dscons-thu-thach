<?php

namespace App\Services;

interface JobDescriptionParser
{
    /** @return array{skills:array<int,string>,years:int,discipline:?string} */
    public function parse(string $description): array;
}
