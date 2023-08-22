<?php
namespace Northplay\NorthplayApi\Controllers\Integrations;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Northplay\NorthplayApi\Controllers\Integrations\ProxyController;
use Illuminate\Support\Str;
use Northplay\NorthplayApi\Models\SoftswissGameModel;
use Northplay\NorthplayApi\Models\GatewayParentSessions;
use Northplay\NorthplayApi\Models\GatewayEntrySessions;
use Illuminate\Support\Facades\Crypt;
use Northplay\NorthplayApi\Controllers\Casino\API\Auth\UserBalanceController;
use Cookie;
use Illuminate\Support\Facades\Cache;

trait GatewayTrait
{

	public function proxy(Request $request, $url)
	{
			 $proxy_controller = new ProxyController;
			 return $proxy_controller->CreateProxy($request)->toUrl($url);
	}

	public function uuid()
	{
			 return Str::uuid();
	}
	
	public function secret_key()
	{
			 return "d68cb363-0303-4c34-951a-9a7c2fed451e";
	}
	
	public function hmac($input)
	{
			return hash_hmac('md5', $input, $this->secret_key());
	}

	public function convert_currency($amount, $origin_currency, $target_currency)
	{
		$currency_controller = new \Northplay\NorthplayApi\Controllers\Casino\API\Currency\CurrencyController;
		$usd_rate = $currency_controller->usd_rate($origin_currency);
		if($target_currency === 'USD') {
			return number_format(($usd_rate * $amount), 2, '.', '');
		} else {
			return number_format(($usd_rate * ($currency_controller->usd_rate($target_currency))), 2, '.', '');
		}
	}

	function getQueryParameter($url, $param) {
		$parsedUrl = parse_url($url);
		if (array_key_exists('query', $parsedUrl)) {
			parse_str($parsedUrl['query'], $queryParameters);
			if (array_key_exists($param, $queryParameters)) {
				return $queryParameters[$param];
			}
		}
	}
	
	public function get_debit_currency($user_id, $currency) 
	{
		$user_balance = new UserBalanceController;
		return $user_balance->get_user_balance($user_id, $currency);	
	}

	public function user_balance($session_id) 
	{
		$user_balance = new UserBalanceController;
		$session = $this->select_parent_session($session_id);
		return $user_balance->get_user_balance($session->user_public_id, $session->debit_currency);	
	}

	public function process_game($session_id, $round_id, $betAmount, $winAmount, $data = NULL)
	{
		//save_log("data", $data);
		$debit_completed = 0;
		$credit_completed = 0;
		$session = $this->select_parent_session($session_id);
		$select_currency = $this->all_currencies()[$session->debit_currency];
		$usd_rate = $select_currency['rate_usd'];
		
		$betAmount = ($betAmount === 0 ? $betAmount : $betAmount / 100);
		$betAmountDebit = ($betAmount * ($usd_rate)) * (100000000);
		$user_balance = $this->user_balance($session_id);

		if($betAmountDebit > 0) {
			$debit_completed = "insufficient_funds";
			if($user_balance['total'] < $betAmountDebit) {
				if($this->is_development_state()) {
					save_log("GatewayTrait", "Tried to charge user more then he has in game event: ".json_encode($data));
					abort(400, "User has insufficient funds to process game: ".json_encode($data));
				}
				return "insufficient funds";
			}
			if($user_balance['balance'] > 0) {
				$balance_type = "balance";
			} else {
				$balance_type = "balance_bonus";
			}
			$this->user_balance_transaction($session_id, "debit", $balance_type, (int) $betAmountDebit, "Slot game event", array("session" => $session, "game_data" => $data));

		}
		
		if($winAmount > 0) {
			if($user_balance['balance'] > 0) {
				$balance_type = "balance";
			} else {
				$balance_type = "balance_bonus";
			}
			$winAmount = ($winAmount === 0 ? $winAmount : $winAmount / 100);
			$winAmountCredit = ($winAmount * ($usd_rate)) * (100000000);
			$this->user_balance_transaction($session_id, "credit", $balance_type, (int) $winAmountCredit, "Slot game event", array("session" => $session, "game_data" => $data));
		}
		

		\Northplay\NorthplayApi\Jobs\GameBufferWrite::dispatch(
			$session_id,
			$round_id,
			$winAmount,
			$betAmount,
			$data,
			$session->debit_currency,
			$session->currency,
		)->onQueue('high');

		return $this->user_balance($session_id);
	}

	public function all_currencies()
	{
		$controller = new \Northplay\NorthplayApi\Controllers\Casino\API\Currency\CurrencyController;
		return $controller->print_currencies();
	}

	public function convert_fiat_to_crypto($debit_currency, $play_currency, $play_amount)
	{
		$user_balance = new UserBalanceController;
		$usd_rate = $this->all_currencies()[$debit_currency]['rate_usd'];
		return  number_format(($usd_rate * $play_amount), 8, '.', '');
	}

	public function convert_crypto_to_fiat($debit_currency, $play_currency, $play_amount)
	{
		$user_balance = new UserBalanceController;
		$usd_rate = $this->all_currencies()[$debit_currency]['rate_usd'];
		return  number_format(($play_amount / $usd_rate), 2, '.', '');
	}

	
	public function user_balance_transaction($session_id, $direction, $balance_type, $amount, $tx_description, $tx_data = NULL)
	{
		$session = $this->select_parent_session($session_id);

		$user_balance = new UserBalanceController;
		
		if($direction === "credit") {
			return $user_balance->credit_user_balance($session->user_public_id, $session->debit_currency, $balance_type, $amount, $tx_description, $tx_data);
		} 
		if($direction === "debit")  {
			return $user_balance->debit_user_balance($session->user_public_id, $session->debit_currency, $balance_type, $amount, $tx_description, $tx_data);
		}
		


		abort(400, "You can only debit or credit user balance");		
	}

	public function random_user_agent() 
	{
		$list = [
				"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Safari/537.36",
				"Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Safari/537.36",
				"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Safari/537.36 Edg/111.0.1661.54",
				"Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Safari/537.36",
				"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Safari/537.36",
				"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Safari/537.36",
				"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Safari/537.36 Edg/112.0.0.0",
				"Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/105.0.0.0 Safari/537.36",
				"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36",
				"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36 OPR/96.0.0.0",
		];
		return $list[rand(0, 8)];
	}


	public function get_redirect_url($url) {
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_HEADER, true);
			curl_setopt($ch, CURLOPT_USERAGENT, "'".$this->random_user_agent()."'");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,0);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			$html = curl_exec($ch);
			$redirectURL = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
			curl_close($ch);
			return $redirectURL;
	}

		
	public function disable_game($game_id)
	{
			$game_model = new SoftswissGameModel;
			$select_game = $game_model->where("id", $game_id)->first();

			if($select_game) {
				$game_model->where("id", $game_id)->update([
					'active' => false
				]);
			}
			if(!$select_game) {
				$select_game = $game_model->where("slug", $game_id)->first();
				if($select_game) {
					if($select_game) {
						$game_model->where("slug", $game_id)->update([
							'active' => false
						]);
					}
				}
				if(!$select_game) {
					save_log("GatewayTrait", $game_id." game not found being called disable_game()");
					abort(400, "Game not found");
				}
			} 


			save_log("disable_game()", $this->select_game($game_id));
			return;
	}

	
	public function select_game($game_id)
	{
			$select_game = Cache::get($game_id);
			if(!$select_game) {
			$game_model = new SoftswissGameModel;
			$select_game = $game_model->where("id", $game_id)->first();
			if(!$select_game) {
				$select_game = $game_model->where("slug", $game_id)->first();
				if(!$select_game) {
					save_log("GatewayTrait", $input." game not found being called select_game()");
					abort(400, "Game not found");
				}
			}
			Cache::set($game_id, $select_game, now()->addSeconds(60));
			}
			return $select_game;
	}

	public function encrypt_string($string)
	{
			 $encrypted = Crypt::encryptString($string);
			 return $encrypted;
	}

	public function decrypt_string($string)
	{
			 $decrypt= Crypt::decryptString($string);
			 return $decrypt;
	}

	public function build_query($query)
	{
			$resp = http_build_query($query);
			$resp = urldecode($resp);
			return $resp;
	}

	public function parse_query($query_string)
	{
			parse_str($query_string, $q_arr);
			return $q_arr;
	}


	public function url_hostname($url)
	{
		return parse_url($url, PHP_URL_HOST);
	}

	public function url_fullpath($url)
	{
		return parse_url($url, PHP_URL_PATH);
	}

	public function url_params($url)
	{
		$parts = parse_url($url);
		return parse_str($parts['query'], $query);
	}
	public function select_provider($provider)
	{
			$game_kernel = new \Northplay\NorthplayApi\Controllers\Integrations\Games\GameKernel;
			$provider = (collect($game_kernel->providers()))->where("id", $provider)->first();
			if($provider) {
				return $provider;
			}
	}

	public function spacebarControl($spacebarEvent, $spaceBarAmount, $responseData)
	{
		$spacebarCookie = Cookie::make('spaceBarEvent', $spacebarEvent, 2, null, null, false, false);
		$spacebarCookie2 = Cookie::make('spaceBarAmount', $spaceBarAmount, 2, null, null, false, false);
		return response()->json($responseData, 200)->withCookie($spacebarCookie)->withCookie($spacebarCookie2);
	}

	public function close_parent_session($session_id, $reason)
	{
		$parent_session_model = new GatewayParentSessions;
		$select_session = $parent_session_model->where("session_id", $session_id)->first();
		if(!$select_session) {
			save_log("GatewayTrait", "Parent session with ID: ".$session_id." not found on close_parent_session()");
			abort(400, "Parent session not found.");
		}

		$select_session->update([
			"active" => false
		]);

		$select_storage = $this->get_parent_session_storage($session_id);
		$entry_token = $select_storage['entry_token'];
		if($entry_token) {
			$entrysession_model = new GatewayEntrySessions;
			$select_entry_session = $entrysession_model->where("entry_token", $entry_token)->first();
			$select_entry_session->update([
				"active" => false
			]);
		}

		$data = [
			"error" => "session_closed",
			"reason" => $reason,
			"session_id" => $session_id,
		];
		$cookie_closed = Cookie::make('gateway_session_close', $session_id, 2, null, null, false, false);
		$cookie_reason = Cookie::make('gateway_session_close_reason', $reason, 2, null, null, false, false);
		$this->update_parent_session($session_id, "close_reason", $reason);
		return response()->json(array("gateway_session_close" => true, "reason" => "error"), 200)->withCookie($cookie_closed)->withCookie($cookie_reason);
	}

	public function select_parent_session($session_id)
	{
			$parent_session_model = new GatewayParentSessions;
			$select_session = $parent_session_model->where("session_id", $session_id)->first();
			if(!$select_session) {
				save_log("GatewayTrait", "Parent session with ID: ".$session_id." not found on select_parent_session().");
				abort(400, "Parent session not found.");
			}
			return $select_session;
	}

	public function update_parent_session($session_id, $key, $value)
	{
			$parent_session_model = new GatewayParentSessions;
			$select_session = $parent_session_model->where("session_id", $session_id)->first();

			if(!$select_session) {
				save_log("GatewayTrait", "Parent session ${session_id} not found when being called update_parent_session.");
				abort(400, "Entry session not found.");
			}

			$select_session->update([
					$key => $value
			]);
			return $this->select_parent_session($session_id);
	}

	public function json_validator($input)
	{
			if (!empty($data)) {
					return is_string($data) && 
						is_array(json_decode($data, true)) ? true : false;
			}
			return false;
	}

	public function is_development_state() {
			if(env("APP_ENV") === "development") {
				return true;
			}
			if(env("APP_DEBUG") === true) {
				return true;
			}
			return false;
	}

	public function get_parent_session_storage($session_id)
	{
			$storage = $this->select_parent_session($session_id)->storage;
			
			if($this->json_validator($storage)) {
				$storage = json_decode($storage, true);
			}
			return $storage;
	}

	public function upsert_parent_session_storage($session_id, $storage_key, $storage_value)
	{
				$parent_session_model = new GatewayParentSessions;
				$select_session = $parent_session_model->where("session_id", $session_id)->first();
				if($select_session) {
					$current_storage = $select_session->storage;
					$current_storage[$storage_key] = $storage_value;
					//save_log("UpsertParentSession", $current_storage);
					$select_session->where("session_id", $session_id)->update([
							"storage" => json_encode($current_storage, JSON_PRETTY_PRINT)
					]);
					return $this->select_parent_session($session_id);
				}
	}


	public function select_entry_session($entry_token)
	{
		$entrysession_model = new GatewayEntrySessions;
		$select_entry_session = $entrysession_model->where("entry_token", $entry_token)->first();
		if(!$select_entry_session) {
			save_log("GatewayTrait", "Entry session ${entry_token} not found when being called update_entry_session.");
			abort(400, "Entry session not found.");
		}
		return $entry_token;
	}
	
	public function update_entry_session($entry_token, $key, $value)
	{
			$entrysession_model = new GatewayEntrySessions;
			$select_entry_session = $entrysession_model->where("entry_token", $entry_token)->first();
			if(!$select_entry_session) {
				save_log("GatewayTrait", "Entry session ${entry_token} not found when being called update_entry_session.");
				abort(400, "Entry session not found.");
			}
			$select_entry_session->update([
					$key => $value
			]);
			return $this->select_entry_session($entry_token);
	}

}