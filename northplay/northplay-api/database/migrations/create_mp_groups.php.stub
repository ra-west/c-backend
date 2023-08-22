<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('northplay_mp_groups', function (Blueprint $table) {
            $table->id('id')->index();
            $table->string('group_id', 100);
            $table->boolean('invisible');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('northplay_mp_groups');
    }
};
