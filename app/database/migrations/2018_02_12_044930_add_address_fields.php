<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAddressFields extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('enterprise_client', function(Blueprint $table)
		{
			$table->text('address');
			$table->string('city');
			$table->string('state');
			$table->string('country');
			$table->string('postalcode');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('enterprise_client', function(Blueprint $table)
		{
			//
		});
	}

}
