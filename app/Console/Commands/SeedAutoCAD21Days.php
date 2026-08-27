<?php

namespace App\Console\Commands;

use Database\Seeders\AutoCAD21DaysSeeder;
use Database\Seeders\Revit21DaysSeeder;
use Illuminate\Console\Command;

class SeedAutoCAD21Days extends Command
{
    protected $signature = 'challenge:seed-autocad-21';

    protected $description = 'Seed/update challenge 21 Ngày Chinh Phục Tool AutoCAD bằng AI Agent';

    public function handle(): int
    {
        $this->call(Revit21DaysSeeder::class);
        $this->call(AutoCAD21DaysSeeder::class);

        return self::SUCCESS;
    }
}
