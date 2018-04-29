<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ConvertContactName extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		foreach(HealthCareProvider::all() as $client) {
			Log::info($client);
			$contactname = $client->first_name . " " . $client->last_name;
			$client->update([ 'contact_name' => $contactname ]);
		}

		Schema::table('healthcare_provider', function(Blueprint $tbl) {
			$tbl->dropColumn('first_name');
			$tbl->dropColumn('last_name');
			$tbl->rename('enterprise_client');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
	}

}
