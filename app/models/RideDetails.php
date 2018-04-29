<?php
/**
 * Created by PhpStorm.
 * User: saumya
 * Date: 14/9/17
 * Time: 4:01 AM
 */

class RideDetails extends Eloquent {
    protected $fillable = [ 'agent_contact_name' ];
    protected $dates = ['deleted_at'];
    protected $table = 'ride_details';
}
?>