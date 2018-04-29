<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TransformContactNameConsumer extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
			foreach(Consumer::all() as $p) {
				$contactname = $p->first_name . " " . $p->last_name;
				$p->update([ 'contact_name' => $contactname ]);
			}
			Schema::table('consumer', function(Blueprint $table) {
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
		Schema::table('consumer', function(Blueprint $table)
		{
			//
		});
	}

}
