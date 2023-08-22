<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('northplay_user_external_auth', function (Blueprint $table) {
            $table->id('id')->index();
            $table->string('user_id', 255);
            $table->string('auth_key', 255);
            $table->string('external_id', 255)->nullable();
            $table->string('type', 255);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('northplay_user_external_auth');
    }
};
