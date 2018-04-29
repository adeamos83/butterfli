<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDispatcherAgent extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dispatcher_agent', function($t) {
            $t->increments('id');
            $t->string('first_name');
            $t->string('last_name');
            $t->string('email');
            $t->string('password');
            $t->string('phone')->nullable();
            $t->dateTime('created_at');
            $t->dateTime('updated_at');
            $t->timestamp('deleted_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $t->unsignedInteger('healthcare_id')->nullable();
            $t->Integer('is_active')->default(0);
        });
        Schema::table('dispatcher_agent', function(Blueprint $t)
        {
            $t->foreign('healthcare_id')->references('id')->on('healthcare_provider')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('dispatcher_agent');
    }

}
