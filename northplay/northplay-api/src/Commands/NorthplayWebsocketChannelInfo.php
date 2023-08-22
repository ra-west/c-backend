<?php

namespace Northplay\NorthplayApi\Commands;

use Illuminate\Console\Command;

class NorthplayWebsocketChannelInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'northplay:websocket-info {channel_id?}';

    protected $description = 'Command description';
    /**
     * Execute the console command.
     */

		public function websocketController()
		{
			$websocket_controller = new \Northplay\NorthplayApi\Controllers\Casino\WebsocketController;
			return $websocket_controller;
		}

		public function handle(): void
		{
			if ($this->argument('channel_id')) {
				$channel_id = $this->argument('channel_id');
			} else {
						$channel_options = [
							"all",
							"specific_channel",
						];
						$channel_method = $this->choice(
							'Channel method?',
							$channel_options, $channel_options[0]);
							$this->line("Selected channel method: [".$channel_method."]");
						if($channel_method === "all") {
							echo json_encode($this->websocketController()->channels(), JSON_PRETTY_PRINT);
							
						}
			}
		}

		public function specific_channel($channel_id)
		{
			$this->websocketController()->channel_info($channel_id);
			$this->websocketController()->history($channel_id);
		}

}
