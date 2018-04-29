<?php

use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment as PaymentPaypal;
use PayPal\Api\RedirectUrls;
use PayPal\Api\ExecutePayment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Transaction;
//use app\Services\PaymentServices;

class DispatcherController extends \BaseController {

    /**
     * Display a listing of the resource.
     *
     *
     * @return Response
     */
    public $status = 0;
    private $_api_context;

    private function get_timezone_offset($remote_tz, $origin_tz = null) {
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
        return $offset;
    }

    public function __construct() {
        if (Config::get('app.production')) {
            echo "Something cool is going to be here soon.";
            die();
        }

        $this->beforeFilter(function() {
            if (!Session::has('user_id')) {
                Session::put('pre_login_url', URL::current());
                return Redirect::to('/dispatcher/signin');
            } else {
                $user_id = Session::get('user_id');
                $Dispatcher = Dispatcher::where('id', $user_id)->first();
                Session::put('user_name', $Dispatcher->contact_name . " " . $Dispatcher->contact_name);
                Session::put('email', $Dispatcher->email);
                Session::put('company', $Dispatcher->company);
                Session::put('is_admin', $Dispatcher->is_admin);
            }
        }, array('except' => array(
                'userLogin',
                'userVerify',
                'CheckUserbyOTP',
                'ResendOTP',
                'dispatcherForgotPassword',
                'userRegister',
                'userSave',
                'surroundingCars',
        )));


        $date = date("Y-m-d H:i:s");
        $time_limit = date("Y-m-d H:i:s", strtotime($date) - (3 * 60 * 60));
        $owner_id = Session::get('user_id');

        $current_request = RideRequest::where('owner_id', $owner_id)
                ->where('is_cancelled', 0)
                ->where('created_at', '>', $time_limit)
                ->orderBy('created_at', 'desc')
                ->where(function($query) {
                    $query->where('status', 0)->orWhere(function($query_inner) {
                        $query_inner->where('status', 1)
                        ->where('is_walker_rated', 0);
                    });
                })
                ->first();
        $this->status = 0;
        if ($current_request) {
            if ($current_request->confirmed_walker) {
                $walker = Walker::find($current_request->confirmed_walker);
            }

            if ($current_request->is_completed) {
                $this->status = 5;
            } elseif ($current_request->is_started) {
                $this->status = 4;
            } elseif ($current_request->is_walker_arrived) {
                $this->status = 3;
            } elseif ($current_request->is_walker_started) {
                $this->status = 2;
            } elseif ($current_request->confirmed_walker) {
                $this->status = 1;
            } else {
                if ($current_request->status == 1) {
                    $this->status = 6;
                }
            }
            Session::put('status', $this->status);
            Session::put('request_id', $current_request->id);
        }

        $paypal_conf = Config::get('paypal');
        $this->_api_context = new ApiContext(new OAuthTokenCredential($paypal_conf['client_id'], $paypal_conf['secret']));
        $this->_api_context->setConfig($paypal_conf['settings']);
    }

    public function index() {
        return Redirect::to('/dispatcher/signin');
    }

    public function userLogin() {
        return View::make('dispatcher.userLogin');
    }

    public function userRegister() {
        $transportation_providers = TransportationProvider::all();

        return View::make('dispatcher.userSignup')
            ->with('transportation_providers', $transportation_providers);
    }
	
    public function userSave() {
        $contact_name = Input::get('contact_name');
        $email = Input::get('email');
        $password = Input::get('password');
        $companyname = Input::get('company_name');
        $code = Input::get('code');
        $phone = Input::get('phone');
        $validator = Validator::make(
            array(
                'contact_name' => $contact_name,
                'email' => $email,
                'password' => $password,
                'phone'=>$phone
            ),
            array(
                'password' => 'required',
                'email' => 'required',
                'contact_name' => 'required',
                'phone'=>'required'
            ),
            array(
                'password' => 'Password field is required.',
                'email' => 'Email field is required',
                'contact_name' => 'Name field is required.',
            )
        );

        $validator1 = Validator::make(
                        array(
                    'email' => $email,
                        ), array(
                    'email' => 'required|email'
                        ), array(
                    'email' => 'Email field is required'
                        )
        );

        $validatorphone = Validator::make(
            array(
                'phone' => $phone,
            ), array(
            'phone' => 'required|numeric'
        ), array(
                'phone' => 'Phone is required'
            )
        );


        if ($validator->fails()) {
            $error_messages = $validator->messages()->first();
            Log::info('Error = ' . print_r($error_messages, true));
            return Redirect::to('dispatcher/signup')->with('error', 'Please fill all the fields.');
        } else if ($validator1->fails()) {
            return Redirect::to('dispatcher/signup')->with('error', 'Please Enter email correctly.');
        }  else if ($validatorphone->fails()) {
            $error_messages = $validatorphone->messages();
            Log::info('Error = ' . print_r($error_messages, true));
            return Redirect::to('dispatcher/signup')->with('error', 'Please Enter phone correctly.');
        } else {
            if (Dispatcher::where('email', $email)->count() == 0) {
                $Dispatcher = new Dispatcher;
                $Dispatcher->contact_name = $contact_name;
                $Dispatcher->email = $email;
                $Dispatcher->phone = $code.$phone;
                $Dispatcher->token = generate_token();
                if ($password != "") {
                   $Dispatcher->password = Hash::make($password);
                }
                $Dispatcher->save();

                $Transportation_provider = TransportationProvider::find($Dispatcher->transportation_provider_id);

				$settings = Settings::where('key', 'admin_email_address')->first();
                $admin_email = $settings->value;
                $pattern = array('admin_email' => $admin_email, 'name' => ucwords($Dispatcher->contact_name), 'web_url' => web_url());
                $subject = "Welcome to " . ucwords(Config::get('app.website_title')) . ", " . ucwords($Dispatcher->contact_name) . "";
                email_notification($Dispatcher->id, 'dispatcher', $pattern, $subject, 'user_register', null);

                $follow_url = web_url() . "/admin/login";
                $pattern1 = array('username' => ucwords($Dispatcher->contact_name),
                    'email' => $Dispatcher->email, 'follow_url' => $follow_url,
                    'web_url' => web_url(), 'provider'=>'Dispatcher');
                $subject = "New Dispatcher Request Created.";
                email_notification($Dispatcher->id, 'admin', $pattern1, $subject, 'user_register_mail_to_admin', null);


                return Redirect::to('dispatcher/signin')->with('success', 'You have successfully registered. <br>Please Wait for Admin Approval.');
            }
            else {
                return Redirect::to('dispatcher/signup')
                    ->with('error', 'This email ID is already registered.');
            }
        }
    }

    public function userVerify() {
        $email = Input::get('email');
        $password = Input::get('password');
        Log::info('userverify function = ' . print_r($email, true));
        Log::info('userverify function password = ' . print_r($password, true));
        $Dispatcher = Dispatcher::where('email', '=', $email)->first();
        if ($Dispatcher && Hash::check($password, $Dispatcher->password)) {
            if ($Dispatcher->is_active == 0) {
                //return Redirect::to('dispatcher/signin')->with('error', 'Your Account is pending approval.');
                return 2;
            } else {
                Log::info('dispatcher = ' . print_r($Dispatcher, true));
                $otp_object = UserMultiFactorAuthentication::where('email','=',$email)->where('user_type','=','1')->first();
                Log::info('otp_object = ' . print_r($otp_object, true));

                if($otp_object==null){
                    $new_otp = mt_rand(100000,999999);
                    //Generate OTP and save it to database and send email and sms to user
                    $generate_otp = new UserMultiFactorAuthentication();
                    $generate_otp->email = $Dispatcher->email;
                    $generate_otp->user_type = 1; //1=>dispatcher, 2=>EnterpriseClient 3=>walker 4=>owner 5=>consumer
                    $generate_otp->OTP = $new_otp;
                    $generate_otp->otp_expiry_time = strtotime("+15 minutes" , time());
                    $generate_otp->created_at = date("Y-m-d H:i:s");
                    $generate_otp->updated_at = date("Y-m-d H:i:s");
                    $generate_otp->save();
                    Log::info('new otp1 = ' . print_r($new_otp, true));
                } else{
                    if(time() > $otp_object->otp_expiry_time){
                        $new_otp = mt_rand(100000,999999);
                        Log::info('new otp2 = ' . print_r($new_otp, true));
                        UserMultiFactorAuthentication::where('id','=',$otp_object->id)->update(
                            array('OTP'=>$new_otp,'otp_expiry_time' => strtotime("+15 minutes" , time()), 'updated_at' => date('Y-m-d H:i:s')));
                    } else{
                        $new_otp =  $otp_object->OTP;
                        Log::info('old otp1 = ' . print_r($new_otp, true));
                        UserMultiFactorAuthentication::where('id','=',$otp_object->id)->update(
                            array('updated_at' => date('Y-m-d H:i:s')));
                    }
                }

                $settings = Settings::where('key', 'admin_email_address')->first();
                $admin_email = $settings->value;

                //sending email to user for new otp
                $pattern1 = array('admin_email'=> $admin_email,'otp' => $new_otp,'web_url' => web_url(), 'otp_text'=>'You have requested a new OTP for login. Please find the OTP. It is valid for 15 minutes only.');
                $subject = "OTP Requested.";
                email_notification($Dispatcher->id, 'dispatcher', $pattern1, $subject, 'new_otp_mail', null);
                if($Dispatcher->phone!=null){
                    $pattern2 = "You have requested a new OTP for login. Please find the OTP . It is valid for 15 minutes only. OTP is $new_otp";
                    sms_notification($Dispatcher->id, 'dispatcher', $pattern2);
                }
                return $Dispatcher->email;
            }
        } else {
            //Log::info('pasword else = ' . print_r(Hash::check($password, $Dispatcher->password), true));
            //return Redirect::to('dispatcher/signin')->with('error', 'Invalid email and password');
            return 3;
        }
    }

    public function CheckUserbyOTP(){
        $email = Input::get('email');
        $otp = Input::get('otp');

       $otp_object = UserMultiFactorAuthentication::where('email','=',$email)->where('user_type','=','1')
            ->where('OTP','=',$otp)->first();

        if($otp_object!=null && ($otp_object->otp_expiry_time >= time())){
            $Dispatcher = Dispatcher::where('email', '=', $email)->first();
            Session::put('user_id', $Dispatcher->id);
            Session::put('user_name', $Dispatcher->contact_name);
            Session::put('email', $Dispatcher->email);
            Session::put('company', $Dispatcher->company);
            Session::put('is_admin', $Dispatcher->is_admin);
            $url = 'submittedrides';
            Session::set('test','1');
            return $url;
        } else{
            return 1;
        }
    }

    public function ResendOTP(){
        $email = Input::get('email');
        $Dispatcher = Dispatcher::where('email', '=', $email)->first();
        $otp_object = UserMultiFactorAuthentication::where('email','=',$email)->where('user_type','=','1')->first();
        if($otp_object==null){
            $new_otp = mt_rand(100000,999999);
            //Generate OTP and save it to database and send email and sms to user
            $generate_otp = new UserMultiFactorAuthentication();
            $generate_otp->email = $Dispatcher->email;
            $generate_otp->user_type = 1; //1=>dispatcher, 2=>EnterpriseClient 3=>walker 4=>owner 5=>consumer
            $generate_otp->OTP = $new_otp;
            $generate_otp->otp_expiry_time = strtotime("+15 minutes" , time());
            $generate_otp->created_at = date("Y-m-d H:i:s");
            $generate_otp->updated_at = date("Y-m-d H:i:s");
            $generate_otp->save();
        } else{
            if(time() > $otp_object->otp_expiry_time){
                $new_otp = mt_rand(100000,999999);
                UserMultiFactorAuthentication::where('id','=',$otp_object->id)->update(
                    array('OTP'=>$new_otp,'otp_expiry_time' => strtotime("+15 minutes" , time()), 'updated_at' => date('Y-m-d H:i:s')));
            } else{
                $new_otp =  $otp_object->OTP;
                UserMultiFactorAuthentication::where('id','=',$otp_object->id)->update(
                    array('updated_at' => date('Y-m-d H:i:s')));
            }
        }

        $settings = Settings::where('key', 'admin_email_address')->first();
        $admin_email = $settings->value;

        //sending email to user for new otp
        $pattern1 = array('admin_email'=> $admin_email,'otp' => $new_otp,'web_url' => web_url(), 'otp_text'=>'You have requested a new OTP for login. Please find the OTP. It is valid for 15 minutes only.');
        $subject = "OTP Requested.";
        email_notification($Dispatcher->id, 'dispatcher', $pattern1, $subject, 'new_otp_mail', null);
        if($Dispatcher->phone!=null){
            $pattern2 = "You have requested a new OTP for login. Please find the OTP . It is valid for 15 minutes only. OTP is $new_otp";
            sms_notification($Dispatcher->id, 'dispatcher', $pattern2);
        }
        return $Dispatcher->email;
    }
    public function userLogout() {
        Session::flush();
        return Redirect::to('/dispatcher/signin');
    }

    /*public function myservices(){
      
        $user_id = Session::get('user_id');
        $walks = DB::table('request')
                ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
                ->leftJoin('dispatcher_assigned', 'request.dispatcher_assigned_id', '=', 'dispatcher_assigned.id')
                ->leftJoin('request_services','request_services.request_id','=','request.id')
				->leftJoin('walker_type','walker_type.id','=','request_services.type')
                ->select('dispatcher_assigned.contact_name as owner_contact_name', 
                'walker.contact_name as walker_contact_name', 'walker.id as walker_id', 'walker.merchant_id as walker_merchant', 'request.id as id', 'request.request_start_time as date', 'request.payment_mode', 'request.is_started', 'request.is_walker_arrived', 'request.payment_mode', 'request.is_completed', 'request.is_paid', 'request.is_walker_started', 'request.confirmed_walker'
                        , 'request.status', 'request.time', 'request.distance', 'request.total', 'request.is_cancelled', 'request.transfer_amount','walker_type.name','request_services.total as total_service_amount',
						'request.promo_payment','request.additional_fee')
                ->orderBy('request.id', 'DESC')
                ->where('request.dispatcher_id', '=', $user_id )
				->where('request.is_manual', '=', '0')
                ->paginate(10);
        
        $setting = Settings::where('key', 'paypal')->first();
        $title = 'Automatic Rides'; /* 'Request'
        
       return View::make('dispatcher.myservice')
                        ->with('title', $title)
                        ->with('page', 'walks')
                        ->with('walks', $walks)
                        ->with('setting', $setting);
    }*/

    public function SubmittedRides(){
        $countarray = array();
        $countarray['submitted'] = $this->SubmittedRidesCount();
        $countarray['confirmed'] = $this->ConfirmedRidesCount();
        $countarray['cancelled'] = $this->CancelledRidesCount();
        $countarray['completed'] = $this->CompletedRidesCount();

        $dispatchers = Dispatcher::where('is_admin', '=', 0)->orderBy('contact_name')->get();

        if(Session::get('is_admin') == 1) {
			$time = date("Y-m-d H:i:s");

            $query = "SELECT walker.* "
                . "FROM walker "
                . "where is_approved = 1 AND "
                . "deleted_at IS NULL "
                . "order by id DESC";
            $available_query = "SELECT walker.* "
                . "FROM walker "
                . "where is_available = 1 AND "
                . "is_active = 1 AND "
                . "is_approved = 1 AND "
                . "TIMESTAMPDIFF(SECOND, lastseen, now()) < 300 AND "
                . "deleted_at IS NULL "
                . "order by id DESC";
            Log::info("Driver SQL = " . $available_query);
            $available_results = DB::select(DB::raw($available_query));
            $results = DB::select(DB::raw($query));
            $walks = DB::table('request')
                ->leftJoin('ride_details', 'request.id','=','ride_details.request_id')
                ->leftJoin('dispatcher_assigned', 'request.dispatcher_assigned_id', '=', 'dispatcher_assigned.id')
                ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                ->leftJoin('assigned_dispatcher_request', 'request.id', '=', 'assigned_dispatcher_request.request_id')
                ->leftJoin('walker_type', 'walker_type.id', '=', 'request.service_type')
                ->leftJoin('dispatcher', 'dispatcher.id', '=', 'assigned_dispatcher_request.assigned_dispatcher_id')
                ->leftJoin('enterprise_client', 'request.healthcare_id', '=', 'enterprise_client.id')
                ->leftJoin('hospital_providers','hospital_providers.id','=','request.hospital_provider_id')
                ->leftJoin('payment', function($join)
                    {
                        $join->on('payment.dispatcher_assigned_id', '=', 'request.dispatcher_assigned_id');
                        $join->on(DB::raw('payment.is_default'), DB::raw('='),DB::raw(1));
                    })
                ->select('dispatcher_assigned.contact_name as owner_contact_name',
                    'dispatcher_assigned.phone as owner_phone',
                    'request.passenger_contact_name as passenger_contact_name',
                    'request.passenger_phone as passenger_phone',
                    'request.id as id', 'request.request_start_time as date',
                    'request.payment_mode', 'request.is_started',
                    'request.is_walker_arrived', 'request.payment_mode',
                    'request.is_completed', 'request.is_paid',
                    'request.current_walker',
                    'request.is_walker_started', 'request.confirmed_walker',
                    'request.status', 'request.time',
                    'request.distance', 'request.total', 'request.is_cancelled',
                    'request.transfer_amount', 'walker_type.name',
                    'request.total as total_service_amount',
                    'request.promo_payment', 'request.additional_fee',
                    'request.driver_name', 'request.driver_phone',
                    'request.is_confirmed', 'request.src_address', 'request.dest_address',
                    'assigned_dispatcher_id', 'assignee_dispatcher_id',
                    'dispatcher.contact_name as assigned_contact_name',
                    'request.service_type as requested_type',
                    'assigned_dispatcher_request.status as tp_status',
                    'assigned_dispatcher_request.is_cancelled as assigned_cancel_status',
                    'request.service_type',
                    'ride_details.oxygen_mask as oxygen_mask',
                    'ride_details.height as user_height',
                    'ride_details.weight as user_weight',
                    'ride_details.condition as user_condition',
                    'ride_details.respirator as respirator',
                    'ride_details.any_tubing as any_tubing',
                    'ride_details.colostomy_bag as colostomy_bag',
                    'ride_details.any_attachments as any_attachments',
                    'request.attendant_travelling as attendant_travelling',
                    'hospital_providers.provider_name',
                    'enterprise_client.contact_name as requester_contact_name',
                    'enterprise_client.company as provider_company',
                    'request.dispatcher_assigned_id as dispatcher_assigned_id',
                    'ride_details.agent_contact_name','payment.id as paymentid')
                ->orderBy('request.id', 'DESC')
                //->where('request.dispatcher_id', '=', $user_id)
                ->where('request.is_confirmed', '=', '0')
                ->where('request.is_cancelled', '=', '0')
                ->where('request.is_completed', '=', '0')
                ->paginate(10);
        } else{

            $user_id = Session::get('user_id');
            $dispatcher = Dispatcher::find($user_id);
            $transportation_provider_id = TransportationProvider::where('id','=',$dispatcher->transportation_provider_id)->first();


            $query = "SELECT walker.* "
                . "FROM walker "
                . "where is_approved = 1 AND "
                . "transportation_provider_id = $transportation_provider_id->id AND "
                . "deleted_at IS NULL "
                . "order by id DESC";
            $available_query = "SELECT walker.* "
                . "FROM walker "
                . "where is_available = 1 AND "
                . "transportation_provider_id = $transportation_provider_id->id AND "
                . "is_active = 1 AND "
                . "is_approved = 1 AND "
                . "deleted_at IS NULL "
                . "order by id DESC";
            $available_results = DB::select(DB::raw($available_query));
            $results = DB::select(DB::raw($query));

            $walks = DB::table('assigned_dispatcher_request')
                ->Join('request', 'request.id', '=', 'assigned_dispatcher_request.request_id')
                ->leftJoin('ride_details', 'request.id','=','ride_details.request_id')
                ->leftJoin('dispatcher_assigned', 'request.dispatcher_assigned_id', '=', 'dispatcher_assigned.id')
                ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                ->leftJoin('walker_type', 'walker_type.id', '=', 'request.service_type')
                ->leftJoin('dispatcher', 'dispatcher.id', '=', 'assigned_dispatcher_request.assigned_dispatcher_id')
                ->leftJoin('enterprise_client', 'request.healthcare_id', '=', 'enterprise_client.id')
                ->leftJoin('hospital_providers','hospital_providers.id','=','request.hospital_provider_id')
                ->select('assigned_dispatcher_request.*', 'dispatcher_assigned.contact_name as owner_contact_name',
                    'dispatcher_assigned.phone as owner_phone',
                    'request.passenger_contact_name as passenger_contact_name',
                    'request.passenger_phone as passenger_phone',
                    'request.id as id', 'request.request_start_time as date',
                    'request.payment_mode', 'request.is_started',
                    'request.is_walker_arrived', 'request.payment_mode',
                    'request.current_walker',
                    'request.is_completed', 'request.is_paid',
                    'request.is_walker_started', 'request.confirmed_walker',
                    'request.status', 'request.time',
                    'request.distance', 'request.total', 'request.is_cancelled',
                    'request.transfer_amount', 'walker_type.name',
                    'request.total as total_service_amount',
                    'request.promo_payment', 'request.additional_fee',
                    'request.driver_name', 'request.driver_phone',
                    'request.is_confirmed', 'request.src_address', 'request.dest_address',
                    'assigned_dispatcher_id', 'assignee_dispatcher_id',
                    'dispatcher.contact_name as assigned_contact_name',
                    'request.service_type as requested_type',
                    'assigned_dispatcher_request.status as tp_status',
                    'assigned_dispatcher_request.is_cancelled as assigned_cancel_status',
                    'request.service_type',
                    'ride_details.oxygen_mask as oxygen_mask',
                    'ride_details.height as user_height',
                    'ride_details.weight as user_weight',
                    'ride_details.condition as user_condition',
                    'ride_details.respirator as respirator',
                    'ride_details.any_tubing as any_tubing',
                    'ride_details.colostomy_bag as colostomy_bag',
                    'ride_details.any_attachments as any_attachments',
                    'request.attendant_travelling as attendant_travelling',
                    'hospital_providers.provider_name',
                    'enterprise_client.contact_name as requester_contact_name',
                    'enterprise_client.company as provider_company',
                    'request.dispatcher_assigned_id as dispatcher_assigned_id',
                    'ride_details.agent_contact_name')
                ->orderBy('assigned_dispatcher_request.id', 'DESC')
                ->where('assigned_dispatcher_request.assigned_dispatcher_id', '=', $user_id)
                ->where('request.is_confirmed', '=', '0')
                ->where('request.is_cancelled', '=', '0')
                ->where('request.is_completed', '=', '0')
                ->where('assigned_dispatcher_request.is_cancelled', '=', '0')
                ->paginate(10);
        }
        $admin = Session::get('is_admin');
        $title = 'UnAssigned Rides'; /* 'Request' */

        return View::make('dispatcher.submittedrides')
            ->with('title', $title)
            ->with('page', 'walks')
            ->with('walks', $walks)
            ->with('available_drivers',$available_results)
            ->with('allDrivers',$results)
            ->with('dispatchers',$dispatchers)
            ->with('admin',$admin)
            ->with('countarray',$countarray);
    }

    public function ConfirmedRides(){

        $countarray = array();
        $countarray['submitted'] = $this->SubmittedRidesCount();
        $countarray['confirmed'] = $this->ConfirmedRidesCount();
        $countarray['cancelled'] = $this->CancelledRidesCount();
        $countarray['completed'] = $this->CompletedRidesCount();

        $user_id = Session::get('user_id');

        if(Session::get('is_admin') ==1) {
            $walks = DB::table('request')
                ->leftJoin('ride_details', 'request.id','=','ride_details.request_id')
                ->leftJoin('dispatcher_assigned', 'request.dispatcher_assigned_id', '=', 'dispatcher_assigned.id')
                ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                ->leftJoin('assigned_dispatcher_request', 'request.id', '=', 'assigned_dispatcher_request.request_id')
                ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
                ->leftJoin('walker_type', 'walker_type.id', '=', 'request.service_type')
                ->leftJoin('dispatcher', 'dispatcher.id', '=', 'assigned_dispatcher_request.assigned_dispatcher_id')
                ->leftJoin('enterprise_client', 'request.healthcare_id', '=', 'enterprise_client.id')
                ->leftJoin('hospital_providers','hospital_providers.id','=','request.hospital_provider_id')
                ->select('dispatcher_assigned.contact_name as owner_contact_name',
                    'dispatcher_assigned.phone as owner_phone',
                    'owner.contact_name as ownercontact_name',
                    'owner.phone as ownerphone',
                    'walker.contact_name as walker_contact_name',
                    'walker.phone as walker_phone',
                    'walker.id as walker_id',
                    'request.id as id', 'request.request_start_time as date',
                    'request.payment_mode', 'request.is_started',
                    'request.is_walker_arrived', 'request.payment_mode',
                    'request.is_completed', 'request.is_paid',
                    'request.is_walker_started', 'request.confirmed_walker'
                    , 'request.status', 'request.time',
                    'request.distance', 'request.total', 'request.is_cancelled',
                    'request.transfer_amount', 'walker_type.name',
                    'request.total as total_service_amount',
                    'request.promo_payment', 'request.additional_fee',
                    'request.driver_name', 'request.driver_phone',
                    'request.is_confirmed', 'request.src_address', 'request.dest_address',
                    'assigned_dispatcher_id', 'assignee_dispatcher_id',
                    'dispatcher.contact_name as assigned_contact_name',
                    'assigned_dispatcher_request.status as tp_status',
                    'assigned_dispatcher_request.is_cancelled as assigned_cancel_status',
                    'ride_details.oxygen_mask as oxygen_mask',
                    'ride_details.height as user_height',
                    'ride_details.weight as user_weight',
                    'ride_details.condition as user_condition',
                    'ride_details.respirator as respirator',
                    'ride_details.any_tubing as any_tubing',
                    'ride_details.colostomy_bag as colostomy_bag',
                    'ride_details.any_attachments as any_attachments',
                    'request.attendant_travelling as attendant_travelling',
                    'hospital_providers.provider_name',
                    'enterprise_client.contact_name as requester_contact_name',
                    'enterprise_client.company as provider_company',
                    'request.dispatcher_assigned_id as dispatcher_assigned_id',
                    'ride_details.agent_contact_name','request.estimated_time','request.is_manual','request.driver_name','request.driver_phone')
                ->orderBy('request.id', 'DESC')
                //->where('request.dispatcher_id', '=', $user_id)
                ->where('request.is_confirmed', '=', '1')
                ->where('request.is_cancelled', '=', '0')
                ->where('request.is_completed', '=', '0')
                ->paginate(10);
        } else{
            $walks = DB::table('assigned_dispatcher_request')
                ->Join('request', 'request.id', '=', 'assigned_dispatcher_request.request_id')
                ->leftJoin('ride_details', 'request.id','=','ride_details.request_id')
                ->leftJoin('dispatcher_assigned', 'request.dispatcher_assigned_id', '=', 'dispatcher_assigned.id')
                ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
                ->leftJoin('walker_type', 'walker_type.id', '=', 'request.service_type')
                ->leftJoin('dispatcher', 'dispatcher.id', '=', 'assigned_dispatcher_request.assigned_dispatcher_id')
                ->leftJoin('enterprise_client', 'request.healthcare_id', '=', 'enterprise_client.id')
                ->leftJoin('hospital_providers','hospital_providers.id','=','request.hospital_provider_id')
                ->select('assigned_dispatcher_request.*', 'dispatcher_assigned.contact_name as owner_contact_name',
                    'dispatcher_assigned.phone as owner_phone',
                    'owner.contact_name as ownercontact_name',
                    'owner.phone as ownerphone',
                    'walker.contact_name as walker_contact_name',
                    'walker.phone as walker_phone',
                    'walker.id as walker_id',
                    'request.id as id', 'request.request_start_time as date',
                    'request.payment_mode', 'request.is_started',
                    'request.is_walker_arrived', 'request.payment_mode',
                    'request.is_completed', 'request.is_paid',
                    'request.is_walker_started', 'request.confirmed_walker'
                    , 'request.status', 'request.time',
                    'request.distance', 'request.total', 'request.is_cancelled',
                    'request.transfer_amount', 'walker_type.name',
                    'request.total as total_service_amount',
                    'request.promo_payment', 'request.additional_fee',
                    'request.driver_name', 'request.driver_phone',
                    'request.is_confirmed', 'request.src_address', 'request.dest_address',
                    'assigned_dispatcher_id', 'assignee_dispatcher_id',
                    'dispatcher.contact_name as assigned_contact_name',
                    'assigned_dispatcher_request.status as tp_status',
                    'assigned_dispatcher_request.is_cancelled as assigned_cancel_status',
                    'ride_details.oxygen_mask as oxygen_mask',
                    'ride_details.height as user_height',
                    'ride_details.weight as user_weight',
                    'ride_details.condition as user_condition',
                    'ride_details.respirator as respirator',
                    'ride_details.any_tubing as any_tubing',
                    'ride_details.colostomy_bag as colostomy_bag',
                    'ride_details.any_attachments as any_attachments',
                    'request.attendant_travelling as attendant_travelling',
                    'hospital_providers.provider_name',
                    'enterprise_client.contact_name as requester_contact_name',
                    'enterprise_client.company as provider_company',
                    'request.dispatcher_assigned_id as dispatcher_assigned_id',
                    'ride_details.agent_contact_name','request.estimated_time','request.is_manual','request.driver_name','request.driver_phone')
                ->orderBy('assigned_dispatcher_request.id', 'DESC')
                ->where('assigned_dispatcher_request.assigned_dispatcher_id', '=', $user_id)
                ->where('request.is_confirmed', '=', '1')
                ->where('request.is_cancelled', '=', '0')
                ->where('request.is_completed', '=', '0')
                ->where('assigned_dispatcher_request.is_cancelled', '=', '0')
                ->paginate(10);
        }

        $title = 'Confirmed Rides'; /* 'Request' */

        return View::make('dispatcher.confirmedrides')
            ->with('title', $title)
            ->with('page', 'walks')
            ->with('walks', $walks)
            ->with('countarray',$countarray);
    }


    public function GenerateReceipt( $request_id,$driver_name,$phone_no_driver,$total_cost,$est_time) {

        if ($request =RideRequest::find($request_id))
        {

                $settings = Settings::where('key', 'admin_email_address')->first();
                $admin_email = $settings->value;
                if($request->dispatcher_assigned_id != '') {
                    $passengerinfo = DispatcherAssigned::where('id', $request->dispatcher_assigned_id)->first();
                }else{
                    $passengerinfo = Owner::where('id','=',$request->owner_id)->first();
                }
                $passenger_name = $passengerinfo->contact_name;
                // request ended
              RideRequest::where('id', '=', $request_id)->update(array('is_confirmed' => 1,
                    'driver_name' => $driver_name, 'driver_phone' => $phone_no_driver, 'total' =>$total_cost,
                    'estimated_time'=>$est_time));

                $settings = Settings::where('key', 'default_distance_unit')->first();
                $unit = $settings->value;
                if ($unit == 0) {
                    $unit_set = 'kms';
                } elseif ($unit == 1) {
                    $unit_set = 'miles';
                }
                $request =RideRequest::find($request->id);
                $estimate = $request->estimated_time;
                $ride_type = $request->is_scheduled;
                if((($estimate)/60.00)>=1.00) {
                    $hours = floor($estimate / 60);
                    $minutes = ($estimate % 60);
                    if ($minutes == 0) {
                        $estimated_time = $hours . ' ' . 'hours';
                    }
                    else{
                        $estimated_time = $hours . ' ' . 'hr' . ' ' . $minutes . 'min';
                    }
                }
                else{
                    $estimated_time =$estimate . "min";
                }
                $providertype = ProviderType::where('id', $request->service_type)->first();

                $file_name_prefix = 'Butterfli';

                if($request->healthcare_id != '') {
                    $HealthCare = EnterpriseClient::find($request->healthcare_id);
                    $file_name_prefix = $HealthCare->company;
                }

                $datetime = new DateTime($request->request_start_time);
                $datetime->format('Y-m-d H:i:s') . "\n";
                $user_time = new DateTimeZone($request->time_zone);
                $datetime->setTimezone($user_time);
                $newpickuptime = $datetime->format('Y-m-d H:i:s');

                $date = new DateTime($request->request_start_time);
                $date->format('Y-m-d') . "\n";
                $user_time = new DateTimeZone($request->time_zone);
                $date->setTimezone($user_time);
                $pickupdate = $date->format('Y-m-d');

                $time = new DateTime($request->request_start_time);
                $time->format('h:ia') . "\n";
                $user_time = new DateTimeZone($request->time_zone);
                $time->setTimezone($user_time);
                $pickuptime = $time->format('h:ia');

                /* PDF GENERATOR CODE START */
                $parameter = array();
                $parameter['service_number'] = $request->id;
                $parameter['service_date'] = $newpickuptime;
                $parameter['passenger_name'] = $passenger_name;
                $parameter['pickupaddress'] = $request->src_address;
                $parameter['dropoffaddress'] = $request->dest_address;
                $parameter['base_price'] = $providertype->base_price;
                $parameter['base_mileage_fee'] = $providertype->price_per_unit_distance;
                $parameter['distance'] = $request->distance;
                $parameter['total_price'] = $request->total;
                $parameter['admin_email_address'] = $admin_email;

                $file_name = $file_name_prefix.'_'.$request->id.'_'.uniqid().'.pdf';
                $ext = 'pdf';
                $file_path = public_path('image'). '/uploads/' .$file_name . "." . $ext;
                $file_path = str_replace(' ', '_', $file_path);

                try {
                    $pdf = PDF::loadView('driver_invoice', $parameter)->setPaper('legal')->setOrientation('portrait')->setWarnings(false);
                    $output = $pdf->output();
                }
                catch (Exception $exception) {
                    echo $exception;
                }
//                file_put_contents($file_path, $output);
                /* PDF GENERATOR CODE  END*/

                /* Uplaod this file to s3bucket */
/*                if (Config::get('app.s3_bucket') != "") {

                    $s3 = App::make('aws')->get('s3');

                    $s3->putObject(array(
                        'Bucket' => Config::get('app.s3_bucket'),
                        'Key' => "uploads/healthcare/receipts/" . $file_name,
                        'SourceFile' =>$file_path,
                    ));


                    $s3->putObjectAcl(array(
                        'Bucket' => Config::get('app.s3_bucket'),
                        'Key' => "uploads/healthcare/receipts/" . $file_name,
                        'ACL' => 'public-read'
                    ));

                    $final_file_name = "uploads/healthcare/receipts/" . $file_name;

                    $s3_url = $s3->getObjectUrl(Config::get('app.s3_bucket'), $final_file_name);
                    $localDispatcherConfifile = $file_path;
                    unlink_image($localDispatcherConfifile);
                } else {
                    $s3_url = $file_path;
                }
*/
                $agent = DispatcherAgent::where('healthcare_id', '=', $request->healthcare_id)->first();

                //Add data to HealthCareDocuments
                $healthcaredocuments = new HealthCareDocuments;
                $healthcaredocuments->request_id = $request->id;
                $healthcaredocuments->healthcare_id = $request->healthcare_id;
                if($agent){
                    $healthcaredocuments->agent_id = $agent->id;
                }
//                $healthcaredocuments->document_url = $s3_url;
                $healthcaredocuments->save();

                // Send SMS
                if($request->healthcare_id != '') {
                $pattern = "Ride has been confirmed for the request-id: ".$request->id." with driver: ".$driver_name." Phone: ".$phone_no_driver;


                    sms_notification($request->healthcare_id, 'operator', $pattern);
                    sms_notification($request->healthcare_id, 'ride_assignee', $pattern);
                    sms_notification($request->healthcare_id, 'ride_assignee_2', $pattern);
                    sms_notification($request->healthcare_id, 'ride_assignee_3', $pattern);

                $pickup_url = "https://www.google.com/maps/dir/".$request->latitude.",".$request->longitude."/";
                $dropoff_url = "https://www.google.com/maps/dir/".$request->D_latitude.",".$request->D_longitude."/";

                $pattern1 = "Ride Confirmed for the request-id: ".$request->id;
                $pattern1.= " Passenger Name: ".$passenger_name;
                $pattern1.= " Passenger Phone: ".$passengerinfo->phone;
                $pattern1.= " Pickup Address: ".$request->src_address." ".$pickup_url;
                $pattern1.= " Dropoff Address: ".$request->dest_address." ".$dropoff_url;


                sms_notification(1, 'manual_driver', $pattern1, $phone_no_driver);


                $settings = Settings::where('key', 'ride_assignee_phone_number')->first();
                $ride_assignee_phone_number = $settings->value;
                $follow_url = web_url() . "/healthcare/myrides";


                $agent_name = $request->agent_contact_name;
                $pattern = array('driver_name'=> $driver_name, 'driver_phone'=> $phone_no_driver,
                    'passenger_name'=>$passenger_name, 'passenger_phone'=> $passengerinfo->phone,
                    'pickup_time'=>$pickuptime,'pickup_date'=>$pickupdate, 'pickup_location'=> $request->src_address,
                    'dropoff_location'=> $request->dest_address,'butterfli_dispatcher_phno'=>$ride_assignee_phone_number,
                    'admin_email' => $admin_email,'trip_id' => $request->id, 'follow_url' => $follow_url,
                    'agent_name'=>$agent_name,'estimated_time'=>$estimated_time,'all_radio'=>$ride_type);
                $subject = "Your ride is confirmed";
                email_notification($request->healthcare_id, 'operator', $pattern, $subject, 'ride_confirm', 'imp');
                email_notification($request->healthcare_id, 'ride_assignee', $pattern, $subject, 'ride_confirm', 'imp');
                email_notification($request->healthcare_id, 'ride_assignee_2', $pattern, $subject, 'ride_confirm', 'imp');
                email_notification($request->healthcare_id, 'ride_assignee_3', $pattern, $subject, 'ride_confirm', 'imp');
                }
                return 1;
        } else {
            return 2;
        }

    }

    public function CancelledRides(){

        $countarray = array();
        $countarray['submitted'] = $this->SubmittedRidesCount();
        $countarray['confirmed'] = $this->ConfirmedRidesCount();
        $countarray['cancelled'] = $this->CancelledRidesCount();
        $countarray['completed'] = $this->CompletedRidesCount();

        $user_id = Session::get('user_id');
        if(Session::get('is_admin') ==1) {
            $walks = DB::table('request')
                ->leftJoin('ride_details', 'request.id','=','ride_details.request_id')
                ->leftJoin('dispatcher_assigned', 'request.dispatcher_assigned_id', '=', 'dispatcher_assigned.id')
                ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                ->leftJoin('assigned_dispatcher_request', 'request.id', '=', 'assigned_dispatcher_request.request_id')
                ->leftJoin('walker_type', 'walker_type.id', '=', 'request.service_type')
                ->leftJoin('dispatcher', 'dispatcher.id', '=', 'assigned_dispatcher_request.assigned_dispatcher_id')
                ->leftJoin('enterprise_client', 'request.healthcare_id', '=', 'enterprise_client.id')
                ->leftJoin('hospital_providers','hospital_providers.id','=','request.hospital_provider_id')
                ->leftJoin('rating_transportation_provider', 'rating_transportation_provider.request_id', '=', 'request.id')
                ->select('dispatcher_assigned.contact_name as owner_contact_name',
                    'dispatcher_assigned.phone as owner_phone',
                    'owner.contact_name as ownercontact_name',
                    'owner.phone as ownerphone',
                    'request.id as id', 'request.request_start_time as date',
                    'request.payment_mode', 'request.is_started',
                    'request.is_walker_arrived', 'request.payment_mode',
                    'request.is_completed', 'request.is_paid',
                    'request.is_walker_started', 'request.confirmed_walker'
                    , 'request.status', 'request.time',
                    'request.distance', 'request.total', 'request.is_cancelled',
                    'request.transfer_amount', 'walker_type.name',
                    'request.total as total_service_amount',
                    'request.promo_payment', 'request.additional_fee',
                    'request.driver_name', 'request.driver_phone',
                    'request.is_confirmed', 'request.src_address', 'request.dest_address',
                    'assigned_dispatcher_id', 'assignee_dispatcher_id',
                    'dispatcher.contact_name as assigned_contact_name',
                    'assigned_dispatcher_request.status as tp_status',
                    'assigned_dispatcher_request.is_cancelled as assigned_cancel_status',
                    'ride_details.oxygen_mask as oxygen_mask',
                    'ride_details.height as user_height',
                    'ride_details.weight as user_weight',
                    'ride_details.condition as user_condition',
                    'ride_details.respirator as respirator',
                    'ride_details.any_tubing as any_tubing',
                    'ride_details.colostomy_bag as colostomy_bag',
                    'ride_details.any_attachments as any_attachments',
                    'request.attendant_travelling as attendant_travelling',
                    'hospital_providers.provider_name',
                    'enterprise_client.contact_name as requester_contact_name',
                    'enterprise_client.company as provider_company',
                    'ride_details.agent_contact_name','rating_transportation_provider.tp_id')
                ->orderBy('request.id', 'DESC')
                //->where('request.dispatcher_id', '=', $user_id)
                ->where('request.is_cancelled', '=', '1')
                ->paginate(10);
        } else{
            $walks = DB::table('assigned_dispatcher_request')
                ->Join('request', 'request.id', '=', 'assigned_dispatcher_request.request_id')
                ->leftJoin('ride_details', 'request.id','=','ride_details.request_id')
                ->leftJoin('dispatcher_assigned', 'request.dispatcher_assigned_id', '=', 'dispatcher_assigned.id')
                ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                ->leftJoin('walker_type', 'walker_type.id', '=', 'request.service_type')
                ->leftJoin('dispatcher', 'dispatcher.id', '=', 'assigned_dispatcher_request.assigned_dispatcher_id') ->leftJoin('enterprise_client', 'request.healthcare_id', '=', 'enterprise_client.id')
                ->leftJoin('hospital_providers','hospital_providers.id','=','request.hospital_provider_id')
                ->leftJoin('rating_transportation_provider', 'rating_transportation_provider.request_id', '=', 'request.id')
                ->select('assigned_dispatcher_request.*', 'dispatcher_assigned.contact_name as owner_contact_name',
                    'dispatcher_assigned.phone as owner_phone',
                    'owner.contact_name as ownercontact_name',
                    'owner.phone as ownerphone',
                    'request.id as id', 'request.request_start_time as date',
                    'request.payment_mode', 'request.is_started',
                    'request.is_walker_arrived', 'request.payment_mode',
                    'request.is_completed', 'request.is_paid',
                    'request.is_walker_started', 'request.confirmed_walker'
                    , 'request.status', 'request.time',
                    'request.distance', 'request.total', 'request.is_cancelled',
                    'request.transfer_amount', 'walker_type.name',
                    'request.total as total_service_amount',
                    'request.promo_payment', 'request.additional_fee',
                    'request.driver_name', 'request.driver_phone',
                    'request.is_confirmed', 'request.src_address', 'request.dest_address',
                    'assigned_dispatcher_id', 'assignee_dispatcher_id',
                    'dispatcher.contact_name as assigned_contact_name',
                    'assigned_dispatcher_request.status as tp_status',
                    'assigned_dispatcher_request.is_cancelled as assigned_cancel_status',
                    'ride_details.oxygen_mask as oxygen_mask',
                    'ride_details.height as user_height',
                    'ride_details.weight as user_weight',
                    'ride_details.condition as user_condition',
                    'ride_details.respirator as respirator',
                    'ride_details.any_tubing as any_tubing',
                    'ride_details.colostomy_bag as colostomy_bag',
                    'ride_details.any_attachments as any_attachments',
                    'request.attendant_travelling as attendant_travelling',
                    'hospital_providers.provider_name',
                    'enterprise_client.contact_name as requester_contact_name',
                    'enterprise_client.company as provider_company',
                    'ride_details.agent_contact_name')
                ->orderBy('assigned_dispatcher_request.id', 'DESC')
                ->where('assigned_dispatcher_request.assigned_dispatcher_id', '=', $user_id)
                ->where('assigned_dispatcher_request.is_cancelled', '=', '1')
                ->paginate(10);
        }
        $admin = Session::get('is_admin');
        $title = 'Cancelled Rides'; /* 'Request' */

        return View::make('dispatcher.cancelledrides')
            ->with('title', $title)
            ->with('page', 'walks')
            ->with('walks', $walks)
            ->with('admin',$admin)
            ->with('countarray',$countarray);
    }

    public function CompletedRides(){

        $countarray = array();
        $countarray['submitted'] = $this->SubmittedRidesCount();
        $countarray['confirmed'] = $this->ConfirmedRidesCount();
        $countarray['cancelled'] = $this->CancelledRidesCount();
        $countarray['completed'] = $this->CompletedRidesCount();

        $user_id = Session::get('user_id');
        if(Session::get('is_admin') ==1) {
            $walks = DB::table('request')
                ->leftJoin('ride_details', 'request.id','=','ride_details.request_id')
                ->leftJoin('dispatcher_assigned', 'request.dispatcher_assigned_id', '=', 'dispatcher_assigned.id')
                ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                ->leftJoin('assigned_dispatcher_request', 'request.id', '=', 'assigned_dispatcher_request.request_id')
                ->leftJoin('walker_type', 'walker_type.id', '=', 'request.service_type')
                ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
                ->leftJoin('dispatcher', 'dispatcher.id', '=', 'assigned_dispatcher_request.assigned_dispatcher_id')
                ->leftJoin('enterprise_client', 'request.healthcare_id', '=', 'enterprise_client.id')
                ->leftJoin('hospital_providers','hospital_providers.id','=','request.hospital_provider_id')
                ->leftJoin('review_walker', 'review_walker.request_id', '=', 'request.id')
                ->leftJoin('rating_transportation_provider', 'rating_transportation_provider.request_id', '=', 'request.id')
                ->select('dispatcher_assigned.contact_name as owner_contact_name',
                    'dispatcher_assigned.phone as owner_phone',
                    'owner.contact_name as ownercontact_name',
                    'owner.phone as ownerphone',
                    'walker.contact_name as walker_contact_name',
                    'walker.phone as walker_phone',
                    'walker.id as walker_id',
                    'request.id as id', 'request.request_start_time as date',
                    'request.payment_mode', 'request.is_started',
                    'request.is_walker_arrived', 'request.payment_mode',
                    'request.is_completed', 'request.is_paid',
                    'request.is_walker_started', 'request.confirmed_walker'
                    , 'request.status', 'request.time',
                    'request.distance', 'request.total', 'request.is_cancelled',
                    'request.transfer_amount', 'walker_type.name',
                    'request.total as total_service_amount',
                    'request.promo_payment', 'request.additional_fee',
                    'request.driver_name', 'request.driver_phone',
                    'request.is_confirmed', 'request.src_address', 'request.dest_address',
                    'assigned_dispatcher_id', 'assignee_dispatcher_id',
                    'dispatcher.contact_name as assigned_contact_name',
                    'assigned_dispatcher_request.status as tp_status',
                    'assigned_dispatcher_request.is_cancelled as assigned_cancel_status',
                    'ride_details.oxygen_mask as oxygen_mask',
                    'ride_details.height as user_height',
                    'ride_details.weight as user_weight',
                    'ride_details.condition as user_condition',
                    'ride_details.respirator as respirator',
                    'ride_details.any_tubing as any_tubing',
                    'ride_details.colostomy_bag as colostomy_bag',
                    'ride_details.any_attachments as any_attachments',
                    'request.attendant_travelling as attendant_travelling',
                    'hospital_providers.provider_name',
                    'enterprise_client.contact_name as requester_contact_name',
                    'enterprise_client.company as provider_company',
                    'ride_details.agent_contact_name',
                    'request.is_manual','request.driver_name','request.driver_phone',
                    'review_walker.rating','review_walker.comment','rating_transportation_provider.tp_id')
                ->orderBy('request.id', 'DESC')
                //->where('request.dispatcher_id', '=', $user_id)
                ->where('request.is_completed', '=', '1')
                ->paginate(10);
        } else{
            $walks = DB::table('assigned_dispatcher_request')
                ->Join('request', 'request.id', '=', 'assigned_dispatcher_request.request_id')
                ->leftJoin('ride_details', 'request.id','=','ride_details.request_id')
                ->leftJoin('dispatcher_assigned', 'request.dispatcher_assigned_id', '=', 'dispatcher_assigned.id')
                ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                ->leftJoin('walker_type', 'walker_type.id', '=', 'request.service_type')
                ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
                ->leftJoin('review_walker', 'review_walker.request_id', '=', 'request.id')
                ->leftJoin('dispatcher', 'dispatcher.id', '=', 'assigned_dispatcher_request.assigned_dispatcher_id')
                ->leftJoin('enterprise_client', 'request.healthcare_id', '=', 'enterprise_client.id')
                ->leftJoin('hospital_providers','hospital_providers.id','=','request.hospital_provider_id')
                ->select('assigned_dispatcher_request.*', 'dispatcher_assigned.contact_name as owner_contact_name',
                    'dispatcher_assigned.phone as owner_phone',
                    'owner.contact_name as ownercontact_name',
                    'owner.phone as ownerphone',
                    'walker.contact_name as walker_contact_name',
                    'walker.phone as walker_phone',
                    'walker.id as walker_id',
                    'request.id as id', 'request.request_start_time as date',
                    'request.payment_mode', 'request.is_started',
                    'request.is_walker_arrived', 'request.payment_mode',
                    'request.is_completed', 'request.is_paid',
                    'request.is_walker_started', 'request.confirmed_walker'
                    , 'request.status', 'request.time',
                    'request.distance', 'request.total', 'request.is_cancelled',
                    'request.transfer_amount', 'walker_type.name',
                    'request.total as total_service_amount',
                    'request.promo_payment', 'request.additional_fee',
                    'request.driver_name', 'request.driver_phone',
                    'request.is_confirmed', 'request.src_address', 'request.dest_address',
                    'assigned_dispatcher_id', 'assignee_dispatcher_id',
                    'dispatcher.contact_name as assigned_contact_name',
                    'assigned_dispatcher_request.status as tp_status',
                    'assigned_dispatcher_request.is_cancelled as assigned_cancel_status',
                    'ride_details.oxygen_mask as oxygen_mask',
                    'ride_details.height as user_height',
                    'ride_details.weight as user_weight',
                    'ride_details.condition as user_condition',
                    'ride_details.respirator as respirator',
                    'ride_details.any_tubing as any_tubing',
                    'ride_details.colostomy_bag as colostomy_bag',
                    'ride_details.any_attachments as any_attachments',
                    'request.attendant_travelling as attendant_travelling',
                    'hospital_providers.provider_name',
                    'enterprise_client.contact_name as requester_contact_name',
                    'enterprise_client.company as provider_company',
                    'ride_details.agent_contact_name',
                    'request.is_manual','request.driver_name','request.driver_phone',
                    'review_walker.rating','review_walker.comment')
                ->orderBy('assigned_dispatcher_request.id', 'DESC')
                ->where('assigned_dispatcher_request.assigned_dispatcher_id', '=', $user_id)
                ->where('request.is_completed', '=', '1')
                ->paginate(10);
        }

        $admin = Session::get('is_admin');
        $title = 'My Rides'; /* 'Request' */

        return View::make('dispatcher.myservice')
            ->with('title', $title)
            ->with('page', 'walks')
            ->with('walks', $walks)
            ->with('admin',$admin)
            ->with('countarray',$countarray);
    }

	public function servicerequest()
	{
        $dispatcher = Dispatcher::find(Session::get('user_id'));
        if(! isset($dispatcher)) {
            return Redirect::to('dispatcher/signin');

        }
        
        $query = "SELECT * FROM walker_type WHERE is_visible=1 AND id!='1'";
        $services = DB::select(DB::raw($query));

        $countarray = array();
        $countarray['submitted'] = $this->SubmittedRidesCount();
        $countarray['confirmed'] = $this->ConfirmedRidesCount();
        $countarray['cancelled'] = $this->CancelledRidesCount();
        $countarray['completed'] = $this->CompletedRidesCount();
        $hospital_data = "SELECT * FROM hospital_providers WHERE is_active=1";
        $hospital_provider = DB::select(DB::raw($hospital_data));
        $healthcare_data = "SELECT * FROM enterprise_client WHERE is_active=1";
        $healthcare_company = DB::select(DB::raw($healthcare_data));
		$paymentflag = '0';

        $stops = null;
        if($dispatcher->is_admin) {
            // kludgy for now until we expand in to more structure with city clients
            $stops = readStopsForSanClemente();
        }

		return View::make('dispatcher.requestservice')
                        ->with('title', 'Request service')
                        ->with('page', 'Request service')
						->with('paymentflag', $paymentflag)
                        ->with('dispatcher', $dispatcher)
                        ->with('stops', $stops)
                        ->with('hospital_provider', $hospital_provider)
                        ->with('healthcare_company', $healthcare_company)
                        ->with('services', $services)
                        ->with('countarray',$countarray);
	}
	
   public function getdrivers(){
		$address = $_POST['address'];
		$prepAddr = str_replace(' ','+',$address);
        $geocode=file_get_contents('https://maps.google.com/maps/api/geocode/json?address='.$prepAddr.'&sensor=false');
        $output= json_decode($geocode);
        $latitude = $output->results[0]->geometry->location->lat;
        $longitude = $output->results[0]->geometry->location->lng;
        $passengerlatitude=$latitude.'00';
        
        $settings = Settings::where('key', 'default_search_radius')->first();
                   $distance = $settings->value;
                   $settings = Settings::where('key', 'default_distance_unit')->first();
                            $unit = $settings->value;
                            if ($unit == 0) {
                                $multiply = 1.609344;
                            } elseif ($unit == 1) {
                                $multiply = 1;
                            }
                            $query = "SELECT walker.*, "
                                    . "ROUND(" . $multiply . " * 3956 * acos( cos( radians('$latitude') ) * "
                                    . "cos( radians(latitude) ) * "
                                    . "cos( radians(longitude) - radians('$longitude') ) + "
                                    . "sin( radians('$latitude') ) * "
                                    . "sin( radians(latitude) ) ) ,8) as distance "
                                    . "FROM walker "
                                    . "where is_available = 1 and "
                                    . "is_active = 1 and "
                                    . "is_approved = 1 and "
                                    . "ROUND((" . $multiply . " * 3956 * acos( cos( radians('$latitude') ) * "
                                    . "cos( radians(latitude) ) * "
                                    . "cos( radians(longitude) - radians('$longitude') ) + "
                                    . "sin( radians('$latitude') ) * "
                                    . "sin( radians(latitude) ) ) ) ,8) <= $distance and "
                                    . "walker.deleted_at IS NULL "
                                    . "order by distance";
                            $results = DB::select(DB::raw($query));
        //$results=DB::select(DB::raw("SELECT id,contact_name,company,type, longitude, SQRT(POW(69.1 * (latitude - $passengerlatitude), 2) + POW(69.1 * ($longitude - longitude) * COS(latitude / 57.3), 2)) AS distance FROM walker HAVING distance < 5 ORDER BY distance"));
      foreach($results as $result)
      {
		  echo "<tr>";
		  echo "<td>$result->contact_name</td>";
		  echo "<td>$result->company</td>";
		  $type=DB::select(DB::raw("SELECT `name`,`id` FROM `walker_type` WHERE `id`=".$result->type));
		 $passengertype=$type[0]->name;
		  $passengertypeid=$type[0]->id;
		  echo "<td>$passengertype</td>";
		  echo "<td><input type=radio id=assignride name=assignride value='$result->id'>
		  <input type=hidden value=$passengertypeid name=type>
		  <input type=hidden value=$latitude id=lat>
		  <input type=hidden value=$longitude id=long>
		  </td>";
		  echo "</tr>";
      }
   }
   public function savedispatcherrequest()
   {
       $ride_type = Input::get('ride');
       $payment_type = Input::get('payment_type');
       Log::info('ride_type' . print_r($ride_type, true));
       $current_walker = null;
       $healthcare_company = 0;
       if($ride_type==1){
           $current_walker = Input::get('assignride');
       } else{
           $distance = Input::get('distance_db');
           $time = Input::get('time_db');
           $total_db = Input::get('total_db');
       }
       if(Input::get('company_name') != 0){
           $healthcare_company = Input::get('company_name');
           $hospital_provider = Input::get('provider_name');
           $agent_name = Input::get('agent_name1');
           $agent_phone = Input::get('agent_phone1');
       }
       Log::info('$healthcare_company' . print_r($healthcare_company, true));

       if($healthcare_company == 0) {
            $healthcare_company = 1;
       }

        $enterprise_client = EnterpriseClient::find($healthcare_company);

        //
        // ingest post parameters
        //
        $pickupaddress = Input::get('passenger_pickupaddress');
        $dropoffaddress = Input::get('passenger_dropoffaddress');

        $origin_latitude =          Input::get('start_lat');
        $origin_longitude =         Input::get('start_lng');
        $destination_latitude =     Input::get('end_lat');
        $destination_longitude =    Input::get('end_lng');



        $pickupdate = Input::get('pickup_date');
        $pickuptime = Input::get('pickup_time');
        $usertimezone = Input::get('user_timezone');
        $services = Input::get('services');
        $payment_mode = Input::get('payment_mode');
        $special_request = Input::get('special_request');
        $billing_code = Input::get('billing_code');
        $wheelchair = Input::get('checkbox');
        $height = Input::get('height');
        $weight = Input::get('weight');
        $condition = Input::get('condition');
        $oxygen_mask = Input::get('oxygen_mask');
        $attendant = Input::get('attendant');
        $respirator = Input::get('respirator');
        $any_tubing = Input::get('any_tubing');
        $colostomy_bag = Input::get('colostomy_bag');
        $any_attachments = Input::get('any_attachments');
        $roundtrip = Input::get('roundtrip');
       $attendant = Input::get('attendant');
	   $passenger_contact_name = Input::get('passenger_contact_name');
	   $passenger_country_code = Input::get('passenger_countryCode');
	   $passenger_phone = Input::get('passenger_phone');
	   $passenger_email = Input::get('passenger_email');
	   $dispatcher_id = Session::get('user_id');
	   $token         = Input::get('Token');
       $demand_scheduled = Input::get('all_radio');

	   if($healthcare_company != 0) {
           $agent_name = Input::get('agent_name1');
           $agent_phone = Input::get('agent_phone1');
           if ($agent_name != '' && $agent_phone!='') {
               $agent_array = explode(" ", $agent_name);
               if (count($agent_array) > 1) {
                   $agent_firstname = $agent_array[0];
                   $agent_lastname = $agent_array[1];
               } else {
                   $agent_firstname = $agent_name;
                   $agent_lastname = '';
               }

               if (strlen($agent_phone) <= 10) {
                   $agent_phone = "+1" . $agent_phone;
               } else {
                   $agent_phone = $agent_phone;
               }
           }
       }
       if ($oxygen_mask == 0) {
           $oxygen_mask = 0;
       }
       if ($respirator == 0) {
           $respirator = 0;
       }
       if ($any_tubing == 0) {
           $any_tubing = 0;
       }
       if ($colostomy_bag == 0) {
           $colostomy_bag = 0;
       }
       if($attendant==1){
           $attendant_name = Input::get('attendant_name');
           $attendant_phone = Input::get('attendant_phone');
//           $attendant_pickupaddress = Input::get('attendant_pickupaddress');

           if(strlen($attendant_phone) <=10){
               $attendant_phone = "+1".$attendant_phone;
           }

 /*          if ($attendant_pickupaddress != '') {
               $prepAddr = str_replace(' ', '+', $attendant_pickupaddress);
               $geocode = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . $prepAddr . '&sensor=false');
               $outputattendant = json_decode($geocode);
               if($outputattendant->status == "OK") {
                   $attendantlatitude = $outputattendant->results[0]->geometry->location->lat;
                   $attendantlongitude = $outputattendant->results[0]->geometry->location->lng;
               }else{
                   return Redirect::to('dispatcher/request-service')
                       ->with('Error','Address not found. Please try again with a nearby address');
               }
           } */
       }
       $distance = Input::get('distance_db');
       $time = Input::get('time_db');
       $total_db = Input::get('total_db');
       $total_roundtrip_amount = Input::get('total_roundtrip_amount');
       if ($wheelchair == 1) {
           $wheelchair = 1;
       } else {
           $wheelchair = 0;
       }

	   $requestpickuptime = $pickupdate." ".$pickuptime;
	   Log::info('pickupdate from input = ' . print_r($pickupdate, true));
	   date_default_timezone_set($usertimezone);
	   Log::info('usertimezone = ' . print_r($usertimezone, true));
	   $finalpickuptime =  get_UTC_time($requestpickuptime);
	   Log::info('finalpickuptime = ' . print_r($finalpickuptime, true));
	   date_default_timezone_set(Config::get('app.timezone'));
       if($roundtrip==1){
           $round_pickup_date = Input::get('round_pickup_date');
           $round_pickup_time  = Input::get('round_pickup_time');
           $round_pickupaddress = Input::get('round_pickupaddress');
           $round_dropoffaddress = Input::get('round_dropoffaddress');
/*
//
// ridiculous, we're paying twice for looking up an address. what if we don't have an address???
//

           if ($round_pickupaddress != '') {
               $prepAddr = str_replace(' ', '+', $round_pickupaddress);
               $geocode = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . $prepAddr . '&sensor=false');
               $output = json_decode($geocode);
               if($output->status == "OK") {
                   $roundlatitude = $output->results[0]->geometry->location->lat;
                   $roundlongitude = $output->results[0]->geometry->location->lng;
               }else{
                   return Redirect::to('dispatcher/request-service')
                       ->with('Error','Address not found. Please try again with a nearby address');
               }
           }
           if ($round_dropoffaddress != '') {
               $dropprepAddr = str_replace(' ', '+', $round_dropoffaddress);
               $geocode = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . $dropprepAddr . '&sensor=false');
               $dropoutput = json_decode($geocode);
               if ($dropoutput->status == "OK") {
                   $rounddroplatitude = $dropoutput->results[0]->geometry->location->lat;
                   $rounddroplongitude = $dropoutput->results[0]->geometry->location->lng;
               }else{
                   return Redirect::to('dispatcher/request-service')
                       ->with('Error','Address not found. Please try again with a nearby address');
               }
           }
*/
           $roundrequestpickuptime = $round_pickup_date . " " . $round_pickup_time;
           Log::info('roundpickupdate from input = ' . print_r($round_pickup_date, true));
           date_default_timezone_set($usertimezone);
           Log::info('usertimezone = ' . print_r($usertimezone, true));
           $roundfinalpickuptime = get_UTC_time($roundrequestpickuptime);
           Log::info('roundfinalpickuptime = ' . print_r($roundfinalpickuptime, true));
           date_default_timezone_set(Config::get('app.timezone'));
           $roundsrc_address = "Address Not Available";
           if (Input::has('round_pickupaddress')) {
               $roundsrc_address = trim(Input::get('round_pickupaddress'));
           }
           $rounddest_address = "Address Not Available";
           if (Input::has('round_dropoffaddress')) {
               $rounddest_address = trim(Input::get('round_dropoffaddress'));
           }
       }
       // calculated time for future request
       $requested_time = strtotime($finalpickuptime);
       $curr_time = strtotime(date('Y-m-d H:i:s'));
       $subratcted_time = $requested_time - $curr_time;
       $subtracted_minutes = $subratcted_time / 60;
/*
//
// ridiculous, we're paying twice for looking up an address. what if we don't have an address???
//

	   if($pickupaddress!='') {
           $prepAddr = str_replace(' ', '+', $pickupaddress);
           $geocode = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . $prepAddr . '&sensor=false');
           $output = json_decode($geocode);
           if ($output->status == "OK") {
               $latitude = $output->results[0]->geometry->location->lat;
               $longitude = $output->results[0]->geometry->location->lng;
           }else{
               return Redirect::to('dispatcher/request-service')
                   ->with('Error','Address not found. Please try again with a nearby address');
           }
       }
       if($dropoffaddress!='') {
           $dropprepAddr = str_replace(' ', '+', $dropoffaddress);
           $geocode = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . $dropprepAddr . '&sensor=false');
           $dropoutput = json_decode($geocode);
           if ($dropoutput->status == "OK") {
               $droplatitude = $dropoutput->results[0]->geometry->location->lat;
               $droplongitude = $dropoutput->results[0]->geometry->location->lng;
           }else{
               return Redirect::to('dispatcher/request-service')
                   ->with('Error','Address not found. Please try again with a nearby address');
           }
       }
*/
       if($payment_type==1){
           $payment_opt = 3;
       } else{
           $payment_opt = 0;
       }
       $user_create_time = date('Y-m-d H:i:s');
       $time_zone = $usertimezone;
       $src_address = "Address Not Available";
		if (Input::has('passenger_pickupaddress')) 
		{
			$src_address = trim(Input::get('passenger_pickupaddress'));
		} 
		$dest_address = "Address Not Available";
		if (Input::has('passenger_dropoffaddress')) {
			$dest_address = trim(Input::get('passenger_dropoffaddress'));
		}

		if($healthcare_company != 0){
            $validator = Validator::make(
                array(
                    'passenger_contact_name' => $passenger_contact_name,
                    'passenger_phone' => $passenger_phone,
                    'passenger_pickupaddress' => $pickupaddress,
                    'passenger_dropoffaddress' => $dropoffaddress

                ), array(
                'passenger_phone' => 'required',
                'passenger_contact_name' => 'required',
                'passenger_pickupaddress' => 'required',
                'passenger_dropoffaddress' => 'required'
            ), array(
                    'passenger_phone' => 'Phone field is required.',
                    'passenger_contact_name' => 'Name field is required.',
                    'passenger_pickupaddress' => 'Pickup adreess is required'
                )
            );
        } else {
            Log::info('$ride_type' . print_r($ride_type, true));
            if ($ride_type == 1) {

                $validator = Validator::make(
                    array(
                        'passenger_contact_name' => $passenger_contact_name,
                        'passenger_phone' => $passenger_phone,
                        'passenger_pickupaddress' => $pickupaddress,
                        'passenger_dropoffaddress' => $dropoffaddress,
                        'assignride' => $current_walker
                    ), array(
                    'passenger_phone' => 'required',
                    'passenger_contact_name' => 'required',
                    'passenger_pickupaddress' => 'required',
                    'passenger_dropoffaddress' => 'required',
                    'assignride' => 'required'
                ), array(
                        'passenger_phone' => 'Phone field is required.',
                        'passenger_contact_name' => 'Name field is required.',
                        'passenger_pickupaddress' => 'Pickup adreess is required',
                        'assignride' => 'Please select a driver',
                    )
                );
            } else {
                $validator = Validator::make(
                    array(
                        'passenger_contact_name' => $passenger_contact_name,
                        'passenger_phone' => $passenger_phone,
                        'passenger_pickupaddress' => $pickupaddress,
                        'passenger_dropoffaddress' => $dropoffaddress
                    ), array(
                    'passenger_phone' => 'required',
                    'passenger_contact_name' => 'required',
                    'passenger_pickupaddress' => 'required',
                    'passenger_dropoffaddress' => 'required'
                ), array(
                        'passenger_phone' => 'Phone field is required.',
                        'passenger_contact_name' => 'Name field is required.',
                        'passenger_pickupaddress' => 'Pickup adreess is required'
                    )
                );
            }
        }

       if ($validator->fails()) {
				return Redirect::to('dispatcher/request-service')
				->with('error', 'Please fill all the fields.')
				->with('passenger_contact_name',$passenger_contact_name)
				->with('passenger_phone',$passenger_phone)
				->with('passenger_email',$passenger_email)
				->with('pickupaddress',$pickupaddress)
				->with('dropoffaddress',$dropoffaddress);
       }

        //
        // retrieve the previous fare estimate (if any)
        //
        $fare = Session::get('fare_estimate');
        $params = array(
            'origin_latitude' =>        $origin_latitude,
            'origin_longitude' =>       $origin_longitude,
            'destination_latitude' =>   $destination_latitude,
            'destination_longitude' =>  $destination_longitude
        );

        if(ValidateStoredEstimate($fare, $params) == FALSE) {
            $params['enterprise_client'] = $enterprise_client;
            $params['is_wheelchair'] = $wheelchair;
            $params['is_roundtrip'] = $roundtrip;
            $params['type'] = $services;

            $fare = CalculateFare($params);
        }


        $user_timezone = Config::get('app.timezone');
        $default_timezone = Config::get('app.timezone');

        //check whether dispatcher_assigned already exists.
        $fullphoneno = $passenger_country_code.$passenger_phone;
        $DispatcherAssigned = DispatcherAssigned::where('phone', '=', $fullphoneno)->first();
        if($DispatcherAssigned){
            DispatcherAssigned::where('id', '=', $DispatcherAssigned->id)->update(array('contact_name' => $passenger_contact_name, 'email' => $passenger_email,'updated_at'=>date('Y-m-d H:i:s')));
        } else{

            $DispatcherAssigned = new DispatcherAssigned;
            $DispatcherAssigned->contact_name = $passenger_contact_name;
            $DispatcherAssigned->email = $passenger_email;
            $DispatcherAssigned->phone = $passenger_country_code.$passenger_phone;
            $DispatcherAssigned->dispatcher_id = $dispatcher_id;
            $DispatcherAssigned->created_at = date('Y-m-d H:i:s');
            $DispatcherAssigned->updated_at = date('Y-m-d H:i:s');
            $DispatcherAssigned->save();
        }

        if ($ride_type==1 && $current_walker!='') {
            $provider_type = Walker::where('id', $current_walker)->first();
            $type = $provider_type->type;

            $typequery = "SELECT distinct provider_id from walker_services where type IN($type)";
            $typewalkers = DB::select(DB::raw($typequery));
            if (count($typewalkers) > 0) {
                foreach ($typewalkers as $key) {

                    $types[] = $key->provider_id;
                }

                $typestring = implode(",", $types);
                //Log::info('typestring = ' . print_r($typestring, true));
            } else {
                send_notifications($current_walker, "walker", 'No ' . Config::get('app.generic_keywords.Provider') . ' Found', 55);
                /* $response_array = array('success' => false, 'error' => 'No ' . $driver->keyword . ' found matching the service type.','error_messages' => array('No ' . $driver->keyword . ' found matching the service type.'), 'error_code' => 416); */
                $response_array = array('success' => false, 'error' => 55, 'error_messages' => array(55), 'error_code' => 416);
                $response_code = 200;
                return Response::json($response_array, $response_code);
            }
            $settings = Settings::where('key', 'default_search_radius')->first();
            $distance = $settings->value;
            $settings = Settings::where('key', 'default_distance_unit')->first();
            $unit = $settings->value;
            if ($unit == 0) {
                $multiply = 1.609344;
            } elseif ($unit == 1) {
                $multiply = 1;
            }
            $query = "SELECT walker.*, "
                . "ROUND(" . $multiply . " * 3956 * acos( cos( radians('$origin_latitude') ) * "
                . "cos( radians(latitude) ) * "
                . "cos( radians(longitude) - radians('$origin_longitude') ) + "
                . "sin( radians('$origin_latitude') ) * "
                . "sin( radians(latitude) ) ) ,8) as distance "
                . "FROM walker "
                . "where is_available = 1 and "
                . "is_active = 1 and "
                . "is_approved = 1 and "
                . "ROUND((" . $multiply . " * 3956 * acos( cos( radians('$origin_latitude') ) * "
                . "cos( radians(latitude) ) * "
                . "cos( radians(longitude) - radians('$origin_longitude') ) + "
                . "sin( radians('$origin_latitude') ) * "
                . "sin( radians(latitude) ) ) ) ,8) <= $distance and "
                . "walker.deleted_at IS NULL and "
                . "walker.id IN($typestring) "
                . "order by distance";
            $walkers = DB::select(DB::raw($query));
            $request = new RideRequest;
            $ride_detail = new RideDetails;

            $request->metadata('fare', $fare);
            $request->passenger_contact_name = $passenger_contact_name;
            $request->passenger_phone = $passenger_phone;
            $request->payment_mode = $payment_opt;
            $request->time_zone = $time_zone;
            $request->src_address = $src_address;
            $request->D_latitude = 0;
            if (isset($destination_latitude)) {
                $request->D_latitude = $destination_latitude;
            }
            $request->D_longitude = 0;
            if (isset($destination_longitude)) {
                $request->D_longitude = $destination_longitude;
            }
            $request->dest_address = $dest_address;
            $request->request_start_time = $finalpickuptime;
            $request->latitude = $origin_latitude;
            $request->longitude = $origin_longitude;
            $request->dispatcher_assigned_id = $DispatcherAssigned->id;
            $request->dispatcher_id = $dispatcher_id;
            $request->req_create_user_time = $user_create_time;
            $request->status = 0;//need to confirm this
            $request->confirmed_walker = '0';//need to confirm this
            $request->current_walker = '0';//need to confirm this
            $request->is_walker_started = '0';
            $request->is_walker_arrived = '0';
            $request->is_started = '0';
            $request->is_completed = '0';
            $request->is_dog_rated = '0';
            $request->is_walker_rated = '0';
            $request->distance = '0.00';
            $request->time = '0.00';
            $request->total = $total_db;
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
            $request->is_wheelchair_request = $wheelchair;
            $ride_detail->oxygen_mask = $oxygen_mask;
            if ($wheelchair == 1 && $oxygen_mask == 1) {
                $request->additional_fee = '40';
            }elseif($wheelchair == 1 && $oxygen_mask == 0){
                $request->additional_fee = '10';
            }elseif($wheelchair == 0 && $oxygen_mask == 1){
                $request->additional_fee = '30';
            }
            if($healthcare_company != 0) {
                $request->hospital_provider_id = $hospital_provider;
                $request->dispatcher_assigned_id = $DispatcherAssigned->id;
                $request->healthcare_id = $healthcare_company;
                if($agent_firstname!='' && $agent_phone!=''){
                    $ride_detail->agent_contact_name = $agent_firstname;
                    $ride_detail->agent_phone = $agent_phone;
                    error_log($agent_firstname);
                }

            }
            $ride_detail->height = $height;
            $ride_detail->weight = $weight;
            $ride_detail->condition = $condition;
            $ride_detail->respirator = $respirator;
            $ride_detail->any_tubing = $any_tubing;
            $ride_detail->colostomy_bag = $colostomy_bag;
            $ride_detail->any_attachments = $any_attachments;
            if($attendant==1) {
                $request->attendant_travelling = $attendant;
                $ride_detail->attendant_name = $attendant_name;
                $ride_detail->attendant_phone = $attendant_phone;
                $ride_detail->created_at = $user_create_time;
                $ride_detail->updated_at = $user_create_time;
            }
            $request->save();

            $reqserv = new RequestServices;
            $reqserv->request_id = $request->id;
            $reqserv->type = $type;

            $reqserv->save();
            $id = RideRequest::where('id','=',$request->id)->first();
            $ride_detail->request_id = $id->id;
            $ride_detail->save();


            if($roundtrip==1){
                $request1 = new RideRequest;
                $ride_detail_1 = new RideDetails;

                $request1->metadata('fare', $fare);
                $request1->passenger_contact_name = $passenger_contact_name;
                $request1->passenger_phone = $passenger_phone;
                $request1->payment_mode = $payment_opt;
                $request1->special_request = $special_request;
                $request1->service_type = $services;
                $ride_detail_1->billing_code = $billing_code;
                $request1->time_zone = $time_zone;
                $request1->src_address = $src_address;
                if($healthcare_company != 0) {
                    $request1->hospital_provider_id = $hospital_provider;
                    $request1->dispatcher_assigned_id = $DispatcherAssigned->id;
                    $request1->healthcare_id = $hospital_provider;
                    if($agent_firstname!='' && $agent_phone!=''){
                        $ride_detail_1->agent_contact_name = $agent_firstname;
                        $ride_detail_1->agent_phone = $agent_phone;
                    }
                }

                if ($demand_scheduled == 2) {
                    $isscheduled = 1;
                } else {
                    $isscheduled = 0;
                }
                $ride_detail_1->is_scheduled = $isscheduled;
                $request1->D_latitude = 0;
                if (isset($origin_latitude)) {
                    $request1->D_latitude = $origin_latitude;
                }
                $request1->D_longitude = 0;
                if (isset($rounddroplongitude)) {
                    $request1->D_longitude = $origin_longitude;
                }
                $request1->dest_address = $dest_address;
                $request1->request_start_time = $finalpickuptime;
                $request1->latitude = $destination_latitude;
                $request1->longitude = $destination_longitude;
                $request1->req_create_user_time = $user_create_time;
                $request1->status = 0;
                $request1->is_walker_started = '0';
                $request1->is_walker_arrived = '0';
                $request1->is_started = '0';
                $request1->is_completed = '0';
                $request1->is_dog_rated = '0';
                $request1->is_walker_rated = '0';
                $request1->distance = $distance;
                $request1->time = $time;
                $request1->total = $total_db;//$total_roundtrip_amount;
                $request1->is_paid = '0.00';
                $request1->card_payment = '0.00';
                $request1->ledger_payment = '0.00';
                $request1->is_cancelled = '0';
                $request1->refund = '0.00';
                $request1->transfer_amount = '0.00';
                $request1->later = '0';
                $request1->promo_code = '0';
                $request1->promo_id = '0';
                $request1->cancel_reason = '0';
                $request1->is_wheelchair_request = $wheelchair;
                $ride_detail_1->oxygen_mask = $oxygen_mask;
                if($oxygen_mask == 1){
                    $request1->additional_fee = '30';
                }
                $ride_detail_1->height = $height;
                $ride_detail_1->weight = $weight;
                $ride_detail_1->condition = $condition;
                $ride_detail_1->respirator = $respirator;
                $ride_detail_1->any_tubing = $any_tubing;
                $ride_detail_1->colostomy_bag = $colostomy_bag;
                $ride_detail_1->any_attachments = $any_attachments;
                if($attendant==1) {
                    $request1->attendant_travelling = $attendant;
                    $ride_detail_1->attendant_name = $round_attendant_name;
                    $ride_detail_1->attendant_phone = $round_attendant_phone;
                    $ride_detail_1->created_at = $user_create_time;
                    $ride_detail_1->updated_at = $user_create_time;
                }
                $request1->save();

                $reqserv1 = new RequestServices;
                $reqserv1->request_id = $request1->id;
                $reqserv1->type = $services;
                $reqserv1->save();

                $id_1 = RideRequest::where('id','=',$request1->id)->first();
                $ride_detail_1->request_id = $id_1->id;
                $ride_detail_1->save();
              RideRequest::where('id', '=', $request->id)->update(array('roundtrip_id' => $request1->id));
            }

            //update dispatcher_assigned_id in payment table according to token
            //Payment::where('card_token', '=', $token)->update(array('dispatcher_assigned_id' => $DispatcherAssigned->id, 'updated_at' => date('Y-m-d H:i:s')));


            $request_meta = new RequestMeta;
            $request_meta->request_id = $request->id;
            $request_meta->walker_id = $current_walker;
            $request_meta->status = '1';
            $request_meta->is_cancelled = '0';
            $request_meta->save();
            $req =RideRequest::find($request->id);
            $req->current_walker = $current_walker;
            $req->save();

            $settings = Settings::where('key', 'provider_timeout')->first();
            $time_left = $settings->value;

            // Send Notification
            $walker = Walker::find($current_walker);
            if ($walker) {
                $msg_array = array();
                $msg_array['unique_id'] = 1;
                $msg_array['request_id'] = $request->id;
                $msg_array['time_left_to_respond'] = $time_left;
                $settings = Settings::where('key', 'default_distance_unit')->first();
                $unit = $settings->value;
                if ($unit == 0) {
                    $unit_set = 'kms';
                } elseif ($unit == 1) {
                    $unit_set = 'miles';
                }

                $msg_array['unit'] = $unit_set;
                $msg_array['payment_mode'] = $payment_opt;

                $request_data = array();
                $request_data['dispatcher'] = array();
                $request_data['dispatcher']['name'] = $passenger_contact_name;
                $request_data['dispatcher']['phone'] = $passenger_phone;
                $request_data['dispatcher']['address'] = $src_address;
                $request_data['dispatcher']['latitude'] = $request->latitude;
                $request_data['dispatcher']['longitude'] = $request->longitude;
                if ($dropoffaddress != '') {
                    $request_data['dispatcher']['d_latitude'] = $request->D_latitude;
                    $request_data['dispatcher']['d_longitude'] = $request->D_longitude;
                }
                $request_data['dispatcher']['owner_dist_lat'] = $request->D_latitude;
                $request_data['dispatcher']['owner_dist_long'] = $request->D_longitude;
                $request_data['dispatcher']['payment_type'] = $payment_opt;
                $msg_array['request_data'] = $request_data;
                $title = "New Request";
                $message = $msg_array;
                send_notifications($current_walker, "walker", $title, $message);

                // Send SMS
                $settings = Settings::where('key', 'sms_request_created')->first();
                $pattern = $settings->value;
                $pattern = str_replace('%user%', $passenger_contact_name, $pattern);
                $pattern = str_replace('%id%', $request->id, $pattern);
                $pattern = str_replace('%user_mobile%', $passenger_phone, $pattern);
                $pattern = str_replace('%pickup_address%', $request->src_address, $pattern);
                $pattern = str_replace('%dropoff_address%', $request->dest_address, $pattern);
                $pattern = str_replace('%start_app_link%', '', $pattern);
                sms_notification(1, 'admin', $pattern);
                sms_notification($current_walker, 'walker', $pattern);


                $settings = Settings::where('key', 'admin_email_address')->first();
                $admin_email = $settings->value;
                $follow_url = web_url() . "/dispatcher/signin";

                $pattern = array('admin_email' => $admin_email, 'trip_id' => $request->id, 'follow_url' => $follow_url,
                    'wheelchair_request' => 'NO');
                $subject = "Ride Booking Request";
                email_notification(1, 'admin', $pattern, $subject, 'new_enterprise_ride_request', null);

                return Redirect::to('/dispatcher/request-service')->with('success', "Request Sent Successfully.");

            } else {
                return Redirect::to('/dispatcher/request-service')->with('error', "No driver Found");
            }
        } elseif($ride_type==2){ /* Actual code which is executing */
            $request = new RideRequest;
            $ride_detail = new RideDetails;

            $request->metadata('fare', $fare);
            $request->passenger_contact_name = $passenger_contact_name;
            $request->passenger_phone = $passenger_phone;
            $request->payment_mode = $payment_opt;
            $request->time_zone = $time_zone;
            $request->src_address = $src_address;
            $request->service_type = $services;
            $request->D_latitude = 0;
            if (isset($destination_latitude)) {
                $request->D_latitude = $destination_latitude;
            }
            $request->D_longitude = 0;
            if (isset($destination_longitude)) {
                $request->D_longitude = $destination_longitude;
            }
            $request->dest_address = $dest_address;
            $request->request_start_time = $finalpickuptime;
            $request->latitude = $origin_latitude;
            $request->longitude = $origin_longitude;
            $request->dispatcher_assigned_id = $DispatcherAssigned->id;
            $request->dispatcher_id = $dispatcher_id;
            $request->req_create_user_time = $user_create_time;
            $request->status = 0;//need to confirm this
            $request->confirmed_walker = '0';//need to confirm this
            $request->current_walker = '0';//need to confirm this
            $request->is_walker_started = '0';
            $request->is_walker_arrived = '0';
            $request->is_started = '0';
            $request->is_completed = '0';
            $request->is_dog_rated = '0';
            $request->is_walker_rated = '0';
            $request->distance = $distance;
            $request->time = $time;
            $request->total = $total_db;
            $request->is_paid = '0.00';
            $request->card_payment = '0.00';
            $request->ledger_payment = '0.00';
            $request->is_cancelled = '0';
            $request->refund = '0.00';
            $request->transfer_amount = '0.00';
            $request->promo_code = '0';
            $request->promo_id = '0';
            $request->cancel_reason = '0';
            $request->is_wheelchair_request = $wheelchair;
            $ride_detail->oxygen_mask = $oxygen_mask;
            if ($wheelchair == 1 && $oxygen_mask == 1) {
                $request->additional_fee = '40';
            }elseif($wheelchair == 1 && $oxygen_mask == 0){
                $request->additional_fee = '10';
            }elseif($wheelchair == 0 && $oxygen_mask == 1){
                $request->additional_fee = '30';
            }
            error_log($healthcare_company);
            if($healthcare_company != 0 && $healthcare_company != 1) {
                $request->hospital_provider_id = $hospital_provider;
                $request->healthcare_id = $healthcare_company;
                error_log($agent_firstname);
                error_log($agent_lastname);
                error_log($agent_phone);
                if ($agent_firstname != '' && $agent_phone != '') {
                    $ride_detail->agent_contact_name = $agent_firstname;
                    $ride_detail->agent_phone = $agent_phone;
                }
            }

            if ($demand_scheduled == 2) {
                $isscheduled = 1;
                $request->later = '1';
            } else {
                $isscheduled = 0;
                $request->later = '0';
            }
            $ride_detail->is_scheduled = $isscheduled;

            $ride_detail->height = $height;
            $ride_detail->weight = $weight;
            $ride_detail->condition = $condition;
            $ride_detail->respirator = $respirator;
            $ride_detail->any_tubing = $any_tubing;
            $ride_detail->colostomy_bag = $colostomy_bag;
            $ride_detail->any_attachments = $any_attachments;
            if($attendant==1) {
                $request->attendant_travelling = $attendant;
                $ride_detail->attendant_name = $attendant_name;
                $ride_detail->attendant_phone = $attendant_phone;
                $ride_detail->created_at = $user_create_time;
                $ride_detail->updated_at = $user_create_time;
            }
            $request->save();

            $reqserv = new RequestServices;
            $reqserv->request_id = $request->id;
            $reqserv->type = $services;
            $reqserv->save();


            $id = RideRequest::where('id','=',$request->id)->first();
            $ride_detail->request_id = $id->id;
            $ride_detail->save();


            if($roundtrip==1){
                $request1 = new RideRequest;
                $ride_detail_1 = new RideDetails;

/*
//
// ridiculous, we're paying twice for looking up an address. what if we don't have an address???
//
                if($attendant==1){
                    $round_attendant_name = Input::get('round_attendant_name');
                    $round_attendant_phone = Input::get('round_attendant_phone');
                    $round_attendant_pickupaddress = Input::get('round_attendant_pickupaddress');

                    if(strlen($round_attendant_phone) <=10){
                        $round_attendant_phone = "+1".$round_attendant_phone;
                    } else{
                        $round_attendant_phone = $round_attendant_phone;
                    }

                    if ($round_attendant_pickupaddress != '') {
                        $prepAddr = str_replace(' ', '+', $round_attendant_pickupaddress);
                        $geocode = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . $prepAddr . '&sensor=false');
                        $outputattendant = json_decode($geocode);
                        $roundattendantlatitude = $outputattendant->results[0]->geometry->location->lat;
                        $roundattendantlongitude = $outputattendant->results[0]->geometry->location->lng;
                    }
                }
*/
                $request1->metadata('fare', $fare);
                $request1->passenger_contact_name = $passenger_contact_name;
                $request1->passenger_phone = $passenger_phone;
                $request1->payment_mode = $payment_opt;
                $request1->special_request = $special_request;
                $request1->service_type = $services;
                $request1->dispatcher_assigned_id = $DispatcherAssigned->id;
                $request1->dispatcher_id = $dispatcher_id;
                $ride_detail_1->billing_code = $billing_code;
                $request1->time_zone = $time_zone;
                $request1->src_address = $roundsrc_address;
                if($healthcare_company != 0 && $healthcare_company != 1) {
                    $request1->hospital_provider_id = $hospital_provider;
                    $request1->healthcare_id = $healthcare_company;
                    if($agent_firstname!='' && $agent_phone!=''){
                        $ride_detail_1->agent_contact_name = $agent_firstname;
                        $ride_detail_1->agent_phone = $agent_phone;
                    }
                }

                if ($demand_scheduled == 2) {
                    $isscheduled = 1;
                    $request1->later = '1';
                } else {
                    $isscheduled = 0;
                    $request1->later = '0';
                }
                $ride_detail_1->is_scheduled = $isscheduled;
                $request1->D_latitude = 0;
                if (isset($origin_latitude)) {
                    $request1->D_latitude = $origin_latitude;
                }
                $request1->D_longitude = 0;
                if (isset($origin_longitude)) {
                    $request1->D_longitude = $origin_longitude;
                }
                $request1->dest_address = $rounddest_address;
                $request1->request_start_time = $roundfinalpickuptime;
                $request1->updated_at = $roundfinalpickuptime;
                $request1->latitude = $destination_latitude;
                $request1->longitude = $destination_longitude;
                $request1->req_create_user_time = $user_create_time;
                $request1->status = 0;
                $request1->is_walker_started = '0';
                $request1->is_walker_arrived = '0';
                $request1->is_started = '0';
                $request1->is_completed = '0';
                $request1->is_dog_rated = '0';
                $request1->is_walker_rated = '0';
                $request1->distance = $distance;
                $request1->time = $time;
                $request1->total = $total_roundtrip_amount;
                $request1->is_paid = '0.00';
                $request1->card_payment = '0.00';
                $request1->ledger_payment = '0.00';
                $request1->is_cancelled = '0';
                $request1->refund = '0.00';
                $request1->transfer_amount = '0.00';
                $request1->promo_code = '0';
                $request1->promo_id = '0';
                $request1->cancel_reason = '0';
                $request1->is_wheelchair_request = $wheelchair;
                $ride_detail_1->oxygen_mask = $oxygen_mask;
                if($oxygen_mask == 1){
                    $request1->additional_fee = '30';
                }
                $ride_detail_1->height = $height;
                $ride_detail_1->weight = $weight;
                $ride_detail_1->condition = $condition;
                $ride_detail_1->respirator = $respirator;
                $ride_detail_1->any_tubing = $any_tubing;
                $ride_detail_1->colostomy_bag = $colostomy_bag;
                $ride_detail_1->any_attachments = $any_attachments;
                if($attendant==1) {
                    $request1->attendant_travelling = $attendant;
                    $ride_detail_1->attendant_name = $attendant_name;
                    $ride_detail_1->attendant_phone = $attendant_phone;
                    $ride_detail_1->created_at = $user_create_time;
                    $ride_detail_1->updated_at = $user_create_time;
                }
                $request1->save();

                $reqserv1 = new RequestServices;
                $reqserv1->request_id = $request1->id;
                $reqserv1->type = $services;
                $reqserv1->save();


                $id_1 = RideRequest::where('id','=',$request1->id)->first();
                $ride_detail_1->request_id = $id_1->id;
                $ride_detail_1->save();
              RideRequest::where('id', '=', $request->id)->update(array('roundtrip_id' => $request1->id));
            }
            if($token!=''){
                //update dispatcher_assigned_id in payment table according to token
                Payment::where('card_token', '=', $token)->update(array('dispatcher_assigned_id' => $DispatcherAssigned->id, 'updated_at' => date('Y-m-d H:i:s')));
            }

            if($attendant==1) {
                $attendantName = $attendant_name;
                $attendantPhone = $attendant_phone;
                $attendantPickupAddress = $pickupaddress;
            } else{
                $attendantName = '';
                $attendantPhone = '';
                $attendantPickupAddress = '';
            }

            // Send SMS
            $settings = Settings::where('key', 'sms_request_created')->first();
            $pattern = $settings->value;
            $pattern = str_replace('%user%', $passenger_contact_name, $pattern);
            $pattern = str_replace('%id%', $request->id, $pattern);
            $pattern = str_replace('%user_mobile%', $passenger_phone, $pattern);
            $pattern = str_replace('%pickup_address%', $request->src_address, $pattern);
            $pattern = str_replace('%dropoff_address%', $request->dest_address, $pattern);
            $pattern = str_replace('%start_app_link%', '', $pattern);
            if ($wheelchair == 1) {
                $pattern .= " . Wheelchair Equipment Requested.";
            }
            if ($attendant== 1) {
                $pattern .= " Attendant Information.";
                $pattern .= " Attendant Name: ".$attendantName;
                $pattern .= " Attendant Phone: ".$attendantPhone;
                $pattern .= " Attendant Pickup Address: ".$attendantPickupAddress;
            }

            sms_notification($request->dispatcher_id, 'ride_assignee', $pattern);
            sms_notification($request->dispatcher_id, 'ride_assignee_2', $pattern);
            sms_notification($request->dispatcher_id, 'ride_assignee_3', $pattern);

            //get user information
            $passengerinfo = DispatcherAssigned::where('id', $request->dispatcher_assigned_id)->first();
            $settings = Settings::where('key', 'admin_email_address')->first();
            $admin_email = $settings->value;
            $settings = Settings::where('key', 'ride_assignee_phone_number')->first();
            $ride_assignee_phone_number = $settings->value;
            $follow_url = web_url() . "/dispatcher/myservice";

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

            if ($wheelchair == 1) {
                $wheelchair_request = 'YES';
            } else {
                $wheelchair_request = 'NO';
            }
            if ($attendant== 1) {
                $attendant_travelling = 'YES';
            }else{
                $attendant_travelling = 'NO';
            }
            if($request->billing_code!=''){
                $billing_code = $request->billing_code;
            }else{
                $billing_code = "NA";
            }

            $dispatcher_email = Session::get('dispatcher_email');
            $dispatcher_name = Session::get('user_name');
            $dispatcher_company = Session::get('company');

            $time = new DateTime($request->request_start_time);
            $time->format('h:ia') . "\n";
            $user_time = new DateTimeZone($request->time_zone);
            $time->setTimezone($user_time);
            $pickuptime = $time->format('h:ia');

            $date = new DateTime($request->request_start_time);
            $date->format('Y-m-d') . "\n";
            $user_time = new DateTimeZone($request->time_zone);
            $date->setTimezone($user_time);
            $pickupdate = $date->format('Y-m-d');

            $pattern = array('driver_name' => $driver_name, 'driver_phone' => $driver_phone,
                'passenger_name' => $passenger_name, 'passenger_phone' => $passengerinfo->phone,
                'pickup_time' => $pickuptime,'pickup_date' => $pickupdate, 'pickup_location' => $request->src_address,
                'dropoff_location' => $request->dest_address, 'butterfli_dispatcher_phno' => $ride_assignee_phone_number,
                'admin_email' => $admin_email, 'trip_id' => $request->id, 'follow_url' => $follow_url,
                'agent_name' => $dispatcher_name, 'wheelchair_request' => 'NO','attendant_travelling' => $attendant_travelling,
                'healthcare_email' => $dispatcher_email, 'healthcare_company' => $dispatcher_company,
                'all_radio'=>$isscheduled,'server' => $server, 'billing_code'=>$billing_code,
                'attendant_name'=>$attendantName,
                'attendant_phone'=>$attendantPhone);


            email_notification($request->dispatcher_id, 'ride_assignee', $pattern, $subject, 'new_enterprise_ride_request', null);
            email_notification($request->dispatcher_id, 'ride_assignee_2', $pattern, $subject, 'new_enterprise_ride_request', null);
            email_notification($request->dispatcher_id, 'ride_assignee_3', $pattern, $subject, 'new_enterprise_ride_request', null);
            email_notification($request->dispatcher_assigned_id, 'dispatcher_assigned', $pattern, $subject, 'new_enterprise_ride_request', null);
            email_notification($request->dispatcher_id, 'dispatcher', $pattern, $subject, 'new_enterprise_ride_request', null);
            return Redirect::to('/dispatcher/request-service')
                ->with('success', "Request Sent Successfully.");
        }
  }

  public function view_map() {
        $id = Request::segment(4);
        $request =RideRequest::find($id);
        $walker = Walker::where('id', $request->confirmed_walker)->first();
        $owner = Owner::where('id', $request->owner_id)->first();
        if($owner=='')
        {
			$owner = DispatcherAssigned::where('id', $request->dispatcher_assigned_id)->first();
			
		}

      $countarray = array();
      $countarray['submitted'] = $this->SubmittedRidesCount();
      $countarray['confirmed'] = $this->ConfirmedRidesCount();
      $countarray['cancelled'] = $this->CancelledRidesCount();
      $countarray['completed'] = $this->CompletedRidesCount();
		
        if ($request->is_paid) {
            $status = "Payment Done";
        } elseif ($request->is_completed) {
            $status = "Request Completed";
        } elseif ($request->is_started) {
            $status = "Request Started";
        } elseif ($request->is_walker_started) {
            $status = "" . Config::get('app.generic_keywords.Provider') . " Started";
        } elseif ($request->confirmed_walker) {
            $status = "" . Config::get('app.generic_keywords.Provider') . " Yet to start";
        } else {
            $status = "" . Config::get('app.generic_keywords.Provider') . " Not Confirmed";
        }
        if ($request->is_cancelled == 1) {
            $status1 = "<span class='badge bg-red'>Cancelled</span>";
        } elseif ($request->is_completed == 1) {
            $status1 = "<span class='badge bg-green'>Completed</span>";
        } elseif ($request->is_started == 1) {
            $status1 = "<span class='badge bg-yellow'>Started</span>";
        } elseif ($request->is_walker_arrived == 1) {
            $status1 = "<span class='badge bg-yellow'>" . Config::get('app.generic_keywords.Provider') . " Arrived</span>";
        } elseif ($request->is_walker_started == 1) {
            $status1 = "<span class='badge bg-yellow'>" . Config::get('app.generic_keywords.Provider') . " Started</span>";
        } else {
            $status1 = "<span class='badge bg-light-blue'>Yet To Start</span>";
        }
        if ($request->payment_mode == 0) {
            $pay_mode = "<span class='badge bg-orange'>Stored Cards</span>";
        } elseif ($request->payment_mode == 1) {
            $pay_mode = "<span class='badge bg-blue'>Pay by Cash</span>";
        } elseif ($request->payment_mode == 2) {
            $pay_mode = "<span class='badge bg-purple'>Paypal</span>";
        } elseif($request->payment_mode == 3){
            $pay_mode = "<span class='badge bg-purple'>Payment Providers</span>";
        }

        if ($request->is_paid == 1) {
            $pay_status = "<span class='badge bg-green'>Completed</span>";
        } elseif ($request->is_paid == 0 && $request->is_completed == 1) {
            $pay_status = "<span class='badge bg-red'>Pending</span>";
        } else {
            $pay_status = "<span class='badge bg-yellow'>Request Not Completed</span>";
        }


        if ($request->is_completed) {
            $full_walk = WalkLocation::where('request_id', '=', $id)->orderBy('created_at')->get();
            $walk_location_start = WalkLocation::where('request_id', $id)->orderBy('created_at')->first();
            $walk_location_end = WalkLocation::where('request_id', $id)->orderBy('created_at', 'desc')->first();
            $walker_latitude = $walk_location_start->latitude;
            $walker_longitude = $walk_location_start->longitude;
            $owner_latitude = $walk_location_end->latitude;
            $owner_longitude = $walk_location_end->longitude;
        } else {
            $full_walk = WalkLocation::where('request_id', '=', $id)->orderBy('created_at')->get();
            /* $full_walk = array(); */
            if ($request->confirmed_walker) {
                $walker_latitude = $walker->latitude;
                $walker_longitude = $walker->longitude;
            } else {
                $walker_latitude = 0;
                $walker_longitude = 0;
            }
            $owner_latitude = $request->latitude;
            $owner_longitude = $request->longitude;
        }

        $request_meta = DB::table('request_meta')
                ->where('request_id', $request->id)
                ->leftJoin('walker', 'request_meta.walker_id', '=', 'walker.id')
                ->paginate(10);
		
        if ($walker) {
            $walker_name = $walker->contact_name;
            $walker_phone = $walker->phone;
        } else {
            $walker_name = "";
            $walker_phone = "";
        }

        if ($request->confirmed_walker) {
            $title = ucwords('Maps');
            return View::make('dispatcher.walk_map')
                            ->with('title', $title)
                            ->with('page', 'walks')
                            ->with('walk_id', $id)
                            ->with('is_started', $request->is_started)
                            ->with('time', $request->time)
                            ->with('start_time', $request->request_start_time)
                            ->with('amount', $request->total)
                            ->with('owner_name', $owner->contact_name)
                            ->with('walker_name', $walker_name)
                            ->with('walker_latitude', $walker_latitude)
                            ->with('walker_longitude', $walker_longitude)
                            ->with('owner_latitude', $owner_latitude)
                            ->with('owner_longitude', $owner_longitude)
                            ->with('walker_phone', $walker_phone)
                            ->with('owner_phone', $owner->phone)
                            ->with('status', $status)
                            ->with('status1', $status1)
                            ->with('pay_mode', $pay_mode)
                            ->with('pay_status', $pay_status)
                            ->with('full_walk', $full_walk)
                            ->with('request_meta', $request_meta)
                            ->with('countarray',$countarray);
        } else {
            $title = ucwords('Maps');
            return View::make('dispatcher.walk_map')
                            ->with('title', $title)
                            ->with('page', 'walks')
                            ->with('walk_id', $id)
                            ->with('is_started', $request->is_started)
                            ->with('time', $request->time)
                            ->with('start_time', $request->request_start_time)
                            ->with('amount', $request->total)
                            ->with('owner_name', $owner->contact_name)
                            ->with('walker_name', "")
                            ->with('walker_latitude', $walker_latitude)
                            ->with('walker_longitude', $walker_longitude)
                            ->with('owner_latitude', $owner_latitude)
                            ->with('owner_longitude', $owner_longitude)
                            ->with('walker_phone', "")
                            ->with('owner_phone', $owner->phone)
                            ->with('request_meta', $request_meta)
                            ->with('full_walk', $full_walk)
                            ->with('status1', $status1)
                            ->with('pay_mode', $pay_mode)
                            ->with('pay_status', $pay_status)
                            ->with('status', $status)
                            ->with('countarray',$countarray);
        }
    }

    public function view_dispatcher_map()
    {
        $id = Request::segment(4);
        $request =RideRequest::find($id);
        $owner = Owner::where('id', $request->owner_id)->first();
        if ($owner == '') {
            $owner = DispatcherAssigned::where('id', $request->dispatcher_assigned_id)->first();

        }

        $countarray = array();
        $countarray['submitted'] = $this->SubmittedRidesCount();
        $countarray['confirmed'] = $this->ConfirmedRidesCount();
        $countarray['cancelled'] = $this->CancelledRidesCount();
        $countarray['completed'] = $this->CompletedRidesCount();

        if ($request->is_paid) {
            $status = "Payment Done";
        } elseif ($request->is_completed) {
            $status = "Request Completed";
        } elseif ($request->is_started) {
            $status = "Request Started";
        } elseif ($request->is_walker_started) {
            $status = "" . Config::get('app.generic_keywords.Provider') . " Started";
        } else {
            $status = "" . Config::get('app.generic_keywords.Provider') . " Yet to start";
        }
        if ($request->is_cancelled == 1) {
            $status1 = "<span class='badge bg-red'>Cancelled</span>";
        } elseif ($request->is_completed == 1) {
            $status1 = "<span class='badge bg-green'>Completed</span>";
        } elseif ($request->is_started == 1) {
            $status1 = "<span class='badge bg-yellow'>Started</span>";
        } elseif ($request->is_walker_arrived == 1) {
            $status1 = "<span class='badge bg-yellow'>" . Config::get('app.generic_keywords.Provider') . " Arrived</span>";
        } elseif ($request->is_walker_started == 1) {
            $status1 = "<span class='badge bg-yellow'>" . Config::get('app.generic_keywords.Provider') . " Started</span>";
        } else {
            $status1 = "<span class='badge bg-light-blue'>Yet To Start</span>";
        }

        if ($request->payment_mode == 0) {
            $pay_mode = "<span class='badge bg-orange'>Stored Cards</span>";
        } elseif ($request->payment_mode == 1) {
            $pay_mode = "<span class='badge bg-blue'>Pay by Cash</span>";
        } elseif ($request->payment_mode == 2) {
            $pay_mode = "<span class='badge bg-purple'>Paypal</span>";
        } elseif($request->payment_mode == 3){
            $pay_mode = "<span class='badge bg-purple'>Payment Providers</span>";
        }

        if ($request->is_paid == 1) {
            $pay_status = "<span class='badge bg-green'>Completed</span>";
        } elseif ($request->is_paid == 0 && $request->is_completed == 1) {
            $pay_status = "<span class='badge bg-red'>Pending</span>";
        } else {
            $pay_status = "<span class='badge bg-yellow'>Request Not Completed</span>";
        }


        $full_walk = RideRequest::where('id', $id)->first();
        $pickup_latitude = $full_walk->latitude;
        $pickup_longitude = $full_walk->longitude;

        $dropoff_latitude = $full_walk->D_latitude;
        $dropoff_longitude = $full_walk->D_longitude;

        if ($request) {
            $walker_name = $request->driver_name;
            $walker_phone = $request->driver_phone;
        } else {
            $walker_name = "";
            $walker_phone = "";
        }

        if ($request->driver_name) {
            $title = ucwords('Maps');
            return View::make('dispatcher.manual_walk_map')
                ->with('title', $title)
                ->with('page', 'walks')
                ->with('walk_id', $id)
                ->with('is_started', $request->is_started)
                ->with('time', $request->time)
                ->with('start_time', $request->request_start_time)
                ->with('amount', $request->total)
                ->with('owner_name', $owner->contact_name)
                ->with('walker_name', $walker_name)
                ->with('dropoff_latitude', $dropoff_latitude)
                ->with('dropoff_longitude', $dropoff_longitude)
                ->with('pickup_latitude', $pickup_latitude)
                ->with('pickup_longitude', $pickup_longitude)
                ->with('walker_phone', $walker_phone)
                ->with('owner_phone', $owner->phone)
                ->with('status', $status)
                ->with('status1', $status1)
                ->with('pay_mode', $pay_mode)
                ->with('pay_status', $pay_status)
                ->with('full_walk', $full_walk)
                ->with('countarray',$countarray);
        } else {
            $title = ucwords('Maps');
            return View::make('dispatcher.manual_walk_map')
                ->with('title', $title)
                ->with('page', 'walks')
                ->with('walk_id', $id)
                ->with('is_started', $request->is_started)
                ->with('time', $request->time)
                ->with('start_time', $request->request_start_time)
                ->with('amount', $request->total)
                ->with('owner_name', $owner->contact_name)
                ->with('walker_name', "")
                ->with('dropoff_latitude', $dropoff_latitude)
                ->with('dropoff_longitude', $dropoff_longitude)
                ->with('pickup_latitude', $pickup_latitude)
                ->with('pickup_longitude', $pickup_longitude)
                ->with('walker_phone', "")
                ->with('owner_phone', $owner->phone)
                ->with('full_walk', $full_walk)
                ->with('status1', $status1)
                ->with('pay_mode', $pay_mode)
                ->with('pay_status', $pay_status)
                ->with('status', $status)
                ->with('countarray',$countarray);
        }
    }

	public function getdrivername(){
		$name = $_POST['name'];
		if($name!=''){
			$query = "SELECT * FROM walker "
				. "where is_available = 1 and (LOWER(walker.contact_name) LIKE LOWER('%$name%') AND"
				. "is_active = 1 and "
				. "is_approved = 1 and "
				. "walker.deleted_at IS NULL "
				. "order by contact_name";
				//echo $query;
			$results = DB::select(DB::raw($query));
        
			foreach($results as $result)
			{
				echo "<tr>";
				echo "<td>$result->contact_name</td>";
				echo "<td>$result->company</td>";
				$type=DB::select(DB::raw("SELECT `name`,`id` FROM `walker_type` WHERE `id`=".$result->type));
				$passengertype=$type[0]->name;
				$passengertypeid=$type[0]->id;
				echo "<td>$passengertype</td>";
				echo "<td><input type=radio id=assignride name=assignride value='$result->id'>
				<input type=hidden value=$passengertypeid name=type>
				</td>";
				echo "</tr>";
			}
		} else{
			echo "<tr>";
			echo "<td colspan='4'>No Driver available</td>";
			echo "</tr>";
		}
	}
	
	public function getalldrivers(){
		
		$query = "SELECT * FROM walker "
			. "where is_approved = 1 and "
			. "walker.deleted_at IS NULL "
			. "order by id ASC";
		$results = DB::select(DB::raw($query));
	
		foreach($results as $result)
		{
			echo "<tr>";
			echo "<td>$result->contact_name</td>";
			echo "<td>$result->company</td>";
			$type=DB::select(DB::raw("SELECT `name`,`id` FROM `walker_type` WHERE `id`=".$result->type));
			$passengertype=$type[0]->name;
			$passengertypeid=$type[0]->id;
			echo "<td>$passengertype</td>";
			echo "<td><input type=radio id=assignride name=assignride value='$result->id'>
			<input type=hidden value=$passengertypeid name=type>
			</td>";
			echo "</tr>";
		}
	}
	
	public function getloggedindrivers(){
		
		$query = "SELECT * FROM walker "
			. "where is_active = 1 and "
			. "walker.deleted_at IS NULL "
			. "order by id ASC";
		$results = DB::select(DB::raw($query));
	
		foreach($results as $result)
		{
			echo "<tr>";
			echo "<td>$result->contact_name</td>";
			echo "<td>$result->company</td>";
			$type=DB::select(DB::raw("SELECT `name`,`id` FROM `walker_type` WHERE `id`=".$result->type));
			$passengertype=$type[0]->name;
			$passengertypeid=$type[0]->id;
			echo "<td>$passengertype</td>";
			echo "<td><input type=radio id=assignride name=assignride value='$result->id'>
			<input type=hidden value=$passengertypeid name=type>
			</td>";
			echo "</tr>";
		}
	}
	
	
	public function dispatcherForgotPassword() {
        $email = Input::get('email');
        $dispatcher = Dispatcher::where('email', $email)->first();
        if ($dispatcher) {
            $new_password = time();
            $new_password .= rand();
            $new_password = sha1($new_password);
            $new_password = substr($new_password, 0, 8);
			Log::info('new password = ' . print_r($new_password, true));
            $dispatcher->password = Hash::make($new_password);
            $dispatcher->save();
			
            $settings = Settings::where('key', 'admin_email_address')->first();
            $admin_email = $settings->value;
            $login_url = web_url() . "/dispatcher/signin";
            $pattern = array('name' => $dispatcher->contact_name, 'admin_email' => $admin_email, 'new_password' => $new_password, 'login_url' => $login_url);
            $subject = "Your New Password";
            email_notification($dispatcher->id, 'dispatcher', $pattern, $subject, 'forgot_password', 'imp');
            return Redirect::to('dispatcher/signin')->with('success', 'Password reset successfully. Please check your inbox for new password.');
        } else {
            return Redirect::to('dispatcher/signin')->with('error', 'This email ID is not registered with us');
        }
    }
	
	public function cancelrideDispatcher() {
		$request_id    = $_POST['request_id'];
		$cancel_reason = $_POST['cancel_reason'];
		if ($request =RideRequest::find($request_id)) {
			if ($request->is_cancelled != 1) {
                $is_admin = Session::get('is_admin');
                $dispatcher_email = Session::get('email');

                if($is_admin==1) {
                    // Archiving that Walker
                    RequestMeta::where('request_id', '=', $request_id)->where('walker_id', '=', $request->current_walker)->update(array('status' => 3, 'is_cancelled' => 1));
                    // Update Walker availability
                    Walker::where('id', '=', $request->current_walker)->update(array('is_available' => 1));
                    // request ended
                  RideRequest::where('id', '=', $request_id)->update(array('current_walker' => 0, 'status' => 0, 'is_cancelled' => 1, 'cancel_reason' => $cancel_reason, 'cancelled_by' => $dispatcher_email));
                    $walker = Walker::where('id', $request->current_walker)->first();

                    AssignedDispatcherRequest::where('request_id', '=', $request_id)->update(array('status' => 0, 'is_cancelled' => 1, 'cancel_reason' => $cancel_reason));

                    if ($walker) {
                        $walker_name = $walker->contact_name;
                        // Send SMS
                        $pattern = "Dear " . $walker_name . " Your current assigned ride has been cancelled by the dispatcher. Please contact support for further information.";
                        sms_notification($request->current_walker, 'walker', $pattern);
                    }
                } else{
                    AssignedDispatcherRequest::where('request_id', '=', $request_id)->update(array('status' => 0, 'is_cancelled' => 1, 'cancel_reason' => $cancel_reason));

                    $tp_data = AssignedDispatcherRequest::where('request_id', '=', $request_id);
                    //sending mail to Main Dispatcher for this cancel ride by mini dispatcher
                    $settings = Settings::where('key', 'admin_email_address')->first();
                    $admin_email = $settings->value;

                    $settings = Settings::where('key', 'ride_assignee_phone_number')->first();
                    $ride_assignee_phone_number = $settings->value;

                    $follow_url = web_url() . "/dispatcher/submittedrides";

                    $subject = "Ride Request Cancelled by Transportation Partner";

                    if ($_SERVER['HTTP_HOST'] == "ride.gobutterfli.com") {
                        $server = "Development";
                    } elseif ($_SERVER['HTTP_HOST'] == "app.gobutterfli.com") {
                        $server = "Production";
                    } elseif ($_SERVER['HTTP_HOST'] == "demo.gobutterfli.com") {
                        $server = "Demo";
                    } else {
                        $server = "Local";
                    }

                    $pattern = array('transport_partner_email'=>$dispatcher_email,'pickup_location' => $request->src_address,
                        'dropoff_location' => $request->dest_address, 'butterfli_dispatcher_phno' => $ride_assignee_phone_number,
                        'admin_email' => $admin_email, 'trip_id' => $request->id, 'follow_url' => $follow_url,
                        'server' => $server);

                    email_notification($request->dispatcher_id, 'dispatcher', $pattern, $subject, 'tp_ride_cancel', null);
                }
				return 1;
			}
		}else{
			return 2;
		}
	}

    public function cancelmanulrideDispatcher() {
        $request_id    = $_POST['request_id'];
        $cancel_reason = $_POST['cancel_reason'];
        if ($request =RideRequest::find($request_id)) {
            if ($request->is_cancelled != 1) {
                // request ended
              RideRequest::where('id', '=', $request_id)->update(array('status' => 0, 'is_cancelled' => 1, 'cancel_reason' => $cancel_reason));
                // Send SMS
                $pattern = "Ride has been cancelled for the request-id: " . $request->id . " with Cancel reason: " . $cancel_reason;
                sms_notification($request->dispatcher_id, 'ride_assignee', $pattern);


                //get user information
                /*$passengerinfo = DispatcherAssigned::where('id', $request->dispatcher_assigned_id)->first();
                $settings = Settings::where('key', 'admin_email_address')->first();
                $admin_email = $settings->value;
                $settings = Settings::where('key', 'ride_assignee_phone_number')->first();
                $ride_assignee_phone_number = $settings->value;
                $follow_url = web_url() . "/dispatcher/manualrides";
                if ($request->driver_name) {
                    $driver_name = $request->driver_name;
                    $driver_phone = $request->driver_phone;
                } else {
                    $driver_name = "NA";
                    $driver_phone = "NA";
                }
                $passenger_name = $passengerinfo->contact_name;

                $datetime = new DateTime($request->request_start_time);
                $datetime->format('Y-m-d H:i:s') . "\n";
                $user_time = new DateTimeZone($request->time_zone);
                $datetime->setTimezone($user_time);
                $newpickuptime = $datetime->format('Y-m-d H:i:s');

                $dispatcher_name = Session::get('user_name');

                $pattern = array('driver_name' => $driver_name, 'driver_phone' => $driver_phone,
                    'passenger_name' => $passenger_name, 'passenger_phone' => $passengerinfo->phone,
                    'pickup_time' => $newpickuptime, 'pickup_location' => $request->src_address,
                    'dropoff_location' => $request->dest_address, 'butterfli_dispatcher_phno' => $ride_assignee_phone_number,
                    'admin_email' => $admin_email, 'trip_id' => $request->id, 'follow_url' => $follow_url,
                    'cancel_reason' => $cancel_reason, 'agent_name' => $dispatcher_name);
                $subject = "You've cancelled your ride request";
                email_notification($request->dispatcher_id, 'operator', $pattern, $subject, 'ride_cancel', 'imp');*/
                return 1;
            }
        }else{
            return 2;
        }
    }

    public function DispatcherManualRideConfirmed()
    {
        $request_id   = $_POST['request_id'];
        $driver_phone = $_POST['driver_phone'];
        $driver_name  = $_POST['driver_name'];
        $comment      = $_POST['comment'];

        if($request_id>0){

            if ($request =RideRequest::find($request_id)) {
                    $walker_payment_remaining = $provider_refund_remaining = 0;
                    $providertype = ProviderType::where('id', $request->service_type)->first();
                    $settings = Settings::where('key', 'default_distance_unit')->first();
                    $unit = $settings->value;
                    $actual_total = 0;
                    $price_per_unit_distance = 0;
                    $price_per_unit_time = 0;
                    $base_price = 0;
                    $base_price = $providertype->base_price;

                    $is_multiple_service = Settings::where('key', 'allow_multiple_service')->first();
                    if ($is_multiple_service->value == 0) {

                        if ($request->distance <= $providertype->base_distance) {
                            $price_per_unit_distance = 0;
                        } else {
                            $price_per_unit_distance = $providertype->price_per_unit_distance * ($request->distance - $providertype->base_distance);
                        }

                        $price_per_unit_time = $providertype->price_per_unit_time * $request->time;
                    }

                    $actual_total = $actual_total + $base_price + $price_per_unit_distance + $price_per_unit_time;

                    Log::info('total_price = ' . print_r($actual_total, true));

                    $settings = Settings::where('key', 'provider_amount_for_each_request_in_percentage')->first();
                    $provider_percentage = $settings->value;
                    $total = 0;
                    $ref_total = 0;
                    $promo_total = 0;
                    if ($request->payment_mode == 0) {
                        $walker_payment_remaining = (($total * $provider_percentage) / 100);
                    }
                    $request =RideRequest::find($request_id);
                    $request->is_confirmed = 1;
                    $request->comments = $comment;
                    $request->total = $actual_total;
                    $request->card_payment = $actual_total;
                    $request->payment_remaining = $walker_payment_remaining;
                    $request->refund_remaining = $provider_refund_remaining;
                    $request->ledger_payment = $ref_total;
                    $request->promo_payment = $promo_total;
                    $request->driver_phone = $driver_phone;
                    $request->driver_name = $driver_name;
                    $request->is_manual = 1;
                    $request->save();

                    // Send SMS
//                if($request->dispatcher_assigned_id == ''){
//
//                }else {
//                    $owner = DispatcherAssigned::find($request->dispatcher_assigned_id);
//                    $settings = Settings::where('key', 'sms_when_provider_completes_job')->first();
//                    $pattern = $settings->value;
//                    $pattern = str_replace('%user%', $owner->contact_name, $pattern);
//                    $pattern = str_replace('%driver%', $request->driver_name, $pattern);
//                    $pattern = str_replace('%driver_mobile%', $request->driver_phone, $pattern);
//                    $pattern = str_replace('%amount%', $request->total, $pattern);
//                }
                    return 1;
            }
        }
    }
	
	public function savecustomerpayments(){
		$token   		 = $_POST['stripeToken'];
		$cardholdername  = $_POST['cardholdername'];
		$cardholderphone = $_POST['cardholderphone'];
		$cardtype        = $_POST['cardtype'];
		$last4 			 = $_POST['last4'];
		$rememberme		 = $_POST['rememberme'];
		$disp_assign_id  = $_POST['dispatcher_assigned_id'];
		$card_id 		 = $_POST['card_id'];
		
		$newpayment = new PaymentServices();
		$response = $newpayment->saveUserPayment($token,$cardtype,$last4,$cardholdername,$rememberme,$disp_assign_id,$card_id); 
		//Log::info('response = ' . print_r($response['error'], true));
		//die;
			
		if($disp_assign_id!='' && ($response ==1 || $response==4)){
			$query = "SELECT * FROM payment WHERE customer_id IS NOT NULL AND dispatcher_assigned_id =".$disp_assign_id;
			$results = DB::select(DB::raw($query));
			foreach($results as $result)
			{
				if($result->is_default==1){
					$selected = 'checked';
				} else{
					$selected = '';
				}
				echo "<tr>";
				echo "<td style='padding-bottom: 10px;'><input style='width:100%' type=text name=last_four readonly value='XXXX-XXXX-XXXX-$result->last_four'></td>";
				echo "<td><input style='width: 50px;' type=radio id='is_default' name=is_default checked=$selected value='$result->id' onclick=changedefault('$result->id');>Active</td>";
				echo "</tr>";
			}
			echo "<input type='hidden' name='dispatcher_assigned_id' id='dispatcher_assigned_id' value='$disp_assign_id'>";
			echo "<input type='hidden' name='paymentflag' id='payment_flag' value='1'>";
		} elseif($response['error']!=''){
			return $response;
		} else{
			echo "<tr>";
			echo "<td style='padding-bottom: 10px;'><input style='width:100%' type=text name=last_four readonly value='XXXX-XXXX-XXXX-$last4'></td>";
			echo "<td><input style='width: 50px;' type=radio id='is_default' name=is_default checked='checked' value='1'>Active</td>";
			echo "</tr>";
			echo "<input type='hidden' name='paymentflag' id='payment_flag' value='1'>";
		}
		//return $response;
	}
	
	public function checkDispatcherassigned(){
		$phone        = $_POST['phone'];
		$country_code = $_POST['country_code'];
		$fullphoneno  = $country_code.$phone;
		
		$DispatcherAssigned = DispatcherAssigned::where('phone', '=', $fullphoneno)->first();
		
		if($DispatcherAssigned){
			return $DispatcherAssigned;
		}else{
			return 1; //when no dispatcher_assigned is exists in table.
		}
	}
	
	public function checkPaymentData(){
		$dispatcher_assign_id = $_POST['dispatch_assign_id'];
		
		if($dispatcher_assign_id>0){
			$query = "SELECT * FROM payment WHERE customer_id IS NOT NULL AND dispatcher_assigned_id =".$dispatcher_assign_id;
			$results = DB::select(DB::raw($query));
			//Log::info('key = ' . print_r($results, true));
			if(empty($results)){
				return 1; // not data available with customer_id in payment table.
			}else{
				foreach($results as $result)
				{
					if($result->is_default==1){
						$selected = 'checked=checked';
					} else{
						$selected = '';
					}
					echo "<tr>";
					echo "<td style='padding-bottom: 10px;'><input style='width:100%' type=text name=last_four readonly value='XXXX-XXXX-XXXX-$result->last_four'></td>";
					echo "<td><input style='width: 50px;vertical-align: -10px;margin-left: 10px;' type=radio id='is_default' name=is_default $selected value='$result->id' onclick=changedefault('$result->id');>Active</td>";
					echo "</tr>";
					
				}
				echo "<input type='hidden' name='dispatcher_assigned_id' id='dispatcher_assigned_id' value='$dispatcher_assign_id'>";
			} 
		}
	}
	
	public function dispatchercharge_user() {
		$request_id        = $_POST['request_id'];
        $final_amount      = $_POST['final_amount'];
		$comments          = $_POST['comments'];
		$additional_fee    = $_POST['add_fee'];
		$approval_text     = $_POST['approval_text'];
		$promotional_offer = $_POST['promo_offer'];
		if ($request =RideRequest::find($request_id)) {
		    if($request->dispatcher_assigned_id>0){
                $payment_data = Payment::where('dispatcher_assigned_id', $request->dispatcher_assigned_id)->where('is_default',1)->first();
            }else{
                $payment_data = Payment::where('owner_id', $request->owner_id)->where('is_default',1)->first();
            }

			if($payment_data!=''){
				$customer_id = $payment_data->customer_id;
				$token = $payment_data->card_token;
				//$setransfer = Settings::where('key', 'transfer')->first();
				//$transfer_allow = $setransfer->value;
				$newpayment = new PaymentServices();
				if($customer_id!=''){
					$response = $newpayment->makePayment($final_amount,$customer_id,'');
					if($response->paid){
						$request->is_paid = 1; 
					} else{
						$request->is_paid = 0; 
					}
				} else{
					$response = $newpayment->makePayment($final_amount,'',$token);
					if($response->paid){
						$request->is_paid = 1; 
					}else{
						$request->is_paid = 0;
					}
				}
				$request->promo_payment = $promotional_offer;
				$request->approval_text = $approval_text;
				$request->additional_fee = $additional_fee;
				$request->payment_comments = $comments;
				$request->total = $final_amount;
				$request->card_payment = $final_amount;
				$request->ledger_payment = $request->total - $final_amount;
				$request->save();
				return 1;
			} else{
				return 2;
			}
		}else{
			return 3;
		}
    }
	
	public function updatedefaultpaymentcard(){
		$payment_id  = $_POST['defaultcard_id'];
		$disp_assign_id  = $_POST['disp_assign_id'];
		
		if($payment_id>0 && $disp_assign_id>0){
			Payment::where('dispatcher_assigned_id', '=', $disp_assign_id)->update(array('is_default' => 0, 'updated_at' => date('Y-m-d H:i:s')));
			
			$newpayment = new PaymentServices();
			if($payment_id!=''){
				$payment_data = Payment::where('id', $payment_id)->first();
				if($payment_data!=''){
					$DispatcherAssigned = DispatcherAssigned::where('id', '=', $disp_assign_id)->first();
					if($DispatcherAssigned!=''){
						$fullname = $DispatcherAssigned->contact_name;
						$response = $newpayment->updateDefaultCard($payment_data->card_id, $payment_data->customer_id,$fullname);
						
						Payment::where('id', '=', $payment_id)->update(array('is_default' => 1, 'updated_at' => date('Y-m-d H:i:s')));
						return $response;
					}
				}
			}			
						
		}		
	}

    public function updatedefaultPassengerpaymentcard()
    {
        $payment_id = $_POST['defaultcard_id'];
        $disp_assign_id = $_POST['disp_assign_id'];
        if ($payment_id > 0 && $disp_assign_id > 0) {
            if(Session::get('user_select') == 3) {
                Payment::where('owner_id', '=', $disp_assign_id)->update(array('is_default' => 0, 'updated_at' => date('Y-m-d H:i:s')));
            }else{
                Payment::where('dispatcher_assigned_id', '=', $disp_assign_id)->update(array('is_default' => 0, 'updated_at' => date('Y-m-d H:i:s')));
            }
            $newpayment = new PaymentServices();
            if ($payment_id != '') {
                $payment_data = Payment::where('id', $payment_id)->first();
                if ($payment_data != '') {
                    if(Session::get('user_select') == 3) {
                        $DispatcherAssigned = owner::where('id', '=', $disp_assign_id)->first();
                    }else{
                        $DispatcherAssigned = DispatcherAssigned::where('id', '=', $disp_assign_id)->first();
                    }
                    if ($DispatcherAssigned != '') {
                        $fullname = $DispatcherAssigned->contact_name;
                        $response = $newpayment->updateDefaultCard($payment_data->card_id, $payment_data->customer_id, $fullname);
                        Payment::where('id', '=', $payment_id)->update(array('is_default' => 1, 'updated_at' => date('Y-m-d H:i:s')));
                        return $response;
                    }
                }
            }
        }
    }

	
	public function DispatcherRideCompleted(){
        $request_id     = $_POST['request_id'];
        $dropoffaddress = $_POST['dropoffaddress'];
        $distance       = $_POST['dist'];
        $time           = $_POST['distancetime'];
        $comment        = $_POST['comment'];
        $rating         = $_POST['walker_rating'];
        $feedback       = $_POST['feedback_comment'];

		Log::info('distance input = ' . print_r($distance, true));
        Log::info('time input = ' . print_r($time, true));
        Log::info('request input = ' . print_r($request_id, true));
        Log::info('dropoffaddress input = ' . print_r($dropoffaddress, true));
        Log::info('comment input = ' . print_r($comment, true));
        Log::info('rating input = ' . print_r($rating, true));
        Log::info('feedback input = ' . print_r($feedback, true));
		
		if($request_id>0 && $dropoffaddress!='' && $distance>0 && $time!='' && $comment!=''){
			if($dropoffaddress!=''){
			   $dropprepAddr = str_replace(' ','+',$dropoffaddress);
			   $geocode=file_get_contents('https://maps.google.com/maps/api/geocode/json?address='.$dropprepAddr.'&sensor=false');
			   $dropoutput= json_decode($geocode);
			   $droplatitude = $dropoutput->results[0]->geometry->location->lat;
			   $droplongitude = $dropoutput->results[0]->geometry->location->lng;
		    }
			if ($request =RideRequest::find($request_id)) {
				if ($request->confirmed_walker != 0 || $request->is_manual == 1) {
					$walker_payment_remaining = $provider_refund_remaining = 0;
                    $walker_data = $this->getWalkerData($request->confirmed_walker);
					if( $request->is_manual == 1 ){
                        $walker_data = null;
                    }
                    $actual_total = 0;
                    $price_per_unit_distance = 0;
                    $price_per_unit_time = 0;
                    $base_price = 0;
                    Log::info('walkerdata = ' . print_r($walker_data, true));

                        if ($walker_data) {
                            $providertype = ProviderType::where('id', $walker_data->type)->first();
                            $settings = Settings::where('key', 'default_distance_unit')->first();
                            $unit = $settings->value;
                            $reqserv = RequestServices::where('request_id', $request_id)->get();


                            foreach ($reqserv as $rse) {
                                Log::info('type = ' . print_r($rse->type, true));
                                $protype = ProviderType::where('id', $rse->type)->first();
                                $pt = ProviderServices::where('provider_id', $request->confirmed_walker)->where('type', $rse->type)->first();
                                if ($pt != null) {
                                    if ($pt->base_price == 0) {
                                        $base_price = $providertype->base_price;
                                        $rse->base_price = $base_price;
                                    } else {
                                        $base_price = $pt->base_price;
                                        $rse->base_price = $base_price;
                                    }
                                } else {
                                    $pt = ProviderServices::where('type', $rse->type)->first();

                                    $base_price = $providertype->base_price;
                                    $rse->base_price = $base_price;
                                }

                                $is_multiple_service = Settings::where('key', 'allow_multiple_service')->first();
                                if ($is_multiple_service->value == 0) {

                                    if ($pt->price_per_unit_distance == 0) {
                                        if ($distance <= $providertype->base_distance) {
                                            $price_per_unit_distance = 0;
                                        } else {
                                            $price_per_unit_distance = $providertype->price_per_unit_distance * ($distance - $providertype->base_distance);
                                        }
                                        $rse->distance_cost = $price_per_unit_distance;
                                    } else {
                                        if ($distance <= $providertype->base_distance) {
                                            $price_per_unit_distance = 0;
                                        } else {
                                            $price_per_unit_distance = $pt->price_per_unit_distance * ($distance - $providertype->base_distance);
                                        }
                                        $rse->distance_cost = $price_per_unit_distance;
                                    }

                                    if ($pt->price_per_unit_time == 0) {
                                        /* $settime_price = Settings::where('key', 'price_per_unit_time')->first();
                                          $price_per_unit_time = $settime_price->value * $time; */
                                        $price_per_unit_time = $providertype->price_per_unit_time * $time;
                                        $rse->time_cost = $price_per_unit_time;
                                    } else {
                                        $price_per_unit_time = $pt->price_per_unit_time * $time;
                                        $rse->time_cost = $price_per_unit_time;
                                    }
                                }

                                //Log::info('total price = ' . print_r($base_price + $price_per_unit_distance + $price_per_unit_time, true));
                                $rse->total = $base_price + $price_per_unit_distance + $price_per_unit_time;
                                $rse->save();
                                $actual_total = $actual_total + $base_price + $price_per_unit_distance + $price_per_unit_time;
                                Log::info('total_price = ' . print_r($actual_total, true));
                            }
                        }

                        $rs = RequestServices::where('request_id', $request_id)->get();

                        $settings = Settings::where('key', 'provider_amount_for_each_request_in_percentage')->first();
                        $provider_percentage = $settings->value;

                        $total = 0;
                        $ref_total = 0;
                        $promo_total = 0;
                        $cash_card_user = 0;
                        foreach ($rs as $key) {
                            $total = $total + $key->total;
                        }

                        if ($request->payment_mode == 0) {
                            $walker_payment_remaining = (($total * $provider_percentage) / 100);
                        }
                        if($request->payment_mode == 3){
                            $request =RideRequest::find($request_id);
                            $request->is_completed = 1;
                            $request->is_walker_started = 1;
                            $request->is_walker_arrived = 1;
                            $request->is_started = 1;
                            $request->comments = $comment;
                            $request->distance = $distance;
                            $request->time = $time;
                            $request->security_key = NULL;
                            $request->total = $total;
                            $request->card_payment = $total;
                            $request->payment_remaining = $walker_payment_remaining;
                            $request->refund_remaining = $provider_refund_remaining;
                            $request->ledger_payment = $ref_total;
                            $request->promo_payment = $promo_total;
                            error_log($request->payment_mode);
                            $request->save();
                        }else{
                            $request =RideRequest::find($request_id);
                            $request->is_completed = 1;
                            $request->is_walker_started = 1;
                            $request->is_walker_arrived = 1;
                            $request->is_started = 1;
                            $request->comments = $comment;
                            $request->distance = $distance;
                            $request->time = $time;
                            $request->security_key = NULL;
                            $request->total = $total;
                            $request->card_payment = $total;
                            $request->payment_remaining = $walker_payment_remaining;
                            $request->refund_remaining = $provider_refund_remaining;
                            $request->ledger_payment = $ref_total;
                            $request->promo_payment = $promo_total;
                            $request->payment_mode = $cash_card_user;
                            error_log($request->payment_mode);
                            $request->save();
                        }


                        //update walker table
                        if ($request->confirmed_walker != 0) {
                            $walker = Walker::find($request->confirmed_walker);
                            $walker->is_available = 1;
                            if (!isset($angle)) {
                                $angle = get_angle($walker->latitude, $walker->longitude, $droplatitude, $droplongitude);
                            }
                            $walker->payment_remaining = $walker->payment_remaining + $walker_payment_remaining;
                            $walker->refund_remaining = $walker->refund_remaining + $provider_refund_remaining;
                            $walker->old_latitude = $walker->latitude;
                            $walker->old_longitude = $walker->longitude;
                            $walker->latitude = $droplatitude;
                            $walker->longitude = $droplongitude;
                            $walker->bearing = $angle;
                            $walker->save();

                            $walk_location = new WalkLocation;
                            $walk_location->latitude = $droplatitude;
                            $walk_location->longitude = $droplongitude;
                            $walk_location->request_id = $request_id;
                            $walk_location->distance = $distance;
                            $walk_location->bearing = $angle;
                            $walk_location->save();
                        }

                        $walker_review = new WalkerReview;
                        $walker_review->request_id = $request_id;
                        if($request->confirmed_walker!=0){
                            $walker_review->walker_id = $request->confirmed_walker;
                        }
                        $walker_review->rating = $rating;
                        if($request->owner_id!=0){
                            $walker_review->owner_id = $request->owner_id;
                        }
                        $walker_review->comment = $feedback;
                        $walker_review->save();
                        Log::info('walker review1 = ' . print_r($walker_review->rating, true));


                        // Send SMS
                        $owner = Owner::where('id', $request->owner_id)->first();
                        if ($owner == '' || $owner == null) {
                            $owner = DispatcherAssigned::where('id', $request->dispatcher_assigned_id)->first();
                        }
                        $settings = Settings::where('key', 'sms_when_provider_completes_job')->first();
                        $pattern = $settings->value;
                        $pattern = str_replace('%user%', $owner->contact_name, $pattern);
                        if ($request->confirmed_walker != 0) {
                            $pattern = str_replace('%driver%', $walker->contact_name, $pattern);
                            $pattern = str_replace('%driver_mobile%', $walker->phone, $pattern);
                        } else if ($request->is_manual == 1) {
                            $pattern = str_replace('%driver%', $request->driver_name, $pattern);
                            $pattern = str_replace('%driver_mobile%', $request->driver_phone, $pattern);
                        }
                        $pattern = str_replace('%amount%', $request->total, $pattern);
                        if ($request->dispatcher_assigned_id > 0) {
                            sms_notification($request->dispatcher_assigned_id, 'dispatcher_assigned', $pattern);
                            error_log("how");
                        } else {
                            error_log("here");
                            sms_notification($request->owner_id, 'owner', $pattern);
                        }
                        if ($request->dispatcher_assigned_id != '') {
                            $id = $request->dispatcher_assigned_id;
                            $passengerinfo = DispatcherAssigned::where('id', $request->dispatcher_assigned_id)->first();
                            $settings = Settings::where('key', 'admin_email_address')->first();
                            $admin_email = $settings->value;
                            $settings = Settings::where('key', 'ride_assignee_phone_number')->first();
                            $ride_assignee_phone_number = $settings->value;
                            $follow_url = web_url() . "/booking/myrides";
                            if ($request->driver_name) {
                                $driver_name = $request->driver_name;
                                $driver_phone = $request->driver_phone;
                            } else {
                                $driver_name = "NA";
                                $driver_phone = "NA";
                            }
                        } else if ($request->owner_id != '') {
                            $id = $request->owner_id;
                            $passengerinfo = Owner::where('id', $request->owner_id)->first();
                            $settings = Settings::where('key', 'admin_email_address')->first();
                            $admin_email = $settings->value;
                            $settings = Settings::where('key', 'ride_assignee_phone_number')->first();
                            $ride_assignee_phone_number = $settings->value;
                            $follow_url = web_url() . "/booking/myrides";
                            if ($request->driver_name) {
                                $driver_name = $request->driver_name;
                                $driver_phone = $request->driver_phone;
                            } else {
                                $driver_name = "NA";
                                $driver_phone = "NA";
                            }
                        }
                        $passenger_name = $passengerinfo->contact_name;
                        $agent_name = $request->agent_contact_name;
                        $datetime = new DateTime($request->request_start_time);
                        $datetime->format('Y-m-d H:i:s');
                        $user_time = new DateTimeZone($request->time_zone);
                        $datetime->setTimezone($user_time);
                        $newpickuptime = $datetime->format('Y-m-d H:i:s');
                        $pattern = array('driver_name' => $driver_name, 'driver_phone' => $driver_phone,
                            'passenger_name' => $passenger_name, 'passenger_phone' => $passengerinfo->phone,
                            'pickup_time' => $newpickuptime, 'pickup_location' => $request->src_address,
                            'dropoff_time' => $request->updated_at, 'dropoff_location' => $request->dest_address,
                            'butterfli_dispatcher_phno' => $ride_assignee_phone_number,
                            'admin_email' => $admin_email, 'trip_id' => $request->id,
                            'follow_url' => $follow_url, 'agent_name' => $agent_name);
                        $subject = "Thanks for Riding with ButterFLi";
                        if ($request->dispatcher_assigned_id != ''){
                            email_notification($id, 'dispatcher_assigned', $pattern, $subject, 'ride_complete', 'imp');
                    }else if($request->owner_id != '') {
                        email_notification($id, 'owner', $pattern, $subject, 'ride_complete', 'imp');
                    }
                        email_notification($id, 'ride_assignee', $pattern, $subject, 'ride_complete', 'imp');
                        email_notification($id, 'ride_assignee_2', $pattern, $subject, 'ride_complete', 'imp');
                        email_notification($id, 'ride_assignee_3', $pattern, $subject, 'ride_complete', 'imp');
						return 1;
				}
			} else{
				return 2;
			}			
		}		
	}
	
	public function getWalkerData($walker_id) {

        $walker_data = Walker::where('id', '=', $walker_id)->first();
		if (!$walker_data) {
			return false;
		}
		return $walker_data;
    }

    public function CalculateAmount() {
        $enterpriseclient_id = $_POST['enterpriseclient_id'];
        $enterpriseClient = EnterpriseClient::find($enterpriseclient_id);

        $params = array(
            'type' =>                   $_POST['type'],
            'enterprise_client' =>      $enterpriseClient,
            'origin_latitude' =>        Input::get('origin_latitude'),
            'origin_longitude' =>       Input::get('origin_longitude'),
            'destination_latitude' =>   Input::get('destination_latitude'),
            'destination_longitude' =>  Input::get('destination_longitude'),
            'is_wheelchair' =>          $_POST['is_wheelchair'],
            'is_roundtrip' =>           $_POST['is_roundtrip']
        );

        $estimate = CalculateFare($params);
        Session::put('fare_estimate', $estimate);

        return
            View::make('enterpriseclient.estimatepanel')
            ->with('estimate', $estimate);
    }

    public function AssignDriver(){
        $request_id = $_POST['request_id'];
        $driver_id  = $_POST['driver_id'];
        $total_cost    = $_POST['total_cost'];
        $est_time      = $_POST['est_time'];

        if ($request =RideRequest::find($request_id))
        {
            if ($request->is_confirmed != 1) {
                // request ended
                if($request->later==1){
                  RideRequest::where('id', '=', $request_id)->update(array('current_walker' => $driver_id, 'confirmed_walker' => $driver_id, 'status' => 1, 'is_confirmed' => 1));
                }else{
                  RideRequest::where('id', '=', $request_id)->update(array('current_walker' => $driver_id, 'confirmed_walker' => $driver_id, 'status' => 1, 'is_confirmed' => 1, 'updated_at'=>date('Y-m-d H:i:s')));
                }

                $request_meta = new RequestMeta;
                $request_meta->request_id = $request->id;
                $request_meta->walker_id = $driver_id;
                $request_meta->status = '1';
                $request_meta->is_cancelled = '0';
                $request_meta->save();
                // Update Walker availability
                Walker::where('id', '=', $driver_id)->update(array('is_available' => 0));
                // remove other schedule_meta
                RequestMeta::where('request_id', '=', $request_id)->where('status', '=', 0)->delete();

                $walker = Walker::find($driver_id);


                if ($walker) {
                    $passengerinfo = DispatcherAssigned::where('id', $request->dispatcher_assigned_id)->first();

                    if($passengerinfo==null || $passengerinfo==''){
                        $passengerinfo = Owner::where('id', $request->owner_id)->first();
                    }
                    // Send SMS
                    $settings = Settings::where('key', 'sms_request_created')->first();
                    $pattern = $settings->value;
                    Log::info('passenger = ' . print_r($passengerinfo, true));
                    $pattern = str_replace('%user%', $passengerinfo->contact_name, $pattern);
                    $pattern = str_replace('%id%', $request->id, $pattern);
                    $pattern = str_replace('%user_mobile%', $passengerinfo->phone, $pattern);
                    $pattern = str_replace('%pickup_address%', $request->src_address, $pattern);
                    $pattern = str_replace('%dropoff_address%', $request->dest_address, $pattern);
                    $pattern = str_replace('%start_app_link%', '', $pattern);

                    sms_notification($walker->id, 'walker', $pattern);

                    $walker_name = $walker->contact_name;

                    $this->GenerateReceipt($request_id,$walker_name,$walker->phone,$total_cost,$est_time);

                    $pattern1 = "Ride has been confirmed for the request-id: " . $request->id . ' ' ."with driver: " . $walker_name . ' ' . " Phone: " . $walker->phone. ' ' . "track driver:".web_url()."/driverlocation/map/".$request->id;

                    sms_notification($request->dispatcher_id, 'ride_assignee', $pattern1);
                    sms_notification($request->dispatcher_id, 'ride_assignee_2', $pattern1);
                    sms_notification($request->dispatcher_id, 'ride_assignee_3', $pattern1);

                    if($request->dispatcher_assigned_id >0){
                        Log::info('pasengerid = ' . print_r($passengerinfo->id, true));
                        sms_notification($passengerinfo->id, 'dispatcher_assigned', $pattern1);
                    }else{
                        sms_notification($passengerinfo->id, 'owner_sms', $pattern1);
                    }

                    $settings = Settings::where('key', 'admin_email_address')->first();
                    $admin_email = $settings->value;
                    $settings = Settings::where('key', 'ride_assignee_phone_number')->first();
                    $ride_assignee_phone_number = $settings->value;
                    $follow_url = web_url() . "/dispatcher/confirmedrides";

                    if($request->dispatcher_id>0){
                        $Dispatcher = Dispatcher::find($request->dispatcher_id);
                    }elseif($request->consumer_id>0) {
                        $Dispatcher = Consumer::find($request->consumer_id);
                    }elseif($request->owner_id>0){
                        $Dispatcher = Owner::find($request->owner_id);
                    }elseif($request->healthcare_id>0){
                        $Dispatcher = EnterpriseClient::find($request->healthcare_id);
                    }

                    $time = new DateTime($request->request_start_time);
                    $time->format('h:ia') . "\n";
                    $user_time = new DateTimeZone($request->time_zone);
                    $time->setTimezone($user_time);
                    $pickuptime = $time->format('h:ia');

                    $date = new DateTime($request->request_start_time);
                    $date->format('Y-m-d') . "\n";
                    $user_time = new DateTimeZone($request->time_zone);
                    $date->setTimezone($user_time);
                    $pickupdate = $date->format('Y-m-d');

                    $passenger_name = $passengerinfo->contact_name;
                    $dispatcher_name = $Dispatcher->contact_name;

                    $pattern = array('driver_name' => $walker_name, 'driver_phone' => $walker->phone,
                        'passenger_name' => $passenger_name, 'passenger_phone' => $passengerinfo->phone,
                        'pickup_time' => $pickuptime,'pickup_date' => $pickupdate, 'pickup_location' => $request->src_address,
                        'dropoff_location' => $request->dest_address, 'butterfli_dispatcher_phno' => $ride_assignee_phone_number,
                        'admin_email' => $admin_email, 'trip_id' => $request->id, 'follow_url' => $follow_url,
                        'agent_name' => $dispatcher_name, 'estimated_time'=>'', 'walker_id' => asset_url()."/driverlocation/map/".$request->id);
                    $subject = "Your ride is confirmed";

                    email_notification($request->dispatcher_id, 'ride_assignee', $pattern, $subject, 'ride_confirm', 'imp');
                    email_notification($request->dispatcher_id, 'ride_assignee_2', $pattern, $subject, 'ride_confirm', 'imp');
                    email_notification($request->dispatcher_id, 'ride_assignee_3', $pattern, $subject, 'ride_confirm', 'imp');
                    error_log(asset_url()."/driverlocation/map/".$request->id);
                    if($request->dispatcher_assigned_id >0){
                        Log::info('pasenger = ' . print_r(asset_url()."/driverlocation/map/".$request->id, true));
                        email_notification($passengerinfo->id, 'dispatcher_assigned', $pattern, $subject, 'ride_confirm', 'imp');
                    }else{
                        email_notification($passengerinfo->id, 'owner_mail', $pattern, $subject, 'ride_confirm', 'imp');
                    }
                    return 1;
                }

            }
        }else {
            return 2;
        }
    }

    public function AssignTP(){
        $request_id = $_POST['request_id'];
        $tp_id  = $_POST['tp_id'];
        $user_id = Session::get('user_id');

        if ($request =RideRequest::find($request_id))
        {
            $assigned_dispatcher = AssignedDispatcherRequest::where('request_id', $request->id)->where('assigned_dispatcher_id', $tp_id)->where('is_cancelled', 0)->first();
            if (!$assigned_dispatcher) {

                $requestdisp = new AssignedDispatcherRequest;
                $requestdisp->request_id = $request->id;
                $requestdisp->assigned_dispatcher_id = $tp_id;
                $requestdisp->assignee_dispatcher_id = $user_id;
                $requestdisp->status = '1';
                $requestdisp->created_at = date('Y-m-d H:i:s');
                $requestdisp->updated_at = date('Y-m-d H:i:s');
                $requestdisp->save();

                //sending mail to TP
                //get user information
                //$passengerinfo = DispatcherAssigned::where('id', $request->dispatcher_assigned_id)->first();
                $settings = Settings::where('key', 'admin_email_address')->first();
                $admin_email = $settings->value;

                $settings = Settings::where('key', 'ride_assignee_phone_number')->first();
                $ride_assignee_phone_number = $settings->value;

                $follow_url = web_url() . "/dispatcher/submittedrides";

//                $passenger_name = $passengerinfo->contact_name;
                $subject = "You've got a ride request";

                if ($_SERVER['HTTP_HOST'] == "ride.gobutterfli.com") {
                    $server = "Development";
                } elseif ($_SERVER['HTTP_HOST'] == "app.gobutterfli.com") {
                    $server = "Production";
                } elseif ($_SERVER['HTTP_HOST'] == "demo.gobutterfli.com") {
                    $server = "Demo";
                } else {
                    $server = "Local";
                }

                $time = new DateTime($request->request_start_time);
                $time->format('h:ia') . "\n";
                $user_time = new DateTimeZone($request->time_zone);
                $time->setTimezone($user_time);
                $pickuptime = $time->format('h:ia');

                $date = new DateTime($request->request_start_time);
                $date->format('Y-m-d') . "\n";
                $user_time = new DateTimeZone($request->time_zone);
                $date->setTimezone($user_time);
                $pickupdate = $date->format('Y-m-d');

                $pattern = array('pickup_time' => $pickuptime,'pickup_date' => $pickupdate,'pickup_location' => $request->src_address,
                    'dropoff_location' => $request->dest_address, 'butterfli_dispatcher_phno' => $ride_assignee_phone_number,
                    'admin_email' => $admin_email, 'trip_id' => $request->id, 'follow_url' => $follow_url,
                    'server' => $server);

                email_notification($tp_id, 'dispatcher', $pattern, $subject, 'new_ride_request', null);

                return 1;
            }
        }else {
            return 2;
        }
    }

    public function SubmittedRidesCount(){

        $user_id = Session::get('user_id');

        if(Session::get('is_admin') ==1) {
            $walks = DB::table('request')
                ->leftJoin('assigned_dispatcher_request', 'request.id', '=', 'assigned_dispatcher_request.request_id')
                //->where('request.dispatcher_id', '=', $user_id)
                ->where('request.is_confirmed', '=', '0')
                ->where('request.is_cancelled', '=', '0')
                ->where('request.is_completed', '=', '0')
                ->count();
        } else{
            $walks = DB::table('assigned_dispatcher_request')
                ->Join('request', 'request.id', '=', 'assigned_dispatcher_request.request_id')
                ->orderBy('assigned_dispatcher_request.id', 'DESC')
                ->where('assigned_dispatcher_request.assigned_dispatcher_id', '=', $user_id)
                ->where('request.is_confirmed', '=', '0')
                ->where('request.is_cancelled', '=', '0')
                ->where('request.is_completed', '=', '0')
                ->where('assigned_dispatcher_request.is_cancelled', '=', '0')
                ->count();
        }

        return $walks;
    }

    public function ConfirmedRidesCount(){

        $user_id = Session::get('user_id');

        if(Session::get('is_admin') ==1) {
            $walks = DB::table('request')
                ->leftJoin('assigned_dispatcher_request', 'request.id', '=', 'assigned_dispatcher_request.request_id')
                //->where('request.dispatcher_id', '=', $user_id)
                ->where('request.is_confirmed', '=', '1')
                ->where('request.is_cancelled', '=', '0')
                ->where('request.is_completed', '=', '0')
                ->count();
        } else{
            $walks = DB::table('assigned_dispatcher_request')
                ->Join('request', 'request.id', '=', 'assigned_dispatcher_request.request_id')
                ->where('assigned_dispatcher_request.assigned_dispatcher_id', '=', $user_id)
                ->where('request.is_confirmed', '=', '1')
                ->where('request.is_cancelled', '=', '0')
                ->where('request.is_completed', '=', '0')
                ->where('assigned_dispatcher_request.is_cancelled', '=', '0')
                ->count();
        }
        return $walks;
    }

    public function CancelledRidesCount(){

        $user_id = Session::get('user_id');

        if(Session::get('is_admin') ==1) {
            $walks = DB::table('request')
                ->leftJoin('assigned_dispatcher_request', 'request.id', '=', 'assigned_dispatcher_request.request_id')
                //->where('request.dispatcher_id', '=', $user_id)
                ->where('request.is_cancelled', '=', '1')
                ->count();
        } else{
            $walks = DB::table('assigned_dispatcher_request')
                ->Join('request', 'request.id', '=', 'assigned_dispatcher_request.request_id')
                ->where('assigned_dispatcher_request.assigned_dispatcher_id', '=', $user_id)
                ->where('assigned_dispatcher_request.is_cancelled', '=', '1')
                ->count();
        }

        return $walks;
    }

    public function CompletedRidesCount(){

        $user_id = Session::get('user_id');
        if(Session::get('is_admin') ==1) {
            $walks = DB::table('request')
                ->leftJoin('assigned_dispatcher_request', 'request.id', '=', 'assigned_dispatcher_request.request_id')
                //->where('request.dispatcher_id', '=', $user_id)
                ->where('request.is_completed', '=', '1')
                ->count();
        } else{
            $walks = DB::table('assigned_dispatcher_request')
                ->Join('request', 'request.id', '=', 'assigned_dispatcher_request.request_id')
                ->where('assigned_dispatcher_request.assigned_dispatcher_id', '=', $user_id)
                ->where('request.is_completed', '=', '1')
                ->count();
        }

        return $walks;
    }

    public function GetServicespecificDrivers(){

        $type = $_POST['service_type'];

        $query = "SELECT walker.* "
            . "FROM walker "
            . "where is_available = 1 AND "
            . "is_active = 1 AND "
            . "is_approved = 1 AND "
            . "type = $type AND "
            . "deleted_at IS NULL "
            . "order by contact_name ASC";

        $results = DB::select(DB::raw($query));

        echo "<select class='form-control' id='driver_id' name='driver_id'>";
        echo "<option value='0'>Select driver</option>";
        if(count($results)>0){
            foreach($results as $result){
                echo "<option value='$result->id'>$result->contact_name ($result->phone)</option>";
            }
        }
        echo "</select>";
    }

    public function MyProfile()
    {
        $countarray = array();
        $countarray['submitted'] = $this->SubmittedRidesCount();
        $countarray['confirmed'] = $this->ConfirmedRidesCount();
        $countarray['cancelled'] = $this->CancelledRidesCount();
        $countarray['completed'] = $this->CompletedRidesCount();

        $user_id = Session::get('user_id');
        $Dispatcher = Dispatcher::find($user_id);

        return View::make('dispatcher.myprofile')
            ->with('title', 'My Profile')
            ->with('provider', $Dispatcher)
            ->with('countarray',$countarray);
    }

    public function UpdateDispatcherProfile() {
        $user_id = Session::get('user_id');
        $contact_name = Input::get('contact_name');
        $phone = Input::get('phone');
        //$company = Input::get('company');

        $validator = Validator::make(
            array(
                'contact_name' => $contact_name,
                'phone'=> $phone
            ), array(
            'contact_name' => 'required',
            'phone' =>'required'
        ));

        $validatorPhone = Validator::make(
            array(
                'phone' => $phone,
            ), array(
            'phone' => 'required|numeric'
        ), array(
                'phone' => 'Phone number must be required.'
            )
        );

        if ($validator->fails()) {
            $error_messages = $validator->messages();
            Log::info('Error = ' . print_r($error_messages, true));
            return Redirect::to('dispatcher/myprofile')->with('error', 'Please Fill all the fields.');
        }else if ($validatorPhone->fails()) {
            return Redirect::to('dispatcher/myprofile')->with('error', 'Invalid Phone Number Format');
        } else {
            $Dispatcher = Dispatcher::find($user_id);

            $Dispatcher->contact_name = $contact_name;
            if(strlen($phone) <=10){
                $Dispatcher->phone = "+1".$phone;
            } else{
                $Dispatcher->phone = $phone;
            }

            //$Dispatcher->company = $company;
            $Dispatcher->save();

            return Redirect::to('/dispatcher/myprofile')->with('success', 'Your profile has been updated successfully');
        }
    }

    public function UpdateDispatcherPassword() {
        $current_password = Input::get('current_password');
        $new_password = Input::get('new_password');
        $confirm_password = Input::get('confirm_password');

        $user_id = Session::get('user_id');
        $Dispatcher = Dispatcher::find($user_id);


        if ($new_password === $confirm_password) {

            if (Hash::check($current_password, $Dispatcher->password)) {
                $password = Hash::make($new_password);
                $Dispatcher->password = $password;
                $Dispatcher->save();

                $message = "Your password is successfully updated";
                $type = "success";
            } else {
                $message = "Please enter your current password correctly";
                $type = "danger";
            }
        } else {
            $message = "Passwords do not match in New Password and Confirm Password fields";

            $type = "danger";
        }
        return Redirect::to('/dispatcher/myprofile')->with('success', $message);
    }


    public function DispatcherSendRideInfo(){
        $code = $_POST['code'];
        $sms = $_POST['sms'];
        $email = $_POST['email'];
        $request_id = $_POST['request_id'];
        $sms_no = $code.$sms;
        if ($request =RideRequest::find($request_id)) {
            Log::info('Smsno:  = ' . print_r($sms_no, true));
            Log::info('email:  = ' . print_r($email, true));
            Log::info('request_id:  = ' . print_r($request_id, true));
            $settings = Settings::where('key', 'admin_email_address')->first();
            $admin_email = $settings->value;
            $passengerinfo = DispatcherAssigned::where('id', $request->dispatcher_assigned_id)->first();
            if($passengerinfo=='')
            {
                $passengerinfo = Owner::where('id', $request->owner_id)->first();

            }
            $passenger_name = $passengerinfo->contact_name;
            $datetime = new DateTime($request->request_start_time);
            $datetime->format('Y-m-d H:i:s') . "\n";
            $user_time = new DateTimeZone($request->time_zone);
            $datetime->setTimezone($user_time);
            $newpickuptime = $datetime->format('Y-m-d H:i:s');
            $date = new DateTime($request->request_start_time);
            $date->format('Y-m-d') . "\n";
            $user_time = new DateTimeZone($request->time_zone);
            $date->setTimezone($user_time);
            $pickupdate = $date->format('Y-m-d');
            $time = new DateTime($request->request_start_time);
            $time->format('h:ia') . "\n";
            $user_time = new DateTimeZone($request->time_zone);
            $time->setTimezone($user_time);
            $pickuptime = $time->format('h:ia');
            if ($request->is_cancelled == 1) {
                $status1 = "Cancelled";
            } elseif ($request->is_completed == 1) {
                $status1 = "Completed";
            } elseif ($request->is_started == 1) {
                $status1 = "Started";
            } elseif ($request->is_walker_arrived == 1) {
                $status1 = "Driver Arrived";
            } elseif ($request->is_walker_started == 1) {
                $status1 = "Driver Started";
            } else {
                $status1 = "Yet To Start";
            }
            if ($request->is_wheelchair_request == 1) {
                $wheelchair_request = 'YES';
            } else {
                $wheelchair_request = 'NO';
            }

            if($request->confirmed_walker != '') {
                $walker = Walker::find($request->confirmed_walker);
                if ($walker) {
                    $driver_name = $walker->contact_name;
                    $driver_phone = $walker->phone;
                }
            }else if($request->is_manual == 1){
                $driver_name = $request->driver_name;
                $driver_phone = $request->driver_phone;
            }


            if ($sms != '') {
                $pattern = "Ride info for the request-id: " . $request->id."\n";
                $pattern .= " Passenger Name: ".$passenger_name."\n";
                $pattern .= " Passenger Phone: ".$passengerinfo->phone."\n";
                $pattern .= " Driver Name: ".$driver_name."\n";
                $pattern .= " Driver Phone: ".$driver_phone."\n";
                $pattern .= " Ride Time: ".$newpickuptime."\n";
                $pattern .= " Pickup Address: ".$request->src_address."\n";
                $pattern .= " Dropoff Address: ".$request->dest_address."\n";
                $pattern .= " Ride Status: ".$status1."\n";
                if ($request->is_wheelchair_request == 1) {
                    $pattern .= " Wheelchair Requested.";
                }
                sms_notification(1, 'ride_info', $pattern, $sms_no);
            }
            if ($email != '') {
                $pattern = array('driver_name'=> $driver_name, 'driver_phone'=> $driver_phone,
                    'passenger_name'=>$passenger_name, 'passenger_phone'=> $passengerinfo->phone,
                    'pickup_time'=>$pickuptime,'pickup_date'=>$pickupdate, 'pickup_location'=> $request->src_address,
                    'dropoff_location'=> $request->dest_address,'butterfli_dispatcher_phno'=>'',
                    'admin_email' => $admin_email,'trip_id' => $request->id, 'follow_url' => '',
                    'ride_status'=>$status1,'wheelchair_request' => $wheelchair_request,);
                $subject = "Ride Information";
                email_notification(1, 'ride_info', $pattern, $subject, 'ride_information', 'imp','',$email);
            }
            return 1;
        } else {
            return 2;
        }
    }
    public function Notification(){
        $user_id = Session::get('user_id');

        if(Session::get('is_admin') == 1) {
            $request = RideRequest::where('request.is_confirmed', '=', '0')
                ->where('request.is_cancelled', '=', '0')
                ->where('request.is_completed', '=', '0')
                ->select('confirmed_walker')->get();
        } else{
            $request = DB::table('assigned_dispatcher_request')
                ->Join('request', 'request.id', '=', 'assigned_dispatcher_request.request_id')
                ->select('request.confirmed_walker')
                ->where('assigned_dispatcher_request.assigned_dispatcher_id', '=', $user_id)
                ->where('request.is_confirmed', '=', '0')
                ->where('request.is_cancelled', '=', '0')
                ->where('request.is_completed', '=', '0')
                ->where('assigned_dispatcher_request.is_cancelled', '=', '0')
                ->get();
        }
        return ($request);
    }


    public function TrainingModules(){
        $countarray = array();
        $countarray['submitted'] = $this->SubmittedRidesCount();
        $countarray['confirmed'] = $this->ConfirmedRidesCount();
        $countarray['cancelled'] = $this->CancelledRidesCount();
        $countarray['completed'] = $this->CompletedRidesCount();


        $learningCategory = LearningCategory::where('is_active', '=', 1)->get();

        $title = 'Training Modules';
        return View::make('dispatcher.training_module')
            ->with('title', $title)
            ->with('page', 'module')
            ->with('learningmodules', $learningCategory)
            ->with('countarray',$countarray);
    }

    public function EnrollDrivers(){
        $countarray = array();
        $countarray['submitted'] = $this->SubmittedRidesCount();
        $countarray['confirmed'] = $this->ConfirmedRidesCount();
        $countarray['cancelled'] = $this->CancelledRidesCount();
        $countarray['completed'] = $this->CompletedRidesCount();

        $id = Request::segment(4);
        $learningCategory = LearningCategory::find($id);
        $TrainingSession = TrainingSessions::where('module_id','=',$id)->get();

        $driverarray = array();
        foreach ($TrainingSession as $driver){
            $driverarray[] = $driver->driver_id;
        }
        $driverlist = '';
        if(count($driverarray)>0){
            $driverlist = implode(",",$driverarray);
        }
        if($learningCategory){
            if(Session::get('is_admin') == 1) {
                $query = "SELECT walker.* FROM walker where is_approved = 1 AND deleted_at IS NULL ";
                    if($driverlist!=''){
                        $query .= " AND walker.id NOT IN ($driverlist) ";
                    }
                $query .=  " order by id DESC";
                $results = DB::select(DB::raw($query));
            }else{
                $user_id = Session::get('user_id');
                $dispatcher = Dispatcher::find($user_id);
                $transportation_provider_id = TransportationProvider::where('id','=',$dispatcher->transportation_provider_id)->first();

                $query = "SELECT walker.* FROM walker where is_approved = 1 AND transportation_provider_id = $transportation_provider_id->id AND 
                        deleted_at IS NULL ";
                if($driverlist!=''){
                    $query .= " AND walker.id NOT IN ($driverlist) ";
                }
                $query .=  " order by id DESC";
                $results = DB::select(DB::raw($query));
            }

            $title = 'Training Module: '.$learningCategory->category;
            return View::make('dispatcher.enrolldrivers')
                ->with('title', $title)
                ->with('page', 'module')
                ->with('drivers', $results)
                ->with('module_id',$learningCategory->id)
                ->with('countarray',$countarray);

        }else{
            return Redirect::to('dispatcher/trainingmodule')->with('error', 'Please try again.');
        }
    }

    public function AddDrivers(){
        $module_id = $_POST['module_id'];
        $driver_id = $_POST['driver_id'];

        if($module_id>0 && $driver_id>0){
            $new_session = new TrainingSessions;
            $new_session->module_id = $module_id;
            $new_session->driver_id = $driver_id;
            $new_session->save();

            $TrainingSession = TrainingSessions::where('module_id','=',$module_id)->get();

            $driverarray = array();
            foreach ($TrainingSession as $driver){
                $driverarray[] = $driver->driver_id;
            }
            $driverlist = '';
            if(count($driverarray)>0){
                $driverlist = implode(",",$driverarray);
            }

            if(Session::get('is_admin') == 1) {
                $query = "SELECT walker.* FROM walker where is_approved = 1 AND deleted_at IS NULL ";
                if($driverlist!=''){
                    $query .= " AND walker.id NOT IN ($driverlist)";
                }
                $query .=  " order by id DESC ";
                $results = DB::select(DB::raw($query));
            }else{
                $user_id = Session::get('user_id');
                $dispatcher = Dispatcher::find($user_id);
                $transportation_provider_id = TransportationProvider::where('id','=',$dispatcher->transportation_provider_id)->first();

                $query = "SELECT walker.* FROM walker where is_approved = 1 AND transportation_provider_id = $transportation_provider_id->id AND 
                        deleted_at IS NULL ";
                if($driverlist!=''){
                    $query .= " AND walker.id NOT IN ($driverlist) ";
                }
                $query .=  " order by id DESC ";
                $results = DB::select(DB::raw($query));
            }

            echo "<table class=\"table table-bordered\"><tbody><tr><th>Driver Name</th><th>Driver Phone</th><th>Enroll</th></tr>";
            foreach($results as $result)
            {
                echo "<tr>";
                echo "<td>$result->contact_name</td>";
                echo "<td>$result->phone </td>";
                echo "<td>";
                echo "<input type=checkbox name=checkbox onclick=enroll_driver($result->id,$module_id); id=driver_id value='$result->id'>";
                echo "</td></tr>";
            }
            echo "</tbody></table>";
        }
    }

    public function DriverListing(){
        $countarray = array();
        $countarray['submitted'] = $this->SubmittedRidesCount();
        $countarray['confirmed'] = $this->ConfirmedRidesCount();
        $countarray['cancelled'] = $this->CancelledRidesCount();
        $countarray['completed'] = $this->CompletedRidesCount();

        $id = Request::segment(4);

        $learningCategory = LearningCategory::find($id);
        $TrainingSession = TrainingSessions::where('module_id','=',$id)->get();

        $driverarray = array();
        foreach ($TrainingSession as $driver){
            $driverarray[] = $driver->driver_id;
        }
        $driverlist = '';
        if(count($driverarray)>0){
            $driverlist = implode(",",$driverarray);

            if(Session::get('is_admin') == 1) {
                $query = "SELECT walker.* FROM walker where is_approved = 1 AND deleted_at IS NULL AND walker.id IN ($driverlist) ";
                $query .=  " order by id DESC";
                $results = DB::select(DB::raw($query));
            }else{
                $user_id = Session::get('user_id');
                $dispatcher = Dispatcher::find($user_id);
                $transportation_provider_id = TransportationProvider::where('id','=',$dispatcher->transportation_provider_id)->first();

                $query = "SELECT walker.* FROM walker where is_approved = 1 AND transportation_provider_id = $transportation_provider_id->id AND 
                            deleted_at IS NULL AND walker.id IN ($driverlist) ";
                $query .=  " order by id DESC";
                $results = DB::select(DB::raw($query));
            }
        } else{
            $results = array();
        }

        $title = 'Enrolled Drivers for the Module: '.$learningCategory->category;
        return View::make('dispatcher.driverlisting')
            ->with('title', $title)
            ->with('page', 'module')
            ->with('drivers', $results)
            ->with('module_id',$learningCategory->id)
            ->with('countarray',$countarray);
    }

    public function DeleteDriver(){

        $module_id = Request::segment(4);
        $driver_id = Request::segment(5);

        if($module_id>0 && $driver_id>0){
            TrainingSessions::where('module_id','=',$module_id)
                ->where('driver_id','=',$driver_id)->delete();
            return Redirect::to('dispatcher/trainingmodule/driverlisting/'.$module_id)->with('success', 'Driver deleted successfully.');
        }
    }

    public function RegisterDrivers(){

        $countarray = array();
        $countarray['submitted'] = $this->SubmittedRidesCount();
        $countarray['confirmed'] = $this->ConfirmedRidesCount();
        $countarray['cancelled'] = $this->CancelledRidesCount();
        $countarray['completed'] = $this->CompletedRidesCount();

        $transportation_providers = TransportationProvider::all();

        $title = 'Register Drivers';

        return View::make('dispatcher.registerdrivers')
            ->with('title', $title)
            ->with('page', 'registerdrivers')
            ->with('transportation_providers',$transportation_providers)
            ->with('countarray',$countarray);
    }

    public function AddNewDriver(){

        $contact_name = Input::get('contact_name');
        $email      = Input::get('email');
        $phone      = Input::get('phone');
        if(Session::get('is_admin') == 1) {
            $company_id = Input::get('transportation_provider_id');
        }
        //
        $password      = Input::get('password');
        $type = '2';
        $device_token = 0;
        $bio = "";

        $validator = Validator::make(
            array(
                'contact_name' => $contact_name,
                'email' => $email,
                'type' => $type,
                'password' => $password,
                'phone' =>$phone
            ), array(
            'password' => 'required',
            'email' => 'required',
            'contact_name' => 'required',
            'phone' => 'required'
        ), array(
                'password' => 'Password field is required.',
                'email' => 'Email field is required',
                'contact_name' => 'Name field is required.',
                'phone' => 'Phone field is required.'
            )
        );
        $validator1 = Validator::make(
            array(
                'email' => $email,
            ), array(
            'email' => 'required|email'
        ), array(
                'email' => 'Email field is required'
            )
        );
        $validatorPhone = Validator::make(
            array(
                'phone' => $phone,
            ), array(
            'phone' => 'phone'
        ), array(
                'phone' => 'Phone number must be required.'
            )
        );

        if(Session::get('is_admin') == 1) {
            $validatorcompany = Validator::make(
                array(
                    'transportation_provider_id' => $company_id,
                ), array(
                'transportation_provider_id' => 'required'
            ), array(
                    'transportation_provider_id' => 'Company is required.'
                )
            );
        }

        if ($validator->fails()) {
            $error_messages = $validator->messages();
            Log::info('Error = ' . print_r($error_messages, true));
            return Redirect::to('dispatcher/registerdrivers')->with('error', 'Please Fill all the fields.');
        } else if ($validator1->fails()) {
            return Redirect::to('dispatcher/registerdrivers')->with('error', 'Please Enter email correctly.');
        } else if ($validatorPhone->fails()) {
            return Redirect::to('dispatcher/registerdrivers')->with('error', 'Invalid Phone Number Format');
        } else {
            if(Session::get('is_admin') == 1) {
                if ($validatorcompany->fails()) {
                    $error_messages = $validator->messages();
                    Log::info('Error = ' . print_r($error_messages, true));
                    return Redirect::to('dispatcher/registerdrivers')->with('error', 'Please select a company.');
                }
            }else{
                $user_id = Session::get('user_id');
                $dispatcher = Dispatcher::find($user_id);
                $transportation_provider = TransportationProvider::where('id','=',$dispatcher->transportation_provider_id)->first();
                $company_id = $transportation_provider->id;
            }
            Log::info('email = ' . print_r($email, true));
            if (Walker::where('email', '=',$email)->first()) {
                return Redirect::to('dispatcher/registerdrivers')->with('error', 'This email ID is already registered.');
            }else{
                $activation_code = uniqid();

                $result = substr($phone, 0, 2);
                if ($result != '+1') {
                    $new_phone_no = "+1" . $phone;
                } else {
                    $new_phone_no = $phone;
                }



                $walker = new Walker;
                $walker->contact_name = $contact_name;
                $walker->email = $email;
                $walker->phone = $new_phone_no;
                $walker->activation_code = $activation_code;
                $walker->email_activation = 1;
                $walker->transportation_provider_id = $company_id;
                if ($password != "") {
                    $walker->password = Hash::make($password);
                }
                $walker->token = generate_token();
                $walker->token_expiry = generate_expiry();
                $walker->type = $type;
                $walker->picture = "NA";
                $walker->device_token = $device_token;
                $walker->bio = $bio;
                $walker->is_available = 1;
                $walker->is_active = 0;
                $walker->is_approved = 0;
                $walker->save();
                if (Input::has('type') != NULL) {
                    $ke = Input::get('type');
                    $proviserv = ProviderServices::where('provider_id', $walker->id)->first();
                    if ($proviserv != NULL) {
                        DB::delete("delete from walker_services where provider_id = '" . $walker->id . "';");
                    }
                    $base_price = '0.00';
                    $service_price_distance = '0.00';
                    $service_price_time ='0.00';
                    $cnkey = count(Input::get('type'));
                    for ($i = 1; $i <= $cnkey; $i++) {
                        $key = Input::get('type');
                        $prserv = new ProviderServices;
                        $prserv->provider_id = $walker->id;
                        $prserv->type = $key;
                        //Log::info('key = ' . print_r($key, true));
                        if (Input::has('service_base_price')) {
                            $prserv->base_price = $base_price[$i - 1];
                        } else {
                            $prserv->base_price = 0;
                        }
                        if (Input::has('service_price_distance')) {
                            $prserv->price_per_unit_distance = $service_price_distance[$i - 1];
                        } else {
                            $prserv->price_per_unit_distance = 0;
                        }
                        if (Input::has('service_price_distance')) {
                            $prserv->price_per_unit_time = $service_price_time[$i - 1];
                        } else {
                            $prserv->price_per_unit_distance = 0;
                        }
                        $prserv->save();
                    }
                }
                return Redirect::to('dispatcher/registerdrivers')->with('success', 'You have successfully registered.');
            }
        }
    }

    public function RateTransportationProvider(){
        $request_id     = $_POST['request_id'];
        $tp_id         = $_POST['tp_id'];
        $rating         = $_POST['tp_rating'];
        $feedback       = $_POST['feedback_comment'];

        $user_id = Session::get('user_id');

        Log::info('request input = ' . print_r($request_id, true));
        Log::info('rating input = ' . print_r($rating, true));
        Log::info('feedback input = ' . print_r($feedback, true));
        Log::info('tp_id input = ' . print_r($tp_id, true));

        if($request_id>0 && $tp_id!='' && $rating>0){
            if ($request =RideRequest::find($request_id)) {

                    $tp_rating = new TransportationProviderRating;
                    $tp_rating->request_id = $request_id;
                    $tp_rating->tp_id = $tp_id;
                    $tp_rating->rating = $rating;
                    $tp_rating->dispatcher_id = $user_id;
                    $tp_rating->comment = $feedback;
                    $tp_rating->save();
                    Log::info(' rating= ' . print_r($tp_rating->rating, true));
                    return 1;
            } else{
                return 2;
            }
        }
    }

    public function AddCSVDrivers(){

        $csv = Input::file('csvdriver');

        $validator = Validator::make(
            array(
                'csvdriver' => $csv
            ), array(
            //'csvdriver' => 'required|mimes:csv',
            'csvdriver'=>'required'
        ), array(
                'csvdriver' => 'file is required'
            )
        );

        if ($validator->fails()) {
            $error_messages = $validator->messages();
            Log::info('file type not valid. Error = ' . print_r($error_messages, true));
            return Redirect::to('dispatcher/registerdrivers')->with('error', 'Invalid file format (Allowed format - csv)');
        }else{
            if (Input::hasfile('csvdriver')) {
                Log::info('input has file = ' . print_r(Input::hasfile('csvdriver'), true));
                $ext = Input::file('csvdriver')->getClientOriginalExtension();
                Log::info('input has file ext = ' . print_r($ext, true));
                $tmpName = $_FILES['csvdriver']['tmp_name'];
                Log::info('input has file tmpname = ' . print_r($tmpName, true));

                // check the file is a csv
                if($ext === 'csv'){
                    if(($handle = fopen($tmpName, 'r')) !== FALSE) {
                        // necessary if a large csv file
                        set_time_limit(0);

                        $row = 0;

                        while(($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                            Log::info('datarow = ' . print_r($data[0], true));
                            if($data[0]!="phone"){
                                Log::info('row = ' . print_r($data[0], true));
                                $phone      = $data[0];
                                $email      = $data[1];
                                $contact_name = $data[2];
                                $company    = $data[3];
                                $password   = $data[4];
                                if($email!='' && $phone!='' && $contact_name!='' && $company!='' && $password!=''){
                                    $type = '2';
                                    $device_token = 0;
                                    $bio = "";
                                    Log::info('email = ' . print_r($email, true));
                                    if (Walker::where('email', '=',$email)->first()) {
                                        Log::info('email id already registered  = ' . print_r($email , true));
                                    }else{
                                        $company = strtolower($company);
                                        if (TransportationProvider::where('company','like',$company)->count() > 0) {
                                            Log::info('company ' . print_r($company, true));
                                            $transporter = TransportationProvider::where('company','like',$company)->first();
                                            $company_id = $transporter->id;
                                        } else{
                                            if(Session::get('is_admin') ==1){
                                                $company_id = NULL;
                                            }else{
                                                $user_id = Session::get('user_id');
                                                $dispatcher = Dispatcher::find($user_id);
                                                $company_id = $dispatcher->transportation_provider_id;
                                            }
                                        }
                                        $activation_code = uniqid();

                                        $result = substr($phone, 0, 2);
                                        if ($result != '+1') {
                                            $new_phone_no = "+1" . $phone;
                                        } else {
                                            $new_phone_no = $phone;
                                        }

                                        $walker = new Walker;
                                        $walker->contact_name = $contact_name;
                                        $walker->email = $email;
                                        $walker->phone = $new_phone_no;
                                        $walker->activation_code = $activation_code;
                                        $walker->email_activation = 1;
                                        $walker->transportation_provider_id = $company_id;
                                        if ($password != "") {
                                            $walker->password = Hash::make($password);
                                        }
                                        $walker->token = generate_token();
                                        $walker->token_expiry = generate_expiry();
                                        $walker->type = $type;
                                        $walker->picture = "NA";
                                        $walker->device_token = $device_token;
                                        $walker->bio = $bio;
                                        $walker->is_available = 1;
                                        $walker->is_active = 0;
                                        $walker->is_approved = 0;
                                        $walker->save();
                                        $proviserv = ProviderServices::where('provider_id', $walker->id)->first();
                                        if ($proviserv != NULL) {
                                            DB::delete("delete from walker_services where provider_id = '" . $walker->id . "';");
                                        }

                                        $prserv = new ProviderServices;
                                        $prserv->provider_id = $walker->id;
                                        $prserv->type = $type;
                                        $prserv->base_price = 0;
                                        $prserv->price_per_unit_distance = 0;
                                        $prserv->price_per_unit_distance = 0;
                                        $prserv->save();

                                    }

                                } else{
                                    Log::info('all fields required  = ' . print_r($data, true));
                                }

                            }
                            // inc the row
                            $row++;
                        }

                        fclose($handle);
                        return Redirect::to('dispatcher/registerdrivers')->with('success', 'CSV uploaded.');
                    }
                }
            }
        }
    }

    /*public function UploadDriverCSV(){
        $module_id = Input::get('module_id');
        $csv = Input::file('csvdriver');

        $validator = Validator::make(
            array(
                'csvdriver' => $csv,
                'module_id' => $module_id
            ), array(
            //'csvdriver' => 'required|mimes:csv',
            'module_id'=>'required'
        ), array(
                /* 'picture' => 'mimes:jpeg,bmp,png'
                'csvdriver' => 'file is required'
            )
        );
        if ($validator->fails()) {
            $error_messages = $validator->messages();
            Log::info('file type not valid. Error = ' . print_r($error_messages, true));
            return Redirect::to('dispatcher/trainingmodule/enrolldrivers/'.$module_id)->with('error', 'Invalid file format (Allowed format - csv)');
        }else{
            if (Input::hasfile('csvdriver')) {
                Log::info('input has file = ' . print_r(Input::hasfile('csvdriver'), true));
                $ext = Input::file('csvdriver')->getClientOriginalExtension();
                Log::info('input has file ext = ' . print_r($ext, true));
                $tmpName = $_FILES['csvdriver']['tmp_name'];
                Log::info('input has file tmpname = ' . print_r($tmpName, true));

                // check the file is a csv
                if($ext === 'csv'){
                    if(($handle = fopen($tmpName, 'r')) !== FALSE) {
                        // necessary if a large csv file
                        set_time_limit(0);

                        $row = 0;

                        while(($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                            Log::info('datarow = ' . print_r($data[0], true));
                            if($data[0]!="phone"){

                                $result = substr($data[0], 0, 2);
                                if ($result != '+1') {
                                    $new_phone_no = "+1" . $operator_phone;
                                } else {
                                    $new_phone_no = $operator_phone;
                                }


                                if (Walker::where('phone', $data[0])->count() > 0) {






                                    TrainingSessions::where('module_id','=',$module_id)
                                        ->where('driver_id','=',$driver_id)->first();
                                    $new_session = new TrainingSessions;
                                    $new_session->module_id = $module_id;
                                    $new_session->driver_id = $driver_id;
                                    $new_session->save();
                                }
                            }

                            // inc the row
                            $row++;
                        }

                       // print_r($csv[$row]['col1']);

                        fclose($handle);
                    }
                }
            }
        }

    }*/

    public function AddEditCardDetails(){

        $countarray = array();
        $countarray['submitted'] = $this->SubmittedRidesCount();
        $countarray['confirmed'] = $this->ConfirmedRidesCount();
        $countarray['cancelled'] = $this->CancelledRidesCount();
        $countarray['completed'] = $this->CompletedRidesCount();

        $request_id = Request::segment(3);

        if($request_id>0) {
            $request = RideRequest::where('id', '=', $request_id)->first();

            if($request->dispatcher_assigned_id>0){
                $passenger_id = $request->dispatcher_assigned_id;
                $query = "SELECT * FROM payment WHERE customer_id IS NOT NULL AND dispatcher_assigned_id =".$request->dispatcher_assigned_id;
                $results = DB::select(DB::raw($query));
                Session::put('user_select',1);
            }elseif($request->owner_id>0){
                $passenger_id = $request->owner_id;
                $query = "SELECT * FROM payment WHERE customer_id IS NOT NULL AND owner_id =".$request->owner_id;
                $results = DB::select(DB::raw($query));
                Session::put('user_select',3);
            }
        }


        $title = "Add/Edit Card Details";
        return View::make('dispatcher.addeditcarddetails')
            ->with('title', $title)
            ->with('page', 'carddetails')
            ->with('request_id', $request->id)
            ->with('passenger_id',$passenger_id)
            ->with('countarray',$countarray)
            ->with('results',$results);
    }

    public function SavePassengerCards(){
        $token = $_POST['stripeToken'];
        $cardholdername = $_POST['cardholdername'];
        $cardholderphone = $_POST['cardholderphone'];
        $cardtype = $_POST['cardtype'];
        $last4 = $_POST['last4'];
        $rememberme = $_POST['rememberme'];
        $disp_assign_id = $_POST['passenger_id'];
        $card_id = $_POST['card_id'];
        $newpayment = new PaymentServices();
        $response = $newpayment->saveUserPayment($token, $cardtype, $last4, $cardholdername, $rememberme, $disp_assign_id, $card_id);

        if ($disp_assign_id != '' && ($response == 1 || $response == 4)) {
            if(Session::get('user_select') == 3) {
                $query = "SELECT * FROM payment WHERE customer_id IS NOT NULL AND owner_id =" . $disp_assign_id;
            }else{
                $query = "SELECT * FROM payment WHERE customer_id IS NOT NULL AND dispatcher_assigned_id =" . $disp_assign_id;
            }
//            $results = DB::select(DB::raw($query));
//            foreach ($results as $result) {
//                if ($result->is_default == 1) {
//                    $selected = 'checked';
//                } else {
//                    $selected = '';
//                }
//                echo "<tr>";
//                echo "<td style='padding-bottom: 10px;'><input style='width:100%' type=text name=last_four readonly value='XXXX-XXXX-XXXX-$result->last_four'></td>";
//                echo "<td><input style='width: 50px;vertical-align: -10px;margin-left: 10px;' type=radio id='is_default' name=is_default checked=$selected value='$result->id' onclick=changedefault('$result->id');>Active</td>";
//                echo "</tr>";
//            }
//                echo "<input type='hidden' name='passengerid' id='passengerid' value='$disp_assign_id'>";
//        } elseif ($response['error'] != '') {
//            return $response;
//        } else {
//            echo "<tr>";
//            echo "<td style='padding-bottom: 10px;'><input style='width:100%' type=text name=last_four readonly value='XXXX-XXXX-XXXX-$last4'></td>";
//            echo "<td><input style='width: 50px;' type=radio id='is_default' name=is_default checked='checked' value='1'>Active</td>";
//            echo "</tr>";
        }
        //return $response;
    }

    public function getCardLink(){
        $request_id = $_POST['request_id'];
        echo "<label class='col-md-12 control-label' style='text-align: center;' for='form_control_1'>";
        echo "<a href='/dispatcher/addeditcarddetails/$request_id'><span style='font-size: 18px;text-align: center;text-align: center;'>Click here to add Credit card details</span></a>";
        echo "</label><div class='form-control-focus'></div>";
    }

}