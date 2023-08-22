<?php
/**
 * NorthplayApi\Controllers\Integrations\Games\Bsg\BsgKernel
 *
 * PHP version 8.2
 *
 * @category Bsg
 * @package  NorthplayApi\Controllers\Integrations\Games\Bsg
 * @author   Ryan West
 */

namespace Northplay\NorthplayApi\Controllers\Integrations\Games\Bsg;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Northplay\NorthplayApi\Controllers\Integrations\GatewayTrait;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Cookie;

/**
 * BsgKernel class
 *
 * Class that contains functions to handle Bsg game integration.
 *
 * @category Bsg
 * @package  NorthplayApi\Controllers\Integrations\Games\Bsg
 * @access   public
 */

class BsgKernel
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
        $this->static_assets_url = "https://static-Bsg-eu-edgenetwork.play-gateway.com/BsgGaming/"; //+game_identifier
        $this->api_url = env('APP_URL')."/northplay/gw/bsg/game_event/"; //+session_id
        $this->session_url = env('APP_URL')."/northplay/play/bsg/"; //+session_id
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
        return $game_identifier;
    }

    /**
     * Create session function
     *
     * Creates session for Bsg game, and sets session and game session urls in session table
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
    
    public function set_real_session($session_id)
    {
        $session = $this->select_parent_session($session_id);
        $this->upsert_parent_session_storage($session_id, "game_debug",  $this->transform_game_id($session->game_id));
        $this->update_parent_session($session_id, "session_url", $this->session_url.$session_id);
        $this->update_parent_session($session_id, "state", "READY");
    }

    /**
     * Show function
     *
     * Renders the view for the Bsg game and returns the session data
     *
     * @param int $session_id Parent session identifier
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */

    public function show($session_id)
    {
		$session = $this->select_parent_session($session_id);
        $game_identifier = $this->transform_game_id($session->game_id);
        $data = [
            "session_id" => $session_id,
            "game_identifier" => $game_identifier,
            "title" => $this->select_game($session->game_id)->name,
            "static_url" => $this->static_assets_url . $game_identifier . '/',
            "api_url" => $this->api_url . $session_id,
            "currency" => $session->currency,
            "storage" => $session->storage,
			"session" => ($this->is_development_state() ? $session : $session_id),
        ];
        return view("northplay::gateway-bsg-game")->with("session_data", $data);
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

        save_log("freespin", $arraySpin);

        if($data_origin['flow']['state'] === "freespins") {
            $this->freespin_player($request, $url, $arraySpin);
        } else {
            return $arraySpin;
        }

    }
        
    public function game_event($session_id, Request $request)
    {
        $internal_token = $session_id;
        $select_session = $this->select_parent_session($session_id);
        if($select_session->active === false) {
            abort(400, "Parent session expired.");
        }
        
        $url = Cache::get($internal_token.":BsgApiUrl".$select_session->game_session);
        if(!$url) {
            Cache::set($internal_token.":BsgApiUrl".$select_session->game_session, $select_session->storage["game_options"]["api"], now()->addMinutes(60));
            $url = Cache::get($internal_token.":BsgApiUrl".$select_session->game_session);
        }
        $request_arrayable = $request->toArray(); //we are cloning the reque
        
        
        if($request->command === 'init') {
            $fs_state = Cache::get($select_session->session_id."_Bsg_freespins_currentSpin");
            if($fs_state) {
                $fs = Cache::get($select_session->session_id."_Bsg_freespins");
                $fsVar = $fs_state."_spin";
                if(isset($fs['spin'][$fsVar])) {
                    Cache::set($select_session->session_id."_Bsg_freespins_currentSpin", ($fs_state+1));
                    $data_origin = $fs['spin'][$fsVar];
                } else {
                    $init_response = $this->proxy($request, $url);
                    $data_origin = json_decode($init_response->getContent(), true);
                }
            } else {
                $init_response = $this->proxy($request, $url);
                $init_details = Cache::get($session_id."::init");
                if(!$init_details) {
                    $data_origin = json_decode($init_response->getContent(), true);
                    if(isset($data_origin['options'])) {
                        Cache::set($session_id."::init", $data_origin, now()->addMinutes(1));
                    }
                } else {
                    $data_origin = Cache::get($session_id."::init");
                }
                if(!Cache::get($select_session->game_id.'_checked')) {
                    if(!isset($data_origin['api_version'])) {
                        $this->disable_game($select_session->game_id);
                        return $this->close_parent_session($session_id, "GAME_UNAVAILABLE");
                    } else {
                        Cache::set($select_session->game_id.'_checked', '1');
                    }
                }
            }
            
            $get_balance = $this->Bsg_balance_helper($internal_token);
            $data_origin['options']['currency']['code'] = $select_session->currency;
            $game_balance = $data_origin['balance']['game'] ?? 0;
            $data_origin['balance'] = [];
            $data_origin['balance']['game'] = $game_balance;
            $data_origin['balance']['wallet'] = (int) $get_balance;
            return $data_origin;
        }


        if($request->command === 'spin' || $request->command === 'freespin' || $request->command === 'gamble' || $request->command === 'close') {
            $hidden_cache_key = $internal_token.':BsgHiddenBalance:'.$select_session->game_session;    
            $fs_state = Cache::get($select_session->session_id."_Bsg_freespins_currentSpin");
            if($fs_state) {
                $fs = Cache::get($select_session->session_id."_Bsg_freespins");
                $fsVar = $fs_state."_spin";
                if(isset($fs['spin'][$fsVar])) {
                    Cache::set($select_session->session_id."_Bsg_freespins_currentSpin", ($fs_state+1));
                    $data_origin = $fs['spin'][$fsVar];
                } else {
                    $init_response = $this->proxy($request, $url);
                    $data_origin = json_decode($init_response->getContent(), true);
                }
            } else {
                $init_response = $this->proxy($request, $url);
                $data_origin = json_decode($init_response->getContent(), true);
            }

                

            if(isset($data_origin['flow']['state'])) {
                if($data_origin['flow']['state'] === "freespins") {
                    Cache::set($select_session->session_id."_Bsg_freespins", $this->freespin_player($request, $url, array()));
                    Cache::set($select_session->session_id."_Bsg_freespins_currentSpin", 1);
                }
            }

            // calculate balance differences from real session, multiplied by the bet value (as balance differences will be on min. bet settings)
            // we store the previous balance in cache, if it is missing we will set it to the current balance
            $bridge_balance = (int) Cache::get($hidden_cache_key);
            if(isset($data_origin['balance'])) {
            $current_balance = (int) $data_origin['balance']['wallet'] + (int) $data_origin['balance']['game'];

            if(!$bridge_balance) {
                Cache::set($hidden_cache_key, (int) $current_balance, now()->addMinutes(60));
                $another_response = $this->proxy($request, $url);
                $data_origin = json_decode($another_response->getContent(), true);

                $bridge_balance = (int) Cache::get($hidden_cache_key);
                $current_balance = (int) $data_origin['balance']['wallet'] + (int) $data_origin['balance']['game'];
                $data_origin['log'] = "cache key missing";
            }

            if($bridge_balance !== $current_balance) {
                if($bridge_balance > $current_balance) {
                    $winAmount = 0;
                    $betAmount = (int) ($bridge_balance - $current_balance);
                } else {
                    $betAmount = 0;
                    $winAmount = (int) ($current_balance - $bridge_balance);
                }
                Cache::set($hidden_cache_key, (int) $current_balance);
                $process_and_get_balance = (int) $this->convert_balance_helper($this->process_game($internal_token, ($betAmount ?? 0), ($winAmount ?? 0), $data_origin));
                $data_origin['balance']['game'] = (int) $data_origin['balance']['game'];
                $data_origin['balance']['wallet'] = (int) $process_and_get_balance;
                } else {
                    Cache::set($hidden_cache_key, (int) $current_balance);
                    $get_balance = $this->user_balance($internal_token);
                    $data_origin['balance']['game'] = (int) $data_origin['balance']['game'];
                    $data_origin['balance']['wallet'] = (int) $get_balance;
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
        Cache::set($session_id.':BsgHiddenBalance:'.$select_session->game_session, (int) $init_balance);
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