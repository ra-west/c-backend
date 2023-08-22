<?php
namespace Northplay\NorthplayApi\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Http;

class ImportSoftswiss implements ShouldQueue
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
				"Authority" => $host,
				"Accept" => 'application/vnd.softswiss.v1+json'
				])->get($this->url);
				$games = json_decode($response->body(), true);
				$games = collect($games)->shuffle();
				save_log("ImportSoftswiss", $games);
				$softswiss_model = new \Northplay\NorthplayApi\Models\SoftswissGameModel;
				$softswiss_model_tag = new \Northplay\NorthplayApi\Models\SoftswissGameTagModel;
				$date_now = now_nice();
				foreach($games as $key=>$game) {
					try {
						if($game["provider"] === $this->provider) {		
						$slug = explode("/", $game["demo"])[4]."/".explode("/", $game["demo"])[5];
						save_log("ImportGames", $slug);
						if(!$softswiss_model->where('slug', $slug)->first()) {
								$row = $softswiss_model->insert([
										"slug" => $slug,
										"title" => $game["title"],
										"provider" => $game["provider"],
										"title" => $game["title"],
										"source" => $host,
										"demo_game" => $game["demo"],
										"created_at" => $date_now,
										"updated_at" => $date_now,
								]);

								if($row) {
										$game_id = $softswiss_model->where('slug', $slug)->first()->id;
										foreach($game['collections'] as $tagKey=>$tagRating) {
												$softswiss_model_tag->insert([
														"game_id" => $game_id,
														"tag" => $tagKey,
														"rating" => $tagRating,
														"created_at" => $date_now,
														"updated_at" => $date_now,
												]);
										}
						}
					}
					}
					} catch(\Exception $e) {
						save_log("ImportGamesJob", "Error: ". $e->getMessage());
					}
				}
		}
}