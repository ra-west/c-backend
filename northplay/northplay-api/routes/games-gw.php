<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;


/* parent & entry session related */
Route::get('/game-gateway/entry', [Northplay\NorthplayApi\Controllers\Integrations\EntryController::class, 'show'])
    ->middleware(['web'])
    ->name('entry_show');

Route::get('/entry/queue_check', [Northplay\NorthplayApi\Controllers\Integrations\EntryController::class, 'queue_check'])
    ->middleware(['web'])
    ->name('queue_check');

Route::get('/gw/session-error', [Northplay\NorthplayApi\Controllers\Integrations\EntryController::class, 'show_session_error'])
    ->middleware(['web'])
    ->name('show_session_error');

/* bgaming game routes */
Route::get('/gw/bgaming/game_event/{session_id}', [Northplay\NorthplayApi\Controllers\Integrations\Games\Bgaming\BgamingKernel::class, 'game_event'])
    ->name('game_event.bgaming.get');
Route::get('/gw/ryangames/game_event/{session_id}', [Northplay\NorthplayApi\Controllers\Integrations\Games\Bgaming\BgamingKernel::class, 'game_event'])
    ->name('game_event.ryangames.get');
Route::post('/gw/ryangames/game_event/{session_id}', [Northplay\NorthplayApi\Controllers\Integrations\Games\Bgaming\BgamingKernel::class, 'game_event'])
    ->name('game_event.ryangames.post');

Route::post('/gw/bgaming/game_event/{session_id}', [Northplay\NorthplayApi\Controllers\Integrations\Games\Bgaming\BgamingKernel::class, 'game_event'])
    ->name('game_event.bgaming.post');

Route::get('/play/bgaming/{session_id}', [Northplay\NorthplayApi\Controllers\Integrations\Games\Bgaming\BgamingKernel::class, 'show'])
    ->middleware(['api'])
    ->name('view_blade_bgaming');
    
/* gapi game routes */
Route::get('/play/gapi/{session_id}', [Northplay\NorthplayApi\Controllers\Integrations\Games\Amatic\AmaticKernel::class, 'show'])
    ->middleware(['api'])
    ->name('view_blade_gapi');


Route::get('/play/ryangames/{session_id}', [Northplay\NorthplayApi\Controllers\Integrations\Games\Bgaming\BgamingKernel::class, 'show'])
    ->middleware(['api'])
    ->name('view_blade_ryangames');


Route::get('/demo', function (Request $request) {
    $get = Http::get(('https://api.casino.east.ovh/casino/auth/start-game?mode=demo&preloader_theme=darkblue&currency=USD&debit_currency=LTC&slug=ryangames/HappyBillions'));

    return redirect($get['session_url']);
});