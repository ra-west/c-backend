<?php
namespace Northplay\NorthplayApi\Models;
use \Illuminate\Database\Eloquent\Model as Eloquent;
class SoftswissGameTagModel extends Eloquent  {
    protected $table = 'northplay_softswiss_game_tag';
    protected $timestamp = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'game_id',
        'tag',
        'rating',
    ];

}