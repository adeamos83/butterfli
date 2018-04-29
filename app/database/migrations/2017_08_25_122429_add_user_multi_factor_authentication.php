<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserMultiFactorAuthentication extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('user_multi_factor_authentication', function($t) {
            // auto increment id (primary key)
            $t->increments('id');
            $t->string('email');
            $t->enum('user_type', array('1', '2','3', '4','5','6','7','8')); //1=>dispatcher, 2=>healthcareprovider 3=>walker 4=>owner 5=>consumer 6=>healtchare agent
            $t->string('OTP')->nullable();
            $t->integer('otp_expiry_time');
            $t->timestamps();
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
