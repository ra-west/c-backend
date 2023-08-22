<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('northplay_softswiss_game_tag', function (Blueprint $table) {
            $table->id();
						$table->string("game_id");
						$table->string("tag");
						$table->integer("rating");
            $table->timestamps();
        });
    }
};