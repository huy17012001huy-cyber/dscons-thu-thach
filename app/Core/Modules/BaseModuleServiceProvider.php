<?php

declare(strict_types=1);

namespace App\Core\Modules;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use ReflectionClass;
use RuntimeException;

abstract class BaseModuleServiceProvider extends ServiceProvider
{
    protected string $moduleName;

    public function register(): void
    {
        $configPath = $this->modulePath().'/Config/config.php';

        if (is_file($configPath)) {
            $this->mergeConfigFrom($configPath, 'modules.'.strtolower($this->moduleName));
        }
    }

    public function boot(): void
    {
        $path = $this->modulePath();

        if (is_dir($path.'/Database/Migrations')) {
            $this->loadMigrationsFrom($path.'/Database/Migrations');
        }

        if (is_dir($path.'/resources/views')) {
            $this->loadViewsFrom($path.'/resources/views', strtolower($this->moduleName));
        }

        if (is_dir($path.'/lang')) {
            $this->loadTranslationsFrom($path.'/lang', strtolower($this->moduleName));
        }

        if (is_file($path.'/Routes/web.php')) {
            Route::middleware('web')->group($path.'/Routes/web.php');
        }

        if (is_file($path.'/Routes/api.php')) {
            Route::prefix('api/v1')->middleware('api')->group($path.'/Routes/api.php');
        }
    }

    protected function modulePath(): string
    {
        $filename = (new ReflectionClass($this))->getFileName();

        if ($filename === false) {
            throw new RuntimeException('Unable to resolve the module provider path.');
        }

        return dirname($filename, 2);
    }
}
