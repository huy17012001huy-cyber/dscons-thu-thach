<?php

declare(strict_types=1);

namespace App\Providers;

use App\Core\CommunityContext;
use App\Core\Modules\ModuleServiceProvider;
use App\Core\Security\RateLimitService;
use Illuminate\Support\ServiceProvider;

final class CoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CommunityContext::class);
        $this->app->register(ModuleServiceProvider::class);
    }

    public function boot(): void
    {
        RateLimitService::register();
    }
}
