<?php

namespace Northplay\NorthplayApi\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GatewayImportSoftswiss extends Command
{
    public $signature = 'gateway:import-softswiss';

    public $description = 'Import games from external source';

    public function handle(): int
    {
        $this->comment('Northplay - Import Softswiss Games');
        $provider = $this->ask('Which provider do you want to update?');
        $url = $this->ask('Enter softswiss gamelist URL location', "https://bitstarz.com/api/games/allowed_desktop");
        \Northplay\NorthplayApi\Jobs\ImportSoftswiss::dispatch($url, $provider);

/*
        $softswiss_model = new \Northplay\NorthplayApi\Models\SoftswissGameModel;

        foreach($softswiss_model->where("demo_game_direct")->get() as $game) {
            \Northplay\NorthplayApi\Jobs\GetDirectDemoLink::dispatch($game['id']);
        }

        \Northplay\NorthplayApi\Jobs\ImportSoftswiss::dispatch();

    */

        return self::SUCCESS;
    }
}
