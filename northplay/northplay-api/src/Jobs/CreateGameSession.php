<?php
namespace Northplay\NorthplayApi\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Http;
use Northplay\NorthplayApi\Controllers\Integrations\Games\GameKernel;

class CreateGameSession implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $session_id;
    private $provider;

    public function __construct($session_id, $provider)
    {
        $this->session_id = $session_id;
        $this->provider = $provider;
        
    }

    public function handle()
    {
        $gamekernel_controller = new GameKernel;
        $gamekernel_controller->create_session($this->provider, $this->session_id);
    }
}