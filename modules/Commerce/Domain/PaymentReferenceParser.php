<?php

declare(strict_types=1);

namespace Modules\Commerce\Domain;

final class PaymentReferenceParser
{
    public function parse(string $content): ?PaymentReference
    {
        $content = strtoupper(trim($content));

        foreach ($this->patterns() as [$type, $pattern, $keys]) {
            if (! preg_match($pattern, $content, $matches)) {
                continue;
            }

            $attributes = [];
            foreach ($keys as $index => $key) {
                $attributes[$key] = (int) $matches[$index + 1];
            }

            return new PaymentReference($type, $attributes);
        }

        return null;
    }

    /** @return list<array{string, string, list<string>}> */
    private function patterns(): array
    {
        return [
            ['course', '/COURSE(\d+)U(\d+)/', ['course_id', 'user_id']],
            ['product', '/PROD(\d+)U(\d+)/', ['product_id', 'user_id']],
            ['challenge', '/CHAL(\d+)U(\d+)/', ['challenge_id', 'user_id']],
            ['membership', '/MEM(\d+)WU(\d+)/', ['weeks', 'user_id']],
            ['community_membership', '/MC(\d+)P(\d+)U(\d+)/', ['brand_id', 'plan_id', 'user_id']],
            ['recruiter_plan', '/RECPLAN(\d+)U(\d+)/', ['plan_id', 'user_id']],
        ];
    }
}
