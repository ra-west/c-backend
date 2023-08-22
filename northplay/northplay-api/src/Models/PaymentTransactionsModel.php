<?php
namespace Northplay\NorthplayApi\Models;
use \Illuminate\Database\Eloquent\Model as Eloquent;

class PaymentTransactionsModel extends Eloquent  {
    protected $table = 'northplay_payment_transactions';
    protected $timestamp = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'tx_id',
        'type',
        'user_id',
        'pending',
        'currency',
        'amount',
    ];

    protected $casts = [
        'data' => 'json',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

}