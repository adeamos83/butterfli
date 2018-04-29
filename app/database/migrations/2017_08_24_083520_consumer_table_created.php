<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ConsumerTableCreated extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('consumer', function($t) {
            // auto increment id (primary key)
            $t->increments('id');
            $t->string('first_name');
            $t->string('last_name');
            $t->string('email');
            $t->string('password');
            $t->string('phone');
            $t->timestamps();
            $t->timestamp('deleted_at')->nullable();
            $t->string('client_id')->nullable();
            $t->string('client_secret')->nullable();
            $t->string('company')->nullable();
            $t->string('picture')->nullable();
            $t->Integer('is_active')->default(1);
        });

        Schema::table('request', function(Blueprint $table)
        {
            $table->integer('consumer_id')->nullable();
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::drop('consumer');
	}

}
