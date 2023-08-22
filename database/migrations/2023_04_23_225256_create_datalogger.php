<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('northplay_logs', function (Blueprint $table) {
            $table->id('id')->index();
            $table->string('uuid', 100);
            $table->string('type', 100);
            $table->json('data', 5000);
            $table->json('extra_data', 5000);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('wainwright_datalogger');
    }
};
