<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('northplay_currency', function (Blueprint $table) {
            $table->id('id')->index();
            $table->string('symbol_id', 255);
            $table->string('name', 1055);
            $table->string('decimals', 255);
            $table->string('type', 255);
            $table->string('rate_usd', 255);
            $table->string('rate_updated', 255);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('northplay_currency');
    }
};
