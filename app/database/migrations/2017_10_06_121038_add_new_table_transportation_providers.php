<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewTableTransportationProviders extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transportation_providers', function(Blueprint $table) {
            $table->increments('id');
            $table->string('email');
            $table->string('phone');
            $table->string('company');
            $table->longText('rate')->nullable();
            $table->string('contact')->nullable();
            $table->string('status')->nullable();
            $table->string('service_area')->nullable();
            $table->string('available_after_hours')->nullable();
            $table->string('service_hours')->nullable();
            $table->string('wheelchair_vehicles')->nullable();
            $table->string('comment')->nullable();
            $table->string('tp_address')->nullable();
            $table->string('tps_vehicles')->nullable();
            $table->string('device')->nullable();
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
        Schema::drop('transportation_providers');
    }

}
