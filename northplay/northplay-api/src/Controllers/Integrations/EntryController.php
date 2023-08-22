<?php
namespace Northplay\NorthplayApi\Controllers\Integrations;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Northplay\NorthplayApi\Controllers\Integrations\ProxyController;
use Northplay\NorthplayApi\Models\GatewayEntrySessions;
use Northplay\NorthplayApi\Controllers\Integrations\GatewayTrait;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Cache;
use Northplay\NorthplayApi\Jobs\CreateSession;
use Illuminate\Support\Facades\Validator;

class EntryController extends ParentSession
{

	use GatewayTrait;
	protected $entrysession_model;
	
	/**
	* EntryController constructor.
	* Initialize a gateway entry session.
	* @return void
	*/
	public function __construct()
	{
		$this->entrysession_model = new GatewayEntrySessions;
	}

	public function show_session_error(Request $request)
	{
		return view('northplay::gateway-session-error');
	}

	/**
	* Create gateway entry session.
	* @param integer $user_id
	* @param integer $game_id
	* @param string $currency
	* @return string|null
	*/
	public function create_entry_session($user_id, $mode, $game_id, $currency, $debit_currency)
	{	
		$entry_token = $this->uuid();

		if($mode === "real") {
				$select_history = $this->entrysession_model->where('user_public_id', $user_id)->where('game_id', $game_id)->where('active', true)->count();
				if($select_history >= 2) {
						$this->entrysession_model->where('user_public_id', $user_id)->where('game_id', $game_id)->where('active', true)->update([
								'active' => false
						]);
						$this->entrysession_model->where('user_public_id', $user_id)->where('game_id', $game_id)->where('active', true)->update([
								'active' => false
						]);
				}
		} else {
		
		
		}

		$inserted_row = $this->entrysession_model->insert([
			"entry_token" => $entry_token,
			"entry_confirmation" => $this->uuid(),
			"session_url" => NULL,
			"session_id" => NULL,
			"user_public_id" => $user_id,
			"user_private_id" => md5($user_id),
			"api_mode" => $mode,
			"game_id" => $game_id,
			"currency" => $currency,
			"debit_currency" => $debit_currency,
			"state" => "CREATED",
			"active" => true,
			"updated_at" => now_nice(),
			"created_at" => now_nice(),
		]);


		if($inserted_row) {
			return $entry_token;
		}
	}

	/**
	* Showing gateway session.
	* @param Request $request
	* @return View
	*/
	public function show(Request $request)
	{			
		/*
		if(!$request->user()) {
			$rateLimitId = $request->ip();
		} else {
			$rateLimitId = $request->user()->id;
		}
		$rateLimit = RateLimiter::attempt(
			'entrySession:'.$rateLimitId,
			$perMinute = 30,
			function() {
			}
		);

		if (!$rateLimit) {
			return 'Too many recent session join attempts, please wait 2 minutes before retrying';
		}
		*/


		if(!$request->entry_token) {
			abort(403, "Entry token missing.");
		}

		$preloader_theme = 'black';
		if($request->preloader_theme) {
			if($request->preloader_theme === 'darkblue') {
				$preloader_theme = 'darkblue';
			}
		}

		$select_entry = $this->entrysession_model->where("entry_token", $request->entry_token)->first();
		if(!$select_entry) {
			abort(400, "Entry session not found for that token.");
		}

		if($select_entry->active === false) {
			return view("northplay::gateway-session-error");
		}

		$select_game = $this->select_game($select_entry['game_id']);

		if($select_game->active === false) {
			return view('northplay::gateway-game-unavailable');
		}
		
		$data = [
			"title" => "Loading Game",
			"entry_token" => $select_entry['entry_token'],
			"ip_address" => $select_entry['ip_address'],
			"entry_confirmation" => $select_entry['entry_confirmation'],
			"customizations" => [
				"preloader_theme" => $preloader_theme,
			],
			"state" => $select_entry['state'],
			"queue_check" => app_url()."/entry/queue_check", // route to hit once player is in the viewer
			"queue_check_options" => [
				"rate_limiter" => [
					"init_interval" => 2500, // amount in MS per http request
					"slowdown_tries" => 8, // amount of http request till slowing
					"slowdown_interval" => 5000, // amount in MS per http request
					"fail_tries" => 30, // amount of trries till fail
				],
			],
		];

		return view('northplay::gateway-preloader')->with("entry_data", $data)->withoutCookie("gateway_session_id");
	}

	/**
	* Checking if session is in queue.
	* @param Request $request
	* @return JsonResponse|null
	*/
	public function queue_check(Request $request) 
	{	
		validate([
			'entry_token' => ['required', 'string', 'min:3', 'max:100', 'regex:/^[^(\|\]`!%^&=};:?><’)]*$/'],
			'confirmation' => ['required', 'string', 'min:3', 'max:100', 'regex:/^[^(\|\]`!%^&=};:?><’)]*$/'],
			'gw_id' => ['required', 'string', 'max:50'],
			'dt_cookie' => ['required', 'integer', 'max:3'],
			'dt_dw' => ['required', 'integer', 'max:3'],
			'dt_storage' => ['required', 'integer', 'max:3'],
		], $request->all());
		$entry_token = $request->entry_token;
		$entry_confirmation = $request->confirmation;
		$select_entry = $this->entrysession_model->where("entry_token", $entry_token)->where("entry_confirmation", $entry_confirmation)->first();
		if($select_entry) {
		
			Cache::set($entry_token."_browserdata", [
				"fingerprint" => $request->gw_id,
				"cookie_support" => $request->dt_cookie,
				"storage_support" => $request->dt_storage,
				"document.write" => $request->dt_dw,
			], now()->addMinutes(5));
			
			if($select_entry->state === "CREATED") {
				$select_entry->update([
					"state" => "QUEUED",
					"updated_at" => now_nice(),
				]);
				CreateSession::dispatch($select_entry);
			}
			if($select_entry->session_id !== NULL) {
				$parent_session = $this->select_parent_session($select_entry->session_id);
				if($parent_session->session_url !== NULL) {
					$select_entry->update([
						"state" => "ACTIVE",
						"session_url" => $parent_session->session_url,
						"updated_at" => now_nice(),
					]);
					$select_entry = $this->entrysession_model->where("entry_token", $entry_token)->where("entry_confirmation", $entry_confirmation)->first();
				}
			}
			
			$public_response = [
				"state" => $select_entry->state,
				"state_message" => $select_entry->state_message,
				"session_id" => $select_entry->session_id,
				"session_url" => $select_entry->session_url,
				"updated_at" => $select_entry->updated_at,
			];

			return $public_response;
		} else {
			$public_response = [
				"state" => "FAILED",
				"state_message" => "Entry token not found",
				"session_id" => null,
				"session_url" => null,
			];
			return $public_response;
		}
	}
}
