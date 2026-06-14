<?php

namespace App\Console\Commands;

use App\Services\DemoResetService;
use Illuminate\Console\Command;

class DemoReset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'league:demo-reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset the demo league: clear fixtures and ensure the seeded teams exist';

    /**
     * Execute the console command.
     */
    public function handle(DemoResetService $service): int
    {
        $service->reset();

        $this->info('Demo league reset: fixtures cleared and teams ready.');

        return self::SUCCESS;
    }
}
