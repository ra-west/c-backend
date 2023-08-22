<?php
namespace Northplay\NorthplayApi\Controllers\Casino\API\Currency;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Northplay\NorthplayApi\Controllers\Casino\API\CasinoTrait;

class ExchangeRateController
{
    use CasinoTrait;


    public function __construct()
    {
        $this->currency_model = new \Northplay\NorthplayApi\Models\CurrencyModel;
    }


    public function abstract_api()
    {
        $api_key = $this->get_config_value("abstractapi_key", "c24a221779b244e6bfd64e5225ff5a5f", "exchange_rate_keys");
        $api_url = "https://exchange-rates.abstractapi.com/v1/live/?api_key=".$api_key."&base=USD";
        return json_decode(Http::get($api_url), true);
    }

    public function coinmarketcap_api()
    {
        $api_key = $this->get_config_value("abstractapi_key", "c0a60bf0-ffef-4c50-8e76-2d64ff8878ee", "exchange_rate_keys");
        $api_url = "https://pro-api.coinmarketcap.com/v1/cryptocurrency/listings/latest?CMC_PRO_API_KEY=".$api_key."&limit=175";
        $http = Http::get($api_url);
    }

    public function update_all_exchange_rates() {
        $abstract_api = $this->abstract_api();
        foreach($this->currency_model->all()->where("symbol_id", "!=", "USD") as $currency) {
            $symbol = $currency["symbol_id"];
            if(isset($abstract_api["exchange_rates"][$symbol])) {
                    $price = number_format($abstract_api["exchange_rates"][$symbol], 7, '.', '');
                    $this->currency_model->where('symbol_id', $symbol)->update([
                        "rate_usd" => $price,
                        "rate_updated" => now(),
                    ]);
                    save_log("ExchangeRateController", "Updated: ".$symbol);
                }
            }
    }

}