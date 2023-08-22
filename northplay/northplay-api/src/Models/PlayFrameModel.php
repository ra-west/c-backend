<?php
namespace Northplay\NorthplayApi\Models;
use \Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class PlayFrameModel extends Eloquent  {
    protected $table = 'northplay_frame';
    protected $timestamp = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'room_id',
        'session_id',
    ];
    
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

}