<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateRequestHealthcareRequestsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('request', function(Blueprint $table)
        {
            $table->float('cancellation_fee');
            $table->float('estimated_time');
        });

        Schema::table('healthcare_request', function(Blueprint $table)
        {
            $table->float('cancellation_fee');
            $table->float('estimated_time');
        });

        Schema::table('healthcare_provider', function(Blueprint $table)
        {
            $table->float('account_balance');
            $table->float('total_amount');
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
