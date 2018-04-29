<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewHealthcareRequestTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('healthcare_request', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('owner_id')->nullable();
            $table->integer('status');
            $table->dateTime('request_start_time');
            $table->timestamps();
            $table->integer('is_walker_started');
            $table->integer('is_walker_arrived');
            $table->integer('is_started');
            $table->integer('is_completed');
            $table->integer('is_cancelled');
            $table->integer('is_dog_rated');
            $table->integer('is_walker_rated');
            $table->integer('is_scheduled')->default(0);
            $table->integer('is_confirmed')->default(0);
            $table->float('distance');
            $table->float('time');
            $table->float('total')->default(0);
            $table->float('refund')->default(0);
            $table->string('transfer_amount')->default(0);
            $table->integer('is_paid');
            $table->float('card_payment');
            $table->float('ledger_payment');
            $table->integer('service_type');
            $table->boolean('later')->default(0);
            $table->double('latitude', 15, 8);
            $table->double('longitude', 15, 8);
            $table->double('D_latitude', 15, 8)->default(0);
            $table->double('D_longitude', 15, 8)->default(0);
            $table->integer('payment_mode')->default(0);
            $table->integer('payment_id')->nullable();
            $table->float('promo_payment', 8, 2)->default(0);
            $table->string('promo_code');
            $table->integer('promo_id')->default(0);
            $table->string('time_zone')->default('UTC');
            $table->string('src_address')->default('Address Not Available');
            $table->string('dest_address')->default('Address Not Available');
            $table->double('payment_remaining', 15, 2)->default(0);
            $table->double('refund_remaining', 15, 2)->default(0);
            $table->string('req_create_user_time');
            $table->string('cancel_reason');
            $table->unsignedInteger('healthcare_id')->nullable();
            $table->unsignedInteger('dispatcher_assigned_id')->nullable();
            $table->string('comments')->nullable();
            $table->string('approval_text')->nullable();
            $table->string('payment_comments')->nullable();
            $table->float('additional_fee');
            $table->string('special_request')->nullable();
            $table->string('payment_provider')->nullable();
            $table->string('billing_code')->nullable();
            $table->string('driver_name')->nullable();
            $table->string('driver_phone')->nullable();
        });

        Schema::table('healthcare_request', function(Blueprint $table)
        {
            $table->foreign('healthcare_id')->references('id')->on('healthcare_provider')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('dispatcher_assigned_id')->references('id')->on('dispatcher_assigned')->onDelete('cascade')->onUpdate('cascade');
        });
    }

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::drop('healthcare_request');
	}

}
