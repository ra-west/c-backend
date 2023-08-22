<?php
namespace Northplay\NorthplayApi\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class PokerController
{

    public function __construct()
    {
				$this->api_key = "e91741cd-9403-4743-a6bf-d45c05a84a85";
				$this->secret_key = "2c4371bd-e76a-4a4a-bdee-cfce4807becb";
				$this->client = new \phpcent\Client("http://localhost:8000/api", $this->api_key, $this->secret_key);
        $this->mail_model = new \Northplay\NorthplayApi\Models\EmailLogModel;
    }

		public function mock_data() 
		{
			if((rand(1, 10)) < 5) {
					$model = [
						"msg_type" => "game_state", 
						"name" => "LVDV", 
						"max_players" => 9, 
						"small_blind" => 1, 
						"big_blind" => 2, 
						"buy_in" => 200, 
						"password" => null, 
						"button_idx" => 0, 
						"hand_num" => 1, 
						"game_suspended" => false, 
						"players" => [
									[
										"index" => 0, 
										"player_name" => "Bot 0", 
										"money" => 198, 
										"is_active" => true, 
										"preflop_cont" => 2, 
										"flop_cont" => 0 
									], 
									[
												"index" => 1, 
												"player_name" => "qwqwqw", 
												"money" => 198, 
												"is_active" => true, 
												"last_action" => "check", 
												"preflop_cont" => 2, 
												"flop_cont" => 0 
									], 
									null, 
									null, 
									null, 
									null, 
									null, 
									null, 
									null 
							], 
						"street" => "flop", 
						"current_bet" => 0, 
						"flop" => "6h6s5c", 
						"pots" => [
													4 
												], 
						"index_to_act" => 0, 
						"your_index" => 1, 
						"hole_cards" => "7hJs" 
				]; 
			} else {
					$model = [
						"msg_type" => "game_state", 
						"name" => "FPNZ", 
						"max_players" => 9, 
						"small_blind" => 1, 
						"big_blind" => 2, 
						"buy_in" => 200, 
						"password" => null, 
						"button_idx" => 1, 
						"hand_num" => 2, 
						"game_suspended" => false, 
						"players" => [
									[
											"index" => 0, 
											"player_name" => "Bot 0", 
											"money" => 174, 
											"is_active" => true, 
											"flop_cont" => 0, 
											"turn_cont" => 0, 
											"preflop_cont" => 26 
									], 
									[
												"index" => 1, 
												"player_name" => "Bot 1", 
												"money" => 173, 
												"is_active" => true, 
												"flop_cont" => 0, 
												"turn_cont" => 0, 
												"preflop_cont" => 26 
											], 
									[
														"index" => 2, 
														"player_name" => "qwqwqw", 
														"money" => 200, 
														"is_active" => false, 
														"is_sitting_out" => true, 
														"flop_cont" => 0, 
														"turn_cont" => 0, 
														"preflop_cont" => 1 
												], 
									null, 
									null, 
									null, 
									null, 
									null, 
									null 
								], 
						"street" => "turn", 
						"current_bet" => 0, 
						"flop" => "8hJd4s", 
						"turn" => "9s", 
						"pots" => [
															53 
														], 
						"index_to_act" => 0, 
						"your_index" => 2, 
						"hole_cards" => "Ts5s" 
					]; 
									
			}
			return $model;

		}


		public static function v4() 
		{
				return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
		
				// 32 bits for "time_low"
				mt_rand(0, 0xffff), mt_rand(0, 0xffff),
		
				// 16 bits for "time_mid"
				mt_rand(0, 0xffff),
		
				// 16 bits for "time_hi_and_version",
				// four most significant bits holds version number 4
				mt_rand(0, 0x0fff) | 0x4000,
		
				// 16 bits, 8 bits for "clk_seq_hi_res",
				// 8 bits for "clk_seq_low",
				// two most significant bits holds zero and one for variant DCE1.1
				mt_rand(0, 0x3fff) | 0x8000,
		
				// 48 bits for "node"
				mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
				);
			}
		public function broadcasting() {
				$random_tx_id = self::v4();
				$channel = "poker_table_1";
				$data = [
					"broadcasting_id" => $random_tx_id,
					"type" => "game_state",
					"game" => "poker",
					"data" => $this->mock_data(),
				];
				$response = $this->client->publish($channel, $data);
				return $response;
		}

	}
