<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewColumnPhoneDispatcherTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('dispatcher', function(Blueprint $table)
        {
            $table->string('phone')->nullable();
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::table('dispatcher', function(Blueprint $table)
        {
            $table->dropColumn('phone');
        });
	}

}
