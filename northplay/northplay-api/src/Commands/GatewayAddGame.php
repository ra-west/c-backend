<?php

namespace Northplay\NorthplayApi\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class GatewayAddGame extends Command
{
    public $signature = 'gateway:add-game-manually';

    public $description = 'Import games from external source';

    public function handle(): int
    {
        $softswiss_model = new \Northplay\NorthplayApi\Models\SoftswissGameModel;

        $slug = $this->ask('Enter slug', "provider_id/game_id");
        
        if($softswiss_model->where("slug", $slug)->first()) {
            return "Slug already exist in database, choose a unique slug ID";
        }

        $title = $this->ask('Enter title', "Game Name");
        $provider = $this->ask('Enter provider id', "Provider ID");
        $source = $this->ask('Enter source id', "manual");
        $demo_game = $this->ask('Enter demo game URL', "");
        $date_now = now_nice();

        $row = $softswiss_model->insert([
                "slug" => $slug,
                "title" => $title,
                "provider" => $provider,
                "source" => $source,
                "demo_game" => $demo_game,
                "created_at" => $date_now,
                "updated_at" => $date_now,
        ]);

        return self::SUCCESS;
    }
}




