<?php
namespace Northplay\NorthplayApi\Models;

use \Illuminate\Database\Eloquent\Model as Eloquent;

class GatewayEntrySessions extends Eloquent  {
    protected $table = 'northplay_gateway_entry_sessions';
    protected $timestamp = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'entry_token',
        'entry_confirmation',
        'session_url',
        'session_id',
        'user_id',
        'api_mode',
        'game_id',
        'state',
    ];

    protected $casts = [
        'active' => 'boolean',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
}