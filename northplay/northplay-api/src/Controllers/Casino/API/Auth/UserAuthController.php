<?php

namespace Northplay\NorthplayApi\Controllers\Casino\API\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use \Northplay\NorthplayApi\Models\UserExternalAuthModel;
use \Northplay\NorthplayApi\Controllers\Casino\API\Auth\ExternalAuthController;
use \Northplay\NorthplayApi\Controllers\Casino\API\Currency\CurrencyController;
use \Northplay\NorthplayApi\Models\UserBalanceModel;
use Illuminate\Support\Facades\Cache;

class UserAuthController extends Controller
{
	protected $user_external_controller;
	protected $user_balance_controller;
	protected $currency_controller;
	protected $user_notifications;
	use \Northplay\NorthplayApi\Controllers\Casino\API\CasinoTrait;

	public function __construct()
	{
		$this->middleware(['web', 'auth']);
		$this->currency_controller = new CurrencyController;
		$this->user_balance_controller = new UserBalanceController;
		$this->user_notifications = new UserNotificationsController;
		$this->user_external_controller = new ExternalAuthController;
	}

	public function me(Request $request)
	{
		$this->registration_bonus($request->user()->id);

		$user = $request->user();
		$data = $user;
		$data["balance"] = $this->user_balance_controller->print_balances($request);
		$data["notifications"] = $this->user_notifications->recent($request->user()->id);
		$data["websocket"] = $this->websocket($request);
		return response()->json($data, 200);
	}

	public function websocket(Request $request)
	{
	return $this->user_external_controller->websocket_details($request);
	}


    public function registration_bonus($user_id)
    {
            $registration_bonus = $this->user_storage_get($user_id, "register_bonus");
            if(!$registration_bonus) {
				$registration_bonus_data = [
					"currency" => config('northplay-api.registration_bonus.currency'),
					"amount" => config('northplay-api.registration_bonus.amount'),
				];

				$registration_bonus = $this->user_storage_set($user_id, "register_bonus", "set", "bonus", $registration_bonus_data);
                $this->sendNotification(
                    $user_id,
                    "Registration Bonus",
                    "You have been granted a registration bonus.",
                    "You have been granted a registration bonus.",
                    "bonus",
                    "none"
                );
                $this->add_bonus_balance($user_id, config('northplay-api.registration_bonus.currency'), config('northplay-api.registration_bonus.amount'), "Registration Bonus in Development environment.");
            }
    }
	
}
