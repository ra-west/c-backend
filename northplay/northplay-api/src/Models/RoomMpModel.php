<?php
namespace Northplay\NorthplayApi\Models;
use \Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class RoomMpModel extends Eloquent  {
    protected $table = 'northplay_mp_rooms';
    protected $timestamp = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'room_id',
        'type',
        'max_players',
        'current_players',
        'spin_cost',
        'defaultAccess',
    ];
    protected $casts = [
        'lastConnectionAt' => 'datetime',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
}