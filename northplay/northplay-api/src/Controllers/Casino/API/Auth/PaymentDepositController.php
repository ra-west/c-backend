<?php

namespace Northplay\NorthplayApi\Controllers\Casino\API\Auth;
use App\Http\Controllers\Controller;
use \Northplay\NorthplayApi\Controllers\Casino\API\Currency\CurrencyController;
use \Northplay\NorthplayApi\Models\UserBalanceModel;
use \Northplay\NorthplayApi\Models\UserBalanceTransactionsModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use \Northplay\NorthplayApi\Models\CryptapiModel;

class PaymentDepositController extends Controller
{
	protected $all_currencies;
	protected $currency_controller;
	
	public function __construct()
	{
        $this->cryptapi_model = new CryptapiModel;
		$this->currency_controller = new CurrencyController;
		$this->all_currencies = $this->currency_controller->print_currencies();
	}

	public function retrieve(Request $request) {
        $user = $request->user();

        $user_id = $user->id;

		foreach($this->all_currencies as $currency) {
            $sym = $currency['symbol_id'];
            $array[] =  $this->payment_method($user_id, $sym);
        }

        $response_data = [
            "success" => true,
            "data" => $array,
        ];

        return response()->json($response_data, 200);
	}

    public function payment_method($user_id, $sym)
    {
        if(config('northplay-api.cryptapi.'.$sym)) {
            $deposit_address = null;
            $select_deposit_address = $this->cryptapi_model->where("user_id", $user_id)->where("currency", $sym)->where("active", true)->first();
            if($select_deposit_address) {
                $deposit_address = $select_deposit_address->address_in;
            }
            return array(
                "symbol" => $sym,
                "crypto" => array(
                    "deposit_address" => $deposit_address,
                ),
                "card" => false,
            );
        }

        return array(
            "symbol" => $sym,
            "crypto" => false,
            "card" => false,
        );
    }

    public function generateAddress(Request $request)
    {
        $user_id = $request->user()->id;
        if(config('northplay-api.cryptapi.'.$request->currency)) {
            return $this->create_cryptApi($user_id, config('northplay-api.cryptapi.'.$request->currency.'.ticker'), config('northplay-api.cryptapi.'.$request->currency.'.to_address'));
        }
    }

    public function create_cryptApi($user_id, $ticker, $to_address)
    {
        $callback_url = config('northplay-api.cryptapi.callback');
        $query = array(
            "callback" => $callback_url.'?user_id='.$user_id,
            "address" => $to_address,
            "pending" => "1",
            "confirmations" => "1",
            "email" => $user_id."@protonmail.com",
            "post" => "0",
            "priority" => "default",
            "multi_token" => "0",
            "multi_chain" => "0",
            "convert" => "0"
        );
    
        $curl = curl_init();
    
        curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.cryptapi.io/" . $ticker . "/create/?" . http_build_query($query),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "GET",
        ]);
    
        $response = curl_exec($curl);
        $error = curl_error($curl);
    
        curl_close($curl);
    
        if ($error) {
        	abort(400, $error);
        } else {
            $response = json_decode($response, true);
            $this->cryptapi_model->create([
                "currency" => $this->transform_ticker($ticker),
                "user_id" => $user_id,
                "address_in" => $response['address_in'],
                "address_out" => $response['address_out'],
                "callback_url" => $callback_url,
                "active" => true,
                "created_at" => now(),
                "updated_at" => now(),
            ]);
            return $response['address_in'];
        }
    }

    public function transform_ticker($ticker)
    {
        return config('northplay-api.cryptapi_ticker.'.$ticker);
    }
	

}