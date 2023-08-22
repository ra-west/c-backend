<?php
namespace Northplay\NorthplayApi\Models;
use \Illuminate\Database\Eloquent\Model as Eloquent;
class UserExternalAuthModel extends Eloquent  {
    protected $table = 'northplay_user_external_auth';
    protected $timestamp = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'auth_key',
        'external_id',
        'type',
    ];

    protected $hidden = [
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
}