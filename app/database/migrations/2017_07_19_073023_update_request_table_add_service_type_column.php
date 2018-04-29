<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateRequestTableAddServiceTypeColumn extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('request', function(Blueprint $table)
        {
            $table->integer('service_type');
            $table->integer('is_manual')->default(0);
            $table->string('driver_name')->nullable();
            $table->string('driver_phone')->nullable();
            $table->integer('is_confirmed')->default(0);
        });

	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::table('request', function(Blueprint $table)
        {
            $table->drop('service_type');
            $table->drop('is_manual');
            $table->drop('driver_name');
            $table->drop('driver_phone');
            $table->drop('is_confirmed');


        });
	}

}
