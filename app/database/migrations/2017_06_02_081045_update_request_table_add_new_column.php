<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateRequestTableAddNewColumn extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('request', function(Blueprint $table)
		{
			$table->string('approval_text')->nullable();
			$table->string('payment_comments')->nullable();
			$table->float('additional_fee');
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
            $table->drop('approval_text');
            $table->drop('payment_comments');
            $table->drop('additional_fee');
        });

	}

}
