<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatePaymentTableAddNewField extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('payment', function(Blueprint $table)
		{
			DB::statement('ALTER TABLE `payment` MODIFY `owner_id` int(10) unsigned NULL;');
			DB::statement('ALTER TABLE `payment` MODIFY `customer_id` varchar(255) NULL;');
			$table->unsignedInteger('dispatcher_assigned_id')->nullable();
			
		});
		
		Schema::table('payment', function(Blueprint $table)
		{
			$table->foreign('dispatcher_assigned_id')->references('id')->on('dispatcher_assigned')->onDelete('cascade')->onUpdate('cascade');
			
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
