<?php

class OpenRideRequest {

    public $consumer_email;
    public $ride_type;
    public $passenger_firstname;
    public $passenger_lastname;
    public $passenger_email;
    public $passenger_phone;
    public $passenger_pickupaddress;
    public $passenger_dropoffaddress;
    public $pickup_date;
    public $pickup_time;
    public $user_timezone;
    public $origin_lat;
    public $origin_long;
    public $dest_lat;
    public $dest_long;


    public function __construct($json = false) {
        if ($json) $this->set(json_decode($json, true));
    }

    public function set($data) {
        foreach ($data AS $key => $value) {
            if (is_array($value)) {
                $sub = new JSONObject;
                $sub->set($value);
                $value = $sub;
            }
            $this->{$key} = $value;
        }
    }
}
