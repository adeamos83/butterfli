<?php
/**
 * Created by PhpStorm.
 * User: Abhishek
 * Date: 06-10-2017
 * Time: 12:36
 */
class TransportationProvider extends Eloquent {
 	protected $fillable = [ 'email', 'phone', 'company', 'password', 'companylogo', 'operator_email', 'is_active', 'account_balance', 'total_amount', 'contact_name', 'address', 'city', 'state', 'country', 'postalcode' ];
    protected $table = 'transportation_provider';

    public function Dispatchers() {
    	return $this->hasMany('Dispatcher', 'transportation_provider_id', 'id');
    }
}
?>