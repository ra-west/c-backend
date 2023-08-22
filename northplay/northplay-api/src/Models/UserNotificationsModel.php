<?php
namespace Northplay\NorthplayApi\Models;
use \Illuminate\Database\Eloquent\Model as Eloquent;
class UserNotificationsModel extends Eloquent  {
    protected $table = 'northplay_user_notifications';
    protected $timestamp = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'title',
        'short_message',
				'long_message',
        'type',
    ];


    protected $casts = [
        'read_at' => 'datetime:Y-m-d H:i:s',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
}