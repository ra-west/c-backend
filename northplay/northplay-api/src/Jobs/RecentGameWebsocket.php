<?php
namespace Northplay\NorthplayApi\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Northplay\NorthplayApi\Models\GameBufferModel;
use Carbon\Carbon;
use Northplay\NorthplayApi\Controllers\Integrations\GatewayTrait;

class RecentGameWebsocket implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, GatewayTrait;
    private $buffer_id;
    private $user;
    private $tx_id;
    private $play_currency;
    private $debit_currency;
    private $game_slug;
    private $game_title;
    private $win;
    private $loss;
    private $provider;
    private $date;

    public function __construct($buffer_id, $user, $tx_id, $play_currency, $debit_currency, $game_slug, $game_title, $win, $loss, $provider, $date)
    {
        $this->buffer_id = $buffer_id;
        $this->user = $user;
        $this->tx_id = $tx_id;
        $this->play_currency = $play_currency;
        $this->debit_currency = $debit_currency;
        $this->game_slug = $game_slug;
        $this->game_title = $game_title;
        $this->win = $win;
        $this->loss = $loss;
        $this->provider = $provider;
        $this->date = $date;
    }

    public function handle()
    {
        $winLose = number_format(($this->win - $this->loss), '2', '.', '');
        
        if($winLose < 0) {
            $outcome_verbatim = "loss";    
        } elseif($winLose > 0) {
            $outcome_verbatim = "win";    
        } else {
            $outcome_verbatim = "draw";
        }
        
        $data = array(
            "type" => "recent-games",
            "user" => $this->user,
            "tx_id" => $this->tx_id,
            "play_currency" => $this->play_currency,
            "debit_currency" => $this->debit_currency,
            "win" => number_format($this->win, '2', '.', ''),
            "loss" =>  number_format($this->loss, '2', '.', ''),
            "winLose" => $winLose,
            "outcome" => $outcome_verbatim,
            "game_slug" => $this->game_slug,
            "game_title" => $this->game_title,
            "ts" => strtotime($this->date),
        );

        
        $websocket_controller = new \Northplay\NorthplayApi\Controllers\Casino\WebsocketController;
        $websocket_controller->publish("pubstates", $data);

        GameBufferModel::where('id', $this->buffer_id)->update([
            "broadcasted" => true,
            "updated_at" => now(),
        ]);


    }
}


