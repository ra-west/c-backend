<?php
namespace Northplay\NorthplayApi\Controllers\Install;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class DatabaseSeedController
{

    public function __construct()
    {
        $this->dog_controller = new \Northplay\NorthplayApi\Controllers\DogCallbackController;
        $this->config_model = new \Northplay\NorthplayApi\Models\ConfigModel;
        $this->currency_model = new \Northplay\NorthplayApi\Models\CurrencyModel;
    }

    public function seeder_options() {
        return array(
            "exit" => ["key" => "exit", "description" => "Exit and stop seeding", "function" => 'exit_seeds', "run_in_controller" => 'no'],
            "config" => ["key" => "config", "description" => "Seeds config values from config/northplay-api.php", "function" => 'run_config_seed', "run_in_controller" => 'yes'],
            "games" =>  ["key" => "games", "description" => "Seeds games from external API's", "function" => 'run_games_seed', "run_in_controller" => 'yes'],
            "currency" =>  ["key" => "currency", "description" => "Seeds currency USD and currency EUR from config/northplay-api.php", "function" => 'run_currency_seed', "run_in_controller" => 'yes'],
            "all" =>  ["key" => "all", "description" => "Run all the available seeds", "function" => 'run_all', "run_in_controller" => 'no']
        );
    }


    public function run_all()
    {
        foreach($this->seeder_options() as $seed_option) {
            try {
                $seed_key = $seed_option['key'];
                if($seed_option['run_in_controller'] === "yes") {
                    echo "Running seed: [".$seed_option['key']."]";
                    echo "\n";
                    $function = $seed_option['function'];
                    $this->$function();
                    echo "Completed seed: [".$seed_option['key']."]";
                    echo "\n";
                    echo "\n";
                    save_log("DatabaseSeedController", "Completed running seed ${seed_key}.");
                    
                } else {
                    echo "Skipped seed: [${seed_key}] (run_in_controller not set to 'yes)";
                    echo "\n";
                    echo "\n";
                }
                } catch(\Exception $e) {
                        save_log("DatabaseSeedController", "Failed running seed. (".$e->getMessage().")");
                        echo $e->getMessage();
                }
            }
    }

    public function run_currency_seed()
    {
        $db_seed = config('northplay-api.seeder_data.currency');
        foreach($db_seed as $key=>$config_category) {
                        $currency_select = $this->currency_model->where('symbol_id', $key)->first();
                        if(!$currency_select) {
                            $prepare_key_array = $db_seed[$key];
                            $prepare_key_array["created_at"] = now_nice();
                            $prepare_key_array["rate_updated"] = now_nice();
                            $prepare_key_array["updated_at"] = now_nice();
                            $this->currency_model->insert($prepare_key_array);
                            echo "Inserted: ".$key;
                            echo "\n";
                        } else {
                            echo "Skipped: ".$key." (exists already)";
                            echo "\n";
                        }
        }
    }

    public function run_games_seed()
    {
        $this->dog_controller->insert_games();
    }

    /**
     * run_config_seed
     * Seeds config values to database from config/northplay-api.php
     *
     * @return void
     */
    public function run_config_seed() {
        $db_seed = config('northplay-api.seeder_data.config');
        foreach($db_seed as $cat=>$config_category) {
                foreach($config_category as $subkey=>$sub_value) {
                        $config_select = $this->config_model->where('key', $subkey)->first();
                        if(!$config_select) {
                            $this->config_model->insert([
                                    "key" => $subkey,
                                    "value" => $sub_value,
                                    "category" => $cat,
                                    "created_at" => now(),
                                    "updated_at" => now(),
                            ]);
                            echo "Inserted: ".$cat.".".$subkey."=".$sub_value;
                            echo "\n";
                        } else {
                            echo "Skipped: ".$cat.".".$subkey." (exists already)";
                            echo "\n";
                        }

                }
        }
    }
}