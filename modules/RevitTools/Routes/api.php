<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\RevitTools\Http\Controllers\V1\RevitEntitlementController;

Route::get('/revit/entitlements', RevitEntitlementController::class)
    ->middleware('throttle:revit-heartbeat')
    ->name('api.v1.revit.entitlements');
