<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewAssigneesInSettingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        $date = date("Y-m-d H:i:s");
        Schema::table('settings', function(Blueprint $table) {

        });
        DB::table('settings')->insert([
            'tool_tip' => 'This mobile number will get SMS notifications for ride assignee',
            'page' => '1',
            'key' => 'ride_assignee_2_phone_number',
            'created_at'=> $date,
            'updated_at'=> $date
        ]);

        DB::table('settings')->insert([
            'tool_tip' => 'This address will get Email notifications for ride assignee',
            'page' => '1',
            'key' => 'ride_assignee_2_email_address',
            'created_at'=> $date,
            'updated_at'=> $date
        ]);
        DB::table('settings')->insert([
            'tool_tip' => 'This mobile number will get SMS notifications for ride assignee',
            'page' => '1',
            'key' => 'ride_assignee_3_phone_number',
            'created_at'=> $date,
            'updated_at'=> $date
        ]);

        DB::table('settings')->insert([
            'tool_tip' => 'This address will get Email notifications for ride assignee',
            'page' => '1',
            'key' => 'ride_assignee_3_email_address',
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
        Schema::table('settings', function (Blueprint $table) {
            $table->drop('ride_assignee_2_phone_number');
            $table->drop('ride_assignee_2_email_address');
            $table->drop('ride_assignee_3_phone_number');
            $table->drop('ride_assignee_3_email_address');
        });
    }
}
