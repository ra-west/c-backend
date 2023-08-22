<?php
namespace Northplay\NorthplayApi\Models;
use \Illuminate\Database\Eloquent\Model as Eloquent;

class GameResultsModel extends Eloquent  {
    protected $table = 'northplay_games_results';
    protected $timestamp = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'game_id',
        'room_id',
        'expired',
        'net_result',
    ];

    protected $casts = [
        'expired' => 'boolean',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'data' => 'json',
    ];

}