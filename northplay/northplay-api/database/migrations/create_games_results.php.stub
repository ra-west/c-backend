<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('northplay_games_results', function (Blueprint $table) {
            $table->id('id')->index();
            $table->string('game_id', 255);
            $table->string('room_id', 255);
            $table->boolean('expired', 100);
            $table->string('net_result', 255);
            $table->json('data', 5000);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('northplay_games_results');
    }
};