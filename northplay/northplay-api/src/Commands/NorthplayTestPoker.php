<?php

namespace Northplay\NorthplayApi\Commands;

use Illuminate\Console\Command;

class NorthplayTestPoker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'northplay:test-poker {channel_id?} {message?}';

    protected $description = 'Command description';
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
      if ($this->argument('channel_id')) {
         $channel_id = $this->argument('channel_id');
      } else {
        $channel_id = $this->ask('Please enter the channel id you want to message');
      }

      if ($this->argument('message')) {
				$message = $this->argument('message');
			} else {
				$message = $this->ask('Please enter the message you want to send');
			}
			
			$message = array (
				'msg_type' => 'game_state',
				'name' => 'TWPD',
				'max_players' => 9,
				'small_blind' => 1,
				'big_blind' => 2,
				'buy_in' => 200,
				'password' => NULL,
				'button_idx' => 0,
				'hand_num' => 1,
				'game_suspended' => false,
				'players' => 
				array (
					0 => 
					array (
						'index' => 0,
						'player_name' => 'Bot 0',
						'money' => 148,
						'is_active' => true,
						'last_action' => 'check',
						'flop_cont' => 50,
						'river_cont' => 0,
						'turn_cont' => 0,
						'preflop_cont' => 2,
					),
					1 => 
					array (
						'index' => 1,
						'player_name' => 'qwqwqqw',
						'money' => 155548,
						'is_active' => true,
						'last_action' => 'check',
						'flop_cont' => 50,
						'river_cont' => 0,
						'turn_cont' => 0,
						'preflop_cont' => 2,
					),
					2 => 
					array (
						'index' => 2,
						'player_name' => 'qwqwqqw',
						'money' => 155548,
						'is_active' => true,
						'last_action' => 'check',
						'flop_cont' => 50,
						'river_cont' => 0,
						'turn_cont' => 0,
						'preflop_cont' => 2,
					),
					3 => NULL,
					4 => NULL,
					5 => NULL,
					6 => NULL,
					7 => NULL,
					8 => NULL,
				),
				'street' => 'showdown',
				'current_bet' => 0,
				'flop' => '4d3sTs',
				'turn' => 'Jh',
				'river' => '6d',
				'pots' => 
				array (
					0 => 104,
				),
				'your_index' => 1,
				'hole_cards' => 'Js5h',
				'showdown' => 
				array (
					0 => 
					array (
						'index' => 1,
						'player_name' => 'qwqwqqw',
						'winner' => false,
						'showCards' => true,
						'hole_cards' => 'Js5h',
						'hand_result' => 'Pair',
						'constituent_cards' => 'Js-Jh',
						'kickers' => '5h-6d-Ts',
					),
					1 => 
					array (
						'index' => 0,
						'player_name' => 'Bot 0',
						'winner' => true,
						'showCards' => true,
						'payout' => 104,
						'hole_cards' => '3c6h',
						'hand_result' => 'Two Pair',
						'constituent_cards' => '3c-3s-6h-6d',
						'kickers' => 'Jh',
					),
				),
			);
			$data = [
				"type" => "poker",
				"message" => $message,
			];

			$websocket_controller = new \Northplay\NorthplayApi\Controllers\Casino\WebsocketController;
			$websocket_controller->sendMessage($channel_id, $data);
    }

}
