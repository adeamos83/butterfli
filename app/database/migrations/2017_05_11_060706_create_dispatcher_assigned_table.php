<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDispatcherAssignedTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('dispatcher_assigned', function($t) {
          // auto increment id (primary key)
          $t->increments('id');
          $t->string('first_name');
          $t->string('last_name');
          $t->string('email');
          $t->string('phone');
          $t->dateTime('created_at');
          $t->dateTime('updated_at');
          $t->integer('dispatcher_id')->unsigned();
          $t->foreign('dispatcher_id')->references('id')->on('dispatcher');
       });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('dispatcher_assigned');
	}

}
