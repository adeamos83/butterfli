<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateRequestTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		//
        Schema::table('request', function (Blueprint $table) {
            $table->unsignedInteger('healthcare_id')->nullable();
        });
        Schema::table('request', function(Blueprint $table)
        {
            $table->foreign('healthcare_id')->references('id')->on('healthcare_provider');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::table('request', function (Blueprint $table) {
            $table->drop('healthcare_id');
        });

	}

}
