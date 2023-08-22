<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('northplay_config', function (Blueprint $table) {
            $table->id('id')->index();
            $table->string('key', 255);
            $table->string('value', 1055);
            $table->string('category', 255);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('northplay_config');
    }
};
