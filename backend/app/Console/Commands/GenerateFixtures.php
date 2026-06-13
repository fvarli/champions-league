<?php

namespace App\Console\Commands;

use App\Exceptions\FixtureGenerationException;
use App\Services\FixtureGenerationService;
use Illuminate\Console\Command;

class GenerateFixtures extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'league:generate-fixtures';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the double round-robin fixture schedule for the league';

    /**
     * Execute the console command.
     */
    public function handle(FixtureGenerationService $service): int
    {
        try {
            $service->generate();
        } catch (FixtureGenerationException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info('Fixtures generated successfully.');

        return self::SUCCESS;
    }
}
