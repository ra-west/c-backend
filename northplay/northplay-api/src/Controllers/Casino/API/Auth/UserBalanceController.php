<?php

namespace Northplay\NorthplayApi\Controllers\Casino\API\Auth;
use App\Http\Controllers\Controller;
use \Northplay\NorthplayApi\Controllers\Casino\API\Currency\CurrencyController;
use \Northplay\NorthplayApi\Models\UserBalanceModel;
use \Northplay\NorthplayApi\Models\UserBalanceTransactionsModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class UserBalanceController extends Controller
{
	protected $all_currencies;
	protected $user_balance_model;
	protected $user_external_controller;
	protected $currency_controller;
	protected $user_balance_transactions_model;
	
	public function __construct()
	{
		$this->currency_controller = new CurrencyController;
		$this->user_balance_model = new UserBalanceModel;
		$this->user_balance_transactions_model = new UserBalanceTransactionsModel;
	}

	public function get_balance($user_id, $sym) {
		return $this->get_user_balance($user_id, $sym);
	}
	
	public function user_balance($user_id, $sym) {
		return $this->get_user_balance($user_id, $sym);
	}
	
	
	public function get_user_balance($user_id, $sym)
	{
		$user_controller = new User;
		$user = $user_controller->where("id", $user_id)->first();
		if(!$user) {
			save_log("UserBalanceController", "User not found");
			abort(400, "User ".$user_id." not found.");
		}
		$user_balance = $this->user_balance_model->where("symbol_id", $sym)->where("user_id", $user_id)->first();
		if(!$user_balance) {
			$this->user_balance_model->insert([
				"symbol_id" => $sym,
				"user_id" => $user->id,
				"balance" => 0,
				"balance_bonus" => 0,
			]);
			$user_balance = $this->user_balance_model->where("symbol_id", $sym)->where("user_id", $user_id)->first();
		}
		$all_currencies = $this->currency_controller->print_currencies();

		$total = $user_balance->balance + $user_balance->balance_bonus;
		$de_int = ($total === 0 ? $total : $total / 100000000);
		$usd_value = number_format(($de_int / $all_currencies[$sym]['rate_usd']), 2, ".", "");

		$balance = [
			"balance" => $user_balance->balance,
			"balance_bonus" => $user_balance->balance_bonus,
			"total" => $total,
			"total_nice" => floatval($de_int),
			"total_usd" => $usd_value,
			"total_eur" => number_format(($this->currency_controller->convert($usd_value, "EUR")), 2, ".", ""),
			"total_gbp" => number_format(($this->currency_controller->convert($usd_value, "GBP")), 2, ".", ""),
		];
		return $balance;
	}


	public function print_balances(Request $request) 
	{
		$all_currencies = $this->currency_controller->print_currencies();
		
		$balance_data = [];
		$auth = $request->user();
		foreach($all_currencies as $currency) {
			$sym = $currency['symbol_id'];
			$user_balance = $this->get_user_balance($auth->id, $sym);
			$de_int = (($user_balance['total']) === 0 ? $user_balance['total'] : $user_balance['total'] / 100000000);
			$int_value = (int) ($user_balance['total']);
			$nice_value = number_format(($de_int), 8, '.', '');
			$usd_value = number_format(($nice_value / $currency['rate_usd']), 8, ".", "");
			$balance_data[$sym] = [
				"name" => $sym,
				"int" => $int_value,
				"balance" => $user_balance['balance'],
				"balance_bonus" => $user_balance['balance_bonus'],
				"nice" => $nice_value,
				"rate_updated" => $currency['rate_updated'],
				"usd" => [
					"value" => number_format($usd_value, 2, ".", ""),
					"sym" => "USD",
					"sign" => "$",
				],
				"eur" => [
					"value" => number_format(($this->currency_controller->convert($usd_value, "EUR")), 2, ".", ""),
					"sym" => "EUR",
					"sign" => "€",
				],
				"gbp" => [
					"value" => number_format(($this->currency_controller->convert($usd_value, "GBP")), 2, ".", ""),
					"sym" => "GBP",
					"sign" => "£",
				],
			];
		}
		return $balance_data;
	}
	
	
	public function add_transaction_data($user_id, $amount, $direction, $sym, $new_balance, $user_balance, $tx_description, $tx_data)
	{
		$time_now = now_nice();
		$tx_data = [
			"user_id" => $user_id,
			"tx_amount" => (int) $amount,
			"tx_direction" => $direction,
			"tx_currency" => $sym,
			"tx_balance" => $new_balance,
			"tx_old_balance" => $user_balance,
			"tx_desc" => $tx_description,			
			"tx_data" => json_encode(array("tx_data_input" => $tx_data)),
			"created_at" => $time_now,
			"updated_at" => $time_now,
		];
		$tx = $this->user_balance_transactions_model->insert($tx_data);

		return $tx_data;
	}
	
	public function debit_user_balance($user_id, $sym, $balance_type, $amount, $tx_description, $tx_data = NULL)
	{
		$user_controller = new User;
		$amount = (int) $amount;
		$direction = "debit";
		
		$user = $user_controller->where("id", $user_id)->first();
		if(!$user) {
			save_log("UserBalanceController", "User does not exist. ".$user_id);
			abort(400, "User does not exist");
		}
		$user_balance = $this->get_user_balance($user_id, $sym)[$balance_type];
		$new_balance = ($user_balance - $amount);
		
		if($new_balance < 0) {
			abort(400, "Not enough balance.");
		} else {
			$select_balance = $this->user_balance_model->where("symbol_id", $sym)->where("user_id", $user_id)->first();
			$select_balance->update([
				$balance_type => $new_balance
			]);
		
			$tx = $this->add_transaction_data($user_id, (int) $amount, $direction, $sym, $new_balance, $user_balance, $tx_description, $tx_data);
			
			return $new_balance;
		}
	}
	
	public function credit_user_balance($user_id, $sym, $balance_type, $amount, $tx_description, $tx_data = NULL)
	{
		$user_controller = new User;
		$amount = (int) $amount;
		$direction = "credit";
		
		$user = $user_controller->where("id", $user_id)->first();
		if(!$user) {
			save_log("UserBalanceController", "User does not exist. ".$user_id);
			abort(400, "User does not exist");
		}
		$user_balance = $this->get_user_balance($user_id, $sym)[$balance_type];
		$new_balance = ($user_balance + $amount);
		
		$tx = $this->add_transaction_data($user_id, (int) $amount, $direction, $sym, $new_balance, $user_balance, $tx_description, $tx_data);

		$select_balance = $this->user_balance_model->where("symbol_id", $sym)->where("user_id", $user_id)->first();
		$select_balance->update([
			$balance_type => $new_balance
		]);
		
		return $new_balance;
	}
	
	

}