<?php

namespace Northplay\NorthplayApi\Commands;

use Illuminate\Console\Command;
use Northplay\NorthplayApi\Controllers\Install\DatabaseSeedController;
use Northplay\NorthplayApi\Controllers\Casino\API\Currency\ExchangeRateController;
use Northplay\NorthplayApi\Controllers\Casino\API\Currency\CurrencyController;
use Illuminate\Support\Facades\Cache;

class NorthplayForceExchangeRateUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'northplay:force-exchange-rate-update';

    /**
     * The console command description.
     *
     * @var string
     */
    public function __construct() {
      parent::__construct();

    }
    protected $description = 'Command description';
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
      $exchange_rate_controller = new ExchangeRateController;
      $currency_controller = new CurrencyController;

      $exchange_rate_controller->update_all_exchange_rates();
      echo json_encode($currency_controller->print_currencies(), JSON_PRETTY_PRINT);
   }

}
