<?php
/**
 * Created by PhpStorm.
 * User: Abhishek
 * Date: 22-08-2017
 * Time: 16:40
 */

class RideController extends \BaseController
{

    public function __construct()
    {
        //$date = date("Y-m-d H:i:s");
    }

    public function CreateNewRideRequest(){

        $jsonString = json_encode(Input::all());
        $ride_request = new OpenRideRequest($jsonString);

        $validation_service = ValidationServices::getInstance();
        $validation_response = $validation_service->ValidateNewRideRequest($ride_request);

        if($validation_response['success']=='1'){
            $newRequest = new RideServices();
            $create_ride = $newRequest->CreateNewRequest($ride_request);

            if($create_ride['success']=='1'){
                $response_array = $create_ride;
                $response_code = 200;
            }else{
                $response_array = array('success' => $create_ride['success'], 'error_messages' => $create_ride['error_messages'], 'error_code' => $create_ride['error_code']);
                $response_code = 200;
            }
        } else{
            $response_array = array('success' => $validation_response['success'], 'error_messages' => $validation_response['error_messages'], 'error_code' => $validation_response['error_code']);
            $response_code = 200;
        }
        $response = Response::json($response_array, $response_code);
        return $response;
    }

    public function CancelRideRequest(){
        $request_id = Input::get('request_id');
        $cancel_reason = Input::get('cancel_reason');
        $consumer_email = Input::get('consumer_email');

        $validation_service = ValidationServices::getInstance();
        $validation_response = $validation_service->ValidateRideRequest($request_id,$consumer_email);

        if($validation_response['success']=='1'){
            $cancelRequest = new RideServices();
            $cancel_ride = $cancelRequest->CancelRideRequest($request_id,$cancel_reason);

            if($cancel_ride['success']=='1'){
                $response_array = array('success' => $cancel_ride['success']);
                $response_code = 200;
            }else{
                $response_array = array('success' => $cancel_ride['success'], 'error_messages' => $cancel_ride['error_messages'], 'error_code' => $cancel_ride['error_code']);
                $response_code = 200;
            }
        } else{
            $response_array = array('success' => $validation_response['success'], 'error_messages' => $validation_response['error_messages'], 'error_code' => $validation_response['error_code']);
            $response_code = 200;
        }
        $response = Response::json($response_array, $response_code);
        return $response;
    }

    public function GetRideInfo(){
        $request_id = Input::get('request_id');
        $consumer_email = Input::get('consumer_email');

        $validation_service = ValidationServices::getInstance();
        $validation_response = $validation_service->ValidateRideRequest($request_id,$consumer_email);

        if($validation_response['success']=='1'){
            $infoRequest = new RideServices();
            $ride_info = $infoRequest->GetRideRequestInfo($request_id);

            if($ride_info['success']=='1'){
                $response_array = $ride_info;
                $response_code = 200;
            }else{
                $response_array = array('success' => $ride_info['success'], 'error_messages' => $ride_info['error_messages'], 'error_code' => $ride_info['error_code']);
                $response_code = 200;
            }
        } else{
            $response_array = array('success' => $validation_response['success'], 'error_messages' => $validation_response['error_messages'], 'error_code' => $validation_response['error_code']);
            $response_code = 200;
        }
        $response = Response::json($response_array, $response_code);
        return $response;
    }
}
