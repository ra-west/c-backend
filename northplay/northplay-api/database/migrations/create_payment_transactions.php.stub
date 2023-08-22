<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('northplay_payment_transactions', function (Blueprint $table) {
            $table->id('id')->index();
            $table->string('tx_id', 255);
            $table->string('type', 255);
            $table->string('user_id', 255);
            $table->string('pending', 1055);
            $table->string('currency', 1055);
            $table->string('amount', 255);
            $table->json('data', 5000);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('northplay_payment_transactions');
    }
};