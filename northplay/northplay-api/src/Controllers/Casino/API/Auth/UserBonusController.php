<?php

namespace Northplay\NorthplayApi\Controllers\Casino\API\Auth;
use App\Http\Controllers\Controller;
use \Northplay\NorthplayApi\Controllers\Casino\API\Currency\CurrencyController;
use \Northplay\NorthplayApi\Models\UserBalanceModel;
use \Northplay\NorthplayApi\Models\UserBalanceTransactionsModel;
use App\Models\User;

class UserBonusController extends Controller
{
	protected $all_currencies;
	protected $user_balance_model;
	protected $user_external_controller;
	protected $currency_controller;
	protected $user_balance_transactions_model;
	
	public function __construct()
	{
		$this->currency_controller = new CurrencyController;
		$this->all_currencies = $this->currency_controller->all_currencies();
		$this->user_balance_model = new UserBalanceModel;
		$this->user_balance_transactions_model = new UserBalanceTransactionsModel;
	}

	public function get_balance($user_id, $sym) {
		return $this->get_user_balance($user_id, $sym);
	}
	
	public function user_balance($user_id, $sym) {
		return $this->get_user_balance($user_id, $sym);
	}
	

}