<?php
namespace Northplay\NorthplayApi\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Northplay\NorthplayApi\Controllers\Integrations\GatewayTrait;
use Northplay\NorthplayApi\Models\GameBufferModel;
use Northplay\NorthplayApi\Jobs\RecentGameWebsocket;
use Northplay\NorthplayApi\Controllers\Casino\API\CasinoTrait;
use App\Models\User;

class GameBufferWrite implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, GatewayTrait, CasinoTrait;

    private $session_id;
    private $round_id;
    private $winAmount;
    private $betAmount;
    private $data;
    private $play_currency;
    private $debit_currency;

    public function __construct(
        $session_id,
        $round_id,
        $winAmount,
        $betAmount,
        $data,
        $play_currency,
        $debit_currency
        )
    {
        $this->session_id = $session_id;
        $this->round_id = $round_id;
        $this->winAmount = $winAmount;
        $this->betAmount = $betAmount;
        $this->data = $data;
        $this->play_currency = $play_currency;
        $this->debit_currency = $debit_currency;
    }


    
    public function handle()
    {
        
        $session = $this->select_parent_session($this->session_id);
		$retrieve_game = $this->select_game($session->game_id);
        $date = now();
        $rounded_id = $retrieve_game->provider.'-'.$this->round_id;
        $select_data = GameBufferModel::where("round_id", $rounded_id)->first();
        if($select_data) {
            $select_data->update([
                "win" => ($this->winAmount + $select_data->win),
                "lose" => ($this->betAmount + $select_data->lose),
                "updated_at" => $date,
            ]);
        } else {
            $tx_id = generate_uid();
            $select_user = User::where("id", $session->user_public_id)->first();

            $username = $select_user->name;
            if($select_user->profile_hidden === true) {
                $username = "@incognito";
            }
            
            $select_data = [
                'user_name' => $username,
                'user_id' => $session->user_public_id,
                'game_id' => $session->game_id,
                'game_slug' => $retrieve_game->slug,
                'debit_currency' => $this->debit_currency,
                'play_currency' => $this->play_currency,
                'session_id' => $this->session_id,
                'finished' => false,
                'broadcasted' => false,
                'round_id' => $rounded_id,
                'internal_id' => $tx_id,
                'win' => $this->winAmount,
                'lose' => $this->betAmount,
                'game_data' => JSON_encode($this->data),
                'bonus_eligible' => true,
                'created_at' => $date,
                'updated_at' => $date,
            ];
            $new = GameBufferModel::insert($select_data);
        }
        $unfinished_games = GameBufferModel::where("finished", false)->where("round_id", "!=", $rounded_id)->where("session_id", $this->session_id)->get();
        foreach($unfinished_games as $game) {
            $select_unfinished = GameBufferModel::where("id", $game['id'])->first();
            $select_unfinished->update([
                'finished' => true,
                'updated_at' => $date,
            ]);
            $retrieved_game = $this->select_game($game['game_id']);

            $vip_points = number_format((($select_unfinished->lose - $select_unfinished->win) * 100), 0, '', '');
            if($vip_points > 0) {
				\App\Models\User::where('id', $session->user_public_id)->increment('vip_points', $vip_points);
                $this->check_vip($session->user_public_id);

            }
            RecentGameWebsocket::dispatch(
                $game['id'],
                $game['user_name'],
                $game['internal_id'],
                $session->currency,
                $session->debit_currency,
                $retrieved_game->slug,
                $retrieved_game->title,
                $select_unfinished->win,
                $select_unfinished->lose,
                $retrieved_game->provider,
                $date,
            )->onQueue('low');
        }
    }
}