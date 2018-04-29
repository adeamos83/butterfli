<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEnterpriseFees extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('rate_profile', function(Blueprint $table) {
			$table->float('deadhead_included_mileage');
			$table->float('deadhead_per_mile');
			$table->float('wait_time_included');
			$table->float('wait_time_per_minute');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('rate_profile', function(Blueprint $table)
		{
			//
		});
	}

}
