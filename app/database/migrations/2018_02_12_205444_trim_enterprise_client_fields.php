<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TrimEnterpriseClientFields extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('enterprise_client', function(Blueprint $table)
		{
			$table->dropColumn('city');
			$table->dropColumn('state');
			$table->dropColumn('country');
			$table->dropColumn('postalcode');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		//
	}

}
