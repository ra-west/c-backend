<?php

namespace Northplay\NorthplayApi\Controllers\Casino;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Northplay\NorthplayApi\Controllers\Integrations\GatewayTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Northplay\NorthplayApi\Models\GameBufferModel;

class RecentGamesController
{
    use GatewayTrait; 
    
    public function show_history(Request $request) {
        $games = GameBufferModel::latest()->limit(10)->get();
        if($games->count() < 2) {
        
        return [
            "success" => true,
            "data" => [],
        ];
        }
        foreach($games as $game) {
            $winAmount = number_format($game['win'], '2', '.', '');
            $lossAmount = number_format($game['lose'], '2', '.', '');
            $retrieved_game = $this->select_game($game['game_id']);
            $winLose = number_format(($winAmount - $lossAmount), '2', '.', '');
        
            if($winLose < 0) {
                $outcome_verbatim = "loss";    
            } elseif($winLose > 0) {
                $outcome_verbatim = "win";    
            } else {
                $outcome_verbatim = "draw";
            }

            $game_array[] = array(
                "tx_id" => $game['internal_id'],
                "type" => "recent-games",
                "user" => $game['user_name'],
                "play_currency" => $game['play_currency'],
                "debit_currency" => $game['debit_currency'],
                "win" => $winAmount,
                "loss" => $lossAmount,
                "winLose" => $winLose,
                "outcome" => $outcome_verbatim,
                "game_slug" => $retrieved_game->slug,
                "game_title" => $retrieved_game->title,
                "ts" => strtotime($game['created_at']),
            );
        }

        $data = [
            "success" => true,
            "data" => collect($game_array),
        ];

        return response()->json($data, 200);
    }
}
 