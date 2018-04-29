<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCostProfile extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('cost_profile', function($tbl) {
			$tbl->increments('id');
			$tbl->unsignedInteger('tp_id');
			$tbl->float('base_fare');
			$tbl->float('included_mileage');
			$tbl->float('per_mile');
			$tbl->unsignedInteger('service_type');
            $tbl->timestamps();
		});

		Schema::table('cost_profile', function(Blueprint $tbl) {
			$tbl->foreign('tp_id')->references('id')
				->on('transportation_providers');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::drop('cost_profile');
	}
}
