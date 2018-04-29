<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('user_events', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('owner_id');
            $table->string('event_name');
            $table->dateTime('start_time');
            $table->dateTime('server_start_time');
            $table->string('event_place_address');
            $table->integer('event_total_members')->default(0);
            $table->double('event_latitude', 15, 8);
            $table->double('event_longitude', 15, 8);
            $table->string('time_zone');
            $table->double('event_pre_pay_for_each_member', 15, 2)->default(0);
            $table->double('event_pre_pay_total', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop('user_events');
    }

}
