<?php

Route::get('/northplay/ama', function (Request $request) {
    $games = Http::get("https://admin.gapi.lol/api/games/all");
    $decode_games = json_decode($games, true)['data'];
    $i = 0;
    foreach($decode_games as $game) {
        if($game['category'] === "amatic") {
                    echo '<img src="https://cdn2.softswiss.net/i/s3/amatic/'.$game['menu_title'].'.png" />';
        }
        

    }
});


Route::get('/northplay/oppadoppa', function (Request $request) {
    $games = Http::get("https://admin.gapi.lol/api/games/all");
    $decode_games = json_decode($games, true)['data'];
    $i = 0;
    foreach($decode_games as $game) {
        if($game['category'] === "amatic") {
            if($i > 100) {
                if($i < 125) {
                    echo '<img src="https://vps-2ca314d6.vps.ovh.net/northplay/oppadoppa/'.$game['menu_title'].'.png" />';
                }
            }
            $i++;

        }
        

    }
});

Route::get('/northplay/oppadoppa/{game_title}', function ($game_title, Request $request) {
    $games = Http::get("https://admin.gapi.lol/api/games/all");
    $game_title = str_replace(".png", "", $game_title);
    $decode_games = json_decode($games, true)['data'];
    foreach($decode_games as $game) {
        if($game['menu_title'] === $game_title) {
                $image = Http::get($game['image']);
                header("Content-type: image/png");
                echo $image;
        }
    }
});

Route::get('/northplay/ama', function (Request $request) {
    $games = Http::get("https://admin.gapi.lol/api/games/all");
    $decode_games = json_decode($games, true)['data'];
    $i = 0;
    foreach($decode_games as $game) {
        if($game['category'] === "amatic") {
             echo '<img src="https://cdn2.softswiss.net/i/s3/amatic/'.$game['menu_title'].'.png" />';
        }
        

    }
});