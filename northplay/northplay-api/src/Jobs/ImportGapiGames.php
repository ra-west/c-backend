<?php
namespace Northplay\NorthplayApi\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Http;

class ImportGapiGames implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;
    private $url;
	private $provider;

    public function __construct($url, $provider)
    {
        $this->url = $url;
		$this->provider = $provider;
    }
	
    public function handle()
    {
				$host = parse_url($this->url, PHP_URL_HOST);
				$response = Http::withHeaders([
				"Authority" => $host
				])->get($this->url);
				$games = json_decode($response->body(), true)['data'];
				$games = collect($games)->shuffle();
				save_log("ImportGapiLol", $games);
				$softswiss_model = new \Northplay\NorthplayApi\Models\SoftswissGameModel;
				$softswiss_model_tag = new \Northplay\NorthplayApi\Models\SoftswissGameTagModel;
				$date_now = now_nice();
				foreach($games as $key=>$game) {
					try {
						if($game["category"] === $this->provider) {		
                            $slug = $this->provider.'/'.$game['menu_title'];
                            save_log("ImportGapiGames (slug)", $slug);
                            if(!$softswiss_model->where('slug', $slug)->first()) {
                                    $row = $softswiss_model->insert([
                                            "slug" => $slug,
                                            "title" => $game["name"],
                                            "provider" => $game["category"],
                                            "source" => "gapi.lol",
                                            "demo_game" => "n/a",
                                            "created_at" => $date_now,
                                            "updated_at" => $date_now,
                                    ]);
                            }
					}
					} catch(\Exception $e) {
						save_log("ImportGamesJob", "Error: ". $e->getMessage());
					}
				}
		}
}