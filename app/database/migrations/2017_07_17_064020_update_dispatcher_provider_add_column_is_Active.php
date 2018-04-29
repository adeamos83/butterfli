<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateDispatcherProviderAddColumnIsActive extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
    public function up()
    {
        Schema::table('healthcare_provider', function(Blueprint $table)
        {
            $table->Integer('is_active')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('healthcare_provider', function(Blueprint $table)
        {
            $table->drop('is_active');
        });
    }

}
