<?php

namespace Northplay\NorthplayApi\Commands;

use Illuminate\Console\Command;

class ScoobiedogGamesImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'northplay:import-games';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $dog_controller = new \Northplay\NorthplayApi\Controllers\DogCallbackController;
        $dog_controller->insert_games();
        //
    }
}
