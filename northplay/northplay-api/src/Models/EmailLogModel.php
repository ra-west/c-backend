<?php
namespace Northplay\NorthplayApi\Models;
use \Illuminate\Database\Eloquent\Model as Eloquent;

class EmailLogModel extends Eloquent  {
    protected $table = 'northplay_email_log';
    protected $timestamp = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'subject',
        'plain_body',
        'from',
        'to',
        'date',
        'direction',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
}