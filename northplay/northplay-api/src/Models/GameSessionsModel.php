<?php
namespace Northplay\NorthplayApi\Models;
use \Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class GameSessionsModel extends Eloquent  {
    protected $table = 'northplay_sessions';
    protected $timestamp = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'token_internal',
        'player_id',
        'player_operator_id',
        'game_id',
        'game_provider',
        'currency',
        'state',
        'operator_id',
        'token_original',
        'token_original_bridge',
        'expired_bool',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'extra_meta' => 'json',
        'user_agent' => 'json'
    ];

}