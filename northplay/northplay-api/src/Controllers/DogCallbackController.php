<?php
namespace Northplay\NorthplayApi\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
class DogCallbackController
{
    protected $operator_key;

    public function __construct()
    {
        $this->config_model = new \Northplay\NorthplayApi\Models\ConfigModel;
        $this->host = $this->config_model->get_config_value("sd_host", "dev.northplay.me");
        $this->operator_key = $this->config_model->get_config_value("sd_apikey", "e03b960509a9f281b708de47ad1f1056");
        $this->operator_secret = $this->config_model->get_config_value("sd_secret", "JIRClyBP6GZO");
        $this->games = new \Northplay\NorthplayApi\Models\GamesModel;
    }

    public function get_list()
    {
        return Http::get('https://'.$this->host.'/api/gameslist/all');
    }

    public function callback(Request $request)
    {
        if($request->action === 'ping') {
            return $this->pong($request);
        }

        if($request->action === 'balance') {
            return $this->balance($request);
        }

        if($request->action === 'game') {
            return $this->game($request);
        }
        abort(403, 'Empty action.');
    }

    public function verify_sign($sign, $salt, $request)
    {
        $create_signature = hash_hmac('md5', $this->operator_secret, $salt); //recreate the signature
        if($create_signature === $sign) {
            return true;
        } else {
            Log::notice('Wrong security signature on callback. '.json_encode($request->all()));
            die();

        }
    }

    public function pong(Request $request)
    {
        $pong_hash = hash_hmac('md5', $this->operator_secret, $request->salt_sign);
        $data = [
            'status' => 200,
            'data' => [
                'pong' => $pong_hash,
            ],
        ];
        return response()->json($data, 200);
    }

    public function balance(Request $request)
    {
        //$player = new PlayerBalances;
        //$select_player = $player->select_player($request->player_operator_id, $request->currency);

        $data = [
            'status' => 200,
            'data' => [
                'balance' => (int) 55555,
            ],
        ];
        return response()->json($data, 200);
    }

    public function insert_games()
    {
        $url = 'https://'.$this->host.'/api/gameslist/all';
        echo $url;
        $list = json_decode(Http::get('https://'.$this->host.'/api/gameslist/all'), true);

        foreach($list as $game) {
            $select_current = $this->games->where('game_id', $game['slug'])->first();
            if(!$select_current) {
                $games = $this->games->insert([
                    'game_id' => $game['slug'],
                    'name' => $game['name'],
                    'cover' => $game['gid'].".png",
                    'provider' => $game['provider'],
                ]);
            }
        }
    }

    public function create_session($slug, $player_id, $currency)
    {
         $game_request = Http::get('https://games.northplay.me/api/createSession?game='.$slug.'&player=ryan&currency='.$currency.'&operator_key='.$this->operator_key.'&mode=real');
         save_log("Callback", json_decode($game_request, true));
         return $game_request['message']['session_url'];
    }

    public function game(Request $request)
    {
        //Log::notice(json_encode($request->all()));
        //$player = new PlayerBalances;

        $this->verify_sign($request->sign, $request->salt_sign, $request);

        //$balance_after_game = $player->process_game($request->player_operator_id, $request->bet, $request->win, $request->currency, $request->game, $request->all());
        //$select_player = $player->select_player($request->player_operator_id, $request->currency);
        $data = [
            'status' => 200,
            'data' => [
                'balance' =>  (int) 5555,
            ],
          ];
          return response()->json($data, 200);
    }


}
