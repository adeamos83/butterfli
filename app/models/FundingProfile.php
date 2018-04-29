<?php
/**
 * Created by PhpStorm.
 * User: Saumya
 * Date: 26-06-2017
 * Time: 10:36
 */
class FundingProfile extends ButterfliEloquent {
	protected $fillable = [ 'funding_rule_type', 'payment_type', 'amount', 'bill_enterpriseclient_id' ];
	protected $dates = ['deleted_at'];
	protected $table = 'funding_profile';
	protected $fields = ['enterpriseclient_id', 'funding_rule_type', 'payment_type', 'amount', 'bill_enterpriseclient_id'];
	protected $fieldErrors = [
		'enterpriseclient_id' =>		'Rate Profile enterpriseclient_id error',
		'funding_rule_type' =>			'Rate Profile funding_rule_type error',
		'payment_type' =>				'Rate Profile payment_type error',
		'amount' =>						'Rate Profile amount error',
		'bill_enterpriseclient_id' =>	'Rate Profile bill_enterpriseclient_id error',
	];

	public function EnterpriseClient() {
		return $this->belongsTo('EnterpriseClient', 'enterpriseclient_id');
	}

	public function BillEnterpriseClient() {
		return $this->hasOne('EnterpriseClient', 'id', 'bill_enterpriseclient_id');
	}

	public static function DefaultProfileForType($funding_rule_type) {
		$profile = new FundingProfile();
		$profile->funding_rule_type = $funding_rule_type;
		$profile->payment_type = 2;
		$profile->amount = 1;

		return $profile;
	}

	public function funding_rules() {
		return [
			"Choose a Rule Type",
			"Flat Rate",
			"Percentage",
			"Remainder of Fare"
		];
	}

	public function funding_rule_text() {
		$rules = $this->funding_rules();
		if(sizeof($rules) > $this->funding_rule_type) {
			return $rules[$this->funding_rule_type];
		}
		
		return "Unknown Funding Rule";
	}
	
	public function payment_types() {
		return [
			"Choose a Payment Type",
			"Credit / Debit",
			"Invoice"
		];
		
	}

	public function payment_type_text() {
		$payment_types = $this->payment_types();
		if(sizeof($payment_types) > $this->payment_type) {
			return $payment_types[$this->payment_type];
		}
		
		return "Unknown Payment Type";
	}

	public function amount_text() {
		switch ($this->funding_rule_type) {
			case 1:
				return sprintf("%0.2f", $this->amount);
				break;

			case 2:
				return sprintf("%0.02f%%", $this->amount * 100);
				break;

			case 3:
				return "Remainder";
				break;
		}
	}

	public function billable_parties() {
		$clients = EnterpriseClient::all()->sortBy('company');
		$rider = FundingProfile::RiderStub();

		$array = array( $rider );

		$i = 1;
		foreach($clients as $client) {
			$array[$i++] = $client;
		}

		return $array;
	}

	private function RiderStub() {
		$rider = new EnterpriseClient;
		$rider->id = 0;
		$rider->company = "Passenger";

		return $rider;
	}
}

?>