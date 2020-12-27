<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Newsfeed extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_newsfeed', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('customer_id');
            $table->integer('prev_load_id');
            $table->integer('prev_id');
            $table->integer('prev_index_id');
            $table->integer('index_id');
            $table->integer('next_id');
            $table->integer('next_load_id');
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
        Schema::dropIfExists('customer_newsfeed');
    }
}
