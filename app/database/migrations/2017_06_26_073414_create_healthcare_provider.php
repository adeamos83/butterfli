<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHealthCareProvider extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('healthcare_provider', function($t) {
            // auto increment id (primary key)
            $t->increments('id');
            $t->string('first_name');
            $t->string('last_name');
            $t->string('email');
            $t->string('password');
            $t->dateTime('created_at');
            $t->dateTime('updated_at');
            $t->timestamp('deleted_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $t->string('token');
            $t->string('company')->nullable();
            $t->string('companylogo')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('healthcare_provider');
    }

}
