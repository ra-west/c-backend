<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('northplay_mp_rooms', function (Blueprint $table) {
            $table->id('id')->index();
            $table->string('room_id', 100);
            $table->string('game_id', 100);
            $table->string('owner_id', 100);
            $table->string('room_name', 100);
            $table->string('type', 100);
            $table->integer('max_players');
            $table->integer('current_players');
            $table->integer('spin_cost');
            $table->string('lastSpinAt', 200);
            $table->string('lastConnectionAt', 200);
            $table->string('defaultAccess', 200);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('northplay_mp_rooms');
    }
};
