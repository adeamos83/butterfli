<?php
/**
 * Created by PhpStorm.
 * User: Saumya
 * Date: 26-06-2017
 * Time: 10:36
 */
class CostProfile extends ButterfliEloquent {
	use SoftDeletingTrait;

	protected $fillable = [ 'base_fare', 'included_mileage', 'per_mile', 'service_type', 'commission_Cost' ];
	protected $dates = ['deleted_at'];
	protected $table = 'Cost_profile';
	protected $fields = ['enterpriseclient_id', 'base_fare', 'included_mileage', 'per_mile', 'service_type', 'commission_Cost' ];
	protected $fieldErrors = [
		'enterpriseclient_id' =>	'Cost Profile enterpriseclient_id error',
		'base_fare' =>				'Cost Profile base_fare error',
		'included_mileage' =>		'Cost Profile included_mileage error',
		'per_mile' =>				'Cost Profile per_mile error',
		'service_type' =>			'Cost Profile service_type error',
		'commission_Cost' =>		'Cost Profile commission_Cost error',
	];

	public function EnterpriseClient() {
		return $this->belongsTo('EnterpriseClient', 'enterpriseclient_id');
	}

	public function WalkerType() {
		return $this->hasOne('WalkerType', 'id', 'service_type');
	}

	public static function DefaultProfileForType($service_type) {
		$profile = new CostProfile();
		$profile->service_type = $service_type;
		$profile->commission_Cost = 0.15;

		switch ($service_type) {
			case 2:
				// Wheelchair
				$profile->base_fare = 25.00;
				$profile->included_mileage = 0;
				$profile->per_mile = 3.00;
				break;

			case 3:
				// Ambulatory
				$profile->base_fare = 10.00;
				$profile->included_mileage = 0;
				$profile->per_mile = 1.65;
				break;

			case 4:
				// Gurney
				$profile->base_fare = 135.00;
				$profile->included_mileage = 0;
				$profile->per_mile = 3.00;
				break;
		}

		return $profile;
	}

	public function service_types() {
		return [
			"Please Select a Service Type",
			null,
			"Wheelchair",
			"Ambulatory",
			"Gurney"
		];
	}

	public function service_type_text() {
		$rules = $this->service_types();
		if(sizeof($rules) > $this->service_type) {
			return $rules[$this->service_type];
		}
		
		return "Unknown Funding Rule";
	}

	public function quoted_base_fare() {
		return $this->base_fare + ($this->base_fare * $this->commission_Cost);
	}
	
	public function quoted_per_mile() {
		return $this->per_mile + ($this->per_mile * $this->commission_Cost);
	}
	
	public function quoted_included_mileage() {
		return $this->included_mileage == 0.00 ? "None" : sprintf("%0.2f", $this->included_mileage);
	}
}
?>