<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDispatcherIdRequestDeleteFromDispatcherAssignedTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('request', function(Blueprint $table)
		{
			$table->unsignedInteger('dispatcher_id')->nullable();
		});
		
		Schema::table('request', function(Blueprint $table)
		{
			$table->foreign('dispatcher_id')->references('id')->on('dispatcher');
		});
		
		Schema::table('request', function(Blueprint $table) {
            $sql = "select id, dispatcher_id from dispatcher_assigned";			
			$dispatcher_assigned = DB::select(DB::raw($sql));
			if (count($dispatcher_assigned) > 0){
				foreach ($dispatcher_assigned as $value) {
					Requests::where('dispatcher_assigned_id', '=', $value->id)->update(array('dispatcher_id' => $value->dispatcher_id));			
				}
			}
			
        });
		
		
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('request', function(Blueprint $table)
		{	
			$table->dropForeign('request_dispatcher_id_foreign');		
			$table->dropColumn('dispatcher_id');
		});
	}

}
