<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
 {
    // Schema::drop('northplay_gamebuffer');
        Schema::create('northplay_gamebuffer', function (Blueprint $table) {
            $table->id('id')->index();
            $table->string('user_id', 255);
            $table->string('user_name', 255);
            $table->string('game_id', 255);
            $table->string('game_slug', 255);
            $table->string('play_currency', 255);
            $table->string('debit_currency', 255);
            $table->string('session_id', 255);
            $table->string('round_id', 255);
            $table->string('internal_id', 255);
            $table->boolean('finished', 100);
            $table->boolean('broadcasted', 100);
            $table->boolean('bonus_eligible', 255);
			$table->string('win', 1000);
			$table->string('lose', 1000);
            $table->json('game_data', 5000);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('northplay_gamebuffer');
    }
};