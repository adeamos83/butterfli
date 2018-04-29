<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RideDetailsTransformContactName extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('ride_details', function(Blueprint $table)
		{
			foreach(RideDetails::all() as $p) {
				$contactname = $p->first_name . " " . $p->last_name;
				$p->update([ 'agent_contact_name' => $contactname ]);
			}
			Schema::table('ride_details', function(Blueprint $table) {
				$table->dropColumn('agent_first_name');
				$table->dropColumn('agent_last_name');
			});
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('ride_details', function(Blueprint $table)
		{
			//
		});
	}

}
