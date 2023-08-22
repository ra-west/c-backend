<?php

namespace Northplay\NorthplayApi\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class NorthplayWebsocketMessage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'northplay:websocket-message {channel_id?} {type?} {message?}';

    protected $description = 'Command description';
    /**
     * Execute the console command.
     */


    public function handle(): void
    {

        $data = [
            "type" => "recent-games",
            "win" => 200,
            "loss" => 100,
            "game" => "softswiss/LuckyLadyMoon",
            "provider" => "bgaming",
            "date" => now(),
        ];

        $websocket_controller = new \Northplay\NorthplayApi\Controllers\Casino\WebsocketController;
        $websocket_controller->publish("pubstates", $data);
    }
}
