<?php
/**
 * NorthplayApi\Controllers\Integrations\Games\Amatic\AmaticKernel
 *
 * PHP version 8.2
 *
 * @category Amatic
 * @package  NorthplayApi\Controllers\Integrations\Games\Amatic
 * @author   Ryan West
 */
namespace Northplay\NorthplayApi\Controllers\Integrations\Games\Amatic;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Northplay\NorthplayApi\Controllers\Integrations\GatewayTrait;
use Illuminate\Support\Facades\Http;
use Cookie;
use Illuminate\Support\Facades\Cache;

class AmaticKernel
{
    use GatewayTrait;

    protected $casino_tag;
    protected $exit_url;
    protected $gapi_api_key;
    protected $gapi_api_id;
    protected $gapi_api_url;
    protected $secure_key;
    protected $gapi_callback_url;

    /**
     * The constructor function initializes the class properties
     */
    public function __construct()
    {
        $this->casino_tag = md5(config("northplay-api.tag"));
        $this->exit_url = app_url()."/gw/session-error";
        $this->gapi_api_key = "2a2cmHnGJ2aNtkJ1x8vvu0Hxjs4PG2H0";
        $this->gapi_api_id = 'Lrjm8MOSc6q7LnOP9uGyVlIiZcwHHvY4';
        $this->gapi_api_url = 'https://play.gapi.lol/api/games/post/';
        $this->secure_key = "21421-nOP9uGysS22" . $this->casino_tag;
        $this->gapi_callback_url = '';
        $this->session_url = app_url()."/play/gapi/"; //+session_id
    }

    /**
     * This function transforms the game id
     * @param string $game_id The game id to transform
     * @return string The transformed game id
     */
    public function transform_game_id($game_id)
    {
        $game_identifier = $this->select_game($game_id);
        $game_identifier = explode('/', $game_identifier->slug);
        return $game_identifier[1];
    }
    
    /**
     * This function logs data if the app is in debug mode
     * @param mixed $data The data to log
     */
    public function debugger($data)
    {
        if(env('APP_DEBUG') === true)
        { 
            save_log("AmaticKernel/Gapi", $data);
        }
    }

    /**
     * Helper to convert balance in Gapi's required balance format (floatval)
     * @return floatval float value
     */
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
    
    /**
     * This function creates a session, its first function called by the job worker
     * @param string $session_id The session id to create
     */
    public function create_session($session_id)
    {
        $session = $this->select_parent_session($session_id);
        $this->set_real_session($session_id);
    }

    /**
     * This function makes a call to the Gapi API
     * @param array $params The parameters for the API call
     * @return mixed The response from the API call
     */
    public function gapi_call($params)
    {
        $client = new \Northplay\NorthplayApi\Controllers\Integrations\GapiLib($this->gapi_api_url, $this->gapi_api_key);
        return $client->SendData($params);
    }

    /**
     * This function sets the real session
     * @param string $session_id The session id to set
     */
    public function set_real_session($session_id)
    {
        $session = $this->select_parent_session($session_id);
        $GAME_URL = 'https://play.gapi.lol/play/';
        $parentid = md5($this->casino_tag).':'.$session->currency; //change this to Hall ID on your server the user belongs to
        $userid = $session->user_public_id.':'.$session->debit_currency.':'.$session->currency.':'.md5($session->user_public_id.$this->casino_tag); // user from wallet API
        $game = $this->transform_game_id($session->game_id);
        $lang = "en"; // Valid values: "en","de","es","ru","tr","cz","gr","ee"
        $exiturl = $this->exit_url;
        $this->gapi_callback_url = app_url().'/casino/callbacks/gapilol?secure='.md5($userid.$this->casino_tag.$this->secure_key);
        $params = array(
            'action'      => 'inituser',
            'api_id'      => $this->gapi_api_id,
            'hash'        => $userid,
            'parenthash'  => $parentid,
            'callbackurl' => $this->gapi_callback_url,
            'callbackkey' => $session_id
        );

        $init = $this->gapi_call($params);

        $url = $GAME_URL.'?game='.$game.'&hash='.$userid.'&api_id='.$this->gapi_api_id.'&lang='.$lang.'&exit='.$exiturl;
        $this->upsert_parent_session_storage($session_id, "game_session_url", $url);
        $this->update_parent_session($session_id, "session_url", $this->session_url.$session_id);
        $this->update_parent_session($session_id, "state", "READY");
    }
    
    /**
     * Show function
     *
     * Renders the view for the Mascot game and returns the session data
     *
     * @param int $session_id Parent session identifier
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */

     public function show($session_id)
     {
         $session = $this->select_parent_session($session_id);
         $game_identifier = $this->transform_game_id($session->game_id);
         $game_session_url = $this->get_parent_session_storage($session_id)['game_session_url'];
         $data = [
             "game_identifier" => $game_identifier,
             "title" => $this->select_game($session->game_id)->name,
             "game_session_url" => $game_session_url,
             "game_session_url_64" => base64_encode($game_session_url),
             "currency" => $session->currency,
             "session" => ($this->is_development_state() ? $session : $session_id), 
         ];
         return view("northplay::gateway-gapi-game")->with("session_data", $data);
     }
     
    /**
    * This function handles callbacks from the Gapi API
    * @param Request $request The request object containing the callback data
    * @return \Illuminate\Http\JsonResponse The response to the callback
    */
    public function callbacks(Request $request)
    {
        $this->debugger($request->all());
        $decoded_request = $request->all();
        $action = $decoded_request['cmd'];
        $user_id = explode(":", $decoded_request['login'])[0];
        $session_id = $decoded_request['key'];

        $recreateSecureKey = md5($decoded_request['login'].$this->casino_tag.$this->secure_key); 
        if($recreateSecureKey !== $decoded_request['secure']) { // secure key extra check
            return response()->json(array(
                "status" => "fail",
                "error" => "user_not_found",
            ), 200);
        }
        
        $select_session = $this->select_parent_session($session_id);
        if($select_session->user_public_id !== $user_id) { // session not matching the user id
            return response()->json(array(
                "status" => "fail",
                "error" => "user_not_found",
            ), 200);
        }

        
        if($action === "getBalance") { // get balance call
            $balance = $this->convert_balance_helper($this->user_balance($session_id), "USD");
        }

        if($action === "writeBet") {  // balance transaction (game result processing)
            $betAmount = $decoded_request['bet'];
            $winAmount = ($decoded_request['winLose'] + $decoded_request['bet']);
            $intBet = (int) ($betAmount * 100);
            $intWin = (int) ($winAmount * 100);
            
            try { //process is done in trait, will error if no sufficient balance
                $balance = (int) $this->convert_balance_helper($this->process_game(
                    $session_id,
                    $decoded_request['tradeId'],
                    ($intBet ?? 0),
                    ($intWin ?? 0),
                    $request->all(),
                ), "USD");
            } catch(\Exception $error) { // most likely not enough balance
                $this->debugger($error->getMessage());
                return array(
                    "status" => "fail",
                    "error" => "fail_balance",
                );
            }

            $this->debugger($balance);
        }
        
        return response()->json(array(
                "status" => "success",
                "error" => "",
                "login" => $decoded_request['login'],
                "balance" => floatval($balance / 100),
        ), 200);
    }
}