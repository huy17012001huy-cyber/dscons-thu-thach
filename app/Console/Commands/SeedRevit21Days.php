<?php

namespace App\Console\Commands;

use Database\Seeders\Revit21DaysSeeder;
use Illuminate\Console\Command;

class SeedRevit21Days extends Command
{
    protected $signature = 'challenge:seed-revit-21';

    protected $description = 'Seed/update chương trình 21 Ngày Chinh Phục Tool Revit bằng AI Agent';

    public function handle(): int
    {
        $this->call(Revit21DaysSeeder::class);

        return self::SUCCESS;
    }
}
