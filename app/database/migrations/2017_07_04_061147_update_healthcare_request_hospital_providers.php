<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateHealthcareRequestHospitalProviders extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('hospital_providers', function (Blueprint $table)
        {
            $table->increments('id');
            $table->integer('healthcare_id');
            $table->string('provider_name');
            $table->integer('is_active')->default(0);
            $table->timestamps();
        });

        Schema::table('healthcare_request', function(Blueprint $table)
        {
            $table->integer('hospital_provider_id')->nullable();
            $table->integer('is_wheelchair_request')->default(0);
        });

        Schema::table('request', function(Blueprint $table)
        {
            $table->integer('is_wheelchair_request')->default(0);
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
