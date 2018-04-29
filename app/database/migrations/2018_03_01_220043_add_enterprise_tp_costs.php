<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEnterpriseTpCosts extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('rate_profile', function(Blueprint $table)
		{
			$table->float('tpcost_base_fare');
			$table->float('tpcost_per_mile');
			$table->float('tpcost_deadhead_per_mile');
			$table->float('tpcost_wait_time_per_minute');
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
