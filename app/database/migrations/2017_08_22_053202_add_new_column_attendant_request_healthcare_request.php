<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewColumnAttendantRequestHealthcareRequest extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('request', function(Blueprint $table)
        {
            $table->integer('attendant_travelling')->default(0);
        });

        Schema::table('healthcare_request', function(Blueprint $table)
        {
            $table->integer('attendant_travelling')->default(0);
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
            $table->dropColumn('attendant_travelling');
        });

        Schema::table('healthcare_request', function(Blueprint $table)
        {
            $table->dropColumn('attendant_travelling');
        });
	}

}
