<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewDataInSettingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        $date = date("Y-m-d H:i:s");

        DB::table('settings')->insert([
            'tool_tip' => 'This mobile number will get SMS notifications for operator',
            'page' => '1',
            'key' => 'operator_phone_number',
            'value' => '+14154188939',
            'created_at'=> $date,
            'updated_at'=> $date
        ]);

        DB::table('settings')->insert([
            'tool_tip' => 'This email address will get Email notifications for operator',
            'page' => '1',
            'key' => 'operator_email_address',
            'value' => 'fi@gobutterfli.com',
            'created_at'=> $date,
            'updated_at'=> $date
        ]);

        DB::table('settings')->insert([
            'tool_tip' => 'This mobile number will get SMS notifications for ride assignee',
            'page' => '1',
            'key' => 'ride_assignee_phone_number',
            'value' => '+14154188939',
            'created_at'=> $date,
            'updated_at'=> $date
        ]);

        DB::table('settings')->insert([
            'tool_tip' => 'This address will get Email notifications for ride assignee',
            'page' => '1',
            'key' => 'ride_assignee_email_address',
            'value' => 'fi@gobutterfli.com',
            'created_at'=> $date,
            'updated_at'=> $date
        ]);
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
