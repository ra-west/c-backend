<?php
namespace Northplay\NorthplayApi\Models;
use \Illuminate\Database\Eloquent\Model as Eloquent;

class GameRoomsModel extends Eloquent  {
    protected $table = 'northplay_game_rooms';
    protected $timestamp = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'room',
        'state_1',
        'state_2',
        'state_3',
        'round_id',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'room_data' => 'json',
        'extra_data' => 'json',
    ];
}


