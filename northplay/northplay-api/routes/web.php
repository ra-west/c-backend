<?php
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return config('northplay-api');
});


require __DIR__.'/casino.php';
require __DIR__.'/games-gw.php';
require __DIR__.'/static-assets.php';
