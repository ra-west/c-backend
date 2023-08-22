<?php
namespace Northplay\NorthplayApi\Controllers\Multiplayer;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class RoomController
{
    public function __construct() {
        $this->model = new \Northplay\NorthplayApi\Models\RoomMpModel;
        $this->games = new \Northplay\NorthplayApi\Models\GamesModel;
        $this->dog = new \Northplay\NorthplayApi\Controllers\DogCallbackController;
        if($this->games->count() < 2) {
          $this->insert_games();
        }

        if($this->model->count() < 2) {
          $this->insert_default();
          $this->insert_default();
          $this->insert_default();
          $this->insert_default();
          $this->insert_default();
          $this->insert_default();
      }
    }

    public function game_img($game_id) {
      try {
        return explode(":", $game_id)[1];
      } catch(\Exception $e) {
        return $game_id;
      }
     }



    public function authorize($room_id)
    {
        $data = $this->model->where('room_id', $room_id)->first();

        return [
            'token' => "12345"
          ];
    }
    
    public function insert_games()
    {
        $list = json_decode($this->dog->get_list(), true);

        foreach($list as $game) {
            $games = $this->games->insert([
              'game_id' => $game['slug'],
              'name' => $game['name'],
              'provider' => $game['provider'],
          ]);
        }
    }

    public function active_users($room_id)
    {
        return [
            'data' => [
            ],
        ];
    }



    public function select_room($room_id)
    {
        $data = $this->model->where('room_id', $room_id)->first();

        return [
            'type' => 'room',
            'id' => $data->room_id,
            'lastConnectionAt' => '2022-08-04T21:07:09.380Z',
            'createdAt' => '2022-07-13T14:32:50.697Z',
            'metadata' => [
              'color' => 'blue',
              'name' => 'oppa',
              'size' => '10',
              'owner' => 'mislav.abha@example.com',
              'target' => [
                0 => 'abc',
                1 => 'def',
              ],
            ],
            'defaultAccesses' => [
              0 => 'room:read',
            ],
            'groupsAccesses' => [
              'engineering' => [
                0 => 'room:write',
              ],
            ],
            'usersAccesses' => [
              'mislav.abha@example.com' => [
                0 => 'room:write',
              ],
            ],
          ];
    }


    public function insert_default() {
      $random_name = array('Super Bets', 'Big Daddy Lobby', 'No bet, no cry', 'Insane Oppa!', 'Irish Clover Lovers');
      $types = array('official', 'community', 'jackpot');
      $spincosts = array(50, 100, 200, 300);
      $room_sizes = array(5, 10, 25, 50);


      save_log('RandomGames', $this->games->get()->random(4)->first()->game_id);
      $this->model->insert([
        'room_id' => Str::random(64),
        'room_name' => $random_name[rand(0,(sizeof($random_name, 1) - 1))],
        'type' => $types[rand(0,(sizeof($types, 1) - 1))],
        'game_id' => $this->games->get()->random(2)->first()->game_id,
        'owner_id' => 'mislav.abha@example.com',
        'max_players' => $room_sizes[rand(0,(sizeof($room_sizes, 1) - 1))],
        'current_players' => 0,
        'spin_cost' => $spincosts[rand(0,(sizeof($spincosts, 1) - 1))],
        'defaultAccess' => 'room:read',
        'lastSpinAt' => Carbon::now()->subMinutes(random_int(0, 500)),
        'lastConnectionAt' => Carbon::now()->subMinutes(random_int(0, 12)),
        'created_at' => Carbon::now()->subMinutes(random_int(0, 55)),
        'updated_at' => Carbon::now()->subMinutes(random_int(0, 55)),
      ]);

    }

    public function list() {
        return $this->response_format();
    }

    public function response_format() {
      
        $data = $this->model->all();
        foreach($data as $room) {
            $array[] = array(
                'type' => 'room',
                'id' => $room['room_id'],
                'lastConnectionAt' =>  $room['lastConnectionAt'],
                'createdAt' => $room['created_at'],
                'metadata' => [
                  'name' => [
                    0 => $room['room_name'],
                  ],
                  'game_name' => [
                    0 => $this->games->where('game_id', $room['game_id'])->first()['name'] ?? 'a gambling game',
                  ],
                  'max_players' => [
                    0 => $room['max_players'],
                  ],
                  'current_players' => [
                    0 => $room['current_players'],
                  ],
                  'game_img' => [
                    0 => explode(':', strtolower($this->games->where('game_id', $room['game_id'])->first()['game_id']))[1].'.png' ?? 'any:default_image',
                  ],
                  'game_id' => [
                    0 => $room['game_id'],
                  ],
                  'type' => [
                    0 => $room['type'],
                  ],
                ],
                'defaultAccesses' => [
                  0 => 'room:write',
                ],
                'groupsAccesses' => [
                  'player' => [
                    0 => 'room:write',
                  ],
                ],
                'usersAccesses' => [
                  'mislav.abha@example.com' => [
                    0 => 'room:write',
                  ],
                ],
            );
            
        }

        return [
            'nextPage' => '/v2/rooms',
            'data' => $array,
        ];

    }

}