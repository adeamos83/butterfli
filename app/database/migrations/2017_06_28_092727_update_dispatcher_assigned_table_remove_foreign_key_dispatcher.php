<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateDispatcherAssignedTableRemoveForeignKeyDispatcher extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('dispatcher_assigned', function(Blueprint $table) {
            $table->dropForeign('dispatcher_assigned_dispatcher_id_foreign');
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
