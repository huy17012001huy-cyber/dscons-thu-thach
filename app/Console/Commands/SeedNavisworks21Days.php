<?php

namespace App\Console\Commands;

use Database\Seeders\Navisworks21DaysSeeder;
use Database\Seeders\Revit21DaysSeeder;
use Illuminate\Console\Command;

class SeedNavisworks21Days extends Command
{
    protected $signature = 'challenge:seed-navisworks-21';

    protected $description = 'Seed/update challenge 21 Ngày Chinh Phục Tool Navisworks bằng AI Agent';

    public function handle(): int
    {
        $this->call(Revit21DaysSeeder::class);
        $this->call(Navisworks21DaysSeeder::class);

        return self::SUCCESS;
    }
}
