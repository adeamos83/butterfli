<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TransformContactNameDispatcherAgent extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
			foreach(DispatcherAgent::all() as $p) {
				$contactname = $p->first_name . " " . $p->last_name;
				$p->update([ 'contact_name' => $contactname ]);
			}
			Schema::table('dispatcher_agent', function(Blueprint $table) {
				$table->dropColumn('first_name');
				$table->dropColumn('last_name');
			});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('dispatcher_agent', function(Blueprint $table)
		{
			//
		});
	}

}
