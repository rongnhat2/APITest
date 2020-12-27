<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CustomerInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_info', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('customer_id');
            $table->string('avatar')->nullable();
            $table->string('date_of_birth')->nullable();
            $table->string('address')->nullable();
            $table->string('telephone')->nullable();
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
        Schema::dropIfExists('customer_info');
    }
}
