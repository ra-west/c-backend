<?php
namespace Northplay\NorthplayApi\Models;

use \Illuminate\Database\Eloquent\Model as Eloquent;

class GatewayParentSessions extends Eloquent  {
    protected $table = 'northplay_gateway_parent_sessions';
    protected $timestamp = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'session_url',
        'session_id',
        'user_private_id',
        'user_public_id',
        'api_mode',
        'game_id',
        'game_session',
        'currency',
				'state',
    ];

    protected $casts = [
			  'storage' => 'json',
        'active' => 'boolean',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
}