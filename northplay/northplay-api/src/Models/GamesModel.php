<?php
namespace Northplay\NorthplayApi\Models;
use \Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class GamesModel extends Eloquent  {
    protected $table = 'northplay_games';
    protected $timestamp = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'game_id',
        'name',
        'type',
        'cover',
		'method_id',
		'method_type',
        'provider',
    ];
    protected $casts = [
        'enabled' => 'boolean',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
    ];
    
}
