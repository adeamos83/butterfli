<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ConvertDispatcherAssignedNames extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('dispatcher_assigned', function(Blueprint $table) {
			$table->text('contact_name');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('dispatcher_assigned', function(Blueprint $table)
		{
			//
		});
	}

}
