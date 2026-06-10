<?php

namespace App\Console\Commands;

use App\Models\Pincode;
use Illuminate\Console\Command;

class SeedPincodes extends Command
{
    protected $signature = 'pincodes:seed {count=50}';
    protected $description = 'Seed the pincodes table with available pincodes';

    public function handle(): int
    {
        $count = $this->argument('count');
        $inserted = 0;
        $skipped = 0;

        $this->info("Generating {$count} pincodes...");

        for ($i = 0; $i < $count; $i++) {
            $code = sprintf('%04d', random_int(1000, 9999));

            if (Pincode::where('code', $code)->exists()) {
                $skipped++;
                continue;
            }

            Pincode::create(['code' => $code, 'available' => true]);
            $inserted++;
        }

        $this->info("Done! Inserted {$inserted} pincodes ({$skipped} duplicates skipped).");

        return Command::SUCCESS;
    }
}
