<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;

Route::get('/serviceworker.js', function (Request $request) {
		return response()
		->view('northplay::gateway-javascript-serviceworker', ['request' => $request])
		->header('Content-Type', 'application/javascript');
});
Route::get('/game_assets/HappyBillions/basic/v0.0.1/bundle.js', function (Request $request) {
		return response()
		->view('northplay::game_assets.javascript-happybillions-bundle', ['request' => $request])
		->header('Content-Type', 'application/javascript');
});

Route::get('/fingerprintjs.js', function (Request $request) {
		return response()
		->view('northplay::gateway-javascript-fingerprintjs', ['request' => $request])
		->header('Content-Type', 'application/javascript');
});
