<?php
namespace Northplay\NorthplayApi\Models;
use \Illuminate\Database\Eloquent\Model as Eloquent;
class SoftswissGameModel extends Eloquent  {
    protected $table = 'northplay_softswiss_game';
    protected $timestamp = true;
    protected $primaryKey = 'id';
    protected $fillable = [
		'slug',
        'title',
        'provider',
		'demo_game',
        'source',
        'demo_game_direct',
    ];

    protected $hidden = [
        'updated_at',
    ];

    protected $casts = [
        'active' => 'boolean',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
}