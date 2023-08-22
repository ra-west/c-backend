<?php
namespace Northplay\NorthplayApi\Models;
use \Illuminate\Database\Eloquent\Model as Eloquent;

class UserBalanceTransactionsModel extends Eloquent  {
    protected $table = 'northplay_user_balance_transactions';
    protected $timestamp = true;
    protected $primaryKey = 'id';
    protected $fillable = [
			'user_id',
			'tx_amount',
			'tx_direction',
			'tx_balance',
			'tx_old_balance',
			'tx_currency',
			'tx_desc',
    ];

    protected $hidden = [
        'updated_at',
    ];

    protected $casts = [
		'tx_data' => 'json',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
}