<?php

namespace Northplay\NorthplayApi\Controllers\Casino;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Northplay\NorthplayApi\Controllers\Integrations\GatewayTrait;

class GameRoomsController
{
    use GatewayTrait;
     protected $game_rooms_model;
     protected $room;
    
     public function __construct()
     {
        $this->game_rooms_model = new \Northplay\NorthplayApi\Models\GameRoomsModel;
     }

     public function set_state($state_1, $state_2)
     {
        $this->game_rooms_model->where("room", $this->room)->update([
            "state_1" => $state_1,
            "state_2" => $state_2,
        ]);
     }

     public function room_config($state)
     {
        $config = [];
        $main_substate_timer_interval = [
            2000,
            2000,
            2000,
            2000,
            2000,
            2000,
            2000,
            2000,
        ];

        $config['default'] = [
            "min_bet" => 100,
            "max_bet" => 1000,
            "play_currency" => "USD",
        ];

        if($state === 1) {
            $config['state'] = [
                "substate_interval" => $main_substate_timer_interval[$state],
            ];
        }
        return $config;
     }

    public function state_check($room_id)
    {
            $this->room = $room_id;
            $this->game_rooms_model = new \Northplay\NorthplayApi\Models\GameRoomsModel;
            $type = "blackjack-event";
            $select_room = $this->game_rooms_model->get()->where("room", $room_id)->first();

            if(!$select_room) {
                $this->game_rooms_model->insert([
                    "room" => $room_id,
                    "state_1" => 0,
                    "state_2" => 0,
                    "state_3" => 0,
                    "round_id" => "BLACKJACK_MP_START",
                    "room_data" => json_encode(array("1" => 1)),
                    "extra_data" => json_encode(array("2" => 2)),
                ]);
                $select_room = $this->game_rooms_model->get()->where("room", $room_id)->first();
            }

            if($select_room->state_1 === 0) {
                if($select_room->state_2 === 0) {
                    $updated =  $this->game_rooms_model->where("room", $room_id)->update([
                        "room_data" => array(
                            "player_cards" => [],
                            "player_score" => 0,
                            "dealer_score" => 0,
                            "dealer_cards" => [],
                            "turn" => "player",
                            "next_card" => 0,
                        ),
                        "extra_data" => array(
                            "bets" => [],
                            "errors" => [],
                            "deck" => $this->deck_nice(),
                        ),
                        "round_id" => "BJ-".Str::random(32)."-".time(),
                        "state_2" => 1,
                    ]);
                    $select_room = $this->game_rooms_model->get()->where("room", $room_id)->first();
                }
                if($select_room->state_2 < 5) {
                    $this->set_state(($select_room->state_1), ($select_room->state_2 + 1));
                }
                if($select_room->state_2 === 5) {
                    $this->set_state(($select_room->state_1 + 1), 0);
                }
            }

            // STATE: PLACE_BETS //
            if($select_room->state_1 === 1) {
                if($select_room->state_2 === 0) {
                    if(isset($select_room->extra_data['bets'][0])) {
                        $this->set_state(($select_room->state_1), 1);
                    }
                } else {
                    if($select_room->state_2 < 5) {
                        $this->set_state(($select_room->state_1), ($select_room->state_2 + 1));
                    }
                }
                if($select_room->state_2 === 5) {
                    if(isset($select_room->extra_data['bets'][0])) {
                        $this->set_state(2, 0);
                    }
                }
            }

            // STATE: START_ROUND //
            if($select_room->state_1 === 2) {
                if($select_room->state_2 === 0) {

                    // draw init cards
                    $card_deck = $select_room->extra_data['deck'];
                    $temp_room_data = $select_room->room_data;
                    $temp_room_data['player_cards'] = [
                        $card_deck[0],
                        $card_deck[2],
                    ];

                    $temp_room_data['dealer_cards'] = [
                        $card_deck[1],
                    ];
                    $temp_room_data['next_card'] = 4;
                    $temp_room_data['player_score'] = $this->handValue($temp_room_data['player_cards']);
                    $temp_room_data['dealer_score'] = $this->handValue($temp_room_data['dealer_cards']);

                    $updated =  $this->game_rooms_model->where("room", $room_id)->update([
                        "room_data" => json_encode($temp_room_data),
                        "extra_data" => json_encode(array(
                            "bets" => [],
                            "errors" => [],
                            "deck" => $this->deck_nice(),
                        )),
                        "state_2" => 1,
                    ]);
                } else {
                    if($select_room->state_2 < 5) {
                        $this->set_state(($select_room->state_1), ($select_room->state_2 + 1));
                    }
                }

                if($select_room->state_2 === 5) {
                    $this->set_state(3, 0);
                }
            }

            

            // STATE: PLAYER_OPTION //
            if($select_room->state_1 === 3) {
                if($select_room->state_2 === 0) {
                    // draw init cards
                    $card_deck = $select_room->extra_data['deck'];
                    $temp_room_data = $select_room->room_data;
                    collect($temp_room_data['player_cards'])->sum();
                    $temp_room_data['player_cards'] = [
                        $card_deck[0],
                        $card_deck[2],
                    ];

                    $temp_room_data['dealer_cards'] = [
                        $card_deck[1],
                    ];

                    $updated =  $this->game_rooms_model->where("room", $room_id)->update([
                        "room_data" => json_encode($temp_room_data),
                        "extra_data" => json_encode(array(
                            "bets" => [],
                            "errors" => [],
                            "deck" => $this->deck_nice(),
                        )),
                        "state_2" => 1,
                    ]);
                } else {
                    if($select_room->state_2 < 5) {
                        $this->set_state(($select_room->state_1), ($select_room->state_2 + 1));
                    }
                }

                if($select_room->state_2 === 5) {
                    $this->set_state(3, 0);
                }
            }

            $main_state = [
                'NEW_ROUND',
                'PLACE_BETS',
                'START_ROUND',
                'PLAYER_OPTION',
                'PLAYER_DRAW',
                'DEALER_DRAW',
                'WIN_PAYOUT',
                'COMPLETED',
            ];

            $main_substate = [
                "INIT",
                "TIMER_GREEN",
                "TIMER_ORANGE",
                "TIMER_RED",
                "TIMER_BLACK",
                "FINAL",
            ];



            $main_substate_timer_interval = [
                2000,
                2000,
                2000,
                2000,
                2000,
                2000,
                2000,
                2000,
            ];

            $select_room = $this->game_rooms_model->where("room", $room_id)->first();
            $select_extra_data = $select_room->extra_data;
            $select_room_data = $select_room->room_data;
            echo " \n";
            echo "ROOM: [" . $select_room->room . "] \n";
            echo "MAINSTATE: ".$select_room->state_1." (". $main_state[$select_room->state_1].") \n"; 
            echo "SUBSTATE: ".$select_room->state_2." (". $main_substate[$select_room->state_2].") \n"; 
            echo "TURN: [".$select_room_data['turn']."] \n"; 
            echo "- \n";

			$data = [
				"type" => "room-event",
                "room" => $select_room->room,
                "round_id" => $select_room->round_id,
                "state_1" => [
                    "int" => $select_room->state_1,
                    "desc" => $main_state[$select_room->state_1]
                ],
                "state_2" => [
                    "int" => $select_room->state_2,
                    "desc" => $main_state[$select_room->state_2]
                ],
                "state_3" => [
                    "int" => $select_room->state_3,
                    "desc" => $main_state[$select_room->state_3]
                ],
                "config" => $this->room_config($select_room->state_1),
                "cards" => [
                    "player" => $select_room_data["player_cards"],
                    "player_score" => $this->handValue($select_room_data["player_cards"]),
                    "dealer" =>  $select_room_data["dealer_cards"],
                    "dealer_score" => $this->handValue($select_room_data["dealer_cards"]),
                ],
                "turn" => $select_room_data["turn"],
			];

			$websocket_controller = new \Northplay\NorthplayApi\Controllers\Casino\WebsocketController;
			$websocket_controller->publishAll($data);
    }

    function handValue($hand) {
        if(!collect($hand)->first()) {
            return 0;
        }
        $handArray = array();
        $handTotal = 0;
        
        // loop through the hand to determine each card value
        foreach ($hand as $cardInput) {
            $card = $cardInput["number"];
          if (is_numeric($card)) {
            array_push($handArray, $card);
          } else if ($card === 'a') {
            array_push($handArray, '11');
          } else if (in_array($card, ['k', 'q', 'j'])) {
            array_push($handArray, '10');
          }
        }
        
        //add up the hand total
        for ($i = 0; $i < count($handArray); $i++) {
          $handTotal += (int) $handArray[$i];
        }
        //do while loop for handling aces, which can be 1 or 11
        do {
          if ($handTotal > 21 && in_array('a', $hand)) {
            $handTotal = $handTotal - 10;
          }
        } while ($handTotal > 21);
        echo $handTotal;
        return $handTotal;
      }

    public function deck_nice() {
        $decker = array(
            [   
                "number" => '2',
                "suit" => "diams",
                "isFirst" => false
            ],
            [   
                "number" => '2',
                "suit" => "clubs",
                "isFirst" => false
            ],
            [   
                "number" => '2',
                "suit" => "hearts",
                "isFirst" => false
            ],
            [   
                "number" => '2',
                "suit" => "spades",
                "isFirst" => false
            ],
            [   
                "number" => '3',
                "suit" => "diams",
                "isFirst" => false
            ],
            [   
                "number" => '3',
                "suit" => "clubs",
                "isFirst" => false
            ],
            [   
                "number" => '3',
                "suit" => "hearts",
                "isFirst" => false
            ],
            [   
                "number" => '3',
                "suit" => "spades",
                "isFirst" => false
            ],
            [   
                "number" => '4',
                "suit" => "diams",
                "isFirst" => false
            ],
            [   
                "number" => '4',
                "suit" => "clubs",
                "isFirst" => false
            ],
            [   
                "number" => '4',
                "suit" => "spades",
                "isFirst" => false
            ],
            [   
                "number" => '5',
                "suit" => "diams",
                "isFirst" => false
            ],
            [   
                "number" => '5',
                "suit" => "clubs",
                "isFirst" => false
            ],
            [   
                "number" => '5',
                "suit" => "hearts",
                "isFirst" => false
            ],
            [   
                "number" => '5',
                "suit" => "spades",
                "isFirst" => false
            ],
            [   
                "number" => '6',
                "suit" => "clubs",
                "isFirst" => false
            ],
            [   
                "number" => '6',
                "suit" => "hearts",
                "isFirst" => false
            ],
            [   
                "number" => '6',
                "suit" => "spades",
                "isFirst" => false
            ],
            [   
                "number" => '7',
                "suit" => "diams",
                "isFirst" => false
            ],
            [   
                "number" => '7',
                "suit" => "clubs",
                "isFirst" => false
            ],
            [   
                "number" => '7',
                "suit" => "hearts",
                "isFirst" => false
            ],
            [   
                "number" => '7',
                "suit" => "spades",
                "isFirst" => false
            ],
            [   
                "number" => '8',
                "suit" => "diams",
                "isFirst" => false
            ],
            [   
                "number" => '8',
                "suit" => "clubs",
                "isFirst" => false
            ],
            [   
                "number" => '8',
                "suit" => "hearts",
                "isFirst" => false
            ],
            [   
                "number" => '9',
                "suit" => "diams",
                "isFirst" => false
            ],
            [   
                "number" => '9',
                "suit" => "clubs",
                "isFirst" => false
            ],
            [   
                "number" => '9',
                "suit" => "hearts",
                "isFirst" => false
            ],
            [   
                "number" => '9',
                "suit" => "spades",
                "isFirst" => false
            ],
            [   
                "number" => '10',
                "suit" => "diams",
                "isFirst" => false
            ],
            [   
                "number" => '10',
                "suit" => "clubs",
                "isFirst" => false
            ],
            [   
                "number" => '10',
                "suit" => "hearts",
                "isFirst" => false
            ],
            [   
                "number" => '10',
                "suit" => "spades",
                "isFirst" => false
            ],
            [   
                "number" => "j",
                "suit" => "diams",
                "isFirst" => false
            ],
            [   
                "number" => "j",
                "suit" => "clubs",
                "isFirst" => false
            ],
            [   
                "number" => "j",
                "suit" => "hearts",
                "isFirst" => false
            ],
            [   
                "number" => "j",
                "suit" => "spades",
                "isFirst" => false
            ],
            [   
                "number" => "q",
                "suit" => "diams",
                "isFirst" => false
            ],
            [   
                "number" => "q",
                "suit" => "clubs",
                "isFirst" => false
            ],
            [   
                "number" => "q",
                "suit" => "hearts",
                "isFirst" => false
            ],
            [   
                "number" => "q",
                "suit" => "spades",
                "isFirst" => false
            ],
            [   
                "number" => "k",
                "suit" => "diams",
                "isFirst" => false
            ],
            [   
                "number" => "k",
                "suit" => "clubs",
                "isFirst" => false
            ],
            [   
                "number" => "k",
                "suit" => "hearts",
                "isFirst" => false
            ],
            [   
                "number" => "k",
                "suit" => "spades",
                "isFirst" => false
            ],
            [   
                "number" => "a",
                "suit" => "diams",
                "isFirst" => false
            ],
            [   
                "number" => "a",
                "suit" => "clubs",
                "isFirst" => false
            ],
            [   
                "number" => "a",
                "suit" => "hearts",
                "isFirst" => false
            ],
            [   
                "number" => "a",
                "suit" => "spades",
                "isFirst" => false
            ]
        );
        shuffle($decker);
        return $decker;
    }

    public function deck()
    {
      $decker = array(
        [   
            "number" => 2,
            "suit" => "diams",
            "isFirst" => false
        ],
        [   
            "number" => 2,
            "suit" => "clubs",
            "isFirst" => false
        ],
        [   
            "number" => 2,
            "suit" => "hearts",
            "isFirst" => false
        ],
        [   
            "number" => 2,
            "suit" => "spades",
            "isFirst" => false
        ],
        [   
            "number" => 3,
            "suit" => "diams",
            "isFirst" => false
        ],
        [   
            "number" => 3,
            "suit" => "clubs",
            "isFirst" => false
        ],
        [   
            "number" => 3,
            "suit" => "hearts",
            "isFirst" => false
        ],
        [   
            "number" => 3,
            "suit" => "spades",
            "isFirst" => false
        ],
        [   
            "number" => 4,
            "suit" => "diams",
            "isFirst" => false
        ],
        [   
            "number" => 4,
            "suit" => "clubs",
            "isFirst" => false
        ],
        [   
            "number" => 4,
            "suit" => "spades",
            "isFirst" => false
        ],
        [   
            "number" => 5,
            "suit" => "diams",
            "isFirst" => false
        ],
        [   
            "number" => 5,
            "suit" => "clubs",
            "isFirst" => false
        ],
        [   
            "number" => 5,
            "suit" => "hearts",
            "isFirst" => false
        ],
        [   
            "number" => 5,
            "suit" => "spades",
            "isFirst" => false
        ],
        [   
            "number" => 6,
            "suit" => "clubs",
            "isFirst" => false
        ],
        [   
            "number" => 6,
            "suit" => "hearts",
            "isFirst" => false
        ],
        [   
            "number" => 6,
            "suit" => "spades",
            "isFirst" => false
        ],
        [   
            "number" => 7,
            "suit" => "diams",
            "isFirst" => false
        ],
        [   
            "number" => 7,
            "suit" => "clubs",
            "isFirst" => false
        ],
        [   
            "number" => 7,
            "suit" => "hearts",
            "isFirst" => false
        ],
        [   
            "number" => 7,
            "suit" => "spades",
            "isFirst" => false
        ],
        [   
            "number" => 8,
            "suit" => "diams",
            "isFirst" => false
        ],
        [   
            "number" => 8,
            "suit" => "clubs",
            "isFirst" => false
        ],
        [   
            "number" => 8,
            "suit" => "hearts",
            "isFirst" => false
        ],
        [   
            "number" => 9,
            "suit" => "diams",
            "isFirst" => false
        ],
        [   
            "number" => 9,
            "suit" => "clubs",
            "isFirst" => false
        ],
        [   
            "number" => 9,
            "suit" => "hearts",
            "isFirst" => false
        ],
        [   
            "number" => 9,
            "suit" => "spades",
            "isFirst" => false
        ],
        [   
            "number" => 10,
            "suit" => "diams",
            "isFirst" => false
        ],
        [   
            "number" => 10,
            "suit" => "clubs",
            "isFirst" => false
        ],
        [   
            "number" => 10,
            "suit" => "hearts",
            "isFirst" => false
        ],
        [   
            "number" => 10,
            "suit" => "spades",
            "isFirst" => false
        ],
        [   
            "number" => "j",
            "suit" => "diams",
            "isFirst" => false
        ],
        [   
            "number" => "j",
            "suit" => "clubs",
            "isFirst" => false
        ],
        [   
            "number" => "j",
            "suit" => "hearts",
            "isFirst" => false
        ],
        [   
            "number" => "j",
            "suit" => "spades",
            "isFirst" => false
        ],
        [   
            "number" => "q",
            "suit" => "diams",
            "isFirst" => false
        ],
        [   
            "number" => "q",
            "suit" => "clubs",
            "isFirst" => false
        ],
        [   
            "number" => "q",
            "suit" => "hearts",
            "isFirst" => false
        ],
        [   
            "number" => "q",
            "suit" => "spades",
            "isFirst" => false
        ],
        [   
            "number" => "k",
            "suit" => "diams",
            "isFirst" => false
        ],
        [   
            "number" => "k",
            "suit" => "clubs",
            "isFirst" => false
        ],
        [   
            "number" => "k",
            "suit" => "hearts",
            "isFirst" => false
        ],
        [   
            "number" => "k",
            "suit" => "spades",
            "isFirst" => false
        ],
        [   
            "number" => "a",
            "suit" => "diams",
            "isFirst" => false
        ],
        [   
            "number" => "a",
            "suit" => "clubs",
            "isFirst" => false
        ],
        [   
            "number" => "a",
            "suit" => "hearts",
            "isFirst" => false
        ],
        [   
            "number" => "a",
            "suit" => "spades",
            "isFirst" => false
        ]
    );
      shuffle($decker);
      return $decker;
    }

  }


