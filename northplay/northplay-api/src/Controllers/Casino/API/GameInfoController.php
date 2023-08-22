<?php

namespace Northplay\NorthplayApi\Controllers\Casino\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Northplay\NorthplayApi\Controllers\Casino\API\CasinoTrait;

class GameInfoController extends Controller
{
    use CasinoTrait;

        public function __construct()
        {
            $this->games_model = collect($this->gamelist());
            $this->games_cached_model = collect($this->gamelist('cached'));
        }
        
        public function retrieve(Request $request) {
            validate([
                'id' => ['required', 'string', 'max:100'],
            ], $request->all());


            $response = array(
                "success" => true,
                "game" => $this->game_info($request->id),
                "suggested_games" => $this->suggested_games($game->provider),
            );

            return $response;
        }

        public function game_info($id) {
            $select_game = $this->games_model->firstWhere("id", $id);
            if(!$select_game) {
                $select_game = $this->games_model->firstWhere("slug", $id);
                if(!$select_game) {
                    abort(404, "Game not found");
                }
            }
            return $select_game;
        }

      public function suggested_games($provider_id) {
            $cache_key = $provider_id."_suggested_games";
            $suggested_games = Cache::get($cache_key);

            if(!$suggested_games) {
                $suggested_games = $this->games_cached_model->where('provider', $provider_id);
                if($suggested_games->count() < 12) {
                    $suggested_games = $this->games_cached_model->random(12);
                } else {
                    $suggested_games = $games_cached_model->random(12);
                    $suggested_games[rand(0,12)] = $this->games_cached_model->where('provider', '!=', $provider_id)->random(1);
                    $suggested_games[rand(0,12)] = $this->games_cached_model->where('provider', '!=', $provider_id)->random(1);
                }
                Cache::set($cache_key, $suggested_games, now()->addMinutes(5));
            }
            return $suggested_games;
        }
}
