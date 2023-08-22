<?php

namespace Northplay\NorthplayApi\Commands;

use Illuminate\Console\Command;
use Northplay\NorthplayApi\Controllers\Install\DatabaseSeedController;

class NorthplaySeederCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'northplay:seeder {seed_method?}';

    /**
     * The console command description.
     *
     * @var string
     */
    public function __construct() {
      parent::__construct();
      $this->seeding_array = [];
      $this->seeding_options = [];

    }
    protected $description = 'Command description';
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
      $this->install_controller = new DatabaseSeedController;
      $this->seeding_options = $this->install_controller->seeder_options();

      $message = $this->show_seed_options();
      $options = array_keys($this->seeding_options);

      if ($this->argument('seed_method')) {
         $seed_method = $this->argument('seed_method');
         sleep(1);
         $this->line("Selected seed method: [".$seed_method."]");
         $this->run_seed($seed_method);
      } else {
        $seed_method = $this->choice(
          'Please select the seed method',
          $options, $options[0]);
          $this->line("Selected seed method: [".$seed_method."]");
          sleep(1);
          $this->run_seed($seed_method);
      }

    }

    public function run_seed($seed_method)
    {
      $this->install_controller = new DatabaseSeedController;
      $this->seeding_options = $this->install_controller->seeder_options();
      if($seed_method === 'exit') {
        $this->info("Exiting seeder..");
        $this->line("");
        return self::SUCCESS;
      }
      if(isset($this->seeding_options[$seed_method])) {
          $function = $this->seeding_options[$seed_method]['function'];
          $this->install_controller->$function();
      } else {
        $this->error($seed_method);
        $this->error("The method specified is not available.");
      }
    }

    public function show_seed_options() {
        $this->line(' ');
        $this->info('Database seed options:');
        
        foreach($this->seeding_options as $seed_option) {
          $this->comment('['.$seed_option['key'].'] - '.$seed_option['description']);
        }
    }
}
