<?php

namespace Northplay\NorthplayApi\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Northplay\NorthplayApi\Controllers\Casino\API\Auth\UserBalanceController;

class NorthplayAddBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'northplay:add-balance {user_id?}';

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
      $user_controller = new UserBalanceController;
	  
	  if ($this->argument('user_id')) {
		$user_id = $this->argument('user_id');
	  } else {
	   	$user_id = $this->ask('Which user do you wish to credit?', 1);
	  }
	 $this->line("Selected user: [".$user_id."]");
	 $sym = $this->ask('Which currency?', "USD");
	 $amount = $this->ask('Amount (integer) to be added? 10$ = 1000', 1000);
     $user_controller->credit_user_balance($user_id, $sym, $amount, "Artisan command manually added balance");
	 
	 echo "New Balance:";
	 echo $user_controller->user_balance($user_id, $sym);
    }
}