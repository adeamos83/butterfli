<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateRequestAndDispatcherAssignedTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('request', function(Blueprint $table)
		{
			$table->unsignedInteger('dispatcher_assigned_id')->nullable();
		});
		
		Schema::table('request', function(Blueprint $table)
		{
			$table->foreign('dispatcher_assigned_id')->references('id')->on('dispatcher_assigned')->onDelete('cascade')->onUpdate('cascade');
			
			DB::statement('ALTER TABLE `request` MODIFY `owner_id` int(11) unsigned NULL;');			
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
