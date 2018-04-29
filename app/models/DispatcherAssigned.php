<?php

class DispatcherAssigned extends Eloquent {
    protected $fillable = [ 'contact_name' ];
	protected $table = 'dispatcher_assigned';

	public $rate = 0;
    public $address = null;
    public $picture = null;
    public $bio = null;
    public $zipcode = null;
    public $state = null;
    public $country = null;
    public $deleted_at = null;
    public $created_at = '0000-00-00 00:00:00';
    public $updated_at = '0000-00-00 00:00:00';
    public $timezone = 'UTC';
}
