<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\Brand;
use Closure;
use LogicException;

final class CommunityContext
{
    public function current(): ?Brand
    {
        $brand = app()->bound('brand') ? app('brand') : null;

        return $brand instanceof Brand ? $brand : null;
    }

    public function require(): Brand
    {
        return $this->current() ?? throw new LogicException('A community context is required.');
    }

    public function run(Brand $brand, Closure $callback): mixed
    {
        $hadBrand = app()->bound('brand');
        $previous = $hadBrand ? app('brand') : null;

        app()->instance('brand', $brand);

        try {
            return $callback();
        } finally {
            if ($previous instanceof Brand) {
                app()->instance('brand', $previous);
            } else {
                app()->forgetInstance('brand');
            }
        }
    }
}
