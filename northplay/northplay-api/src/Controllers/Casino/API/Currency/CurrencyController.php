<?php
namespace Northplay\NorthplayApi\Controllers\Casino\API\Currency;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Northplay\NorthplayApi\Controllers\Casino\API\Currency\ExchangeRateController;
use Northplay\NorthplayApi\Controllers\Casino\API\CasinoTrait;

class CurrencyController
{
    use CasinoTrait;

    public function __construct()
    {    
      $this->currency_model = new \Northplay\NorthplayApi\Models\CurrencyModel;
      $this->currency_count = $this->count_and_check();
      $this->check_force_exchange_rate_update();
      $this->exchange_rate_update();
   }
   public function prices_update_interval() {
         return $this->get_config_value("interval_currency_price_update", 30, "currency");
   }

   public function exchange_rate_update() 
   {
      $last_update = Cache::get("exchange_rate_update");
      if(!$last_update) {
         Cache::set("exchange_rate_update", now(), now()->addMinutes($this->prices_update_interval()));
         $printed_currencies = Cache::get("printed_currencies");
         if($printed_currencies) {
         Cache::forget("printed_currencies");
         }
         $exchange_rate = new ExchangeRateController;
         $exchange_rate->update_all_exchange_rates();
      }
   }
   
   public function check_force_exchange_rate_update() {
      $force_exchange_rate = Cache::get("force_exchange_rate");
      if(!$force_exchange_rate) {
          northplay_config_get("force_exchange_rate", "yes", "environment");
          Cache::set("force_exchange_rate", $force_exchange_rate, now()->addSeconds(60));
      }
      if($force_exchange_rate === "yes") {
          $exchange_rate = new ExchangeRateController;
          $exchange_rate->update_all_exchange_rates();
          northplay_config_update("force_exchange_rate", "no", "environment");
          save_log("CurrencyController", "Forced an exchange rate update");
          Cache::forget("force_exchange_rate");
      }
  }

   public function fallback_currency() {
      return [
         "USD" => [
            "symbol_id" => "USD",
            "name" => "US Dollar",
            "type" => "fiat",
            "decimals" => "2",
            "active" => true,
            "rate_usd" => "1.00",
            "rate_updated" => now_nice(),
         ],
      ];
   }
   public function all() {
      try {
            $currency_array = [];
            foreach($this->currency_model->all()->where("active", true) as $currency) {
               $currency_array[$currency["symbol_id"]] = [
                  "id" => $currency["id"],
                  "symbol_id" => $currency['symbol_id'],
                  "name" => $currency['name'],
                  "type" => $currency['type'],
                  "decimals" => $currency['decimals'],
                  "active" => $currency['active'],
                  "rate_usd" => $currency['rate_usd'],
                  "rate_updated" => $currency['rate_updated'],
                  "created_at" => $currency["created_at"],
                  "updated_at" => $currency["updated_at"],
               ];
            }
            return [
               "success" => true,
               "currencies" => $currency_array,
            ];
      } catch(\Exception $e) {
         save_log("CurrencyController", "Critical error: ".$e->getMessage()." at line ".$e->getLine());
         return [
            "success" => false,
            "currencies" => $this->fallback_currency(),
         ];
      }
   }


   public function print_currencies() {
         $currencies = Cache::get("printed_currencies");
         if(!$currencies) {
            $all_currencies = $this->all();
            if($all_currencies['success'] === true) {
               Cache::set("printed_currencies", $all_currencies['currencies'], now()->addMinutes(3));
            } else {
               abort(400, "Currency error");
            }
            $currencies = Cache::get("printed_currencies");
         }
         return $currencies;
   }

	public function convert($amount, $currency)
	{
			return $amount * $this->usd_rate($currency);	
	}

	public function usd_rate($currency)
	{
         if($currency === "USD") {
            return 1;
         }
			$rate = Cache::get($currency."_currency_rate");
			if(!$rate) {
				$currencies = $this->currency_model->all();
				$rate = $currencies->where("symbol_id", $currency)->first()['rate_usd'];
				Cache::set($currency."_currency_rate", $rate, now()->addSeconds(60));
			}
			return $rate;	
	}


   public function active_currencies() {
      try {
         $currency_array = [];
         foreach($this->currency_model->all()->where("active", true) as $currency) {
            $currency_array[$currency["symbol_id"]] = [
               "symbol_id" => $currency['symbol_id'],
               "name" => $currency['name'],
               "type" => $currency['type'],
               "decimals" => $currency['decimals'],
               "rate_usd" => $currency['rate_usd'],
               "rate_updated" => $currency['rate_updated'],
            ];
         }
         return $currency_array;
      } catch(\Exception $e) {
         save_log("CurrencyController", "Critical error: ".$e->getMessage()." at line ".$e->getLine());
         return $this->fallback_currency();
      }
   }

   public function count_and_check() {
      $counted_currency = $this->currency_model->count();
      if($counted_currency < 1) {
         save_log("CurrencyController", "Critical Error: no currencies found!");
         abort(400, "Critical Error: no currencies found.");
         $counted_currency = $this->currency_model->count();
      }
      return $counted_currency;
   }
}