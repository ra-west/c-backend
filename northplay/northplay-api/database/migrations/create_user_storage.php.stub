<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('northplay_user_storage', function (Blueprint $table) {
            $table->id('id')->index();
            $table->string('user_id', 100);
            $table->string('storage_key', 255);
			$table->string('storage_value', 1000);
			$table->string('storage_category', 1000);
            $table->json('extra_data', 5000);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('northplay_user_storage');
    }
};