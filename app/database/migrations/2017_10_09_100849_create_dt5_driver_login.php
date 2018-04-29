<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDt5DriverLogin extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
    public function up()
    {
        Schema::create('dt5_driver_login', function(Blueprint $table) {
            $table->increments('id');
            $table->string('vehicle_guid');
            $table->string('driver_guid');
            $table->double('epoch_time');
            $table->string('contact_phone_number');
            $table->string('token');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });
    }

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
    public function down()
    {
        Schema::drop('dt5_driver_login');
    }

}
