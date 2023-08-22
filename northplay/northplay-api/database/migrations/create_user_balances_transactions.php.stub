<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('northplay_user_balance_transactions', function (Blueprint $table) {
            $table->id('id')->index();
			$table->string('user_id');
            $table->string('tx_amount');
			$table->string('tx_direction');
            $table->string('tx_balance');
			$table->string('tx_old_balance');
			$table->string('tx_currency');
			$table->string('tx_desc');
			$table->json('tx_data');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('northplay_user_balance_transactions');
    }
};