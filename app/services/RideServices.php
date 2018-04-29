<?php
namespace helpers;

use Illuminate\Events\Dispatcher;

class RideServices {

    // constructor
    function __construct() {

    }

    public function CreateNewRequest($model)
    {
        //print_r($model);
        \Log::info('pickupdate from input = ' . print_r($model->pickup_date, true));
        \Log::info('pickuptime from input = ' . print_r($model->pickup_time, true));
        $requestpickuptime = $model->pickup_date . " " . $model->pickup_time;
        date_default_timezone_set($model->user_timezone);
        \Log::info('usertimezone = ' . print_r($model->user_timezone, true));
        $finalpickuptime = get_UTC_time($requestpickuptime);
        \Log::info('finalpickuptime = ' . print_r($finalpickuptime, true));
        date_default_timezone_set(\Config::get('app.timezone'));

        $src_address = "Address Not Available";
        $dest_address = "Address Not Available";

        if ($model->origin_lat != '' && $model->origin_long != '' && $model->dest_lat != '' && $model->dest_long != '') {
            $src_address = get_address($model->origin_lat, $model->origin_long);
            $dest_address = get_address($model->dest_lat, $model->dest_long);

            $latitude     = $model->origin_lat;
            $longitude    = $model->origin_long;
            $droplatitude = $model->dest_lat;
            $droplongitude= $model->dest_long;

        }elseif ($model->passenger_pickupaddress != '' && $model->passenger_dropoffaddress != ''){

            $output = $this->GetLocationFromAddress($model->passenger_pickupaddress);
            $latitude = $output->results[0]->geometry->location->lat;
            $longitude = $output->results[0]->geometry->location->lng;
            $src_address = $model->passenger_pickupaddress;

            $output1 = $this->GetLocationFromAddress($model->passenger_dropoffaddress);
            $droplatitude = $output1->results[0]->geometry->location->lat;
            $droplongitude = $output1->results[0]->geometry->location->lng;
            $dest_address = $model->passenger_dropoffaddress;
        }

        $Consumer = \Consumer::where('email', '=', $model->consumer_email)->first();

        $DispatcherAssigned = \DispatcherAssigned::where('phone', '=', $model->passenger_phone)->first();
        if($DispatcherAssigned){
            \DispatcherAssigned::where('id', '=', $DispatcherAssigned->id)->update(array('contact_name' =>  $model->contact_name, 'email' => $model->passenger_email,'updated_at'=>date('Y-m-d H:i:s')));
        } else{
            $DispatcherAssigned = new \DispatcherAssigned;
            $DispatcherAssigned->contact_name    = $model->contact_name;
            $DispatcherAssigned->email         = $model->passenger_email;
            $DispatcherAssigned->phone         = $model->passenger_phone;
            $DispatcherAssigned->dispatcher_id = $Consumer->id;
            $DispatcherAssigned->created_at = date('Y-m-d H:i:s');
            $DispatcherAssigned->updated_at = date('Y-m-d H:i:s');
            $DispatcherAssigned->save();
        }
        $obj_ride_type = \ProviderType::where('name','like',$model->ride_type)->first();

        $ride_info = $this->GetExpectedRideAmount($latitude,$longitude,$droplatitude,$droplongitude,$obj_ride_type->id,0);
        $request = new \Request;
        $request->payment_mode = 0;
        $request->service_type = $obj_ride_type->id;
        $request->time_zone    = $model->user_timezone;
        $request->src_address = $src_address;
        $request->D_latitude = 0;
        if (isset($droplatitude)) {
            $request->D_latitude = $droplatitude;
        }
        $request->D_longitude = 0;
        if (isset($droplongitude)) {
            $request->D_longitude = $droplongitude;
        }
        $request->dest_address = $dest_address;
        $request->request_start_time = $finalpickuptime;
        $request->latitude = $latitude;
        $request->longitude = $longitude;
        $request->dispatcher_assigned_id = $DispatcherAssigned->id;
        $request->dispatcher_id = Null;
        $request->req_create_user_time = date('Y-m-d H:i:s');
        $request->status = 0;
        $request->confirmed_walker = '0';
        $request->current_walker = '0';
        $request->is_walker_started = '0';
        $request->is_walker_arrived = '0';
        $request->is_started = '0';
        $request->is_completed = '0';
        $request->is_dog_rated = '0';
        $request->is_walker_rated = '0';
        $request->distance = $ride_info['distance'];
        $request->time = $ride_info['time'];
        $request->total = $ride_info['amount'];
        $request->is_paid = '0.00';
        $request->card_payment = '0.00';
        $request->ledger_payment = '0.00';
        $request->is_cancelled = '0';
        $request->refund = '0.00';
        $request->transfer_amount = '0.00';
        $request->later = '0';
        $request->promo_code = '0';
        $request->promo_id = '0';
        $request->cancel_reason = '0';
        $request->is_manual = 0;
        $request->consumer_id = $Consumer->id;
        $request->save();

        $reqserv = new \RequestServices;
        $reqserv->request_id = $request->id;
        $reqserv->type = $obj_ride_type->id;
        $reqserv->save();

        // Send SMS
        $settings = \Settings::where('key', 'sms_request_created')->first();
        $pattern = $settings->value;
        $pattern = str_replace('%user%', $model->contact_name, $pattern);
        $pattern = str_replace('%id%', $request->id, $pattern);
        $pattern = str_replace('%user_mobile%', $model->passenger_phone, $pattern);
        $pattern = str_replace('%pickup_address%', $request->src_address, $pattern);
        $pattern = str_replace('%dropoff_address%', $request->dest_address, $pattern);
        $pattern = str_replace('%start_app_link%', '', $pattern);
        //$pattern .= " . Wheelchair Requested.";

        sms_notification($request->consumer_id, 'ride_assignee', $pattern);
        sms_notification($request->consumer_id, 'ride_assignee_2', $pattern);
        sms_notification($request->consumer_id, 'ride_assignee_3', $pattern);

        //get user information
        $passengerinfo = \DispatcherAssigned::where('id', $request->dispatcher_assigned_id)->first();
        $settings = \Settings::where('key', 'admin_email_address')->first();
        $admin_email = $settings->value;
        $settings = \Settings::where('key', 'ride_assignee_phone_number')->first();
        $ride_assignee_phone_number = $settings->value;
        $follow_url = web_url() . "/dispatcher/submittedrides";

        if ($request->driver_name) {
            $driver_name = $request->driver_name;
            $driver_phone = $request->driver_phone;
        } else {
            $driver_name = "NA";
            $driver_phone = "NA";
        }

        $passenger_name = $passengerinfo->contact_name;
        $subject = "We've received your ride request";

        if ($_SERVER['HTTP_HOST'] == "ride.gobutterfli.com") {
            $server = "Development";
        } elseif ($_SERVER['HTTP_HOST'] == "app.gobutterfli.com") {
            $server = "Production";
        } elseif ($_SERVER['HTTP_HOST'] == "demo.gobutterfli.com") {
            $server = "Demo";
        } else {
            $server = "Local";
        }

        $attendant_travelling = 'NO';

        $dispatcher_email   = $Consumer->email;
        $dispatcher_name    = $Consumer->contact_name;
        $dispatcher_company = $Consumer->company;

        $time = new \DateTime($request->request_start_time);
        $time->format('h:ia') . "\n";
        $user_time = new \DateTimeZone($request->time_zone);
        $time->setTimezone($user_time);
        $pickuptime = $time->format('h:ia');

        $date = new \DateTime($request->request_start_time);
        $date->format('Y-m-d') . "\n";
        $user_time = new \DateTimeZone($request->time_zone);
        $date->setTimezone($user_time);
        $pickupdate = $date->format('Y-m-d');

        $pattern = array('driver_name' => $driver_name, 'driver_phone' => $driver_phone,
            'passenger_name' => $passenger_name, 'passenger_phone' => $passengerinfo->phone,
            'pickup_time' => $pickuptime,'pickup_date' => $pickupdate, 'pickup_location' => $request->src_address,
            'dropoff_location' => $request->dest_address, 'butterfli_dispatcher_phno' => $ride_assignee_phone_number,
            'admin_email' => $admin_email, 'trip_id' => $request->id, 'follow_url' => $follow_url,
            'agent_name' => $dispatcher_name, 'wheelchair_request' => 'NO','attendant_travelling' => $attendant_travelling,
            'healthcare_email' => $dispatcher_email, 'healthcare_company' => $dispatcher_company,
            'server' => $server, 'billing_code'=>'NA','all_radio'=>'2');

        email_notification($request->consumer_id, 'ride_assignee', $pattern, $subject, 'new_enterprise_ride_request', null);
        email_notification($request->consumer_id, 'ride_assignee_2', $pattern, $subject, 'new_enterprise_ride_request', null);
        email_notification($request->consumer_id, 'ride_assignee_3', $pattern, $subject, 'new_enterprise_ride_request', null);

        if ($request->is_cancelled == 1) {
            $status = "Cancelled";
        } elseif ($request->is_completed == 1) {
            $status = "Completed";
        } elseif ($request->is_started == 1) {
            $status = "Started";
        } elseif ($request->is_walker_arrived == 1) {
            $status = "Walker Arrived";
        } elseif ($request->is_walker_started == 1) {
            $status = " Walker Started";
        } else {
            $status = "Yet To Start";
        }

        if($request->is_confirmed==1){
            $confirmed = "TRUE";
        }else{
            $confirmed = "FALSE";
        }

        $response_array = array(
            'success' => 1,
            'ride_status'          => $status,
            'ride_id'              => $request->id,
            'ride_type'            => $model->ride_type,
            'passenger_contact_name' => $model->contact_name,
            'passenger_phone'      => $model->passenger_phone,
            'passenger_email'      => $model->passenger_email,
            'timezone'             => $request->time_zone,
            'total_amount'         => $request->total,
            'pickup_address'       => $request->src_address,
            'dropoff_address'      => $request->dest_address,
            'origin_latitude'      => $request->latitude,
            'origin_longitude'     => $request->longitude,
            'dest_latitude'        => $request->D_latitude,
            'dest_longitude'       => $request->D_longitude,
            'request_date_time'    => $request->request_start_time,
            'request_create_time'  => $request->req_create_user_time,
            'confirmed'            => $confirmed
        );
        return $response_array;
    }

    public function CancelRideRequest($request_id,$cancel_reason){
        if ($request = \Request::find($request_id)) {
            if ($request->is_cancelled != 1) {
                $consumer = \Consumer::find($request->consumer_id);
                // Archiving that Walker
                \RequestMeta::where('request_id', '=', $request_id)->where('walker_id', '=', $request->current_walker)->update(array('status' => 3, 'is_cancelled' => 1));
                // Update Walker availability
                \Walker::where('id', '=', $request->current_walker)->update(array('is_available' => 1));
                // request ended
                \Request::where('id', '=', $request_id)->update(array('current_walker' => 0, 'status' => 0, 'is_cancelled' => 1, 'cancel_reason' => $cancel_reason, 'cancelled_by' => $consumer->email));
                $walker = \Walker::where('id', $request->current_walker)->first();

                \AssignedDispatcherRequest::where('request_id', '=', $request_id)->update(array('status' => 0, 'is_cancelled' => 1, 'cancel_reason' => $cancel_reason));

                if ($walker) {
                    $walker_name = $walker->contact_name;
                    // Send SMS
                    $pattern = "Dear " . $walker_name . " Your current assigned ride has been cancelled by the Consumer. Please contact support for further information.";
                    sms_notification($request->current_walker, 'walker', $pattern);
                }
                $response_array = array('success' => 1);
                return $response_array;
            }else{
                $response_array = array('success' => 0, 'error_messages' => "Already Cancelled Request", 'error_code' => 406);
                return $response_array;
            }
        }
    }

    public function GetRideRequestInfo($request_id){
        if ($request = \Request::find($request_id)) {

            $rideinfo = \DB::table('request')
                ->leftJoin('dispatcher_assigned', 'request.dispatcher_assigned_id', '=', 'dispatcher_assigned.id')
                ->leftJoin('walker', 'walker.id', '=', 'request.current_walker')
                ->leftJoin('walker_type', 'walker_type.id', '=', 'request.service_type')
                ->leftJoin('dispatcher', 'dispatcher.id', '=', 'request.dispatcher_id')
                ->leftJoin('consumer', 'consumer.id', '=', 'request.consumer_id')
                ->select('dispatcher_assigned.contact_name as owner_contact_name',
                    'dispatcher_assigned.phone as owner_phone',
                    'dispatcher_assigned.email as owner_email',
                    'walker.contact_name as walker_contact_name',
                    'walker.phone as walker_phone',
                    'walker.email as walker_email',
                    'walker.id as walker_id',
                    'request.id as id', 'request.request_start_time as date',
                    'request.payment_mode', 'request.is_started',
                    'request.is_walker_arrived', 'request.payment_mode',
                    'request.is_completed', 'request.is_paid',
                    'request.current_walker',
                    'request.estimated_time',
                    'request.is_walker_started', 'request.confirmed_walker',
                    'request.status', 'request.time',
                    'request.distance', 'request.total', 'request.is_cancelled',
                    'request.transfer_amount', 'walker_type.name',
                    'request.total as total_service_amount',
                    'request.promo_payment', 'request.additional_fee',
                    'request.driver_name', 'request.driver_phone',
                    'request.is_confirmed', 'request.src_address', 'request.dest_address',
                    'dispatcher.contact_name as assigned_contact_name',
                    'request.service_type','request.time_zone','request.cancelled_by',
                    'request.req_create_user_time','request.latitude',
                    'request.longitude','request.D_latitude','request.D_longitude')
                ->where('request.id', '=', $request_id)->first();

            if ($rideinfo->is_cancelled == 1) {
               $status = "Cancelled";
            } elseif ($rideinfo->is_completed == 1) {
                $status = "Completed";
            } elseif ($rideinfo->is_started == 1) {
                $status = "Started";
            } elseif ($rideinfo->is_walker_arrived == 1) {
                $status = "Walker Arrived";
            } elseif ($rideinfo->is_walker_started == 1) {
                $status = " Walker Started";
            } else {
                $status = "Yet To Start";
            }

            if($rideinfo->is_confirmed==1){
                $confirmed = "TRUE";
            }else{
                $confirmed = "FALSE";
            }

            $response_array = array(
                'success' => 1,
                'ride_status'          => $status,
                'ride_id'              => $rideinfo->id,
                'ride_type'            => $rideinfo->name,
                'passenger_contact_name' => $rideinfo->owner_contact_name,
                'passenger_phone'      => $rideinfo->owner_phone,
                'passenger_email'      => $rideinfo->owner_email,
                'driver_contact_name'    => $rideinfo->walker_contact_name,
                'driver_phone'         => $rideinfo->walker_phone,
                'driver_email'         => $rideinfo->walker_email,
                'timezone'             => $rideinfo->time_zone,
                'estimated_time'       => $rideinfo->estimated_time.' '. 'minutes',
                'total_amount'         => $rideinfo->total,
                'pickup_address'       => $rideinfo->src_address,
                'dropoff_address'      => $rideinfo->dest_address,
                'origin_latitude'      => $rideinfo->latitude,
                'origin_longitude'     => $rideinfo->longitude,
                'dest_latitude'        => $rideinfo->D_latitude,
                'dest_longitude'       => $rideinfo->D_longitude,
                'request_date_time'    => $rideinfo->date,
                'request_create_time'  => $rideinfo->req_create_user_time,
                'cancelled_by'         => $rideinfo->cancelled_by,
                'confirmed'            => $confirmed
            );

            return $response_array;
        }
    }

    public function GetLocationFromAddress($address){
        if($address!='')
        {
            $prepAddr = str_replace(' ','+',$address);
            $geocode=file_get_contents('https://maps.google.com/maps/api/geocode/json?address='.$prepAddr.'&sensor=false');
            $output= json_decode($geocode);

            return $output;
        }
    }

    public function GetExpectedRideAmount($start_lat,$start_long,$end_lat,$end_long,$service_type,$is_wheelchair){

        $geocode=file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?origins='.$start_lat.','.$start_long.'&destinations='.$end_lat.','.$end_long.'&mode=driving&language=pl-PL');
        $output= json_decode($geocode,true);


        $durationInMinutes = $output['rows'][0]['elements'][0]['duration']['value'] / 60;
        $distanceInMiles   = $output['rows'][0]['elements'][0]['distance']['value'] / (1000 * 1.609344);

        $distanceInMiles = round($distanceInMiles,2);

        $pt = \ProviderType::where('id', $service_type)->first();
        $base_price = $pt->base_price;
        $price_per_unit_distance = $pt->price_per_unit_distance;
        $price_per_unit_time = $pt->price_per_unit_time;

        $wheelchair_cost = 0;

        $distance_cost = $price_per_unit_distance * $distanceInMiles;
        $time_cost = $price_per_unit_time * $durationInMinutes;


        $ride_total = $base_price + $distance_cost + $time_cost;
        if ($is_wheelchair == 1) {
            $ride_total = $ride_total + 10;
            $wheelchair_cost = "10.00";
        }
        $amount = round($ride_total,2);

        return array('amount' => $amount, 'distance' => $distanceInMiles, 'time' => $durationInMinutes);
    }
}
?>