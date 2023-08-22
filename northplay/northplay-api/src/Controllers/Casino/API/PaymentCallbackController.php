<?php
namespace Northplay\NorthplayApi\Controllers\Casino\API;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use \Northplay\NorthplayApi\Models\CryptapiModel;
use \Northplay\NorthplayApi\Models\PaymentTransactionsModel;
use \Northplay\NorthplayApi\Controllers\Casino\API\Currency\CurrencyController;
use Northplay\NorthplayApi\Controllers\Casino\API\Auth\UserBalanceController;

class PaymentCallbackController
{

    use \Northplay\NorthplayApi\Controllers\Casino\API\CasinoTrait;

    public function __construct()
	{
        $this->cryptapi_model = new CryptapiModel;
        $this->currency_controller = new CurrencyController;
        $this->payment_transactions_model = new PaymentTransactionsModel;
        $this->user_balance = new UserBalanceController;
	}

 
    public function cryptapi(Request $request) {
        $address_in = $request->address_in;
        save_log("PaymentCallbackController", $request->all());


        $select_address = $this->cryptapi_model->where("address_in", $address_in)->first();
        if(!$select_address) {
            $debug_id = rand(100000, 9999999999999);
            save_log("PaymentCallbackController-".$debug_id, "Detected a deposit, however could not find corresponding deposit address");
            save_log("PaymentCallbackController-".$debug_id,  $request->all());
            return 'OK';
        }

        $user_id = $select_address->user_id;
        $pending = $request->pending;
        $currency = config('northplay-api.cryptapi_ticker.'.$request->coin);
        $select_transaction = $this->payment_transactions_model->where("tx_id", $request->uuid)->first();

        if($pending === "0") {
              $this->completed_transaction($user_id, $currency, $request->value_coin);
              $all_currencies = $this->currency_controller->print_currencies();
              $converted_amount = (int) ($request->value_coin * 100000000);
              save_log("Converted Amount", $converted_amount);
              save_log("Currency", $currency);
              save_log("User ID", $user_id);
              $this->user_balance->credit_user_balance($user_id, $currency, "balance", $converted_amount, "Crypt api deposit on ".$address_in, $request->all());
              //$this->add_balance($user_id, $currency, $converted_amount, "Cryptapi deposit on ".$address_in);
        } else {
            $this->pending_transaction($user_id, $currency);
        }

        if(!$select_transaction) {
            $tx_data = json_decode($request->getContent(), true);
            $this->payment_transactions_model->insert([
                "tx_id" => $request->uuid,
                "user_id" => $user_id,
                "type" => "cryptapi",
                "pending" => $pending,
                "amount" => $request->value_coin,
                "currency" => $currency,
                "data" => json_encode($request->all),
                "created_at" => now(),
                "updated_at" => now(),
            ]);

        }

        return 'OK';
    }

    public function completed_transaction($user_id, $currency, $amount)
    {
        $this->sendNotification(
            $user_id,
            "Deposit credited",
            "Deposit credited to your casino account for ".$amount." ".$currency.".",
            "Deposit credited to your casino account for ".$amount." ".$currency.".",
            "account",
            "none"
        );
    }

    public function pending_transaction($user_id, $currency)
    {
        $this->sendNotification(
            $user_id,
            "Pending deposit",
            "A deposit transaction is currently pending for currency: ".$currency.".",
            "A deposit transaction is currently pending for currency: ".$currency.".",
            "account",
            "none"
        );
    }

}