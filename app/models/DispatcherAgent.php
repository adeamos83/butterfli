<?php
/**
 * Created by PhpStorm.
 * User: Saumya
 * Date: 26-06-2017
 * Time: 10:36
 */
class DispatcherAgent extends Eloquent {
	protected $fillable = [ 'contact_name' ];
    protected $dates = ['deleted_at'];
    protected $table = 'dispatcher_agent';
}
?>