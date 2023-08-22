<?php

namespace Northplay\NorthplayApi\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class NorthplayTestCacheDriver extends Command
{
    public $signature = 'northplay:test-cache';

    public $description = 'Test Cache Driver';

    public function handle(): int
    {
        $this->comment('Northplay - Testing Cache');
        $random_key = rand(10000001, 21000000);
        $random_value = rand(40000001, 81000000);
        $this->comment('Generated random key:value');
        $this->info("Key ".$random_key.", value ".$random_value);
        sleep(1);
        $this->comment(' ');
        $this->comment("Storing in cache for 9 seconds:");
        $this->info("Key ${random_key}, value ${random_value}");
        $this->comment(' ');
        Cache::put($random_key, $random_value, now()->addSeconds(9));
        $this->comment('Sleeping for 5 seconds before trying to retrieve..');
        sleep(3);
        $this->line('..2');
        sleep(1);
        $this->line('..1');
        sleep(1);
        $value_from_cache = Cache::get($random_key);
        if(!$value_from_cache) {
            $this->error("Key ${random_key} not found.");
            die();
        }
        $this->comment(' ');
        $this->info("Retrieved ${random_key}:${value_from_cache}");
        $this->comment(' ');
        sleep(2);
        $this->comment('Waiting 5 more seconds to test another time.. ');
        $this->comment('(it should fail to retrieve from cache, due storing for only 9 seconds)');
        sleep(3);
        $this->line('..2');
        sleep(1);
        $this->line('..1');
        sleep(1);
        $value_from_storage = Cache::get($random_key);
        if($value_from_storage) {
            $this->error("Key ${random_key} should've been deleted, please check driver.");
        } else {
            $this->comment("Key ${random_key} is missing from cache as intended.");
            $this->comment(' ');
            $this->info("All seem to be working fine!");
        }

        return self::SUCCESS;
    }
}
