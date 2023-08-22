<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('northplay_user_notifications', function (Blueprint $table) {
            $table->id('id')->index();
            $table->string('user_id', 300);
            $table->string('title', 300);
            $table->string('short_message', 300);
            $table->string('long_message', 2000)->nullable();
            $table->string('type', 255);
            $table->string('action', 255);
            $table->date('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('northplay_user_notifications');
    }
};
