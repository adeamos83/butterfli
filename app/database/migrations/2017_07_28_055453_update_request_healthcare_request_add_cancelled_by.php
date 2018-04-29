<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateRequestHealthcareRequestAddCancelledBy extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
    public function up()
    {
        Schema::table('request', function(Blueprint $table)
        {
            $table->string('cancelled_by');
        });

        Schema::table('healthcare_request', function(Blueprint $table)
        {
            $table->string('cancelled_by');
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
            $table->drop('cancelled_by');
        });

        Schema::table('healthcare_request', function(Blueprint $table)
        {
            $table->drop('cancelled_by');
        });
	}

}
