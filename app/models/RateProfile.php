<?php
/**
 * Created by PhpStorm.
 * User: Saumya
 * Date: 26-06-2017
 * Time: 10:36
 */
class RateProfile extends ButterfliEloquent {
	use SoftDeletingTrait;

	protected $fillable = [
		'base_fare', 'included_mileage', 'per_mile', 'service_type', 'commission_rate',
		'deadhead_included_mileage', 'deadhead_per_mile', 'wait_time_included', 'wait_time_per_minute',
		'tpcost_base_fare', 'tpcost_per_mile', 'tpcost_deadhead_per_mile', 'tpcost_wait_time_per_minute'
	];
	protected $dates = ['deleted_at'];
	protected $table = 'rate_profile';
	protected $fields = [
		'enterpriseclient_id', 'base_fare', 'included_mileage', 'per_mile', 'service_type', 'commission_rate',
		'deadhead_included_mileage', 'deadhead_per_mile', 'wait_time_included', 'wait_time_per_minute',
		'tpcost_base_fare', 'tpcost_per_mile', 'tpcost_deadhead_per_mile', 'tpcost_wait_time_per_minute'
	];
	protected $fieldErrors = [
		'enterpriseclient_id' =>			'Rate Profile enterpriseclient_id error',
		'base_fare' =>						'Rate Profile base_fare error',
		'included_mileage' =>				'Rate Profile included_mileage error',
		'per_mile' =>						'Rate Profile per_mile error',
		'service_type' =>					'Rate Profile service_type error',
		'commission_rate' =>				'Rate Profile commission_rate error',
		'deadhead_included_mileage' =>		'Rate Profile deadhead_included_mileage error',
		'deadhead_per_mile' =>				'Rate Profile deadhead_per_mile error',
		'wait_time_included' =>				'Rate Profile wait_time_included error',
		'wait_time_per_minute' =>			'Rate Profile wait_time_per_minute error',
	];

	public function EnterpriseClient() {
		return $this->belongsTo('EnterpriseClient', 'enterpriseclient_id');
	}

	public function WalkerType() {
		return $this->hasOne('WalkerType', 'id', 'service_type');
	}

	public static function DefaultProfileForType($service_type) {
		$profile = new RateProfile();
		$profile->service_type = $service_type;
		$profile->commission_rate = 0.15;

		switch ($service_type) {
			case 2:
				// Wheelchair
				$profile->base_fare = 28.75;
				$profile->included_mileage = 0;
				$profile->per_mile = 3.45;
				$profile->deadhead_included_mileage = 0;
				$profile->deadhead_per_mile = 0;
				$profile->wait_time_included = 0;
				$profile->wait_time_per_minute = 0;
				$profile->tpcost_base_fare = 25;
				$profile->tpcost_per_mile = 3;
				$profile->tpcost_deadhead_per_mile = 0;
				$profile->tpcost_wait_time_per_minute = 0;
				break;

			case 3:
				// Ambulatory
				$profile->base_fare = 11.50;
				$profile->included_mileage = 0;
				$profile->per_mile = 1.90;
				$profile->deadhead_included_mileage = 0;
				$profile->deadhead_per_mile = 0;
				$profile->wait_time_included = 0;
				$profile->wait_time_per_minute = 0;
				$profile->tpcost_base_fare = 10;
				$profile->tpcost_per_mile = 1.65;
				$profile->tpcost_deadhead_per_mile = 0;
				$profile->tpcost_wait_time_per_minute = 0;
				break;

			case 4:
				// Gurney
				$profile->base_fare = 135.00;
				$profile->included_mileage = 0;
				$profile->per_mile = 3.00;
				$profile->deadhead_included_mileage = 0;
				$profile->deadhead_per_mile = 0;
				$profile->wait_time_included = 0;
				$profile->wait_time_per_minute = 0;
				$profile->tpcost_base_fare = 85.00;
				$profile->tpcost_per_mile = 3.00;
				$profile->tpcost_deadhead_per_mile = 0;
				$profile->tpcost_wait_time_per_minute = 0;

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
		return sprintf("%0.2f", $this->base_fare);
	}
	
	public function quoted_per_mile() {
		return sprintf("%0.2f", $this->per_mile);
	}
	
	public function quoted_included_mileage() {
		return $this->included_mileage == 0.00 ? "None" : sprintf("%0.2f", $this->included_mileage);
	}

	public function quoted_deadhead_per_mile() {
		return $this->deadhead_per_mile == 0.00 ? "None" : sprintf("%0.2f", $this->deadhead_per_mile);
	}

	public function quoted_deadhead_included_mileage() {
		return $this->deadhead_included_mileage == 0.00 ? "None" : sprintf("%0.2f", $this->deadhead_included_mileage);
	}

	public function quoted_wait_time_per_minute() {
		return $this->wait_time_per_minute == 0.00 ? "None" : sprintf("%0.2f", $this->wait_time_per_minute);
	}

	public function quoted_wait_time_included() {
		return $this->wait_time_included == 0.00 ? "None" : sprintf("%0.2f", $this->wait_time_included);
	}

	public function quoted_tpcost_base_fare() {
		return sprintf("%0.2f", $this->tpcost_base_fare);
	}
	
	public function quoted_tpcost_per_mile() {
		return sprintf("%0.2f", $this->tpcost_per_mile);
	}
	
	public function quoted_tpcost_deadhead_per_mile() {
		return $this->tpcost_deadhead_per_mile == 0.00 ? "None" : sprintf("%0.2f", $this->tpcost_deadhead_per_mile);
	}

	public function quoted_tpcost_wait_time_per_minute() {
		return $this->tpcost_wait_time_per_minute == 0.00 ? "None" : sprintf("%0.2f", $this->tpcost_wait_time_per_minute);
	}


}
?>