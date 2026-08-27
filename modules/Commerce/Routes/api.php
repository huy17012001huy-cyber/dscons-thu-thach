<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Commerce\Http\Controllers\V1\SepayWebhookController;

Route::post('/webhooks/sepay', SepayWebhookController::class)
    ->middleware('throttle:sepay-webhook')
    ->name('api.v1.webhooks.sepay');
