<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Setting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('setting', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('customer_id');
            $table->integer('like_comment');
            $table->integer('from_friends');
            $table->integer('requested_friend');
            $table->integer('suggested_friend');
            $table->integer('birthday');
            $table->integer('video');
            $table->integer('report');
            $table->integer('sound_on');
            $table->integer('notification_on');
            $table->integer('vibrant_on');
            $table->integer('led_on');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('setting');
    }
}
