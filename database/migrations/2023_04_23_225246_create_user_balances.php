<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('northplay_user_balance', function (Blueprint $table) {
            $table->id('id')->index();
            $table->string('user_id', 300);
            $table->string('symbol_id', 15);
            $table->integer('balance')->default(0);
            $table->integer('balance_bonus')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('northplay_user_balance');
    }
};
