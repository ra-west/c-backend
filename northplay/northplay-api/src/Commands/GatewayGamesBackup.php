<?php

namespace Northplay\NorthplayApi\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class GatewayGamesBackup extends Command
{
    public $signature = 'gateway:game-backup';

    public $description = 'Import games from external source';

    public function handle(): int
    {

        $choice = $this->choice("Do you wish to create new backup or restore existing backup?", [
            "restore",
            "backup",
        ]);

        if($choice === "restore") {
            $this->info("Make sure to have the gameslist JSON in storage/ path");
            $recent_gameslist_id = Cache::get("recent_gamebackup_id");
            $suggested_file = "game-list-xx.json";
            $suggested_file_tags = "game-tags-xx.json";
            if($recent_gameslist_id) {
                $suggested_file = $recent_gameslist_id;
            }
            $recent_gamebackup_tags_id = Cache::get("recent_gamebackup_tags_id");
            if($recent_gamebackup_tags_id) {
                $suggested_file_tags = $recent_gamebackup_tags_id;
            }

            $games = $this->ask("What is games-list file name?", $suggested_file);
            if($suggested_file !== $games) {
                Cache::set("recent_gamebackup_id", $games);
            }
            $tags = $this->ask("What is games-tags file name?", $suggested_file_tags);
            if($suggested_file_tags !== $tags) {
                Cache::set("recent_gamebackup_tags_id", $tags);
            }

            $this->restore($games, $tags);
        } else {
            $this->backup();
        }

        return self::SUCCESS;
    }

    public function restore($games, $tags) {
        $softswiss_model = new \Northplay\NorthplayApi\Models\SoftswissGameModel;
        $softswiss_tags_model = new \Northplay\NorthplayApi\Models\SoftswissGameTagModel;

        $storage_location = storage_path($games);
        $storage_location2 = storage_path($tags);
        $games = json_decode(file_get_contents($storage_location), true);
        $tags = collect(json_decode(file_get_contents($storage_location2), true));

        $skipped = 0;
        $imported = 0;
        $i = 0;

        foreach($games as $game) {
            if($softswiss_model->where("slug", $game['slug'])->first()) {
                echo "[".$i."] ".$game['slug']." exist already \n";
                $skipped++;
            } else {
                $old_id = $game['id'];
                unset($game['id']);
                $softswiss_model->insert($game);
                $new_id = $softswiss_model->where("slug", $game['slug'])->first()['id'];
                $select_tags = $tags->where("game_id", $old_id);
                foreach($select_tags as $tagged) {
                    $tagged['game_id'] = $new_id;
                    $softswiss_tags_model->insert($tagged);
                    $this->info($game['slug'].' added tag: '.$tagged['tag']);
                }

                echo "[".$i."] ".$game['slug']." imported \n";
                $imported++;
            }
            $i++;
        }

        $this->info("IMPORTED: ".$imported);
        $this->error("SKIPPED: ".$skipped);

    }

    public function backup() {
        $softswiss_model = new \Northplay\NorthplayApi\Models\SoftswissGameModel;
        $timestamped = str_replace(":", "-", str_replace(" ", "_", now_nice()));

        $list_id = 'backup-list-'.$timestamped.'-list-'.$softswiss_model->count().'.json';
        $tags_id = 'backup-tags-'.$timestamped.'-tags-'.$softswiss_model->count().'.json';

        $storage_location = storage_path($list_id);
        $store = file_put_contents($storage_location, $softswiss_model->all());



        $this->info("Games stored: ".count(json_decode(file_get_contents($storage_location), true)));

        $softswiss_model_tags = new \Northplay\NorthplayApi\Models\SoftswissGameTagModel;
        $storage_location = storage_path($tags_id);

        $store = file_put_contents($storage_location, $softswiss_model_tags->all());
        $this->info("Games Tags stored: ".count(json_decode(file_get_contents($storage_location), true)));

    }
}
