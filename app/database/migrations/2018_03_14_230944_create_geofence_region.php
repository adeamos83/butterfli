<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGeofenceRegion extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('geofence', function($t) {
            $t->increments('id');
            $t->unsignedInteger('enterpriseclient_id');
            $t->string('promocode');
            $t->string('description');
            $t->text('metadata_storage');
        });

        Schema::table('geofence', function(Blueprint $t)
        {
            $t->foreign('enterpriseclient_id')->references('id')->on('enterprise_client')->onDelete('cascade')->onUpdate('cascade');
        });
    }

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('geofence');
	}

}
