<?php
namespace Northplay\NorthplayApi\Controllers\Casino\API;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Northplay\NorthplayApi\Traits\ApiResponderTrait;
use Illuminate\Support\Facades\Auth;
use Northplay\NorthplayApi\Models\GamesModel;
class PlayGameController
{
   use ApiResponderTrait;
   public function __construct()
   {
      $this->game_model = new GamesModel;
   }

   public function retrieve(Request $request)
   {
      $id = $request->header('x-game-req');
      return array(
         "id" => $id,
         "auth" => $request->user(),
      );
      if(!$id) {
         abort(400, "x-game-req not specified");
      }
      $select_game = $this->game_model->where('id', $id)->first();
      if(!$select_game) {
         $select_game = $this->game_model->where('slug', $id)->first();
      }
      if(!$select_game) {
         abort(400, "Game was not found");
      }
      $main_data = [
         "game" => $select_game,
      ];
      $cache_data = false;
      return $this->responder_success($main_data, $cache_data, "MetadataController");
   }

}