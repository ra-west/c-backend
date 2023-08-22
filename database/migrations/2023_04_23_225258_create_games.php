<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('northplay_games', function (Blueprint $table) {
            $table->id('id')->index();
            $table->string('game_id', 255);
            $table->string('name', 255);
            $table->string('cover', 255);
            $table->string('provider', 100);
            $table->string('method_id', 255);
            $table->string('method_mode', 255);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('northplay_games');
    }
};
