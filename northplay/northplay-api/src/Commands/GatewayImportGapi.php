<?php

namespace Northplay\NorthplayApi\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GatewayImportGapi extends Command
{
    public $signature = 'gateway:import-gapi';

    public $description = 'Import games from external source';

    public function handle(): int
    {
        $this->comment('Northplay - Import Gapi.lol Games');
        $provider = $this->ask('Which provider do you want to update?');
        $url = $this->ask('Enter gapi gamelist URL', "https://admin.gapi.lol/api/games/all");
        \Northplay\NorthplayApi\Jobs\ImportGapiGames::dispatch($url, $provider);

        return self::SUCCESS;
    }
}
