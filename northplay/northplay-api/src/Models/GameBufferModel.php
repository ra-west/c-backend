<?php
namespace Northplay\NorthplayApi\Models;
use \Illuminate\Database\Eloquent\Model as Eloquent;

class GameBufferModel extends Eloquent  {
    protected $table = 'northplay_gamebuffer';
    protected $timestamp = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'user_name',
        'game_slug',
        'game_id',
        'finished',
        'broadcasted',
        'round_id',
        'internal_id',
        'win',
        'lose',
        'bonus_eligible',
    ];

    protected $casts = [
        'bonus_eligible' => 'boolean',
        'finished' => 'boolean',
        'broadcasted' => 'boolean',
        'game_data' => 'json',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
}
