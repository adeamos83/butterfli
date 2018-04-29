<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewTableRequestDispatcher extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('assigned_dispatcher_request', function($t) {
            $t->increments('id');
            $t->integer('request_id');
            $t->integer('assigned_dispatcher_id');
            $t->integer('assignee_dispatcher_id');
            $t->integer('status')->default(0);
            $t->integer('is_cancelled')->default(0);
            $t->string('cancel_reason')->nullable();
            $t->timestamps();
            $t->timestamp('deleted_at')->nullable();
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		//
	}

}
