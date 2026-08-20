<?php

if (!function_exists('brand')) {
    function brand(): \App\Models\Brand
    {
        return app('brand');
    }
}

if (!function_exists('community_route')) {
    function community_route(string $name, array $parameters = [], bool $contextual = true): string
    {
        if (!$contextual || !app()->bound('brand')) {
            return route($name, $parameters);
        }

        $contextName = str_starts_with($name, 'community.') ? $name : 'community.'.$name;
        return route($contextName, array_merge(['community' => brand()->slug], $parameters));
    }
}
