<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('northplay_cryptapi', function (Blueprint $table) {
            $table->id('id')->index();
            $table->string('currency', 255);
            $table->string('user_id', 255);
            $table->string('address_in', 1055);
            $table->string('address_out', 255);
            $table->string('callback_url', 255);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('northplay_cryptapi');
    }
};
