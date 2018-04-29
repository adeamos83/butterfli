<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateDispatchersTableAddDefaultValues extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('dispatcher', function(Blueprint $table)
		{
			//DB::statement("ALTER TABLE `dispatcher` MODIFY COLUMN `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;");
			//DB::statement("ALTER TABLE `dispatcher` MODIFY COLUMN `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;");
		});
		
		Schema::table('dispatcher_assigned', function(Blueprint $table)
		{
			//DB::statement("ALTER TABLE `dispatcher_assigned` MODIFY COLUMN `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;");
			//DB::statement("ALTER TABLE `dispatcher_assigned` MODIFY COLUMN `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;");
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
