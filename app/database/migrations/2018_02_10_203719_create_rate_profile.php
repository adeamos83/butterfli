<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRateProfile extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('rate_profile', function($tbl) {
			$tbl->increments('id');
			$tbl->unsignedInteger('enterpriseclient_id');
			$tbl->float('base_fare');
			$tbl->float('included_mileage');
			$tbl->float('per_mile');
			$tbl->unsignedInteger('service_type');
            $tbl->timestamps();
		});

		Schema::table('rate_profile', function(Blueprint $tbl) {
			$tbl->foreign('enterpriseclient_id')->references('id')
				->on('healthcare_provider');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::drop('rate_profile');
	}
}
