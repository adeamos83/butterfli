<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateDispatcherAssignedTableChangeFields extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('dispatcher_assigned', function(Blueprint $table)
		{
			DB::statement('ALTER TABLE `dispatcher_assigned` MODIFY `last_name` varchar(255) NULL;');
			DB::statement('ALTER TABLE `dispatcher_assigned` MODIFY `email` varchar(255) NULL;');
			$table->dropColumn('pickupdate');
            $table->dropColumn('pickuptime');
			
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
