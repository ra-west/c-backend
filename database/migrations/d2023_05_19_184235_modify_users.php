<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function ($table) {
            $table->string('metamask_address', 128)->unique()->nullable()->after('name');
            $table->integer('vip_points')->default(0)->after('name');
            $table->boolean('profile_hidden')->default(false)->after('name');
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};