<?php

namespace Northplay\NorthplayApi\Commands;

use Illuminate\Console\Command;

class NorthplayApiCommand extends Command
{
    public $signature = 'northplay-api:no';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
