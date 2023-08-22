<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('northplay_softswiss_game', function (Blueprint $table) {
            $table->id();
            $table->string("slug")->unique();
            $table->string("title");
            $table->string("provider");
            $table->string("demo_game");
            $table->string("source");
            $table->string("demo_game_direct", 555)->nullable();
            $table->boolean("active")->default(true);
            $table->timestamps();
        });
    }
};
