<?php
namespace Northplay\NorthplayApi\Controllers\Casino\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Northplay\NorthplayApi\Traits\ApiResponderTrait;
use Northplay\NorthplayApi\Resources\GameRowResource;
use Northplay\NorthplayApi\Models\SoftswissGameModel;
use Northplay\NorthplayApi\Models\ConfigModel;
use Northplay\NorthplayApi\Controllers\Casino\API\CasinoTrait;

class GamedataController
{
    use ApiResponderTrait, CasinoTrait;
    
    protected $cache_timer;
    protected $config_model;
    protected $reset_cache_check_timer;

    public function __construct()
    {
        $this->config_model = new ConfigModel;
        $this->cache_timer = 300;
        $this->reset_cache_check_timer = 300;
        $this->reset_cache_check();
    }

    public function retrieve(Request $request)
    {
        if(!$request->key) {
            abort(400, "Game key not set.");
        }
        $request->validate([
            'key' => ['required', 'string', 'max:255'],
        ]);

        $data =  $this->metadata_cache_helper($request->key, $this->cache_timer);

        $main_data = [            
            "data" => $data['data'] ?? array(),
            "cached_at" => $data['cached'] ?? array(),
            "success" => true,
        ];
        return response()->json($main_data, 200);
    }

    public function build_data($datakey)
    {
        $games_data = $this->gamelist('cached');

        if($datakey === "popular") {
            $collection = $games_data->where("active", true)->random(14);
            $manual_game = $games_data->where("slug", "ryangames/HappyBillions")->first();
            if($manual_game) {
                $collection[0] = $manual_game;
            }
            return $collection;
        }
        if($datakey === "new") {
            return $games_data->where("active", true)->random(14);
        }
        if($datakey === "provider_bgaming") {
            return $games_data->where("provider", "bgaming")->where("active", true)->random(16);
        }
        if($datakey === "provider_pragmatic") {
            return $games_data->where("provider", "pragmatic")->where("active", true)->random(16);
        }
        if($datakey === "provider_egt") {
            return $games_data->where("provider", "egt")->where("active", true)->random(16);
        }
        if($datakey === "provider_netent") {
            return $games_data->where("provider", "netent")->where("active", true)->random(16);
        }
        if($datakey === "provider_wazdan") {
            return $games_data->where("provider", "wazdan")->where("active", true)->random(16);
        }
        if($datakey === "provider_amatic") {
            return $games_data->where("provider", "amatic")->where("active", true)->random(16);
        }
        if($datakey === "provider_novomatic") {
            return $games_data->where("provider", "novomatic")->where("active", true)->random(16);
        }
        if($datakey === "provider_mascot") {
            return $games_data->where("provider", "mascot")->where("active", true)->random(16);
        }
        if($datakey === "reward_boosted") {
            return $games_data->where("active", true)->random(16);
        }
    }

    public function reset_cache_check() {
        $reset_cache = Cache::get("reset_cache_check");
        if(!$reset_cache) {
            $reset_cache = $this->config_model->get_config_value("force_cache_reset_metadata", "no", "environment");
            Cache::set("reset_cache_check", $reset_cache, now()->addSeconds($this->reset_cache_check_timer));
        }
        if($reset_cache === "yes") {
            $this->config_model->update_config_value("force_cache_reset_metadata", "no", "environment");
            Cache::forget("metadata_popular");
            Cache::forget("metadata_new");
            Cache::forget("metadata_provider_mascot");
            Cache::forget("metadata_provider_pragmatic");
            Cache::forget("metadata_provider_bgaming");
            Cache::forget("metadata_provider_egt");
            Cache::forget("metadata_provider_wazdan");
            Cache::forget("metadata_provider_netent");
            Cache::forget("metadata_provider_novomatic");
            Cache::forget("metadata_provider_amatic");
            Cache::forget("metadata_reward_boosted");
            Cache::forget("gamerow_gameslist");
        }
    }


    public function metadata_cache_helper($datakey, $cache_seconds) {
        $cache_key = "metadata_".$datakey;
        $cached_array = Cache::get($cache_key);
        if(!$cached_array) {
            $build_data = $this->build_data($datakey);
            $data = array(
                "success" => true,
                "cached" => now_nice(),
                "data" => $build_data,
            );
            if($build_data) {
                Cache::set($cache_key, $data, $cache_seconds);
                $cached_array = Cache::get($cache_key);
            } else {
                return array(
                    "success" => false,
                    "cached" => now_nice(),
                    "data" => $build_data,
                );
                save_log("GamedataController", "Building data for ".$cache_key." seems to return a empty result.");
            }

            if(!$cached_array) {
                save_log("GamedataController", "Error trying to retrieve ".$cache_key." key from cache while just tried to set.");
                return array(
                    "success" => "Error trying to retrieve ".$cache_key." key from cache while just tried to set.",
                    "cached" => false,
                    "data" => $this->build_data($datakey),
                );
            }
        }
        return $cached_array;
    }

}