<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\RevitTools\Http\Controllers\V1\RevitDeviceAuthorizationController;
use Modules\RevitTools\Http\Controllers\V1\RevitEntitlementController;
use Modules\RevitTools\Http\Controllers\V1\RevitRuntimeController;
use Modules\RevitTools\Http\Controllers\V1\RevitToolManifestController;

Route::post('/revit/device/start', [RevitDeviceAuthorizationController::class, 'start'])
    ->middleware('throttle:revit-device-start')
    ->name('api.v1.revit.device.start');
Route::post('/revit/device/poll', [RevitDeviceAuthorizationController::class, 'poll'])
    ->middleware('throttle:revit-device-poll')
    ->name('api.v1.revit.device.poll');
Route::get('/revit/entitlements', RevitEntitlementController::class)
    ->middleware('throttle:revit-heartbeat')
    ->name('api.v1.revit.entitlements');
Route::get('/revit/tools/{toolKey}/manifest', RevitToolManifestController::class)
    ->where('toolKey', '[a-z0-9-]+')
    ->middleware('throttle:revit-heartbeat')
    ->name('api.v1.revit.manifest');
Route::post('/revit/heartbeat', [RevitRuntimeController::class, 'heartbeat'])
    ->middleware('throttle:revit-heartbeat')
    ->name('api.v1.revit.heartbeat');
Route::post('/revit/logout', [RevitRuntimeController::class, 'logout'])
    ->middleware('throttle:revit-heartbeat')
    ->name('api.v1.revit.logout');
