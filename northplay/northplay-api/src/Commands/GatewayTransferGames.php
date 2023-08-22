<?php

namespace Northplay\NorthplayApi\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GatewayTransferGames extends Command
{
    public $signature = 'gateway:transfer-games';

    public $description = 'Process existing games to get demo link';

    public function handle(): int
    {
        $this->comment('Northplay - Testing');
        $softswiss_model = new \Northplay\NorthplayApi\Models\SoftswissGameModel;
        $games_model = new \Northplay\NorthplayApi\Models\GamesModel;
        foreach($softswiss_model->where("provider", "mascot")->where("provider", "bgaming")->get() as $game) {
            $select_game = $games_model->where("game_id", $game['slug'])->first();
            if(!$select_game) {
                $games_model->insert([
                    'game_id' => $game['slug'],
                    'name' => $game['title'],
                    'cover' => $game['slug'],
                    'provider' => $game['provider'],
                    'method_id' => $game['slug'],
                    'method_type' => "softswiss",
                ]);
            }
        }

        return self::SUCCESS;
    }
}
