<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateWalkerTypeTableAddNewColomn extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        $date = date("Y-m-d H:i:s");

        DB::table('walker_type')->insert([
            'name' => 'Gurney',
            'max_size' => '2',
            'is_default' => '0',
            'is_visible' => '1',
            'created_at'=> $date,
            'updated_at'=> $date,
            'icon' => '',
            'price_per_unit_distance'=>'3.45',
            'price_per_unit_time'=>'1.00',
            'base_price'=>'158.70',
            'base_distance'=>'1'
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
