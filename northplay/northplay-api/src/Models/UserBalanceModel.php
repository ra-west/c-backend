<?php
namespace Northplay\NorthplayApi\Models;
use \Illuminate\Database\Eloquent\Model as Eloquent;

class UserBalanceModel extends Eloquent  {
    protected $table = 'northplay_user_balance';
    protected $timestamp = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'symbol_id',
        'balance',
        'balance_bonus',
    ];

    protected $hidden = [
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
}