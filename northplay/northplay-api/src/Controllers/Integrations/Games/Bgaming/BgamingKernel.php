<?php
/**
 * NorthplayApi\Controllers\Integrations\Games\Bgaming\BgamingKernel
 *
 * PHP version 8.2
 *
 * @category Bgaming
 * @package  NorthplayApi\Controllers\Integrations\Games\Bgaming
 * @author   Ryan West
 */

namespace Northplay\NorthplayApi\Controllers\Integrations\Games\Bgaming;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Northplay\NorthplayApi\Controllers\Integrations\GatewayTrait;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Cookie;
use Illuminate\Support\Facades\RateLimiter;

/**
 * BgamingKernel class
 *
 * Class that contains functions to handle Bgaming game integration.
 *
 * @category Bgaming
 * @package  NorthplayApi\Controllers\Integrations\Games\Bgaming
 * @access   public
 */

class BgamingKernel
{
    use GatewayTrait;

    /**
     * Constructor function
     *
     * Initializes the urls to be used in API calls
     *
     * @return void
     */

    public function __construct()
    {
        $this->api_url = app_url()."/gw/bgaming/game_event/"; //+session_id
        $this->session_url = app_url()."/play/"; //+provider/session_id
    }

     public function get_provider($game_id)
     {
         $game_identifier = $this->select_game($game_id);
         $game_identifier = explode('/', $game_identifier->slug);
         if($game_identifier[0] === "softswiss") {
            return "bgaming";
         }
         return $game_identifier[0];
     }

    /**
     * Transform game id function
     *
     * Extracts slug from the game identifier and returns it
     *
     * @param int $game_id Game identifier to be transformed
     * 
     * @return string|null
     */

    public function transform_game_id($game_id)
    {
        $game_identifier = $this->select_game($game_id);
        $game_identifier = explode('/', $game_identifier->slug);
        return $game_identifier[1];
    }

    /**
     * Create session function
     *
     * Creates session for Bgaming game, and sets session and game session urls in session table
     *
     * @param int $session_id Parent session identifier
     *
     * @return void
     */

    public function create_session($session_id)
    {
        $session = $this->select_parent_session($session_id);
        $this->set_real_session($session_id);

    }

    public function bgaming_balance_helper($internal_token)
    {
        $balance = $this->user_balance($internal_token);
        return $this->convert_balance_helper($balance);
    }

    
    public function convert_balance_helper($array, $currency)
    {
        if($currency === "USD") {
            return (int) ($array['total_usd'] * 100);
        } elseif($currency === "GBP") {
            return (int) ($array['total_gbp'] * 100);
        } elseif($currency === "EUR") {
            return (int) ($array['total_eur'] * 100);
        }
    }
    
    public function set_real_session($session_id)
    {
        $session = $this->select_parent_session($session_id);
        $this->create_game_session($session_id);
        $this->update_parent_session($session_id, "session_url", $this->session_url.$this->get_provider($session->game_id).'/'.$session_id);
        $this->update_parent_session($session_id, "state", "READY");
    }
    
    public function create_game_session($session_id)
    {
        $session = $this->select_parent_session($session_id);
        $game_identifier = $this->transform_game_id($session->game_id);
        if($game_identifier === "HappyBillions") {
            $game_identifier = "BonanzaBillion";
        }

        $url = "https://bgaming-network.com/prm/launch?cid=parimatch&productId=".$game_identifier."&lang=en&targetChannel=desktop&consumerId=bgaming&providerId=EVA_SLOTS_BGAMING";
        $real_session_url = $this->get_redirect_url($url);
        $game_session = explode("play_token=", $real_session_url);
        $play_token = $game_session[1];
        
        if(!isset($game_session[1])) {
            $this->update_parent_session($session_id, "state", "FAIL");
            abort(500, "Error");
        }

        $http_get = Http::get($real_session_url);
        $options_inbetween = in_between("__OPTIONS__ =", "}}", $http_get->body());
        $options = json_decode($options_inbetween."}}", true);

        $session_history_count = array($play_token => array(
            "win" => 0,
            "bet" => 0,
            "created_at" => time(),
            "active" => true,
            "bridge_balance" => 100000,
        ));

        if(isset($session->storage['game_session_history'])) {
            $history = $session->storage['game_session_history'];
            foreach($history as $key=>$game_session) {
                $history[$key]['active'] = false;
            }
            $history[$play_token] = [];
            $history[$play_token]['win'] = 0;
            $history[$play_token]['bet'] = 0;
            $history[$play_token]['active'] = true;
            $history[$play_token]['created_at'] = time();
            $history[$play_token]['bridge_balance'] = 100000;


            $this->upsert_parent_session_storage($session_id, "game_session_history", $history);
        } else {
            $this->upsert_parent_session_storage($session_id, "game_session_history", $session_history_count);
        }
        
        $this->upsert_parent_session_storage($session_id, "game_options", $options);
        $this->upsert_parent_session_storage($session_id, "created_at", time());
        $this->upsert_parent_session_storage($session_id, "game_session_url", $real_session_url);
        $this->update_parent_session($session_id, "game_session", $play_token);

    }

    /**
     * Show function
     *
     * Renders the view for the Bgaming game and returns the session data
     *
     * @param int $session_id Parent session identifier
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */

    public function show($session_id)
    {
		$session = $this->select_parent_session($session_id);
        $game_identifier = $this->transform_game_id($session->game_id);
        $game_loader_source = "https://cdn.bgaming-network.com/html/".$session->storage["game_options"]["identifier"]."/loader.js";
        $resources_path = $session->storage["game_options"]["resources_path"];
        
        $game_loader_source = "https://cdn.bgaming-network.com/html/".$session->storage["game_options"]["identifier"]."/loader.js";
        $game_bundle_source = "https://cdn.bgaming-network.com/html/".$session->storage["game_options"]["identifier"]."/bundle.js";
        if($game_identifier === "HappyBillions") {
            $this->api_url = app_url()."/gw/ryangames/game_event/";
            $resources_path = app_url()."/game_assets/HappyBillions";
            $game_loader_source = app_url()."/game_assets/HappyBillions/loader.js";
            $game_bundle_source = app_url()."/game_assets/HappyBillions/basic/v0.0.1/bundle.js";
        }
        $this->upsert_parent_session_storage($session_id, "init_apiresponse", null);

        $data = [
            "session_id" => $session_id,
            "game_identifier" => $game_identifier,
            "game_provider" => $this->get_provider($session->game_id),
            "title" => $this->select_game($session->game_id)->name,
            "games_loader_source" => $game_loader_source,
            "games_bundle_source" => $game_bundle_source,
            "resources_path" => $resources_path,
            "api_url" => $this->api_url . $session_id,
            "currency" => $session->currency,
            "storage" => $session->storage["game_options"],
			"session" => ($this->is_development_state() ? $session : $session_id),
        ];
        return view("northplay::gateway-bgaming-game")->with("session_data", $data);
    }

    public function freespin_player($request, $url, $arraySpin)
    {
        $spinCount = sizeof($arraySpin) + 1;
        $spinVar = $spinCount."_spin";
        $request_clone = $request->toArray();
        $request_clone['command'] = 'init';
        $request_cloned = (clone $request)->replace($request_clone); 
        $init_response = $this->proxy($request_cloned, $url);
        $init_data_origin = json_decode($init_response->getContent(), true);
        if(!isset($arraySpin['init'])) {
            $arraySpin['init'] = [];
        }
        $arraySpin['init'][$spinVar] = $init_data_origin;
        $request_clone['command'] = 'freespin';
        $request_cloned = (clone $request)->replace($request_clone); 
        $init_response = $this->proxy($request_cloned, $url);
        $data_origin = json_decode($init_response->getContent(), true);
        if(!isset($arraySpin['spin'])) {
            $arraySpin['spin'] = [];
        }
        $arraySpin['spin'][$spinVar] = $data_origin;

        if($data_origin['flow']['state'] === "freespins") {
            $this->freespin_player($request, $url, $arraySpin);
        } else {
            return $arraySpin;
        }
    }
        
    public function game_event($session_id, Request $request)
    {
        $internal_token = $session_id;
        $rateLimitId = $internal_token;
        $rateLimit = RateLimiter::attempt(
            'entrySession:'.$rateLimitId,
            $perMinute = 200,
            function() {
            }
        );

		if (!$rateLimit) {
            return response()->json(array("errors" => 
            array(array(
                "code" => 204,
                "desc" => "unavailable_action",
            )
            )), 400);
		}

        $select_session = $this->select_parent_session($session_id);
        $real_mode = $select_session->api_mode === "real" ? "real" : null;

        if($select_session->active === false) {
               return response()->json(array("errors" => 
                array(array(
                    "code" => 105,
                    "desc" => "unavailable_action",
                )
                )), 400);
        }

        $url = $select_session->storage["game_options"]["api"];

        if($request->command === 'init') {
            $session_storage = $this->get_parent_session_storage($session_id);
            $loadResponseFromStorage = false;
            if(isset($session_storage['init_apiresponse'])) {
                if(isset($session_storage['init_apiresponse']['flow'])) {
                    $loadResponseFromStorage = true;
                }
            }

            if($loadResponseFromStorage === false) {
                $init_response = $this->proxy($request, $url);
                $data_origin = json_decode($init_response->getContent(), true);
                if(isset($data_origin['api_version'])) {
                    $this->upsert_parent_session_storage($session_id, "init_apiresponse", $data_origin);
                } 
            } else {
                $data_origin = $session_storage['init_apiresponse'];
            }
            $data_origin['options']['currency']['code'] = $select_session->currency;
            if($real_mode) {
                if(isset($data_origin['balance'])) {
                    $get_balance = $this->convert_balance_helper($this->user_balance($internal_token), $select_session->currency);
                    $data_origin['balance']['wallet'] = (int) ($get_balance - $data_origin['balance']['game']);
                }
            }
            return $data_origin;
        }


        if($request->command === 'spin' || $request->command === 'freespin' || $request->command === 'gamble' || $request->command === 'close') {
            $init_response = $this->proxy($request, $url);
            $data_origin = json_decode($init_response->getContent(), true);
            if($real_mode) {
                $get_balance =  (int) $this->convert_balance_helper($this->user_balance($internal_token), $select_session->currency);
            } else {
                $get_balance = (int) ($data_origin['balance']['wallet'] + $data_origin['balance']['game']);
            }

            $bet_options = $request->options;
            $bet_amount = $bet_options['bet'];

            if(isset($bet_options['purchased_feature'])) {
                $session_storage = $this->get_parent_session_storage($session_id);
                $init_options = $session_storage['init_apiresponse']['options']['feature_options']['feature_multipliers'][$bet_options['purchased_feature']];
                $bet_amount = $bet_amount / 100 * $init_options;
            }

            if($get_balance < $bet_amount) {
                return response()->json(array("errors" => 
                array(array(
                    "code" => 301,
                    "desc" => "unavailable_action",
                )
                )), 400);
            }

            if(isset($data_origin['errors'])) {
                $error = [
                    "game_response" => $data_origin,
                    "session_id" => $session_id,
                    "request" => $request->all(),
                    "solution" => "creating new session",
                ];
                if(isset($select_session->storage["recent_error"])) {
                    $secondsAgo = time() - $select_session->storage["recent_error"];
                    if($secondsAgo < 1) {
                        return $this->close_parent_session($session_id, "EXTERNAL_ERROR");
                    }
                }
                $this->upsert_parent_session_storage($session_id, "recent_error", time());
                $this->create_game_session($session_id);
                return $this->game_event($session_id, $request);
            }

            $round_id = $data_origin['flow']['round_id'];
            // calculate balance differences from real session, multiplied by the bet value (as balance differences will be on min. bet settings)
            // we store the previous balance in cache, if it is missing we will set it to the current balance
     
            $game_session_stats = $select_session->storage['game_session_history'];
            if(!isset($game_session_stats[$select_session->game_session]['bridge_balance'])) {
                $this->create_game_session($session_id);
                return $this->game_event($session_id, $request);
            }
            $bridge_balance = $game_session_stats[$select_session->game_session]['bridge_balance'];
            save_log("bridge_balance", $bridge_balance);
            if(isset($data_origin['balance'])) {
            $current_balance = (int) ($data_origin['balance']['wallet'] + $data_origin['balance']['game']);
            $game_session_stats[$select_session->game_session]['bridge_balance'] = $current_balance;
            $this->upsert_parent_session_storage($session_id, "game_session_history", $game_session_stats);

                if($real_mode) {
                    if($bridge_balance !== $current_balance) {
                        $change = $current_balance - $bridge_balance;
                        if($change < 0) {
                            $betAmount = (int) str_replace('-', '', ($current_balance - $bridge_balance));
                            $winAmount = 0;
                        } else {
                            $betAmount = (int) 0;
                            $winAmount = str_replace('-', '', ($current_balance - $bridge_balance));                    
                        }
                        $process_and_get_balance = $this->convert_balance_helper($this->process_game(
                            $internal_token,
                            $round_id,
                            $betAmount,
                            $winAmount,
                            $data_origin
                        ), $select_session->currency);
                        save_log("betamount", $betAmount);
                        save_log("winAmount", $winAmount);
                        save_log("init_betamount", $bet_amount);

                        $game_session_stats[$select_session->game_session]['win'] = $winAmount + $game_session_stats[$select_session->game_session]['win'];
                        $game_session_stats[$select_session->game_session]['bet'] = $betAmount + $game_session_stats[$select_session->game_session]['bet'];

                        $data_origin['balance']['game'] = 0;
                        $data_origin['balance']['wallet'] = (int) $process_and_get_balance;
                    } 
                    
                    
                    $get_balance =  (int) $this->convert_balance_helper($this->user_balance($internal_token), $select_session->currency);
                $data_origin['balance']['wallet'] = (int) $get_balance;

                $game_session_stats[$select_session->game_session]['bridge_balance_updated'] = time();

                $this->upsert_parent_session_storage($session_id, "game_session_history", $game_session_stats);
                }
            }

            return $data_origin;
        }

        return [];
	}

    public function session_transfer($session_id) // creates new real session and assigns it to the parent session
    {
		$this->create_session($session_id);		
		$select_session = $this->select_parent_session($session_id);
        $init_balance = 10000; // value of starting balance on real session - defaulted 100.00, used when doing session transfer
        Cache::set($session_id.':bgbal:'.$select_session->game_session, (int) $init_balance);
    }

    public function replaceInBetweenDataset($a, $b, $replace_from_data, $replace_in_data)
    {
        $value_from = in_between($a, $b, $replace_from_data);
        $value_in = in_between($a, $b, $replace_in_data);
        return str_replace($value_in, $value_from, $replace_in_data);
    }

    public function replaceInBetweenValue($a, $b, $data, $value)
    {
        $value_from = in_between($a, $b, $data);
        return str_replace($value_from, $value, $data);
    }
}