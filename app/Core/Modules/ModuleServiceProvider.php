<?php

declare(strict_types=1);

namespace App\Core\Modules;

use Illuminate\Support\ServiceProvider;
use RuntimeException;

final class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        foreach ($this->providersInDependencyOrder() as $provider) {
            $this->app->register($provider);
        }
    }

    /** @return list<class-string<ServiceProvider>> */
    private function providersInDependencyOrder(): array
    {
        $manifests = [];

        foreach (config('modules.enabled', []) as $module) {
            $manifestPath = base_path('modules/'.$module.'/module.json');
            if (! is_file($manifestPath)) {
                throw new RuntimeException("Module manifest missing: {$module}");
            }

            $manifest = json_decode((string) file_get_contents($manifestPath), true, flags: JSON_THROW_ON_ERROR);
            $manifests[$module] = $manifest;
        }

        $ordered = [];
        $visiting = [];
        $visit = function (string $module) use (&$visit, &$ordered, &$visiting, $manifests): void {
            if (in_array($module, $ordered, true)) {
                return;
            }
            if (isset($visiting[$module])) {
                throw new RuntimeException("Circular module dependency: {$module}");
            }
            if (! isset($manifests[$module])) {
                throw new RuntimeException("Enabled module dependency missing: {$module}");
            }

            $visiting[$module] = true;
            foreach ($manifests[$module]['depends'] ?? [] as $dependency) {
                $visit((string) $dependency);
            }
            unset($visiting[$module]);
            $ordered[] = $module;
        };

        foreach (array_keys($manifests) as $module) {
            $visit($module);
        }

        return array_map(function (string $module) use ($manifests): string {
            $provider = $manifests[$module]['provider'] ?? null;
            if (! is_string($provider) || ! is_a($provider, ServiceProvider::class, true)) {
                throw new RuntimeException("Invalid provider for module: {$module}");
            }

            /** @var class-string<ServiceProvider> $provider */
            return $provider;
        }, $ordered);
    }
}
