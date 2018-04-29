<?php
/**
 * Created by PhpStorm.
 * User: Saumya
 * Date: 26-06-2017
 * Time: 10:36
 */
class WalkerType extends ButterfliEloquent {
	protected $fillable = [ ];
	protected $dates = ['deleted_at'];
	protected $table = 'walker_type';
	protected $fields = ['enterpriseclient_id', 'base_fare', 'included_mileage', 'per_mile', 'service_type'];

	public function RateProfile() {
		return $this->belongsTo('RateProfile');
	}
}
?>