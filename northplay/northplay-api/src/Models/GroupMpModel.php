<?php
namespace Northplay\NorthplayApi\Models;
use \Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class GroupMpModel extends Eloquent  {
    protected $table = 'northplay_mp_groups';
    protected $timestamp = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'group_id',
    ];
    protected $casts = [
        'invisible' => 'boolean',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
}
