<?php
namespace Northplay\NorthplayApi\Models;
use \Illuminate\Database\Eloquent\Model as Eloquent;

class CurrencyModel extends Eloquent  {
    protected $table = 'northplay_currency';
    protected $timestamp = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'symbol_id',
        'name',
        'decimals',
        'rate_value',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'id',
    ];

    protected $casts = [
        'rate_updated' => 'datetime:Y-m-d H:i:s',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
}