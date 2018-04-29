<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFundingProfile extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */

	public function up() {
		Schema::create('funding_profile', function($tbl) {
			$tbl->increments('id');
			$tbl->unsignedInteger('enterpriseclient_id');
			$tbl->integer('funding_rule_order');
			$tbl->integer('funding_rule_type');
			$tbl->integer('payment_type');
			$tbl->float('amount');
            $tbl->timestamps();
		});

		Schema::table('funding_profile', function(Blueprint $tbl) {
			$tbl->foreign('enterpriseclient_id')->references('id')
				->on('healthcare_provider');
		});
	}
	
	public function down()
	{
		//
	}
}
