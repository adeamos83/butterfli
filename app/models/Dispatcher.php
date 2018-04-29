<?php

class Dispatcher extends ButterfliEloquent {
    protected $fillable = [ 'email', 'password', 'token', 'is_active', 'is_admin', 'phone', 'transportation_provider_id', 'contact_name' ];
	protected $dates = ['deleted_at'];
	protected $table = 'dispatcher';
	protected $fields = [ 'email', 'password', 'token', 'is_active', 'is_admin', 'phone', 'transportation_provider_id', 'contact_name' ];
	protected $fieldErrors = [
		'email' => 'Please enter the email',
		'password' => 'Please enter the password',
		'token' => 'Please enter the token',
		'is_active' => 'Please enter the is_active',
		'is_admin' => 'Please enter the is_admin',
		'phone' => 'Please enter the phone',
		'transportation_provider_id' => 'Please enter the TP',
		'contact_name' => 'Please enter the Name',
	];

	function transportation_providers() {
		return TransportationProvider::all()->sortBy('company');
	}

	public function TransportationProvider() {
		return $this->belongsTo('TransportationProvider', 'transportation_provider_id');
	}
}
