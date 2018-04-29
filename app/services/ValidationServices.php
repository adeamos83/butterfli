<?php
namespace helpers;

class ValidationServices
{
    /**
     * Returns the *Singleton* instance of this class.
     *
     * @staticvar Singleton $instance The *Singleton* instances of this class.
     *
     * @return Singleton The *Singleton* instance.
     */
    public static function getInstance()
    {
        static $instance = null;
        if (null === $instance) {
            $instance = new static();
        }
        return $instance;
    }
    /**
     * Protected constructor to prevent creating a new instance of the
     * *Singleton* via the `new` operator from outside of this class.
     */
    protected function __construct()
    {
    }
    /**
     * Private clone method to prevent cloning of the instance of the
     * *Singleton* instance.
     *
     * @return void
     */
    private function __clone()
    {
    }
    /**
     * Private unserialize method to prevent unserializing of the *Singleton*
     * instance.
     *
     * @return void
     */
    private function __wakeup()
    {
    }

    public function ValidateNewRideRequest($model){

        $validator = \Validator::make(
            array(
                'consumer_email'           => $model->consumer_email,
                'ride_type'                => $model->ride_type,
                'passenger_firstname'      => $model->passenger_firstname,
                'passenger_lastname'       => $model->passenger_lastname,
                'passenger_email'          => $model->passenger_email,
                'passenger_phone'          => $model->passenger_phone,
                'origin_lat'               => $model->origin_lat,
                'origin_long'              => $model->origin_long,
                'dest_lat'                 => $model->dest_lat,
                'dest_long'                => $model->dest_long,
                'passenger_pickupaddress'  => $model->passenger_pickupaddress,
                'passenger_dropoffaddress' => $model->passenger_dropoffaddress,
                'pickup_date'              => $model->pickup_date,
                'pickup_time'              => $model->pickup_time,
                'user_timezone'            => $model->user_timezone
            ), array(
            'consumer_email'=> 'required',
            'ride_type' => 'required',
            'passenger_firstname' => 'required',
            'passenger_lastname' => 'required',
            'passenger_email' => 'required',
            'passenger_phone' => 'required',
            'pickup_date' => 'required',
            'pickup_time' => 'required',
            'user_timezone' => 'required'
        ));

        if ($validator->fails()) {
            $error_messages = $validator->messages();
            $response_array = array('success' => 0, 'error_messages' => $error_messages, 'error_code' => 400);
        } else {
            if($model->origin_lat!='' && $model->origin_long!='' && $model->dest_lat!='' && $model->dest_long!=''){
                $Consumer = \Consumer::where('email', '=', $model->consumer_email)->first();
                if($Consumer==null){
                    $response_array = array('success' => 0, 'error_messages' => "Consumer email doesn't exists", 'error_code' => 402);
                }else{
                    $model->ride_type = strtolower($model->ride_type);
                    //checking ride_type in our database.
                    $obj_ride_type = \ProviderType::where('name','like',$model->ride_type)->first();
                    if($obj_ride_type==null){
                        $response_array = array('success' => 0, 'error_messages' => "Ride type doesn't exists", 'error_code' => 407);
                    }else{
                        $response_array = array('success' => 1);
                    }
                }
            } elseif($model->passenger_pickupaddress!='' && $model->passenger_dropoffaddress!=''){
                $Consumer = \Consumer::where('email', '=', $model->consumer_email)->first();
                if($Consumer==null){
                    $response_array = array('success' => 0, 'error_messages' => "Consumer email doesn't exists", 'error_code' => 402);
                }else{
                    $model->ride_type = strtolower($model->ride_type);
                    //checking ride_type in our database.
                    $obj_ride_type = \ProviderType::where('name','like',$model->ride_type)->first();
                    if($obj_ride_type==null){
                        $response_array = array('success' => 0, 'error_messages' => "Ride type doesn't exists", 'error_code' => 407);
                    }else{
                        $response_array = array('success' => 1);
                    }
                }
            } else{
                $response_array = array('success' => 0, 'error_messages' => "Please enter proper values for origin and destinations", 'error_code' => 408);
            }

        }
        return $response_array;
    }

    public function ValidateRideRequest($request_id,$consumer_email){
        $Consumer = \Consumer::where('email', '=', $consumer_email)->first();
        if($Consumer==null){
            $response_array = array('success' => 0, 'error_messages' => "Consumer email doesn't exists", 'error_code' => 403);
        }else {
            $request = \Request::find($request_id);
            if($request==null){
                $response_array = array('success' => 0, 'error_messages' => "Request-id not exists", 'error_code' => 404);
            } elseif ($request->id > 0 && ($request->consumer_id == $Consumer->id)) {
                $response_array = array('success' => 1);
            } elseif ($request->consumer_id != $Consumer->id) {
                $response_array = array('success' => 0, 'error_messages' => "Consumer-id not match with this request-id", 'error_code' => 405);
            }
        }
        return $response_array;
    }
}