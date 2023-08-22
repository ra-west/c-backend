<?php
namespace Northplay\NorthplayApi\Models;
use \Illuminate\Database\Eloquent\Model as Eloquent;
class UserStorageModel extends Eloquent  {
    protected $table = 'northplay_user_storage';
    protected $timestamp = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'storage_key',
        'storage_value',
		'storage_category',
    ];


    protected $casts = [
        'extra_data' => 'json',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
}