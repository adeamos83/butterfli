<?php
/**
 * Created by PhpStorm.
 * User: Saumya
 * Date: 26-06-2017
 * Time: 10:36
 */
class EnterpriseClient extends ButterfliEloquent {
	protected $fillable = [ 'email', 'password', 'company', 'companylogo', 'operator_email', 'operator_phone', 'is_active', 'account_balance', 'total_amount', 'contact_name', 'address', 'city', 'state', 'country', 'postalcode' ];
	protected $dates = ['deleted_at'];
	protected $table = 'enterprise_client';
	protected $fields = ['email', 'password', 'token', 'company', 'companylogo', 'operator_email', 'operator_phone', 'is_active', 'account_balance', 'total_amount', 'contact_name', 'address', 'city', 'state', 'country', 'postalcode'];
	protected $fieldErrors = [
		'email' => 				'Please enter the Email Address',
		'password' => 			'Please enter the password',
		'company' => 			'Please enter the Company Name',
		'operator_email' => 	'Please enter the Email Address',
		'operator_phone' => 	'Please enter the Phone Number',
		'contact_name' => 		'Please enter the Contact Name',
		'address' => 			'Please enter the Address',
		'city' => 				'Please enter the City',
		'state' => 				'Please enter the State',
		'country' => 			'Please enter the Country',
		'postalcode' =>			'Please enter the Zip Code'
	];

	public function Ingest($required = [], $index = "") {
		$ret = ButterfliEloquent::Ingest($required);
		if($ret != null) {
			return $ret;
		}

		$rate_profile_count = Input::get("rate_profile_count");

		if($rate_profile_count != null && $rate_profile_count > 0) {
			for($i = 0; $i < $rate_profile_count; $i++) {
				$deleteArray = Input::get("delete_rate_profile");
				if(! is_null($deleteArray)) {
					if(array_key_exists($i, $deleteArray)) {
						$delete = $deleteArray[$i];
						if($delete == 1) {
							$id = Input::get("rate_profile_id")[$i];
							$rateprofile = RateProfile::find($id);
							if(! is_null($rateprofile)) {
								$rateprofile->delete();
							}
							continue;
						}
					}
				}

				$rate_profile_id = Input::get("rate_profile_id")[$i];

				if($rate_profile_id == -1) {
					$rateprofile = new RateProfile();
					$rateprofile->EnterpriseClient()->associate($this);
					$rateprofile->save();
				}
				else {
					$rateprofile = RateProfile::find($rate_profile_id);
				}

				$ret = $rateprofile->Ingest([], $i);
				if($ret != null) {
					return $ret;
				}

				$rateprofile->update($rateprofile->IngestedAttributes());
			}
		}

		$funding_profile_count = Input::get("funding_profile_count");

		if($funding_profile_count != null && $funding_profile_count > 0) {
			for($i = 0; $i < $funding_profile_count; $i++) {
				$deleteArray = Input::get("delete_funding_profile");
				if(! is_null($deleteArray)) {
					if(array_key_exists($i, $deleteArray)) {
						$delete = $deleteArray[$i];
						if($delete == 1) {
							$id = Input::get("funding_profile_id")[$i];
							$fundingprofile = FundingProfile::find($id);
							if(! is_null($fundingprofile)) {
								$fundingprofile->delete();
							}
							continue;
						}
					}
				}

				$funding_profile_id = Input::get("funding_profile_id")[$i];

				if($funding_profile_id == -1) {
					$fundingprofile = new FundingProfile();
					$fundingprofile->EnterpriseClient()->associate($this);
					$fundingprofile->save();
				}
				else {
					$fundingprofile = FundingProfile::find($funding_profile_id);
				}

				$ret = $fundingprofile->Ingest([], $i);
				if($ret != null) {
					return $ret;
				}

				$fundingprofile->update($fundingprofile->IngestedAttributes());
			}
		}

		return null;
	}

	public function PartialDetails($clientid) {
		if($clientid == $this->id) {
			return $this->company . ' <i class="fa fa-check-circle" style="color: #00b300;"></i>';
		}
		return $this->company . ' <i class="fa fa-exclamation-triangle" style="color: #ccc000;"></i>';
	}

	public function RateProfile() {
		return $this->hasMany('RateProfile', 'enterpriseclient_id', 'id');
	}

	public function FundingProfile() {
		return $this->hasMany('FundingProfile', 'enterpriseclient_id', 'id');
	}

	public function BillEnterpriseClient() {
		return $this->belongsTo('FundingProfile');
	}

	public function Geofence() {
		return $this->hasMany('Geofence', 'enterpriseclient_id', 'id');
	}

}
?>