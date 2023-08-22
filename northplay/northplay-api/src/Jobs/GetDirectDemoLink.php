<?php
namespace Northplay\NorthplayApi\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Http;

class GetDirectDemoLink implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $game;

    public function __construct($game)
    {
        $this->game = $game;
    }

    public function handle()
    {
				$softswiss_model = new \Northplay\NorthplayApi\Models\SoftswissGameModel;
				$game_model = $softswiss_model->where("id", $this->game)->first();
				if($game_model->demo_game_direct === NULL) {
						$http_get = Http::get($game_model->demo_game);
						$url = in_between('game_url', ',', $http_get->body());
						$url = str_replace('"', '', $url);
						$url = str_replace(':http', 'http', $url);
						$url = str_replace(',', '', $url);
						echo $url;
						$softswiss_model->where("id", $this->game)->update([
								"demo_game_direct" => $url
						]);
				}
		}
}