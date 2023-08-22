<?php
namespace Northplay\NorthplayApi\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Http;
use Northplay\NorthplayApi\Controllers\Integrations\ParentSession;

class CreateSession implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tries = 3;

    private $entry_session;

    public function __construct($entry_session)
    {
        $this->entry_session = $entry_session;
    }

    public function handle()
    {
		$parent_session_controller = new ParentSession;
		$parent_session_controller->create_parent_session($this->entry_session);
    }
}