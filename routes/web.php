<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|

Route::any('/northplay/sportstest/{apikey}/{path}', function ($apikey, $path, Request $request) {
    return 'disabled route';
    $allowed_apikeys = ['groupmember1key', 'groupmember2key'];
    
    if(!in_array($apikey, $allowed_apikeys)) {
        abort(403, "Specified apikey not allowed");
    }

    $cache_key = json_encode($request->query().$request->getContent());
    $cache_seconds = 3;

    $response = Cache::remember($cache_key, $cache_seconds, function ($path, $request) {
        $client = new GuzzleHttp\Client([
            // Base URI is used with relative requests, additional paths is specified in the $path 
            'base_uri' => 'https://betsapi.net/endpoint', 
            // You can set any number of default request options.
            'timeout'  => 5.0,
            'http_errors' => false, // disable guzzle exception on 4xx or 5xx response code, this is if you want to return the betsapi error to your groupmember
        ]);

        // create request according to your needs
        // add in requirements that you do not want your group members to know
        // like the original betsapi key
        // u can also add custom logic such as auth flow, caching mechanism, etc
        $resp = $client->request($request->method(), $path, [
            'headers' => array(
                'x-betsapi-sportskey' => 'INSERT REAL BETSAPIKEY',
                'content-type' => 'application/json',
            ),
            'query' => $request->query(),
            'body' => $request->getContent(),
        ]);

        // recreate response object to be passed to actual caller 
        // according to our needs.
        return response($resp->getBody()->getContents(), $resp->getStatusCode())
        ->withHeaders(filterHeaders($resp->getHeaders()));
    });

    return $response; // return the (cached) response to your group member
    
})->where('path', '.*'); // required to allow $path to catch all sub-path
*/

