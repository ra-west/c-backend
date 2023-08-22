<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {        
        
      // Schema::drop('northplay_game_rooms');

        Schema::create('northplay_game_rooms', function (Blueprint $table) {
            $table->id('id')->index();
            $table->string('room', 200)->nullable();
            $table->integer('state_1')->default(0);
            $table->integer('state_2')->default(0);
            $table->integer('state_3')->default(0);
            $table->string('round_id', 200)->unique();
            $table->json('room_data', 5000);
            $table->json('extra_data', 5000);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('northplay_game_rooms');
    }
};