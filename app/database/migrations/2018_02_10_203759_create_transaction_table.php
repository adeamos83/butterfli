<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('transaction', function($tbl) {
			$tbl->increments('id');
			$tbl->string('reference_table');
			$tbl->unsignedInteger('reference_id');
			$tbl->unsignedInteger('enterpriseclient_id');
			$tbl->unsignedInteger('tp_id');
			$tbl->text('description');
			$tbl->float('amount');
			$tbl->float('qb_account_number');
            $tbl->timestamps();
		});

		Schema::table('transaction', function(Blueprint $tbl) {
			$tbl->foreign('enterpriseclient_id')->references('id')
				->on('healthcare_provider');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::drop('transaction');
	}
}
