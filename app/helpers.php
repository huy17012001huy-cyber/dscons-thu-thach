<?php

use App\Models\Brand;

if (! function_exists('brand')) {
    function brand(): Brand
    {
        return app('brand');
    }
}

if (! function_exists('community_route')) {
    /** @param array<string, mixed> $parameters */
    function community_route(string $name, array $parameters = [], bool $contextual = true): string
    {
        if (! $contextual || ! app()->bound('brand')) {
            return route($name, $parameters);
        }

        $contextName = str_starts_with($name, 'community.') ? $name : 'community.'.$name;

        return route($contextName, array_merge(['community' => brand()->slug], $parameters));
    }
}
