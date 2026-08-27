<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Str;
use Tests\TestCase;

final class ArchitectureBoundaryTest extends TestCase
{
    /** @return list<string> */
    private function moduleNames(): array
    {
        return collect(config('modules.enabled', []))
            ->map(static fn (mixed $module): string => (string) $module)
            ->values()
            ->all();
    }

    public function test_enabled_modules_have_valid_manifests_and_providers(): void
    {
        foreach ($this->moduleNames() as $module) {
            $manifestPath = base_path('modules/'.$module.'/module.json');
            self::assertFileExists($manifestPath);

            $manifest = json_decode((string) file_get_contents($manifestPath), true, flags: JSON_THROW_ON_ERROR);
            self::assertSame($module, $manifest['name'] ?? null);
            self::assertIsString($manifest['provider'] ?? null);
            self::assertTrue(class_exists($manifest['provider']));
            self::assertSame([], $manifest['depends'] ?? [], $module.' must depend on App Core contracts, not another module.');
        }
    }

    public function test_modules_do_not_import_other_modules_or_presentation_layers(): void
    {
        $moduleNames = $this->moduleNames();

        foreach ($moduleNames as $module) {
            $directory = new \RecursiveDirectoryIterator(base_path('modules/'.$module));
            $files = new \RecursiveIteratorIterator($directory);

            foreach ($files as $file) {
                if (! $file->isFile() || $file->getExtension() !== 'php') {
                    continue;
                }

                $path = $file->getPathname();
                $contents = (string) file_get_contents($path);

                self::assertStringContainsString(
                    'declare(strict_types=1);',
                    $contents,
                    $path.' must opt into strict types.',
                );

                foreach ($moduleNames as $otherModule) {
                    if ($otherModule === $module) {
                        continue;
                    }

                    self::assertStringNotContainsString(
                        'Modules\\'.$otherModule.'\\',
                        $contents,
                        $path.' imports another module directly.',
                    );
                }

                self::assertFalse(
                    Str::contains($contents, ['App\\Livewire\\', 'App\\Http\\Controllers\\']),
                    $path.' imports a presentation layer.',
                );
                self::assertStringNotContainsString(
                    'App\\Services\\',
                    $contents,
                    $path.' imports an application service instead of an App Core contract.',
                );
            }
        }
    }
}
