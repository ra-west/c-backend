<?php
namespace Northplay\NorthplayApi\Models;
use \Illuminate\Database\Eloquent\Model as Eloquent;

class CryptapiModel extends Eloquent  {
    protected $table = 'northplay_cryptapi';
    protected $timestamp = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'currency',
        'user_id',
        'address_in',
        'address_out',
        'callback_url',
    ];

    protected $casts = [
        'active' => 'boolean',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

}