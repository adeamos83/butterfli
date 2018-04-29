<?php
/**
 * Created by PhpStorm.
 * User: Saumya
 * Date: 26-06-2017
 * Time: 10:36
 */
class Geofence extends ButterfliEloquent {
	protected $table = 'geofence';
	protected $fields = [ 'enterpriseclient_id', 'promocode', 'description', 'metadata_storage' ];
	protected $fieldErrors = [
		'enterpriseclient_id' =>	'Please enter the Enterprise Client ID',
		'promocode' => 				'Please enter the promocode',
		'description' => 			'Please enter the description'
	];

	public function EnterpriseClient() {
		return $this->belongsTo('EnterpriseClient', 'enterpriseclient_id');
	}


}
?>