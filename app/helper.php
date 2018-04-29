<?php
/*  CalculateFare

    $params - dictionary with parameters:

        type =>                 Fare Type:
                                    2 => Wheelchair
                                    3 => Ambulatory
                                    4 => Gurney
        enterprise_client =>    EnterpriseClient Model
        origin_latitude =>          Lat/Long of origin     
        origin_longitude =>
        destination_latitude =>     Lat/Long of destination
        destination_longitude =>    
        is_wheelchair =>        Wheelchair requested
        is_roundtrip =>         Is the trip a round trip


    Returns - dictionary

        base_price =>               The pick up charge or entire fare if flat rate
        distance =>                 Distance of trip
        is_wheelchair =>            Wheelchair Requested
        per_mile_price =>           Price per mile
        mileage_price =>            Milage charge (distance * per_mile_price)
        wheelchair_price =>         Wheelchair Charge
        duration =>                 Duration in seconds
        durationText =>             Duration in human readable form
        is_roundtrip =>             0 = One way, 1 = round trip
        ride_estimated_price =>     Total for ride
        origin_latitude =>          Lat/Long of origin     
        origin_longitude =>
        destination_latitude =>     Lat/Long of destination
        destination_longitude =>    

*/

function CalculateFare($params) {
    $enterpriseClient =         $params['enterprise_client'];
    $type =                     $params['type'];
    $is_wheelchair =            $params['is_wheelchair'];
    $is_roundtrip =             $params['is_roundtrip'];
    $origin_latitude =          $params['origin_latitude'];
    $origin_longitude =         $params['origin_longitude'];
    $destination_latitude =     $params['destination_latitude'];
    $destination_longitude =    $params['destination_longitude'];

    $rateProfile =
        RateProfile::where('enterpriseclient_id', '=', $enterpriseClient->id)
        ->where('service_type', '=', $type)
        ->first();

    $distance = 0;
    $duration = 0;

    if($rateProfile->per_mile > 0) {
        //
        // calculate the distance using Google Maps Directions API
        //
        $key = 'AIzaSyBh5zs08CTuBh0rKXnW4Li3IW8scAVnLl4';

        $url =
            'https://maps.googleapis.com/maps/api/directions/json?'
            . 'origin=' . $origin_latitude . ',' . $origin_longitude
            . '&destination=' . $destination_latitude . ',' . $destination_longitude
            . '&mode=driving'
            . '&units=imperial'
            . '&region=us'
            . '&alternatives=true'
            . '&key=' . $key;

        $response = file_get_contents($url);
        $response = json_decode($response, true);

        $route = ShortestRoute($response);
        $distance = $route['legs'][0]['distance']['value'] / 1609.344;
        $duration = $route['legs'][0]['duration']['value'];
//        $durationText = hmsText($duration);
    }

    $base_price = $rateProfile->base_fare;
    $per_mile_price = $rateProfile->per_mile;
    if($distance < $rateProfile->included_mileage) {
        $distance = 0;
    }
    else {
        $distance -= $rateProfile->included_mileage;
    }

    $wheelchair_price = 0;
    $wheelchair_price = $is_wheelchair == 1 ? 10 : 0;

    if($is_roundtrip == 1){
        $base_price *= 2;
        $wheelchair_price *= 2;
        $distance *= 2;
    }

    $mileage_price = $per_mile_price * $distance;
    $ride_estimated_price = $base_price + $mileage_price + $wheelchair_price;

    $fare = array(
        'base_price' =>             $base_price,
        'distance' =>               $distance,
        'duration' =>               $duration,
//        'durationText' =>           $durationText,
        'is_wheelchair' =>          $is_wheelchair,
        'per_mile_price' =>         $per_mile_price,
        'mileage_price' =>          $mileage_price,
        'wheelchair_price' =>       $wheelchair_price,
        'is_roundtrip' =>           $is_roundtrip,
        'ride_estimated_price' =>   $ride_estimated_price,
        'origin_latitude' => $origin_latitude,
        'origin_longitude' => $origin_longitude,
        'destination_latitude' => $destination_latitude,
        'destination_longitude' => $destination_longitude
    );

    return $fare;
}

/*  ValidateStoredEstimate

    Take a stored estimate previously returned by CalculateFare and verify that updated parameters
    such as lat/long for origin and destination still match.

    Used to determine if another Google Maps API Call is necessary or if we can use the information
    returned by a prior call to that API.

    $fare -     The stored profile previously returned by CalculateFare, in the same format

    $params -   Input values sent from a client, matching keys in $fare, and including as a minimum:
                origin_latitude, origin_longitude, destination_latitude, destination_longitude
*/

function ValidateStoredEstimate($fare, $params) {
	if(NULL == $fare) {
		return FALSE;
	}
	
	foreach(array_keys($params) as $key) {
		if(! array_key_exists($key, $params)) {
            continue;
		}
		if($fare[$key] != $params[$key]) {
			return FALSE;
		}
	}
	
	return TRUE;
}

/*  ShortestRoute

    Take the response from Google Maps Directions API and pull the shortest route out of it

    $response -     The response from the API

    Returns:

    $theroute -     An element from $response['routes']

*/

function ShortestRoute($response) {
    $distance = 0;
    $theroute = NULL;

    $routes = $response['routes'];
    foreach($routes as $route) {
        $d = $route['legs'][0]['distance']['value'];
        if(NULL == $theroute) {
            $distance = $d;
            $theroute = $route;
        }

        if($distance > $d) {
            $distance = $d;
            $theroute = $route;
        }
    }

    return $theroute;
}

/*  hmsText

    Take a duration in seconds and return it in human-readable form.

*/

function hmsText($seconds) {
    if($seconds < 60) {
        $seconds . " seconds";
    }
    if($seconds < 3600) {
        $minutes = ($seconds / 60);
        if($seconds % 60 > 0) {
            $minutes += 1;
        }

        return $minutes . " minutes";
    }

    $hours = $seconds / 3600;
    $seconds -= $hours * 3600;
    $minutes = $seconds / 60;
    if($seconds % 60 > 0) {
        $minutes += 1;
    }

    return $hours . " hours " . $minutes . " minutes";
}

/*  ValueForSetting

    Look up a setting and return its value

    $key -      name of the setting
*/

function ValueForSetting($key) {
    $settings = Settings::where('key', $key)->first();
    return $settings->value;
}

/*  readStopsForSanClemente

    Look up the geofence metadata for San Clemente and return an array with the stop numbers
*/

function readStopsForSanClemente() {
    $query = "SELECT * FROM geofence WHERE enterpriseclient_id=30";
    $geofenceRecord = DB::select(DB::raw($query));
    Log::info($geofenceRecord[0]->metadata_storage);
    $geofence = json_decode($geofenceRecord[0]->metadata_storage, true);
    return $geofence['stops'];
}

function get_UTC_time($inputtime){
	
	$given = new DateTime($inputtime);
	$given->format("Y-m-d H:i:s");
	$given->setTimezone(new DateTimeZone("UTC"));
	return $given->format("Y-m-d H:i:s");
}
	   
function get_user_time($remote_tz, $origin_tz = null, $time) {
    if ($origin_tz === null) {
        if (!is_string($origin_tz = date_default_timezone_get())) {
            return false; // A UTC timestamp was returned -- bail out!
        }
    }
	
    $origin_dtz = new DateTimeZone($origin_tz);
    $remote_dtz = new DateTimeZone($remote_tz);
    $origin_dt = new DateTime("now", $origin_dtz);
    $remote_dt = new DateTime("now", $remote_dtz);
    $offset = $origin_dtz->getOffset($origin_dt) - $remote_dtz->getOffset($remote_dt);

    $time_new = strtotime($time) + $offset;

    $new_time = date("Y-m-d H:i:s", $time_new);
    return $new_time;
}

function date_time_differ($datetime1, $week = NULL) {
    $datetime1 = new DateTime($datetime1);
    if ($week != NULL) {
        $datetime2 = new DateTime(date("Y-m-d H:i:s", strtotime("+2 weeks")));
    } else {
        $datetime2 = new DateTime(date("Y-m-d H:i:s"));
    }
    $interval = $datetime1->diff($datetime2);
    return $interval;
}

function date_time_differ_two($datetime1) {
    $datetime1 = new DateTime($datetime1);
    $datetime2 = new DateTime(date("Y-m-d H:i:s"));
    $interval = $datetime1->diff($datetime2);
    return $interval;
}

function unlink_image($image) {
    $base_asset_url = asset_url();

    $base = str_replace($base_asset_url, '../public', $image);
    try {
        unlink($base);
    } catch (Exception $e) {
        
    }
}

function get_location($lat, $long) {
    try {
        $curl_string = "https://roads.googleapis.com/v1/snapToRoads?path=$lat,$long&key=" . Config::get('app.gcm_browser_key') . "&interpolate=true";
        $session = curl_init($curl_string);
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
        $msg_chk = curl_exec($session);
        $msg_chk = json_decode($msg_chk);
        $lat1 = $msg_chk->snappedPoints[0]->location->latitude;
        $long1 = $msg_chk->snappedPoints[0]->location->longitude;
        $location = array('lat' => $lat1, 'long' => $long1);
        return $location;
    } catch (Exception $ex) {
        $location = array('lat' => $lat, 'long' => $long);
        return $location;
    }
}

function get_dist($source_lat, $source_long, $dest_lat, $dest_long) {
    /* $curl_string = "https://maps.googleapis.com/maps/api/directions/json?origin=" . $source_lat . "," . $source_long . "&destination=" . $dest_lat . "," . $dest_long . "&key=AIzaSyD6ZVevefP2THEQrOaDGNANbrnbRLmzQdA"; */
    $curl_string = "https://maps.googleapis.com/maps/api/directions/json?origin=" . $source_lat . "," . $source_long . "&destination=" . $dest_lat . "," . $dest_long . "&key=" . Config::get('app.gcm_browser_key') . "";
//url_string='www.google.com';
    $session = curl_init($curl_string);
    curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
    $msg_chk = curl_exec($session);
    $phpObj = json_decode($msg_chk);
    /* echo $msg_chk; */
    /* print_r($phpObj); */
    /* echo "Text :- " . $phpObj->routes[0]->legs[0]->distance->text; */
    $settings = Settings::where('key', 'default_distance_unit')->first();
    $unit = $settings->value;
    if (isset($phpObj->routes[0]->legs[0]->distance->value)) {
        if ($unit == 1) {
            $dist = ($phpObj->routes[0]->legs[0]->distance->value / 1000) * 0.621371;
        } else {
            $dist = ($phpObj->routes[0]->legs[0]->distance->value / 1000);
        }
    } else {
        $dist = 0;
    }
    return $dist;
}

function get_zipcode($source_lat, $source_long) {
    /* $curl_string = "https://maps.googleapis.com/maps/api/directions/json?origin=" . $source_lat . "," . $source_long . "&destination=" . $dest_lat . "," . $dest_long . "&key=AIzaSyD6ZVevefP2THEQrOaDGNANbrnbRLmzQdA"; */
    $curl_string = "https://maps.googleapis.com/maps/api/geocode/json?latlng=" . $source_lat . "," . $source_long . "&key=" . Config::get('app.gcm_browser_key') . "&sensor=false";
//url_string='www.google.com';
    $session = curl_init($curl_string);
    curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
    $msg_chk = curl_exec($session);
    $phpObj = json_decode($msg_chk);

    if (isset($phpObj->results[0]->address_components)) {
        $count = sizeof($phpObj->results[0]->address_components);
        $count = $count - 1;
        $zip_code = preg_replace("/[^0-9,.]/", "", $phpObj->results[0]->address_components[$count]->long_name);
        if ($zip_code == "") {
            $zip_code = 0;
        }
    } else {
        $zip_code = 0;
    }
    return trim($zip_code);
}

function my_random6_number() {
    $min = 1;
    $max = 9;
    $random_number1 = rand($min, $max);


    //first capital  
    $length = 1;

    $chars = 'ABCDEFGHJKLMNOPQRSTUVWXYZ';
    $count = strlen($chars);

    for ($i = 0, $result = ''; $i < $length; $i++) {
        $index = rand(0, $count - 1);
        $result .= substr($chars, $index, 1);
    }


    //second  capital                  


    $chars1 = 'ABCDEFGHJKLMNOPQRSTUVWXYZ';
    $count = strlen($chars1);

    for ($i = 0, $result1 = ''; $i < $length; $i++) {
        $index = rand(0, $count - 1);
        $result1 .= substr($chars1, $index, 1);
    }


    //first small           


    $smallch = 'abcdefghijkmnopqrstuvwxyz';
    $counts = strlen($smallch);

    for ($i = 0, $smallchar = ''; $i < $length; $i++) {
        $index = rand(0, $counts - 1);
        $smallchar .= substr($smallch, $index, 1);
    }

    //second small    

    $smallch2 = 'abcdefghijkmnopqrstuvwxyz';
    $counts2 = strlen($smallch2);

    for ($i = 0, $smallchar2 = ''; $i < $length; $i++) {
        $index = rand(0, $counts - 1);
        $smallchar2 .= substr($smallch2, $index, 1);
    }


    $special = array("0", "7");
    $spe_random = rand(0, 1);
    $spe = $special[$spe_random];

    $rnd = $random_number1;

    $main_no = "";

    if ($random_number1 % 2 == 0) {
        if ($random_number1 == 2) {

            $main_no = $result . $smallchar . $rnd . $smallchar2 . $spe . $result1;
        }
        if ($random_number1 == 4) {
            $main_no = $smallchar . $rnd . $smallchar2 . $spe . $result1 . $result;
        }

        if ($random_number1 == 6) {
            $main_no = $rnd . $smallchar2 . $spe . $result1 . $result . $smallchar;
        }
        if ($random_number1 == 8) {
            $main_no = $smallchar2 . $spe . $result1 . $result . $smallchar . $rnd;
        }
    }

    if ($random_number1 % 2 != 0) {
        if ($random_number1 == 1) {
            $main_no = $spe . $result1 . $result . $smallchar . $rnd . $smallchar2;
        }

        if ($random_number1 == 3) {
            $main_no = $result1 . $result . $smallchar . $rnd . $smallchar2 . $spe;
        }

        if ($random_number1 == 5) {
            $main_no = $result . $smallchar . $rnd . $smallchar2 . $spe . $result1;
        }
        if ($random_number1 == 7) {
            $main_no = $smallchar . $rnd . $smallchar2 . $spe . $result1 . $result;
        }
        if ($random_number1 == 9) {
            $main_no = $rnd . $smallchar2 . $spe . $result1 . $result . $smallchar;
        }
    }
    return $main_no;
    //echo "<br><br><br>r1-".$random_number1;
}

function get_address($source_lat, $source_long) {
    /* $curl_string = "https://maps.googleapis.com/maps/api/directions/json?origin=" . $source_lat . "," . $source_long . "&destination=" . $dest_lat . "," . $dest_long . "&key=AIzaSyD6ZVevefP2THEQrOaDGNANbrnbRLmzQdA"; */
//    $curl_string = "https://maps.googleapis.com/maps/api/geocode/json?latlng=" . $source_lat . "," . $source_long . "&key=" . Config::get('app.gcm_browser_key') . "&sensor=false";
//url_string='www.google.com';
    if(is_null($source_lat) && is_null($source_long)) {
        return null;
    }
    $curl_string = "https://maps.googleapis.com/maps/api/geocode/json?latlng=" . $source_lat . "," . $source_long . "&sensor=false";
    $session = curl_init($curl_string);
    curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
    $msg_chk = curl_exec($session);
    $phpObj = json_decode($msg_chk);

    if (isset($phpObj->results[0]->address_components)) {
        $Address = "";
        foreach ($phpObj->results[0]->address_components as $get_add) {
            $Address .=$get_add->long_name . ", ";
        }
    } else {
        $Address = "Address Not Available.";
    }
    return trim(rtrim($Address, ", "));
}

function distanceGeoPoints($lat1, $lng1, $lat2, $lng2) {

    $earthRadius = 3958.75;

    $dLat = deg2rad($lat2 - $lat1);
    $dLng = deg2rad($lng2 - $lng1);


    $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLng / 2) * sin($dLng / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    $dist = $earthRadius * $c;

    // from miles
    $meterConversion = 1609;
    $geopointDistance = $dist * $meterConversion;

    return $geopointDistance;
}

function get_angle($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 3958.75) {
    // convert from degrees to radians
    $latFrom = deg2rad($latitudeFrom);
    $lonFrom = deg2rad($longitudeFrom);
    $latTo = deg2rad($latitudeTo);
    $lonTo = deg2rad($longitudeTo);

    $latDelta = $latTo - $latFrom;
    $lonDelta = $lonTo - $lonFrom;

    $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
    return $angle * $earthRadius;
}

function sprintf2($value, $decimal = null) {
    /* $decimal = 2; */
    return sprintf("%." . $decimal . "f", $value);
}

function weektotal($totalarray) {
    $weekpayout = 0;
    foreach ($totalarray as $total) {
        $weekpayout = $weekpayout + $total->payment_remaining - $total->refund_remaining;
    }
    return $weekpayout;
}

function payment_remaining_total($totalarray) {
    $weekpayout = 0;
    foreach ($totalarray as $total) {
        $weekpayout = $weekpayout + $total->payment_remaining;
    }
    return $weekpayout;
}

function refund_remaining_total($totalarray) {
    $weekpayout = 0;
    foreach ($totalarray as $total) {
        $weekpayout = $weekpayout + $total->refund_remaining;
    }
    return $weekpayout;
}

function formated_value($valu) {
    $value = str_replace('.', '.', number_format((float) $valu, 2));
    return $value;
}

function patyoutday($payoutarr, $date, $id) {
    $weekpayout = 0;
    foreach ($payoutarr as $daytotal) {
        $newdate = date('Y-m-d', strtotime($daytotal->date));

        if ($newdate == $date) {
            if ($id == $daytotal->confirmed_walker) {
                $weekpayout = $weekpayout + $daytotal->payment_remaining - $daytotal->refund_remaining;
            }
        }
    }
    return $weekpayout;
}

function driverweek($payoutarr, $id) {
    $weekpayout = 0;
    foreach ($payoutarr as $daytotal) {

        if ($id == $daytotal->confirmed_walker) {
            /* $weekpayout = $weekpayout + $daytotal->card_payment; */
            $weekpayout = $weekpayout + $daytotal->payment_remaining - $daytotal->refund_remaining;
        }
    }
    return $weekpayout;
}

function trip_total($payoutarr, $id) {
    $weekpayout = 0;
    foreach ($payoutarr as $daytotal) {

        if ($id == $daytotal->confirmed_walker) {
            /* $weekpayout = $weekpayout + $daytotal->card_payment; */
            $weekpayout = $weekpayout + $daytotal->card_payment;
        }
    }
    return $weekpayout;
}

function check_cache($key) {

    $time = time();
    $cash = Cash::where('key', 'like', '%' . $key . '%')->where('expiry', '>', $time)->first();

    if (isset($cash)) {
        return true;
    } else {
        return false;
    }
}

function update_cache($key, $rate) {

    $cash = Cash::where('key', 'like', '%' . $key . '%')->first();

    if ($cash != NULL) {

        $cash->value = $rate;
        $time = time() + 86400;
        $cash->expiry = $time;
        $cash->save();
    } else {
        $cash = new Cash;
        $cash->key = $key;
        $cash->value = $rate;
        $time = time() + 86400;
        $cash->expiry = $time;
        $cash->save();
    }
}

function currency_converted($total) {

    /* $currency_selected = Keywords::find(5);
      $currency_sel = $currency_selected->keyword; */
    $currency_sel = Config::get('app.generic_keywords.Currency');
    if ($currency_sel == '$') {
        $currency_sel = "USD";
    } else {
        $currency_sel = Config::get('app.generic_keywords.Currency');
    }
    if ($currency_sel != 'USD') {
        $check = check_cache($currency_sel);

        if (!$check) {
            $url = "https://currency-api.appspot.com/api/USD/" . $currency_sel . ".json?key=65d69f1a909b37e41272574dcd20c30fb2fbb06e";

            $result = file_get_contents($url);
            $result = json_decode($result);
            $rate = $result->rate;
            update_cache($currency_sel, $rate);
            $total = $total * $rate;
        } else {
            $rate = Cash::where('key', 'like', '%' . $currency_sel . '%')->first();
            $total = $total * $rate->value;
        }
    } else {
        $total = $total;
    }
    return $total;
}

function clean($string) {
    $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

    return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
}

function generate_token() {
    return clean(Hash::make(rand() . time() . rand()));
}

function generate_client_keys($length, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
{
    $str = '';
    $max = mb_strlen($keyspace, '8bit') - 1;
    for ($i = 0; $i < $length; ++$i) {
        $str .= $keyspace[random_int(0, $max)];
    }
    return $str;
}

function generate_expiry() {
    return time() + 3600000;
}

function convert($value, $type) {
    if ($value > 0) {
        if ($type == 1) {
            // Miles
            return $value / 1609;
        } else {
            // KM
            return $value / 1000;
        }
    } else {
        return 0;
    }
}

function is_token_active($ts) {
    if ($ts >= time()) {
        return true;
    } else {
        return false;
    }
}

function email_notification($id, $type, $message_body, $subject, $trip = null, $is_imp = null, $file_path = null, $sending_email = null) {

    /* Fix Mandrill app. For now just don't do anything */
   // return;

    $settings = Settings::where('key', 'email_notification')->first();
    $email_notification = $settings->value;
    /* echo $file_path; */
    if ($type == 'walker') {
        $user = Walker::find($id);
        $email = $user->email;
        $ccemail = 'developer@gobutterfli.com';
    } elseif ($type == 'dispatcher') {
        $user = Dispatcher::find($id);
        $email = $user->email;
        $ccemail = 'developer@gobutterfli.com';
	} elseif ($type == 'dispatcher_assigned') {
        $user = DispatcherAssigned::find($id);
        $email = $user->email;
        $ccemail = 'developer@gobutterfli.com';
    } elseif ($type == 'admin') {
        $settings = Settings::where('key', 'admin_email_address')->first();
        $email = $settings->value;
        $ccemail = 'developer@gobutterfli.com';
    } elseif ($type == 'invite') {
        $email = $id;
        $ccemail = 'developer@gobutterfli.com';
    } elseif($type == 'healthcare'){
        $user = EnterpriseClient::find($id);
        $email = $user->email;
        $ccemail = 'developer@gobutterfli.com';
    } elseif($type == 'agent'){
        $user = DispatcherAgent::find($id);
        $email = $user->email;
        $ccemail = 'developer@gobutterfli.com';
    } elseif($type == 'operator'){
        $user = EnterpriseClient::find($id);
        $email = $user->operator_email;
        $ccemail = 'developer@gobutterfli.com';
    } elseif($type == 'owner_mail'){
        $user = Owner::find($id);
        $email = $user->email;
        $ccemail = 'developer@gobutterfli.com';
    } elseif($type == 'ride_assignee'){
        $settings = Settings::where('key', 'ride_assignee_email_address')->first();
        $email = $settings->value;
        $ccemail = 'developer@gobutterfli.com';
    } elseif($type == 'ride_assignee_2'){
        $settings = Settings::where('key', 'ride_assignee_2_email_address')->first();
        $email = $settings->value;
        $ccemail = 'developer@gobutterfli.com';
    } elseif($type == 'ride_assignee_3'){
        $settings = Settings::where('key', 'ride_assignee_3_email_address')->first();
        $email = $settings->value;
        $ccemail = 'developer@gobutterfli.com';
    } elseif($type == 'ride_info'){
        $email = $sending_email;
        $ccemail = 'developer@gobutterfli.com';
    } elseif($type == 'consumer'){
        $user = Consumer::find($id);
        $email = $user->email;
        $ccemail = 'developer@gobutterfli.com';
    } elseif($type == 'certificate'){
        $user = Walker::find($id);
        $email = $user->email;
        $ccemail = 'developer@gobutterfli.com';
    }elseif($type == 'send_pdf'){
        $email = $sending_email;
        $ccemail = 'developer@gobutterfli.com';
    } else {
        //$user = Owner::find($id);
        $user = DB::table('owner')->where('id', $id)->first();
        $email = $user->email;
        $ccemail = 'developer@gobutterfli.com';
    }

    if (($email_notification == 1 || $is_imp == "imp") && $email != null) {
        if ($trip == 'invoice') {
            try {
                Mail::send('emails.invoice', array('mail_body' => $message_body), function ($message) use ($email, $subject ,$ccemail) {
                    $message->to($email)->cc($ccemail)->subject($subject);
                });
            } catch (Services_Twilio_RestException $e) {
                //Log::error($e->getMessage());
            } catch (Exception $e) {
				Log::info('email sending = ' . print_r($e->getMessage(), true));
			}
        } else if ($trip == 'Certificate') {
            try {
                Mail::send('test_certificate', array('mail_body' => $message_body), function ($message) use ($email, $subject ,$ccemail, $file_path) {
                    $message->to($email)->cc($ccemail)->subject($subject)->attach($file_path);

                });
            } catch (Services_Twilio_RestException $e) {
                //Log::error($e->getMessage());
            } catch (Exception $e) {
                \Log::info('email sending = ' . print_r($e->getMessage(), true));
            }
        } else if ($trip == 'walker_approve') {
            try {
                Mail::send('emails.approve_walker_mail', array('mail_body' => $message_body), function ($message) use ($email, $subject ,$ccemail) {
                    $message->to($email)->cc($ccemail)->subject($subject);
                });
            } catch (Services_Twilio_RestException $e) {
                //Log::error($e->getMessage());
            } catch (Exception $e) {
				\Log::info('email sending = ' . print_r($e->getMessage(), true));
			}
        } else if ($trip == 'healthcare_approve') {
            try {
                Mail::send('emails.approve_healthcare_provider_mail', array('mail_body' => $message_body), function ($message) use ($email, $subject ,$ccemail) {
                    $message->to($email)->cc($ccemail)->subject($subject);
                });
            } catch (Services_Twilio_RestException $e) {
                //Log::error($e->getMessage());
            } catch (Exception $e) {
                \Log::info('email sending = ' . print_r($e->getMessage(), true));
            }
        } else if ($trip == 'healthcare_decline') {
            try {
                Mail::send('emails.decline_healthcare_provider_mail', array('mail_body' => $message_body), function ($message) use ($email, $subject ,$ccemail) {
                    $message->to($email)->cc($ccemail)->subject($subject);
                });
            } catch (Services_Twilio_RestException $e) {
                //Log::error($e->getMessage());
            } catch (Exception $e) {
                \Log::info('email sending = ' . print_r($e->getMessage(), true));
            }
        } else if ($trip == 'walker_decline') {
            try {
                Mail::send('emails.decline_walker_mail', array('mail_body' => $message_body), function ($message) use ($email, $subject ,$ccemail) {
                    $message->to($email)->cc($ccemail)->subject($subject);
                });
            } catch (Services_Twilio_RestException $e) {
                //Log::error($e->getMessage());
            } catch (Exception $e) {
				\Log::info('email sending = ' . print_r($e->getMessage(), true));
			}
        } else if ($trip == 'forgot_password') {
            try {
                $data = array('mail_body' => $message_body);
                Mail::send('emails.reset_password', $data, function ($message) use ($email, $subject ,$ccemail)
                {
                    $message->to($email)->cc($ccemail)->subject($subject);

                });
            } catch (Services_Twilio_RestException $e) {
                //Log::error($e->getMessage());
                \Log::info('email sending = ' . print_r($e->getMessage(), true));
            } catch (Exception $e) {
                \Log::info('email sending = ' . print_r($e->getMessage(), true));
            }
        } else if ($trip == 'new_otp_mail') {
            try {
                $data = array('mail_body' => $message_body);
                Mail::send('emails.new_otp_mail', $data, function ($message) use ($email, $subject ,$ccemail)
                {
                    $message->to($email)->cc($ccemail)->subject($subject);

                });
            } catch (Services_Twilio_RestException $e) {
                //Log::error($e->getMessage());
                \Log::info('email sending = ' . print_r($e->getMessage(), true));
            } catch (Exception $e) {
                \Log::info('email sending = ' . print_r($e->getMessage(), true));
            }
        } else if ($trip == 'new_request') {
            try {
                Mail::send('emails.email_new_request', array('mail_body' => $message_body), function ($message) use ($email, $subject ,$ccemail) {
                    $message->to($email)->cc($ccemail)->subject($subject);
                });
            } catch (Services_Twilio_RestException $e) {
                \Log::info('email sending = ' . print_r($e->getMessage(), true));
            } catch (Exception $e) {
				\Log::info('email sending = ' . print_r($e->getMessage(), true));
			}
        } else if ($trip == 'new_ride_request') {
            try {
                Mail::send('emails.email_new_ride_request', array('mail_body' => $message_body), function ($message) use ($email, $subject ,$ccemail) {
                    $message->to($email)->cc($ccemail)->subject($subject);
                });
            } catch (Services_Twilio_RestException $e) {
                \Log::info('email sending = ' . print_r($e->getMessage(), true));
            } catch (Exception $e) {
                \Log::info('email sending = ' . print_r($e->getMessage(), true));
            }
        } else if ($trip == 'new_enterprise_ride_request') {
            try {
                Mail::send('emails.email_new_enterprise_ride_request', array('mail_body' => $message_body), function ($message) use ($email, $subject ,$ccemail) {
                    $message->to($email)->cc($ccemail)->subject($subject);
                });
            } catch (Services_Twilio_RestException $e) {
                \Log::info('email sending = ' . print_r($e->getMessage(), true));
            } catch (Exception $e) {
                \Log::info('email sending = ' . print_r($e->getMessage(), true));
            }
        } else if ($trip == 'request_not_answered') {
            try {
                Mail::send('emails.email_request_unanswered', array('mail_body' => $message_body), function ($message) use ($email, $subject ,$ccemail) {
                    $message->to($email)->cc($ccemail)->subject($subject);
                });
            } catch (Services_Twilio_RestException $e) {
                //Log::error($e->getMessage());
            } catch (Exception $e) {
				\Log::info('email sending = ' . print_r($e->getMessage(), true));
			}
        } else if ($trip == 'user_register') {
            try {
                Mail::send('emails.email_owner_new_register', array('mail_body' => $message_body), function ($message) use ($email, $subject ,$ccemail) {
                    $message->to($email)->cc($ccemail)->subject($subject);
                });
            } catch (Services_Twilio_RestException $e) {
                //Log::error($e->getMessage());
            } catch (Exception $e) {
				\Log::info('email sending = ' . print_r($e->getMessage(), true));
			}
        } else if ($trip == 'user_register_mail_to_admin') {
            try {
                Mail::send('emails.email_enterprise_client_new_register', array('mail_body' => $message_body), function ($message) use ($email, $subject ,$ccemail) {
                    $message->to($email)->cc($ccemail)->subject($subject);
                });
            } catch (Services_Twilio_RestException $e) {
                //Log::error($e->getMessage());
            } catch (Exception $e) {
                \Log::info('email sending = ' . print_r($e->getMessage(), true));
            }
            }else if ($trip == 'user_register_mail_to_admin_agent') {
                try {
                    Mail::send('emails.email_dispatcher_agent_new_register', array('mail_body' => $message_body), function ($message) use ($email, $subject ,$ccemail) {
                        $message->to($email)->cc($ccemail)->subject($subject);
                    });
                } catch (Services_Twilio_RestException $e) {
                    //Log::error($e->getMessage());
                } catch (Exception $e) {
                    \Log::info('email sending = ' . print_r($e->getMessage(), true));
                }
        } else if ($trip == 'walker_register') {
            try {
                Mail::send('emails.email_walker_new_register', array('mail_body' => $message_body), function ($message) use ($email, $subject ,$ccemail) {
                    $message->to($email)->cc($ccemail)->subject($subject);
                });
            } catch (Services_Twilio_RestException $e) {
                //Log::error($e->getMessage());
            } catch (Exception $e) {
				\Log::info('email sending = ' . print_r($e->getMessage(), true));
			}
        } else if ($trip == 'consumer_register') {
            try {
                Mail::send('emails.email_consumer_new_register', array('mail_body' => $message_body), function ($message) use ($email, $subject ,$ccemail) {
                    $message->to($email)->cc($ccemail)->subject($subject);
                });
            } catch (Services_Twilio_RestException $e) {
                //Log::error($e->getMessage());
            } catch (Exception $e) {
                \Log::info('email sending = ' . print_r($e->getMessage(), true));
            }
        } else if ($trip == 'pay_charged') {
            try {
                Mail::send('emails.email_payment_charged', array('mail_body' => $message_body), function ($message) use ($email, $subject ,$ccemail) {
                    $message->to($email)->cc($ccemail)->subject($subject);
                });
            } catch (Services_Twilio_RestException $e) {
                //Log::error($e->getMessage());
            } catch (Exception $e) {
				\Log::info('email sending = ' . print_r($e->getMessage(), true));
			}
        } else if ($trip == 'pre_payment') {
            try {
                Mail::send('emails.email_payment_made_client', array('mail_body' => $message_body), function ($message) use ($email, $subject ,$ccemail) {
                    $message->to($email)->cc($ccemail)->subject($subject);
                });
            } catch (Services_Twilio_RestException $e) {
                //Log::error($e->getMessage());
            } catch (Exception $e) {
				\Log::info('email sending = ' . print_r($e->getMessage(), true));
			}
        } else if ($trip == 'accept_request') {
            try {
                Mail::send('emails.email_owner_request_accept_by_driver', array('mail_body' => $message_body), function ($message) use ($email, $subject ,$ccemail) {
                    $message->to($email)->cc($ccemail)->subject($subject);
                });
            } catch (Services_Twilio_RestException $e) {
                //Log::error($e->getMessage());
            } catch (Exception $e) {
				\Log::info('email sending = ' . print_r($e->getMessage(), true));
			}
        } else if ($trip == 'invite') {
            try {
                if ($file_path == null) {
                    Mail::send('emails.email_user_invite_event', array('mail_body' => $message_body), function ($message) use ($email, $subject ,$ccemail) {
                        $message->to($email)->cc($ccemail)->subject($subject);
                    });
                } else {
                    Mail::send('emails.email_user_invite_event_1', array('mail_body' => 'Invitation Code in attached file.'), function ($message) use ($email, $subject, $file_path ,$ccemail) {
                        $message->to($email)->cc($ccemail)->subject($subject)->attach($file_path);
                    });
                }
            } catch (Services_Twilio_RestException $e) {
                //Log::error($e->getMessage());
            } catch (Exception $e) {
				\Log::info('email sending = ' . print_r($e->getMessage(), true));
			}
        } elseif($trip == 'request_not_accepted'){
			try {
                Mail::send('emails.email_user_request_not_accept', array('mail_body' => $message_body), function ($message) use ($email, $subject ,$ccemail) {
                    $message->to($email)->cc($ccemail)->subject($subject);
                });
            } catch (Services_Twilio_RestException $e) {
                //Log::error($e->getMessage());
            } catch (Exception $e) {
				\Log::info('email sending = ' . print_r($e->getMessage(), true));
			}
		} else if ($trip == 'ride_confirm') {
            try {
                Mail::send('emails.email_confirm_ride', array('mail_body' => $message_body), function ($message) use ($email, $subject ,$ccemail) {
                    $message->to($email)->cc($ccemail)->subject($subject);
                });
            } catch (Services_Twilio_RestException $e) {
                \Log::info('email sending = ' . print_r($e->getMessage(), true));
            } catch (Exception $e) {
                \Log::info('email sending = ' . print_r($e->getMessage(), true));
            }
        } else if ($trip == 'ride_cancel') {
            try {
                Mail::send('emails.email_cancel_ride', array('mail_body' => $message_body), function ($message) use ($email, $subject ,$ccemail) {
                    $message->to($email)->cc($ccemail)->subject($subject);
                });
            } catch (Services_Twilio_RestException $e) {
                \Log::info('email sending = ' . print_r($e->getMessage(), true));
            } catch (Exception $e) {
                \Log::info('email sending = ' . print_r($e->getMessage(), true));
            }
        } else if ($trip == 'tp_ride_cancel') {
            try {
                Mail::send('emails.email_cancel_ride_request_TP', array('mail_body' => $message_body), function ($message) use ($email, $subject ,$ccemail) {
                    $message->to($email)->cc($ccemail)->subject($subject);
                });
            } catch (Services_Twilio_RestException $e) {
                \Log::info('email sending = ' . print_r($e->getMessage(), true));
            } catch (Exception $e) {
                \Log::info('email sending = ' . print_r($e->getMessage(), true));
            }
        } else if ($trip == 'ride_complete') {
            try {
                Mail::send('emails.email_complete_ride', array('mail_body' => $message_body), function ($message) use ($email, $subject ,$ccemail) {
                    $message->to($email)->cc($ccemail)->subject($subject);
                });
            } catch (Services_Twilio_RestException $e) {
                \Log::info('email sending = ' . print_r($e->getMessage(), true));
            } catch (Exception $e) {
                \Log::info('email sending = ' . print_r($e->getMessage(), true));
            }
        } else if ($trip == 'ride_information') {
            try {
                Mail::send('emails.email_ride_info', array('mail_body' => $message_body), function ($message) use ($email, $subject ,$ccemail) {
                    $message->to($email)->cc($ccemail)->subject($subject);
                });
            } catch (Services_Twilio_RestException $e) {
                \Log::info('email sending = ' . print_r($e->getMessage(), true));
            } catch (Exception $e) {
                \Log::info('email sending = ' . print_r($e->getMessage(), true));
            }
        }else if ($trip == 'sending_pdf') {
            try {
                Mail::send('sending_fee_pdf', array('mail_body' => $message_body), function ($message) use ($email, $subject ,$ccemail, $file_path) {
                    $message->to($email)->cc($ccemail)->subject($subject)->attach($file_path);

                });
            } catch (Services_Twilio_RestException $e) {
                //Log::error($e->getMessage());
            } catch (Exception $e) {
                \Log::info('email sending = ' . print_r($e->getMessage(), true));
            }
        }  else {
            try {
                Mail::send('emails.layout', array('mail_body' => $message_body), function ($message) use ($email, $subject ,$ccemail) {
                    $message->to($email)->cc($ccemail)->subject($subject);
                });
            } catch (Exception $e) {
                //Log::error($e->getMessage());
            } catch (Exception $e) {
				\Log::info('email sending = ' . print_r($e->getMessage(), true));
			}
        }
    } else {
        if ($subject == 'forgotpassword' or $subject == 'Your New Password') {
            //Log::info('Forget password mail.');
            Mail::send('emails.layout', array('mail_body' => $message_body), function ($message) use ($email, $subject ,$ccemail) {
                $message->to($email)->cc($ccemail)->subject($subject);
            });
        }
        //Log::info('Mail turned off.');
    }
}

function send_email($id, $type, $email_data, $subject, $email_type) {

    $settings = Settings::where('key', 'email_notification')->first();
    $email_notification = $settings->value;
    if ($type == 'walker') {
        $user = Walker::find($id);
        $email = $user->email;
        // dd($email);
    } elseif ($type == 'admin') {
        $settings = Settings::where('key', 'admin_email_address')->first();
        $email = $settings->value;
        //dd($email);
    } else {
        $user = Owner::find($id);
        $email = $user->email;
        //  dd($email);
    }
    if ($email_notification == 1) {

        try {
            //  dd($email);
            if ($email_type == "invoice") {
                Mail::send('emails.invoice', array('email_data' => $email_data), function ($message) use ($email, $subject) {
                    $message->to($email)->subject($subject);
                });
            } else if ($email_type == 'userregister') {
                Mail::send('emails.userregister', array('email_data' => $email_data), function ($message) use ($email, $subject) {
                    $message->to($email)->subject($subject);
                });
            } else if ($email_type == 'providerregister') {
                Mail::send('emails.providerregister', array('email_data' => $email_data), function ($message) use ($email, $subject) {
                    $message->to($email)->subject($subject);
                });
            } else if ($email_type == 'forgotpassword') {
                Mail::send('emails.forgotpassword', array('email_data' => $email_data), function ($message) use ($email, $subject) {
                    $message->to($email)->subject($subject);
                });
            } else {
                Mail::send('emails.layout', array('mail_body' => $message_body), function ($message) use ($email, $subject) {
                    $message->to($email)->subject($subject);
                });
            }
            // dd('yoyo');
        } catch (Exception $e) {
            //Log::error($e->getMessage());
        }
    }
}

function send_eta_email($email, $message_body, $subject) {

    $settings = Settings::where('key', 'email_notification')->first();
    $email_notification = $settings->value;

    if ($email_notification == 1) {

        try {
            //  dd($email);
            Mail::send('emails.layout', array('mail_body' => $message_body), function ($message) use ($email, $subject) {
                $message->to($email)->subject($subject);
            });

            // dd('yoyo');
        } catch (Exception $e) {
            //Log::error($e->getMessage());
        }
    }
}

function send_ride_request_notifications($request, $params) {
    // Send SMS
    $settings = Settings::where('key', 'sms_request_created')->first();
    $pattern = $settings->value;
    $pattern = str_replace('%user%', $params['passenger_contact_name'], $pattern);
    $pattern = str_replace('%id%', $request->id, $pattern);
    $pattern = str_replace('%user_mobile%', $params['passenger_phone'], $pattern);
    $pattern = str_replace('%pickup_address%', $request->src_address, $pattern);
    $pattern = str_replace('%dropoff_address%', $request->dest_address, $pattern);
    $pattern = str_replace('%start_app_link%', '', $pattern);
    
    if($params['has_wheelchair'] == 1) {
        $pattern .= " . Wheelchair Equipment Requested.";
    }

    if($params['has_attendant'] == 1) {
        $pattern .= " Attendant Information.";
        $pattern .= " Attendant Name: " . $params['attendant_contact_name'];
        $pattern .= " Attendant Phone: " . $params['attendant_phone'];
    }

    $the_id = 0;
    $the_sms_type = "";
    $the_email_type = "";
    if($params['user_select'] == 1) {
        // enterprise client
        $the_id = $request->healthcare_id;
        $the_sms_type = 'operator';
        $the_email_type = 'operator';
    }
    else {
        $the_id = $request->owner_id;
        $the_sms_type = 'owner_sms';
        $the_email_type = 'owner_mail';
    }
 
    sms_notification($the_id, $the_sms_type, $pattern);
    sms_notification(NULL, 'ride_assignee_1', $pattern);
    sms_notification(NULL, 'ride_assignee_2', $pattern);
    sms_notification(NULL, 'ride_assignee_3', $pattern);

    $subject = $params['subject'];

    email_notification($the_id, $the_email_type, $params, $subject, 'new_enterprise_ride_request', null);
    email_notification(NULL, 'ride_assignee', $params, $subject, 'new_enterprise_ride_request', null);
    email_notification(NULL, 'ride_assignee_2', $params, $subject, 'new_enterprise_ride_request', null);
    email_notification(NULL, 'ride_assignee_3', $params, $subject, 'new_enterprise_ride_request', null);
}

function sms_notification($id, $type, $message, $phone_no = null) {
    $settings = Settings::where('key', 'sms_notification')->first();
    $sms_notification = $settings->value;
    $phone = NULL;
    if ($sms_notification != 1) {
        // not configured for SMS notifications
        return;
    }

    if ($type == 'walker') {
        //$user = Walker::find($id);
        $user = DB::table('walker')->where('id', $id)->first();
        $phone = $user->phone;
    }
    elseif ($type == 'admin') {
        $settings = Settings::where('key', 'admin_phone_number')->first();
        $phone = $settings->value;
    }
    elseif ($type == 'dispatcher_assigned') {
        $user = DB::table('dispatcher_assigned')->where('id', $id)->first();
        $phone = $user->phone;
    }
    elseif ($type == 'operator') {
        $user = EnterpriseClient::find($id);
        $phone = $user->operator_phone;
    }
    elseif ($type == 'owner_sms') {
        $user = Owner::find($id);
        $phone = $user->phone;
    }
    elseif ($type == 'ride_assignee_1') {
        $settings = Settings::where('key', 'ride_assignee_phone_number')->first();
        $phone = $settings->value;
    }
    elseif ($type == 'ride_assignee_2') {
        $settings = Settings::where('key', 'ride_assignee_2_phone_number')->first();
        $phone = $settings->value;
    }
    elseif ($type == 'ride_assignee_3') {
        $settings = Settings::where('key', 'ride_assignee_3_phone_number')->first();
        $phone = $settings->value;
    }
    elseif ($type == 'ride_info') {
        $phone = $phone_no;
    }
    elseif ($type == 'manual_driver') {
        $phone = $phone_no;
    }
    elseif ($type == 'dispatcher') {
        $user = DB::table('dispatcher')->where('id', $id)->first();
        $phone = $user->phone;
    }
    elseif ($type == 'healthcare') {
        $user = DB::table('enterprise_client')->where('id', $id)->first();
        $phone = $user->operator_phone;
    }
    elseif ($type == 'healthcare_agent') {
        $user = DB::table('dispatcher_agent')->where('id', $id)->first();
        $phone = $user->phone;
    }
    else {
        $user = Owner::find($id);
        //$user = DB::table('owner')->where('id', $id)->first();
        if($user != NULL) {
            $phone = $user->phone;
        }
    }

    if($phone != NULL && strlen($phone) > 0) {
        $AccountSid = Config::get('app.twillo_account_sid');
        $AuthToken = Config::get('app.twillo_auth_token');
        $twillo_number = Config::get('app.twillo_number');

        $client = new Services_Twilio($AccountSid, $AuthToken);

        try {
            $messages = $client->account->messages->create(
                array(
                    "From" => $twillo_number,
                    "To" => $phone,
                    "Body" => $message,
                )
            );
        }
        catch (Exception $e) {
            Log::info('error = ' . print_r($e->getMessage(), true));
            Log::error($e->getMessage());
        }
    }
}

function send_eta($phone, $message) {
    $settings = Settings::where('key', 'sms_notification')->first();
    $sms_notification = $settings->value;



    if ($sms_notification == 1) {

        $AccountSid = Config::get('app.twillo_account_sid');
        $AuthToken = Config::get('app.twillo_auth_token');
        $twillo_number = Config::get('app.twillo_number');

        $client = new Services_Twilio($AccountSid, $AuthToken);

        try {
            $message = $client->account->messages->create(array(
                "From" => $twillo_number,
                "To" => $phone,
                "Body" => $message,
            ));
        } catch (Services_Twilio_RestException $e) {
            //Log::error($e->getMessage());
        }
    }
}

/* from HelloController it jumps to the test_ios_noti() */

function test_ios_noti($id, $type, $title, $message) {
    /* $deviceTokens = array("11F1530C543DA98EF4BC013D28FF91B4906BE0EA0523DD4B0A04732CC91B4570"); */ /* ckUberForXOwner.pem token */
    $deviceTokens = array($id); /* ckUberForXProvider.pem token */
    send_ios_push2($deviceTokens, $title, $message, $type);
}

function send_notifications($id, $type, $title, $message, $is_imp = NULL) {
    //Log::info('push notification');
    $settings = Settings::where('key', 'push_notification')->first();
    $push_notification = $settings->value;

    if ($type == 'walker') {
        $user = DB::table('walker')->where('id', $id)->first();
    } elseif($type == 'dispatcher_assigned') {
        $user = DB::table('dispatcher_assigned')->where('id', $id)->first();
    } elseif($type == 'owner'){
        $user = DB::table('owner')->where('id', $id)->first();
    }

    if ($push_notification == 1 || $is_imp == "imp") {
        if ($user->device_type == 'ios') {
            /* WARNING:- you can't pass devicetoken as string in GCM or IOS push
             * you have to pass array of devicetoken even thow it's only one device's token. */
            /* send_ios_push("E146C7DCCA5EBD49803278B3EE0C1825EF0FA6D6F0B1632A19F783CB02B2617B",$title,$message,$type); */
            send_ios_push($user->device_token, $title, $message, $type);
        } else {

            $message = json_encode($message);

            send_android_push($user->device_token, $title, $message);
        }
    }
}

function send_ios_push($user_id, $title, $message, $type) {
    if ($type == 'walker') {
        include_once 'ios_push/walker/apns.php';

        $msg = array("alert" => $title,
            "status" => "success",
            "title" => $title,
            "message" => $message,
            "badge" => 1,
            "sound" => "default");

        if (!isset($user_id) || empty($user_id)) {
            $deviceTokens = array();
        } else {
            $deviceTokens = array(trim($user_id));
        }

        $apns = new ProviderApns();
        $apns->send_notification($deviceTokens, $msg);
    } else {
        include_once 'ios_push/apns.php';

        $msg = array("alert" => $title,
            "status" => "success",
            "title" => $title,
            "message" => $message,
            "badge" => 1,
            "sound" => "default");

        if (!isset($user_id) || empty($user_id)) {
            $deviceTokens = array();
        } else {
            $deviceTokens = array(trim($user_id));
        }

        $apns = new ClientApns();
        $apns->send_notification($deviceTokens, $msg);
    }
    /* normally we have to send three perameters to ios device which are "alert","badge","sound", if it is not in aps{} object then push will not deliver.
     * in this array just add that veriable which's text in to "alert" you want to display in device screen as a notification
     * "status" is my strategy to display success or Filear or push data
     * "title" is a string which is send as a push string and i hed put it in this perameter because if ios developer wants that message then ios developer can get it from here
     * "messsage" is a bulk of data which is send from database
     *
     * don't concat title & message in alert if not required.
     *
     * if you want ot check the json will be proper or not then you can echo "$payload" variable which is generated in "apns.php"
     * and if you git is as a perfect json then only push data is perfect and may be send to device.
     *
     * i use "may" word in my sentence because if you hed made any mistake like devicetoken will not array if dubble jsonencode or etc then also it will not work.
     *
     * if in push you will not send perfect json then also it will not deliver to device
     * EXAMPLE of perfect json for ios push (formate taken from your "create_request" code. and also I put a comment in it. after formated array)
     *
      {
      "aps":{
      "alert":"message",
      "title":"title",
      "badge":1,
      "sound":"default",
      "message":{
      "unique_id":1,
      "request_id":2,
      "time_left_to_respond":"12 minutes",
      "request_data":{
      "owner":{
      "name":"first name last name",
      "picture":"picture",
      "phone":"+919876543210",
      "address":"address",
      "latitude":"22",
      "longitude":"77",
      "rating":1,
      "num_rating":1
      },
      "dog":{
      "name":"dog_name",
      "age":"dog_age",
      "breed":"dog_breed",
      "likes":"dog_likes",
      "picture":"dog_image"
      }
      }
      }
      }
      }
     */
}

function send_ios_push2($user_id, $title, $message, $type) {
    if ($type == 'walker') {
        include_once 'ios_push/walker/apns.php';
        $apns = new ProviderApns();
    } else {
        include_once 'ios_push/apns.php';
        $apns = new ClientApns();
    }
    $msg = array("alert" => "" . $title,
        "status" => "success",
        "title" => $title,
        "message" => $message,
        "badge" => 1,
        "sound" => "default");

    if (!isset($user_id) || empty($user_id)) {
        $deviceTokens = array();
    } else {
        /* here not required to make it array, it's already an array. If we assign it as an array then it will be array in array and it will not work while it pass to apns file. */
        /* to check whether it is array or variable then you can uncomment all echo's from apns files
          now from http://54.148.195.44/test we can get the push to our company's device as I had made changes.
         */
        $deviceTokens = $user_id;
    }

    $apns->send_notification($deviceTokens, $msg);
}

function send_android_push($user_id, $message, $title) {
    require_once 'gcm/GCM_1.php';
    /* require_once 'gcm/const.php'; */

    if (!isset($user_id) || empty($user_id)) {
        $registatoin_ids = "0";
    } else {
        $registatoin_ids = trim($user_id);
    }
    if (!isset($message) || empty($message)) {
        $msg = "Message not set";
    } else {
        $msg = trim($message);
    }
    if (!isset($title) || empty($title)) {
        $title1 = "Message not set";
    } else {
        $title1 = trim($title);
    }

    /* $message = array(TEAM => $title1, MESSAGE => $msg); */
    $message = array('team' => $title1, 'message' => $msg);

    $gcm = new GCM();
    $registatoin_ids = array($registatoin_ids);
    $gcm->send_notification($registatoin_ids, $message);
}

function asset_url() {
    return URL::to('../');
}

function web_url() {
    return URL::to('/');
}

function generate_db_config($host, $username, $password, $database) {
    return "<?php

return array(

    /*
    |--------------------------------------------------------------------------
    | PDO Fetch Style
    |--------------------------------------------------------------------------
    |
    | By default, database results will be returned as instances of the PHP
    | stdClass object; however, you may desire to retrieve records in an
    | array format for simplicity. Here you can tweak the fetch style.
    |
    */

    'fetch' => PDO::FETCH_CLASS,

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => 'mysql',

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examp les of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => array(

        'sqlite' => array(
            'driver'   => 'sqlite',
            'database' => __DIR__.'/../database/production.sqlite',
            'prefix'   => '',
        ),

        'mysql' => array(
            'driver'    => 'mysql',
            'host'      => '$host',
            'database'  => '$database',
            'username'  => '$username',
            'password'  => '$password',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ),

        'pgsql' => array(
            'driver'   => 'pgsql',
            'host'     => 'localhost',
            'database' => 'forge',
            'username' => 'forge',
            'password' => '',
            'charset'  => 'utf8',
            'prefix'   => '',
            'schema'   => 'public',
        ),

        'sqlsrv' => array(
            'driver'   => 'sqlsrv',
            'host'     => 'localhost',
            'database' => 'database',
            'username' => 'root',
            'password' => '',
            'prefix'   => '',
        ),

    ),

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer set of commands than a typical key-value systems
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => array(

        'cluster' => false,

        'default' => array(
            'host'     => '127.0.0.1',
            'port'     => 6379,
            'database' => 0,
        ),

    ),

);
";
}

function generate_generic_page_layout($body) {

    return "@extends('website.layout')

    @section('content')
        $body
    @stop

";
}

function generate_app_config($braintree_cse, $stripe_publishable_key, $url, $timezone, $website_title, $s3_bucket, $twillo_account_sid, $twillo_auth_token, $twillo_number, $default_payment, $stripe_secret_key, $braintree_environment, $braintree_merchant_id, $braintree_public_key, $braintree_private_key, $customer_certy_url = null, $customer_certy_pass = null, $customer_certy_type = null, $provider_certy_url = null, $provider_certy_pass = null, $provider_certy_type = null, $gcm_browser_key = null, $key_provider = null, $key_user = null, $key_taxi = null, $key_trip = null, $key_currency = null, $total_trip = null, $cancelled_trip = null, $total_payment = null, $completed_trip = null, $card_payment = null, $credit_payment = null, $key_ref_pre = null, $android_client_app_url = null, $android_provider_app_url = null, $ios_client_app_url = null, $ios_provider_app_url = null, $cash_payment = null, $promotional_payment = null, $schedules_icon = null, $dev_comp_name = null, $dev_web_link = null, $dev_email = null, $dev_fb_link = null, $dev_twit_link = null) {
    if ($dev_comp_name != null) {
        $dev_comp_name = $dev_comp_name;
    } else {
        $dev_comp_name = trim(Config::get('app.developer_company_name'));
    }
    if ($dev_web_link != null) {
        $dev_web_link = $dev_web_link;
    } else {
        $dev_web_link = trim(Config::get('app.developer_company_web_link'));
    }
    if ($dev_email != null) {
        $dev_email = $dev_email;
    } else {
        $dev_email = trim(Config::get('app.developer_company_email'));
    }
    if ($dev_fb_link != null) {
        $dev_fb_link = $dev_fb_link;
    } else {
        $dev_fb_link = trim(Config::get('app.developer_company_fb_link'));
    }
    if ($dev_twit_link != null) {
        $dev_twit_link = $dev_twit_link;
    } else {
        $dev_twit_link = trim(Config::get('app.developer_company_twitter_link'));
    }
    if ($key_ref_pre != null) {
        $key_ref_pre = $key_ref_pre;
    } else {
        $key_ref_pre = Config::get('app.referral_prefix');
    }
    if ($cash_payment != null) {
        $cash_payment = $cash_payment;
    } else {
        $cash_payment = Config::get('app.generic_keywords.cash_payment');
    }
    if ($promotional_payment != null) {
        $promotional_payment = $promotional_payment;
    } else {
        $promotional_payment = Config::get('app.generic_keywords.promotional_payment');
    }
    if ($schedules_icon != null) {
        $schedules_icon = $schedules_icon;
    } else {
        $schedules_icon = Config::get('app.generic_keywords.schedules_icon');
    }
    if ($key_provider != null) {
        $key_provider = $key_provider;
    } else {
        $key_provider = Config::get('app.generic_keywords.Provider');
    }
    if ($key_user != null) {
        $key_user = $key_user;
    } else {
        $key_user = Config::get('app.generic_keywords.User');
    }
    if ($key_taxi != null) {
        $key_taxi = $key_taxi;
    } else {
        $key_taxi = Config::get('app.generic_keywords.Services');
    }
    if ($key_trip != null) {
        $key_trip = $key_trip;
    } else {
        $key_trip = Config::get('app.generic_keywords.Trip');
    }
    if ($key_currency != null) {
        $key_currency = $key_currency;
    } else {
        $key_currency = Config::get('app.generic_keywords.Currency');
    }
    if ($total_trip != null) {
        $total_trip = $total_trip;
    } else {
        $total_trip = Config::get('app.generic_keywords.total_trip');
    }
    if ($cancelled_trip != null) {
        $cancelled_trip = $cancelled_trip;
    } else {
        $cancelled_trip = Config::get('app.generic_keywords.cancelled_trip');
    }
    if ($total_payment != null) {
        $total_payment = $total_payment;
    } else {
        $total_payment = Config::get('app.generic_keywords.total_payment');
    }
    if ($completed_trip != null) {
        $completed_trip = $completed_trip;
    } else {
        $completed_trip = Config::get('app.generic_keywords.completed_trip');
    }
    if ($card_payment != null) {
        $card_payment = $card_payment;
    } else {
        $card_payment = Config::get('app.generic_keywords.card_payment');
    }
    if ($credit_payment != null) {
        $credit_payment = $credit_payment;
    } else {
        $credit_payment = Config::get('app.generic_keywords.credit_payment');
    }
    /* $customer_certy_url = null, $customer_certy_pass = null, $customer_certy_type = null, $provider_certy_url = null, $provider_certy_pass = null, $provider_certy_type = null, $gcm_browser_key = null */
    if ($customer_certy_url != null) {
        $customer_certy_url = $customer_certy_url;
    } else {
        $customer_certy_url = Config::get('app.customer_certy_url');
    }
    if ($customer_certy_pass != null) {
        $customer_certy_pass = $customer_certy_pass;
    } else {
        $customer_certy_pass = Config::get('app.customer_certy_pass');
    }
    if ($customer_certy_type != null) {
        $customer_certy_type = $customer_certy_type;
    } else {
        $customer_certy_type = Config::get('app.customer_certy_type');
    }
    if ($provider_certy_url != null) {
        $provider_certy_url = $provider_certy_url;
    } else {
        $provider_certy_url = Config::get('app.provider_certy_url');
    }
    if ($provider_certy_pass != null) {
        $provider_certy_pass = $provider_certy_pass;
    } else {
        $provider_certy_pass = Config::get('app.provider_certy_pass');
    }
    if ($provider_certy_type != null) {
        $provider_certy_type = $provider_certy_type;
    } else {
        $provider_certy_type = Config::get('app.provider_certy_type');
    }
    if ($gcm_browser_key != null) {
        $gcm_browser_key = $gcm_browser_key;
    } else {
        $gcm_browser_key = Config::get('app.gcm_browser_key');
    }
    if ($android_client_app_url != null) {
        $android_client_app_url = $android_client_app_url;
    } else {
        $android_client_app_url = Config::get('app.android_client_app_url');
    }
    if ($android_provider_app_url != null) {
        $android_provider_app_url = $android_provider_app_url;
    } else {
        $android_provider_app_url = Config::get('app.android_provider_app_url');
    }
    if ($ios_client_app_url != null) {
        $ios_client_app_url = $ios_client_app_url;
    } else {
        $ios_client_app_url = Config::get('app.ios_client_app_url');
    }
    if ($ios_provider_app_url != null) {
        $ios_provider_app_url = $ios_provider_app_url;
    } else {
        $ios_provider_app_url = Config::get('app.ios_provider_app_url');
    }

    return "<?php

return array(

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => FALSE,

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url' => '$url',

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => '$timezone',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => '" . Config::get('app.locale') . "',

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => '" . Config::get('app.fallback_locale') . "',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => '" . Config::get('app.key') . "',

    'cipher' => MCRYPT_RIJNDAEL_128,

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => array(

        'Illuminate\Foundation\Providers\ArtisanServiceProvider',
        'Illuminate\Auth\AuthServiceProvider',
        'Illuminate\Cache\CacheServiceProvider',
        'Illuminate\Session\CommandsServiceProvider',
        'Illuminate\Foundation\Providers\ConsoleSupportServiceProvider',
        'Illuminate\Routing\ControllerServiceProvider',
        'Illuminate\Cookie\CookieServiceProvider',
        'Illuminate\Database\DatabaseServiceProvider',
        'Illuminate\Encryption\EncryptionServiceProvider',
        'Illuminate\Filesystem\FilesystemServiceProvider',
        'Illuminate\Hashing\HashServiceProvider',
        'Illuminate\Html\HtmlServiceProvider',
        'Illuminate\Log\LogServiceProvider',
        'Illuminate\Mail\MailServiceProvider',
        'Illuminate\Database\MigrationServiceProvider',
        'Illuminate\Pagination\PaginationServiceProvider',
        'Illuminate\Queue\QueueServiceProvider',
        'Illuminate\Redis\RedisServiceProvider',
        'Illuminate\Remote\RemoteServiceProvider',
        'Illuminate\Auth\Reminders\ReminderServiceProvider',
        'Illuminate\Database\SeedServiceProvider',
        'Illuminate\Session\SessionServiceProvider',
        'Illuminate\Translation\TranslationServiceProvider',
        'Illuminate\Validation\ValidationServiceProvider',
        'Illuminate\View\ViewServiceProvider',
        'Illuminate\Workbench\WorkbenchServiceProvider',
        'Aws\Laravel\AwsServiceProvider',
        'Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider',
        'Way\Generators\GeneratorsServiceProvider',
        'Raahul\LarryFour\LarryFourServiceProvider',
        'Davibennun\LaravelPushNotification\LaravelPushNotificationServiceProvider',
        'Intervention\Image\ImageServiceProvider',
        'Barryvdh\DomPDF\ServiceProvider',
    ),

    /*
    |--------------------------------------------------------------------------
    | Service Provider Manifest
    |--------------------------------------------------------------------------
    |
    | The service provider manifest is used by Laravel to lazy load service
    | providers which are not needed for each request, as well to keep a
    | list of all of the services. Here, you may set its storage spot.
    |
    */

    'manifest' => storage_path().'/meta',

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are lazy loaded so they don't hinder performance.
    |
    */

    'aliases' => array(

        'App'               => 'Illuminate\Support\Facades\App',
        'Artisan'           => 'Illuminate\Support\Facades\Artisan',
        'Auth'              => 'Illuminate\Support\Facades\Auth',
        'Blade'             => 'Illuminate\Support\Facades\Blade',
        'Cache'             => 'Illuminate\Support\Facades\Cache',
        'ClassLoader'       => 'Illuminate\Support\ClassLoader',
        'Config'            => 'Illuminate\Support\Facades\Config',
        'Controller'        => 'Illuminate\Routing\Controller',
        'Cookie'            => 'Illuminate\Support\Facades\Cookie',
        'Crypt'             => 'Illuminate\Support\Facades\Crypt',
        'DB'                => 'Illuminate\Support\Facades\DB',
        'Eloquent'          => 'Illuminate\Database\Eloquent\Model',
        'Event'             => 'Illuminate\Support\Facades\Event',
        'File'              => 'Illuminate\Support\Facades\File',
        'Form'              => 'Illuminate\Support\Facades\Form',
        'Hash'              => 'Illuminate\Support\Facades\Hash',
        'HTML'              => 'Illuminate\Support\Facades\HTML',
        'Input'             => 'Illuminate\Support\Facades\Input',
        'Lang'              => 'Illuminate\Support\Facades\Lang',
        'Log'               => 'Illuminate\Support\Facades\Log',
        'Mail'              => 'Illuminate\Support\Facades\Mail',
        'Paginator'         => 'Illuminate\Support\Facades\Paginator',
        'Password'          => 'Illuminate\Support\Facades\Password',
        'Queue'             => 'Illuminate\Support\Facades\Queue',
        'Redirect'          => 'Illuminate\Support\Facades\Redirect',
        'Redis'             => 'Illuminate\Support\Facades\Redis',
        'Request'           => 'Illuminate\Support\Facades\Request',
        'Response'          => 'Illuminate\Support\Facades\Response',
        'Route'             => 'Illuminate\Support\Facades\Route',
        'Schema'            => 'Illuminate\Support\Facades\Schema',
        'Seeder'            => 'Illuminate\Database\Seeder',
        'Session'           => 'Illuminate\Support\Facades\Session',
        'SoftDeletingTrait' => 'Illuminate\Database\Eloquent\SoftDeletingTrait',
        'SSH'               => 'Illuminate\Support\Facades\SSH',
        'Str'               => 'Illuminate\Support\Str',
        'URL'               => 'Illuminate\Support\Facades\URL',
        'Validator'         => 'Illuminate\Support\Facades\Validator',
        'View'              => 'Illuminate\Support\Facades\View',
        'AWS' => 'Aws\Laravel\AwsFacade',
        'PushNotification' => 'Davibennun\LaravelPushNotification\Facades\PushNotification',
        'Image' => 'Intervention\Image\Facades\Image',
        'PDF' => 'Barryvdh\DomPDF\Facade',
    ),
    'menu_titles' => array(
        'admin_control' => '" . Config::get('app.menu_titles.admin_control') . "',
        'income_history' => '" . Config::get('app.menu_titles.income_history') . "',
        'log_out' => '" . Config::get('app.menu_titles.log_out') . "',
        'dashboard' => '" . Config::get('app.menu_titles.dashboard') . "',
        'map_view' => '" . Config::get('app.menu_titles.map_view') . "',
        'providers' => '" . Config::get('app.menu_titles.providers') . "',
        'requests' => '" . Config::get('app.menu_titles.requests') . "',
        'customers' => '" . Config::get('app.menu_titles.customers') . "',
        'reviews' => '" . Config::get('app.menu_titles.reviews') . "',
        'information' => '" . Config::get('app.menu_titles.information') . "',
        'types' => '" . Config::get('app.menu_titles.types') . "',
        'documents' => '" . Config::get('app.menu_titles.documents') . "',
        'settings' => '" . Config::get('app.menu_titles.settings') . "',
        'balance' => '" . Config::get('app.menu_titles.balance') . "',
        'create_request' => '" . Config::get('app.menu_titles.create_request') . "',
        'promotional_codes' => '" . Config::get('app.menu_titles.promotional_codes') . "',
    ),
    'generic_keywords'=> array(
        'Provider' => '$key_provider',
        'User' => '$key_user',
        'Services' => '$key_taxi',
        'Trip' => '$key_trip',
        'Currency' => '$key_currency',
        'total_trip' => '$total_trip',
        'cancelled_trip' => '$cancelled_trip',
        'total_payment' => '$total_payment',
        'completed_trip' => '$completed_trip',
        'card_payment' => '$card_payment',
        'credit_payment' => '$credit_payment',
        'cash_payment' => '$cash_payment',
        'promotional_payment' => '$promotional_payment',
        'schedules_icon' => '$schedules_icon',
    ),
    /* DEVICE PUSH NOTIFICATION DETAILS */
    'customer_certy_url' => '" . $customer_certy_url . "',
    'customer_certy_pass' => '" . $customer_certy_pass . "',
    'customer_certy_type' => '" . $customer_certy_type . "',
    'provider_certy_url' => '" . $provider_certy_url . "',
    'provider_certy_pass' => '" . $provider_certy_pass . "',
    'provider_certy_type' => '" . $provider_certy_type . "',
    'gcm_browser_key' => '" . $gcm_browser_key . "',
    /* DEVICE PUSH NOTIFICATION DETAILS END */
    'currency_symb' => '$key_currency', 
    
    /* Developer Company Details */
    'developer_company_name' => '" . $dev_comp_name . "',
    'developer_company_web_link' => '" . $dev_web_link . "', 
    'developer_company_email' => '" . $dev_email . "', 
    'developer_company_fb_link' => '" . $dev_fb_link . "', 
    'developer_company_twitter_link' => '" . $dev_twit_link . "',
    /* Developer Company Details END */
    
    /* APP LINK DATA */
    'android_client_app_url'=>'" . $android_client_app_url . "',
    'android_provider_app_url'=>'" . $android_provider_app_url . "',
    'ios_client_app_url'=>'" . $ios_client_app_url . "',
    'ios_provider_app_url'=>'" . $ios_provider_app_url . "',
    /* APP LINK DATA END */
    
    'no_data_available' => '" . Config::get('app.no_data_available') . "', 
    'data_not_available' => '" . Config::get('app.data_not_available') . "', 
    'blank_fiend_val' => '" . Config::get('app.blank_fiend_val') . "',

    'website_title' => '$website_title',
    'referral_prefix' => '$key_ref_pre',
    'datenow'=>'" . Config::get('app.datenow') . "',
    'appdate'=>'" . Config::get('app.appdate') . "',
    'referral_zero_len' => " . Config::get('app.referral_zero_len') . ",
    'website_meta_description' => '" . Config::get('app.website_meta_description') . "',
    'website_meta_keywords' => '" . Config::get('app.website_meta_keywords') . "',

    's3_bucket' => '$s3_bucket',

    'twillo_account_sid' => '$twillo_account_sid',
    'twillo_auth_token' => '$twillo_auth_token',
    'twillo_number' => '$twillo_number',

    'production' => false,

    'default_payment' => '$default_payment',

    'stripe_secret_key' => '$stripe_secret_key',
    'stripe_publishable_key' => '$stripe_publishable_key',
    'braintree_environment' => '$braintree_environment',
    'braintree_merchant_id' => '$braintree_merchant_id',
    'braintree_public_key' => '$braintree_public_key',
    'braintree_private_key' => '$braintree_private_key',
    'braintree_cse' => '$braintree_cse',
        
    'coinbaseAPIKey' => '" . Config::get('app.coinbaseAPIKey') . "',
    'coinbaseAPISecret' => '" . Config::get('app.coinbaseAPISecret') . "',

    'paypal_sdk_mode' => '" . Config::get('app.paypal_sdk_mode') . "',
    'paypal_sdk_UserName' => '" . Config::get('app.paypal_sdk_UserName') . "',
    'paypal_sdk_Password' => '" . Config::get('app.paypal_sdk_Password') . "',
    'paypal_sdk_Signature' => '" . Config::get('app.paypal_sdk_Signature') . "',
    'paypal_sdk_AppId' => '" . Config::get('app.paypal_sdk_AppId') . "',

);
";
}

function generate_custome_key($dashboard, $map_view, $provider, $user, $taxi, $trip, $walk, $request, $reviews, $information, $types, $documents, $promo_codes, $customize, $payment_details, $settings, $val_admin, $admin_control, $log_out, $schedule, $weekstatement) {
    return "<?php
return array(

    'Dashboard' => '$dashboard',
    'map_view' => '$map_view',
    'Reviews' => '$reviews',
    'Information' => '$information',
    'Types' => '$types',
    'Documents' => '$documents',
    'promo_codes' => '$promo_codes',
    'Customize' => '$customize',
    'payment_details' => '$payment_details',
    'Settings' => '$settings',
    'Admin' => '$val_admin',
    'admin_control' => '$admin_control',
    'log_out' => '$log_out',
    'Provider' => '$provider',
    'User' => '$user',
    'Taxi' => '$taxi',
    'Trip' => '$trip',
    'Walk' => '$walk',
    'Request' => '$request',
    'Schedules' => '$schedule',
    'WeekStatement' => '$weekstatement',
);
";
}

function import_db($mysql_username, $mysql_password, $mysql_host, $mysql_database) {
    // Name of the file
    $filename = public_path() . '/uberx.sql';


    // Connect to MySQL server
    $db_conn = mysqli_connect($mysql_host, $mysql_username, $mysql_password, $mysql_database) or die('Error connecting to MySQL server: ' . mysql_error());
    // Select database
    //mysql_select_db($mysql_database) or die('Error selecting MySQL database: ' . mysql_error());
    // Temporary variable, used to store current query
    $templine = '';
    // Read in entire file
    $lines = file($filename);
    // Loop through each line
    foreach ($lines as $line) {
        // Skip it if it's a comment
        if (substr($line, 0, 2) == '--' || $line == '')
            continue;

        // Add this line to the current segment
        $templine .= $line;
        // If it has a semicolon at the end, it's the end of the query
        if (substr(trim($line), -1, 1) == ';') {
            // Perform the query
            mysqli_query($db_conn, $templine) or print('Error performing query \'<strong>' . $templine . '\': ' . mysql_error() . '<br /><br />');
            // Reset temp variable to empty
            $templine = '';
        }
    }
    //echo "Tables imported successfully";
}

function generate_mail_config($host, $mail_driver, $email_name, $email_address) {

    return "<?php

return array(

    /*
    |--------------------------------------------------------------------------
    | Mail Driver
    |--------------------------------------------------------------------------
    |
    | Laravel supports both SMTP and PHP's 'mail' function as drivers for the
    | sending of e-mail. You may specify which one you're using throughout
    | your application here. By default, Laravel is setup for SMTP mail.
    |
    | Supported: 'smtp', 'mail', 'sendmail', 'mailgun', 'mandrill', 'log'
    |
    */

    'driver' => '$mail_driver',

    /*
    |--------------------------------------------------------------------------
    | SMTP Host Address
    |--------------------------------------------------------------------------
    |
    | Here you may provide the host address of the SMTP server used by your
    | applications. A default option is provided that is compatible with
    | the Mailgun mail service which will provide reliable deliveries.
    |
    */

    'host' => '$host',

    /*
    |--------------------------------------------------------------------------
    | SMTP Host Port
    |--------------------------------------------------------------------------
    |
    | This is the SMTP port used by your application to deliver e-mails to
    | users of the application. Like the host we have set this value to
    | stay compatible with the Mailgun e-mail application by default.
    |
    */

    'port' => 587,

    /*
    |--------------------------------------------------------------------------
    | Global 'From' Address
    |--------------------------------------------------------------------------
    |
    | You may wish for all e-mails sent by your application to be sent from
    | the same address. Here, you may specify a name and address that is
    | used globally for all e-mails that are sent by your application.
    |
    */

    'from' => array('address' => '$email_address', 'name' => '$email_name'),

    /*
    |--------------------------------------------------------------------------
    | E-Mail Encryption Protocol
    |--------------------------------------------------------------------------
    |
    | Here you may specify the encryption protocol that should be used when
    | the application send e-mail messages. A sensible default using the
    | transport layer security protocol should provide great security.
    |
    */

    'encryption' => 'tls',

    /*
    |--------------------------------------------------------------------------
    | SMTP Server Username
    |--------------------------------------------------------------------------
    |
    | If your SMTP server requires a username for authentication, you should
    | set it here. This will get used to authenticate with your server on
    | connection. You may also set the 'password' value below this one.
    |
    */

    'username' => null,

    /*
    |--------------------------------------------------------------------------
    | SMTP Server Password
    |--------------------------------------------------------------------------
    |
    | Here you may set the password required by your SMTP server to send out
    | messages from your application. This will be given to the server on
    | connection so that the application will be able to send messages.
    |
    */

    'password' => null,

    /*
    |--------------------------------------------------------------------------
    | Sendmail System Path
    |--------------------------------------------------------------------------
    |
    | When using the 'sendmail' driver to send e-mails, we will need to know
    | the path to where Sendmail lives on this server. A default path has
    | been provided here, which will work well on most of your systems.
    |
    */

    'sendmail' => '/usr/sbin/sendmail -bs',

    /*
    |--------------------------------------------------------------------------
    | Mail 'Pretend'
    |--------------------------------------------------------------------------
    |
    | When this option is enabled, e-mail will not actually be sent over the
    | web and will instead be written to your application's logs files so
    | you may inspect the message. This is great for local development.
    |
    */

    'pretend' => false,

);
";
}

function generate_services_config($mandrill_secret, $mandrill_username) {

    return "<?php

return array(

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, Mandrill, and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => array(
        'domain' => '',
        'secret' => '',
    ),

    'mandrill' => array(
        'secret' => '$mandrill_secret',
        'username' => '$mandrill_username',
    ),

    'stripe' => array(
        'model'  => 'User',
        'secret' => '',
    ),

);
";
}

class PhoneValidationRule extends \Illuminate\Validation\Validator {

    public function validatePhone($attribute, $value, $parameters) {
        return preg_match("/^([0-9\+]*)$/", $value);
    }

}

Validator::resolver(function($translator, $data, $rules, $messages) {
    return new PhoneValidationRule($translator, $data, $rules, $messages);
});
?>