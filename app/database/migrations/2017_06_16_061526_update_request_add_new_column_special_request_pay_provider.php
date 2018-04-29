<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateRequestAddNewColumnSpecialRequestPayProvider extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('request', function(Blueprint $table)
		{
			$table->string('special_request')->nullable();
			$table->string('payment_provider')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::table('request', function(Blueprint $table)
        {
            $table->drop('special_request');
            $table->drop('payment_provider');
        });

		
	}

}
