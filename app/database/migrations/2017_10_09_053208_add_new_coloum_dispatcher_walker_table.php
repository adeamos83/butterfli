<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewColoumDispatcherWalkerTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('dispatcher', function (Blueprint $table) {
            $table->integer('transportation_provider_id')->nullable();
        });

        Schema::table('walker', function (Blueprint $table) {
            $table->integer('transportation_provider_id')->nullable();
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::table('dispatcher', function (Blueprint $table) {
            $table->dropColumn('transportation_provider_id');
        });

        Schema::table('walker', function (Blueprint $table) {
            $table->dropColumn('transportation_provider_id');
        });
	}

}
