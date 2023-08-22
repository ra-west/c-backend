<?php
namespace Northplay\NorthplayApi\Controllers\Multiplayer;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PlayFrameController
{
    public function __construct() 
    {
        $this->model = new \Northplay\NorthplayApi\Models\RoomMpModel;
        $this->games = new \Northplay\NorthplayApi\Models\GamesModel;
        $this->dog = new \Northplay\NorthplayApi\Controllers\DogCallbackController;
    }

    public function init($type, $room_id)
    {
        $select_room = $this->model->where('room_id', $room_id)->first();

        if(!$select_room) {
            abort(404, 'Room not found');
        }

        return view('northplay::oppa')->with('url', $this->dog->create_session($select_room->game_id, 'player', 'USD'));

    }


}