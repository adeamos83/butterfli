<?php

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;

class Walker extends ButterfliEloquent implements UserInterface, RemindableInterface{

	use UserTrait, RemindableTrait, SoftDeletingTrait;

	protected $dates = ['deleted_at'];
    protected $table = 'walker';
	protected $fillable = [ 'phone', 'email', 'is_approved', 'is_authorize', 'transportation_provider_id', 'contact_name' ];
	protected $fields = [ 'phone', 'email', 'is_approved', 'is_authorize', 'transportation_provider_id', 'contact_name' ];

	protected $fieldErrors = [
		'email' => 						'Please enter the email',
		'is_approved' => 				'Please enter the is_approved',
		'is_authorize' => 				'Please enter the is_authorize',
		'phone' => 						'Please enter the phone',
		'transportation_provider_id' => 'Please enter the TP',
		'contact_name' => 				'Please enter the Name',
	];

	public function tp() {
		return $this->hasOne('TransportationProvider', 'id', 'transportation_provider_id');
	}

	function transportation_providers() {
		return TransportationProvider::all()->sortBy('company');
	}
}
