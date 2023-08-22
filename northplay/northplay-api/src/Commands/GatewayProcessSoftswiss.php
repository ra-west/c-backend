<?php

namespace Northplay\NorthplayApi\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GatewayProcessSoftswiss extends Command
{
    public $signature = 'gateway:process-softswiss';

    public $description = 'Process existing games to get demo link';

    public function handle(): int
    {
        $this->comment('Northplay - Testing');
        $softswiss_model = new \Northplay\NorthplayApi\Models\SoftswissGameModel;
        echo $softswiss_model->count();
        
        foreach($softswiss_model->where("demo_game_direct")->get() as $game) {
            \Northplay\NorthplayApi\Jobs\GetDirectDemoLink::dispatch($game['id']);
			echo "Dispatched job for ".$game['id'];
        }

        return self::SUCCESS;
    }
}
