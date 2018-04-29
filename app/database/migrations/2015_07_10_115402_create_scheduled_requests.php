<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScheduledRequests extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('scheduled_requests', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('owner_id');
            $table->double('latitude', 15, 8);
            $table->double('longitude', 15, 8);
            $table->double('dest_latitude', 15, 8)->default(0);
            $table->double('dest_longitude', 15, 8)->default(0);
            $table->string('time_zone');
            $table->string('src_address');
            $table->string('dest_address');
            $table->time('start_time');
            $table->string('promo_code');
            $table->integer('promo_id')->default(0);
            $table->integer('payment_mode')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop('scheduled_requests');
    }

}
