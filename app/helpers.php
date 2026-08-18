<?php

if (!function_exists('brand')) {
    function brand(): \App\Models\Brand
    {
        return app('brand');
    }
}
