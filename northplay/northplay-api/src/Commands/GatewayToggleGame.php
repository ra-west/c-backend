<?php

namespace Northplay\NorthplayApi\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class GatewayToggleGame extends Command
{
    public $signature = 'gateway:toggle-game-active';

    public $description = 'Import games from external source';

    public function handle(): int
    {
        $softswiss_model = new \Northplay\NorthplayApi\Models\SoftswissGameModel;

        $slug = $this->ask('Enter slug', "provider_id/game_id");
        $select_game = $softswiss_model->where("slug", $slug)->first();
        if(!$select_game) {
            echo "Slug not found";
            return self::SUCCESS;
        }
        
        echo json_encode($softswiss_model->where("slug", $slug)->first());
        echo "\n";

        if($select_game->active === true) {
            $disable_game = $this->ask('Game is enabled, do you want to disable it?', "yes");
            if($disable_game === "yes") {
                $softswiss_model->where("slug", $slug)->update([
                    "active" => false,
                    "updated_at" => now(),
                ]);
            }
        } else {
                $disable_game = $this->ask('Game is disabled, do you want to enable it?', "yes");
                if($disable_game === "yes") {
                    $softswiss_model->where("slug", $slug)->update([
                        "active" => true,
                        "updated_at" => now(),
                    ]);
                }
        }

        echo json_encode($softswiss_model->where("slug", $slug)->first());


        return self::SUCCESS;
    }
}




