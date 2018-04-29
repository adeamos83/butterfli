<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRideDetails extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('ride_details', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('is_scheduled')->default(0);
            $table->string('billing_code')->nullable();
            $table->integer('hospital_provider_id')->nullable();
            $table->string('agent_first_name')->nullable();
            $table->string('agent_last_name')->nullable();
            $table->integer('oxygen_mask');
            $table->string('height')->nullable();
            $table->string('weight')->nullable();
            $table->string('condition')->nullable();
            $table->integer('respirator');
            $table->string('any_tubing');
            $table->string('colostomy_bag');
            $table->string('any_attachments')->nullable();
            $table->string('agent_phone')->nullable();
            $table->integer('request_id')->default(0);
            $table->string('attendant_name')->nullable();
            $table->string('attendant_phone')->nullable();
            $table->string('attendant_pickup_address')->nullable();
            $table->string('attendant_latitude')->nullable();
            $table->string('attendant_longitude')->nullable();
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
        Schema::drop('ride_details');
	}

}
