<?php
namespace Northplay\NorthplayApi\Controllers\Integrations;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Northplay\NorthplayApi\Controllers\Integrations\GatewayTrait;
use Northplay\NorthplayApi\Models\GatewayParentSessions;
use Northplay\NorthplayApi\Jobs\CreateGameSession;
use Illuminate\Support\Facades\Cache;

class ParentSession
{
	use GatewayTrait;
		protected $parent_session_model;
		public function __construct()
		{
			$this->parent_session_model = new GatewayParentSessions;
		}


		public function create_parent_session($entry_session)
		{
			try {
				$session_id = $this->uuid();
				$retrieve_game = $this->select_game($entry_session->game_id);
				$provider = $retrieve_game->provider;
				$currency = $entry_session->currency;
				if($entry_session->active === false) {
					$this->update_entry_session($entry_session->entry_token, "state", "FAILED");
					$this->update_entry_session($entry_session->entry_token, "state_message", "Entry session was set to inactive (probably because of user creating new session in the meantime).");
					return;
				}
				$debit_currency = $entry_session->debit_currency;
				$user_private_id = $this->hmac($entry_session->user_id."-".$currency);
				$time_now = now_nice();

				if(!$this->select_provider($provider)) {
					$this->update_entry_session($entry_session->entry_token, "state", "FAILED");
					$this->update_entry_session($entry_session->entry_token, "state_message", "Provider not available currently.");
					return;
				}

				$created_session = $this->parent_session_model->insert([
					"session_id" => $session_id,
					"session_url" => NULL,
					"game_session" => NULL,
					"api_mode" => $entry_session->api_mode,
					"user_private_id" => $user_private_id,
					"user_public_id" => $entry_session->user_public_id,
					"currency" => $currency,
					"debit_currency" => $debit_currency,
					"game_id" => $entry_session->game_id,
					"state" => "INIT",
					"storage" => json_encode(array("entry_token" => $entry_session->entry_token, "browser_data" => Cache::pull($entry_session->entry_token.'_browserdata')), JSON_PRETTY_PRINT),
					"active" => true,
					"created_at" => $time_now,
					"updated_at" => $time_now,
				]);

				$this->update_entry_session($entry_session->entry_token, "session_id", $session_id);
				$this->update_entry_session($entry_session->entry_token, "state", "CREATE_GAME_SESSION_JOB_DISPATCHED");
				$this->update_entry_session($entry_session->entry_token, "state_message", "Requesting game session at provider.");

				CreateGameSession::dispatch($session_id, $provider);
			} catch(\Exception $e) {
					$this->update_entry_session($entry_session->entry_token, "state", "FAILED");
					save_log("Create Parent Error", $e->getMessage()." line ". $e->getLine());
			}
		}
}

/*


protected $table = 'northplay_gateway_parent_sessions';
protected $timestamp = true;
protected $primaryKey = 'id';
protected $fillable = [
		'session_url',
		'session_id',
		'user_private_id',
		'user_public_id',
		'api_mode',
		'game_id',
		'game_session',
		'currency',
		'state',
];

protected $casts = [
		'storage' => 'json',
		'active' => 'boolean',
		'created_at' => 'datetime:Y-m-d H:i:s',
		'updated_at' => 'datetime:Y-m-d H:i:s',
];
}


*/