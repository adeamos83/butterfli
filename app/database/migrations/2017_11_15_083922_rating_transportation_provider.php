<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RatingTransportationProvider extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('rating_transportation_providers', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('tp_id')->nullable();
            $table->integer('dispatcher_id')->nullable();
            $table->integer('request_id')->nullable();
            $table->integer('rating');
            $table->string('comment')->nullable();
            $table->timestamps();
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::drop('rating_transportation_providers');
	}

}
