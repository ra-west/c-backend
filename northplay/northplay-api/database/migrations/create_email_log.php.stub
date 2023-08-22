<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('c', function (Blueprint $table) {
            $table->id('id')->index();
            $table->string('subject', 255);
            $table->string('plain_body', 5055);
            $table->string('from', 255);
            $table->string('to', 255);
            $table->string('date', 255);
            $table->string('direction', 255);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('northplay_email_log');
    }
};
