<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        //Schema::drop('northplay_gateway_parent_sessions');

        Schema::create('northplay_gateway_parent_sessions', function (Blueprint $table) {
            $table->id();
            $table->string("session_url")->nullable();
            $table->string("session_id");
            $table->string("user_private_id");
						$table->string("user_public_id")->nullable();
            $table->string("api_mode")->nullable();
            $table->string("game_id");
						$table->json("storage");
						$table->string("game_session")->nullable();
            $table->string("currency")->default("USD");
            $table->string("debit_currency")->default("USD");
            $table->string("state")->nullable();
            $table->boolean("active")->default(true);
            $table->timestamps();
        });
    }
};