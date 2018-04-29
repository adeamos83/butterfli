/<?php
class EnterpriseClientController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     *
     * @return Response
     */
    public $status = 0;
    private $_api_context;
    private function get_timezone_offset($remote_tz, $origin_tz = null)
    {
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
    public function __construct()
    {
        if (Config::get('app.production')) {
            echo "Something cool is going to be here soon.";
            die();
        }
        $this->beforeFilter(function () {
            if (!Session::has('user_id')) {
//                $url = URL::current();
//                Session::put('pre_login_url', $url);
                return Redirect::to('/booking/signin');
            }
        }, array('except' => array(
            'userLogin',
            'userVerify',
            'CheckUserbyOTP',
            'ResendOTP',
            'healthcareForgotPassword',
            'userRegister',
            'userSave',
            'surroundingCars',
        )));
        $date = date("Y-m-d H:i:s");
    }
    public function index()
    {
        return Redirect::to('/booking/signin');
    }
    public function userLogin()
    {
        return View::make('enterpriseclient.userLogin');
    }
    public function userRegister()
    {
        return View::make('enterpriseclient.userSignup');
    }
    public function userSave()
    {
        if(Input::get('user_select') == 1) {
            if (Input::get('agent_select') != 2) {
                $contact_name = Input::get('contact_name');
                $email = Input::get('email');
                $password = Input::get('password');
                $companyname = Input::get('company_name');
                $operator_phone = Input::get('operator_phone');
                if (Input::hasFile('companylogo') && Config::get('app.s3_bucket') != "") {
                    $file_name = time();
                    $ext = Input::file('companylogo')->getClientOriginalExtension();
                    $local_url = $file_name . "." . $ext;
                    Input::file('companylogo')->move(public_path('image') . "/uploads", $file_name . "." . $ext);
                    $s3 = App::make('aws')->get('s3');
                    $pic = $s3->putObject(array(
                        'Bucket' => Config::get('app.s3_bucket'),
                        'Key' => "uploads/booking/" . $file_name,
                        'SourceFile' => public_path('image') . "/uploads/" . $local_url,
                    ));
                    $s3->putObjectAcl(array(
                        'Bucket' => Config::get('app.s3_bucket'),
                        'Key' => "uploads/booking/" . $file_name,
                        'ACL' => 'public-read'
                    ));
                    $final_file_name = "uploads/booking/" . $file_name;
                    $s3_url = $s3->getObjectUrl(Config::get('app.s3_bucket'), $final_file_name);
                    $localfile = public_path('image') . "/uploads/" . $local_url;
                    unlink_image($localfile);
                } else {
                    $s3_url = '';
                }
                $validator = Validator::make(
                    array(
                        'contact_name' => $contact_name,
                        'email' => $email,
                        'password' => $password,
                        'company_name' => $companyname,
                        'operator_phone' => $operator_phone,
                    ), array(
                    'password' => 'required',
                    'email' => 'required',
                    'contact_name' => 'required',
                    'company_name' => 'required',
                    'operator_phone' => 'required',
                ), array(
                        'password' => 'Password field is required.',
                        'email' => 'Email field is required',
                        'contact_name' => 'Contact Name field is required.',
                        'company_name' => 'Company Name is required.',
                        'operator_phone' => 'Operator Phone is required.',
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
                $validator2 = Validator::make(
                    array(
                        'operator_phone' => $operator_phone,
                    ), array(
                    'operator_phone' => 'required|numeric'
                ), array(
                        'operator_phone' => 'Operator Phone is required'
                    )
                );
                if ($validator->fails()) {
                    $error_messages = $validator->messages()->first();
                    return Redirect::to('booking/signup')->with('error', $error_messages . '\n\nPlease fill all the fields.');
                } else if ($validator1->fails()) {
                    return Redirect::to('booking/signup')->with('error', 'Please Enter email correctly.');
                } else if ($validator2->fails()) {
                    $error_messages = $validator2->messages();
                    Log::info('Error = ' . print_r($error_messages, true));
                    return Redirect::to('booking/signup')->with('error', 'Please Enter phone correctly.');
                } else {
                    if (EnterpriseClient::where('email', $email)->count() == 0) {
						$company  = strtolower($companyname);
						if(EnterpriseClient::where('company', 'LIKE', $company)->count() == 0){
							$result = substr($operator_phone, 0, 2);
							if ($result != '+1') {
								$new_phone_no = "+1" . $operator_phone;
							} else {
								$new_phone_no = $operator_phone;
							}
							$Healthcare = new EnterpriseClient;
							$Healthcare->contact_name = $contact_name;
							$Healthcare->email = $email;
							$Healthcare->company = $companyname;
							$Healthcare->token = generate_token();
							$Healthcare->companylogo = $s3_url;
							if ($password != "") {
								$Healthcare->password = Hash::make($password);
							}
							$Healthcare->operator_email = $email;
							$Healthcare->operator_phone = $new_phone_no;
							$Healthcare->save();
							//add data in hospital providers
							$hospital = new HospitalProviders;
							$hospital->healthcare_id = $Healthcare->id;
							$hospital->provider_name = $Healthcare->company;
							$hospital->is_active = 1;
							$hospital->save();
							$settings = Settings::where('key', 'admin_email_address')->first();
							$admin_email = $settings->value;
							$follow_url = web_url() . "/booking/signin";
							$pattern = array('admin_email' => $admin_email, 'name' => ucwords($Healthcare->contact_name),
								'web_url' => web_url(), 'follow_url' => $follow_url);
							$subject = "Welcome to " . ucwords(Config::get('app.website_title')) . ", " . ucwords($Healthcare->contact_name) . "";
							email_notification($Healthcare->id, 'healthcare', $pattern, $subject, 'user_register', null);
							$follow_url = web_url() . "/admin/enterpriseclient/profile/" . $Healthcare->id;
							$pattern1 = array('username' => ucwords($Healthcare->contact_name),
								'email' => $Healthcare->email, 'company' => $Healthcare->company, 'follow_url' => $follow_url,
								'web_url' => web_url(), 'provider' => 'Healthcare Provider');
							$subject = "New Enterprise Client";
							email_notification($Healthcare->id, 'admin', $pattern1, $subject, 'user_register_mail_to_admin', null);
							return Redirect::to('booking/signin')->with('success', 'You have successfully registered. <br>Please Wait for Admin Approval');
						}else{
							return Redirect::to('booking/signup')->with('error', 'Company name already exists.');
						}
                    } else {
                        return Redirect::to('booking/signup')->with('error', 'This email ID is already registered.');
                    }
                }
            } else {
                $contact_name = Input::get('contact_name');
                $email = Input::get('email');
                $password = Input::get('password');
                $company_name = Input::get('company_name');
                $agent_phone = Input::get('operator_phone');
                $validator = Validator::make(
                    array(
                        'contact_name' => $contact_name,
                        'email' => $email,
                        'password' => $password,
                        'company_name' => $company_name,
                        'operator_phone' => $agent_phone,
                    ), array(
                    'password' => 'required',
                    'email' => 'required',
                    'contact_name' => 'required',
                    'company_name' => 'required',
                    'operator_phone' => 'required',
                ), array(
                        'password' => 'Password field is required.',
                        'email' => 'Email field is required',
                        'contact_name' => 'Contact Name field is required.',
                        'company_name' => 'Company Name is required.',
                        'operator_phone' => 'Operator Phone is required.',
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
                $validator2 = Validator::make(
                    array(
                        'operator_phone' => $agent_phone,
                    ), array(
                    'operator_phone' => 'required|numeric'
                ), array(
                        'operator_phone' => 'Operator Phone is required'
                    )
                );
                if ($validator->fails()) {
                    $error_messages = $validator->messages()->first();
                    return Redirect::to('booking/signup')->with('error', 'Please fill all the fields.');
                } else if ($validator1->fails()) {
                    return Redirect::to('booking/signup')->with('error', 'Please Enter email correctly.');
                } else if ($validator2->fails()) {
                    $error_messages = $validator2->messages();
                    Log::info('Error = ' . print_r($error_messages, true));
                    return Redirect::to('booking/signup')->with('error', 'Please Enter phone correctly.');
                } else {
                    $dispatcher_count = DispatcherAgent::where('email', $email)->count();
                    if ($dispatcher_count == 0) {
                        $result = substr($agent_phone, 0, 2);
                        if ($result != '+1') {
                            $new_phone_no = "+1" . $agent_phone;
                        } else {
                            $new_phone_no = $agent_phone;
                        }
                        $Agent = new DispatcherAgent();
                        $Agent->contact_name = $contact_name;
                        $Agent->email = $email;
                        $Agent->healthcare_id = $company_name;
                        if ($password != "") {
                            $Agent->password = Hash::make($password);
                        }
                        $Agent->phone = $new_phone_no;
                        $Agent->save();
                        $id = $Agent->healthcare_id;
                        $HealthCare = EnterpriseClient::find($id);
                        $settings = Settings::where('key', 'admin_email_address')->first();
                        $admin_email = $settings->value;
                        $follow_url = web_url() . "/booking/signin";
                        $pattern = array('admin_email' => $admin_email, 'name' => ucwords($Agent->contact_name),
                            'web_url' => web_url(), 'follow_url' => $follow_url);
                        $subject = "Welcome to " . ucwords(Config::get('app.website_title')) . ", " . ucwords($Agent->contact_name) . "";
                        email_notification($Agent->id, 'agent', $pattern, $subject, 'user_register', null);
                        $pattern1 = array('username' => ucwords($Agent->contact_name),
                            'email' => $Agent->email, 'company' => $HealthCare->company, 'follow_url' => $follow_url,
                            'web_url' => web_url());
                        $subject = "New Enterprise Client";
                        email_notification($Agent->id, 'admin', $pattern1, $subject, 'user_register_mail_to_admin_agent', null);
                        return Redirect::to('booking/signin')->with('success', 'You have successfully registered. <br>Please Wait for Admin Approval');
                    } else {
                        return Redirect::to('booking/signup')->with('error', 'This email ID is already registered.');
                    }
                }
            }
        } else{
            $contact_name = Input::get('contact_name');
            $email = Input::get('email');
            $password = Input::get('password');
            $user_phone = Input::get('operator_phone');

            $validator = Validator::make(
                array(
                    'contact_name' => $contact_name,
                    'email' => $email,
                    'password' => $password,
                    'operator_phone' => $user_phone,
                ), array(
                'password' => 'required',
                'email' => 'required',
                'contact_name' => 'required',
                'operator_phone' => 'required',
            ), array(
                    'password' => 'Password field is required.',
                    'email' => 'Email field is required',
                    'contact_name' => 'Contact Name field is required.',
                    'operator_phone' => 'Phone is required.',
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
            $validator2 = Validator::make(
                array(
                    'operator_phone' => $user_phone,
                ), array(
                'operator_phone' => 'required|numeric'
            ), array(
                    'operator_phone' => 'Phone is required'
                )
            );
            if ($validator->fails()) {
                $error_messages = $validator->messages()->first();
                return Redirect::to('booking/signup')->with('error', 'Please fill all the fields.');
            } else if ($validator1->fails()) {
                return Redirect::to('booking/signup')->with('error', 'Please Enter email correctly.');
            } else if ($validator2->fails()) {
                $error_messages = $validator2->messages();
                Log::info('Error = ' . print_r($error_messages, true));
                return Redirect::to('booking/signup')->with('error', 'Please Enter phone correctly.');
            } else {
                if (Owner::where('email', '=', $email)->first()) {
                    return Redirect::to('booking/signup')->with('error', 'This email ID is already registered.');
                } else {
                    $result = substr($user_phone, 0, 2);
                    if ($result != '+1') {
                        $new_phone_no = "+1" . $user_phone;
                    } else {
                        $new_phone_no = $user_phone;
                    }
                    $device_token = 0;
                    $owner = new Owner;
                    $owner->contact_name = $contact_name;
                    $owner->email = $email;
                    $owner->phone = $new_phone_no;
                    if ($password != "") {
                        $owner->password = Hash::make($password);
                    }
                    $owner->token = generate_token();
                    $owner->token_expiry = generate_expiry();
                    $owner->device_token = $device_token;
                    $owner->bio = "";
                    $owner->address = "";
                    $owner->state = "";
                    $owner->login_by = "";
                    $owner->country = "";
                    $owner->zipcode = "0";
                    $owner->timezone = 'UTC';
                    $owner->is_referee = 0;
                    $owner->promo_count = 0;
                    $owner->save();

                    regenerate:
                    $referral_code = my_random6_number();
                    if (Ledger::where('referral_code', $referral_code)->count()) {
                        goto regenerate;
                    }
                    /* Referral entry */
                    $ledger = new Ledger;
                    $ledger->owner_id = $owner->id;
                    $ledger->referral_code = $referral_code;
                    $ledger->save();

                    $settings = Settings::where('key', 'admin_email_address')->first();
                    $admin_email = $settings->value;
                    $pattern = array('admin_email' => $admin_email, 'name' => ucwords($owner->contact_name), 'web_url' => web_url());
                    $subject = "Welcome to " . ucwords(Config::get('app.website_title')) . ", " . ucwords($owner->contact_name) . "";
                    email_notification($owner->id, 'owner', $pattern, $subject, 'user_register', null);
                    return Redirect::to('booking/signin')->with('success', 'You have successfully registered.');
                }
            }
        }
    }
    public function userVerify()
    {
        $email = Input::get('email');
        $password = Input::get('password');
        Log::info('userverify function = ' . print_r($email, true));
        Log::info('userverify function password = ' . print_r($password, true));
        if (Input::get('user_select') == 1) {
            if(Input::get('agent_select') == 2) {
                Log::info('agent elsepart = ' . print_r($email, true));
                $agent = DispatcherAgent::where('email', '=', $email)->first();
                if ($agent && Hash::check($password, $agent->password)) {
                    if ($agent->is_active == 0) {
                        //return Redirect::to('booking/signin')->with('error', 'Your Account is pending approval.');
                        return 2;
                    } else {
                        /*Session::put('user_id', $agent->id);
                        Session::put('is_agent', '1');
                        $id = $agent->healthcare_id;
                        $Healthcare = EnterpriseClient::where('id', '=', $id)->first();
                        Session::put('username', $agent->contact_name);
                        Session::put('user_pic', $Healthcare->companylogo);
                        Session::put('healthcare_email', $Healthcare->email);
                        Session::put('healthcare_company', $Healthcare->company);
                        if (Session::has('pre_login_url')) {
                            $url = Session::get('pre_login_url');
                            Session::forget('pre_login_url');
                            return Redirect::to($url);
                        } else {
                            return Redirect::to('/booking/myrides');
                        }*/

                        Log::info('booking agent = ' . print_r($agent->email, true));
                        $otp_object = UserMultiFactorAuthentication::where('email', '=', $email)->where('user_type', '=', '6')->first();
                        Log::info('otp_object = ' . print_r($otp_object, true));
                        if ($otp_object == null) {
                            $new_otp = mt_rand(100000, 999999);
                            //Generate OTP and save it to database and send email and sms to user
                            $generate_otp = new UserMultiFactorAuthentication();
                            $generate_otp->email = $agent->email;
                            $generate_otp->user_type = 6; //1=>dispatcher, 2=>EnterpriseClient 3=>walker 4=>owner 5=>consumer 6=>healtchare agent
                            $generate_otp->OTP = $new_otp;
                            $generate_otp->otp_expiry_time = strtotime("+15 minutes", time());
                            $generate_otp->created_at = date("Y-m-d H:i:s");
                            $generate_otp->updated_at = date("Y-m-d H:i:s");
                            $generate_otp->save();
                            Log::info('new otp1 = ' . print_r($new_otp, true));
                        } else {
                            if (time() > $otp_object->otp_expiry_time) {
                                $new_otp = mt_rand(100000, 999999);
                                Log::info('new otp2 = ' . print_r($new_otp, true));
                                UserMultiFactorAuthentication::where('id', '=', $otp_object->id)->update(
                                    array('OTP' => $new_otp, 'otp_expiry_time' => strtotime("+15 minutes", time()), 'updated_at' => date('Y-m-d H:i:s')));
                            } else {
                                $new_otp = $otp_object->OTP;
                                Log::info('old otp1 = ' . print_r($new_otp, true));
                                UserMultiFactorAuthentication::where('id', '=', $otp_object->id)->update(
                                    array('updated_at' => date('Y-m-d H:i:s')));
                            }
                        }

                        $settings = Settings::where('key', 'admin_email_address')->first();
                        $admin_email = $settings->value;

                        //sending email to user for new otp
                        $pattern1 = array('admin_email' => $admin_email, 'otp' => $new_otp, 'web_url' => web_url(), 'otp_text' => 'You have requested a new OTP for login. Please find the OTP. It is valid for 15 minutes only.');
                        $subject = "OTP Requested.";
                        email_notification($agent->id, 'agent', $pattern1, $subject, 'new_otp_mail', null);
                        if ($agent->phone != null) {
                            $pattern2 = "You have requested a new OTP for login. Please find the OTP . It is valid for 15 minutes only. OTP is $new_otp";
                            sms_notification($agent->id, 'healthcare_agent', $pattern2);
                        }
                        return $agent->email;
                    }
                } else {
                    //return Redirect::to('booking/signin')->with('error', 'Invalid email and password');
                    return 3;
                }
            } else {
                $Healthcare = EnterpriseClient::where('email', '=', $email)->first();
                if ($Healthcare && Hash::check($password, $Healthcare->password)) {
                    if ($Healthcare->is_active == 0) {
                        return 2;
                    } else {

                        Log::info('elsepart = ' . print_r($email, true));
                        Log::info('Healthcare = ' . print_r($Healthcare->email, true));
                        $otp_object = UserMultiFactorAuthentication::where('email', '=', $email)->where('user_type', '=', '2')->first();
                        Log::info('otp_object = ' . print_r($otp_object, true));
                        if ($otp_object == null) {
                            $new_otp = mt_rand(100000, 999999);
                            //Generate OTP and save it to database and send email and sms to user
                            $generate_otp = new UserMultiFactorAuthentication();
                            $generate_otp->email = $Healthcare->email;
                            $generate_otp->user_type = 2; //1=>dispatcher, 2=>EnterpriseClient 3=>walker 4=>owner 5=>consumer 6=>healtchare agent
                            $generate_otp->OTP = $new_otp;
                            $generate_otp->otp_expiry_time = strtotime("+15 minutes", time());
                            $generate_otp->created_at = date("Y-m-d H:i:s");
                            $generate_otp->updated_at = date("Y-m-d H:i:s");
                            $generate_otp->save();
                            Log::info('new otp1 = ' . print_r($new_otp, true));
                        } else {
                            if (time() > $otp_object->otp_expiry_time) {
                                $new_otp = mt_rand(100000, 999999);
                                Log::info('new otp2 = ' . print_r($new_otp, true));
                                UserMultiFactorAuthentication::where('id', '=', $otp_object->id)->update(
                                    array('OTP' => $new_otp, 'otp_expiry_time' => strtotime("+15 minutes", time()), 'updated_at' => date('Y-m-d H:i:s')));
                            } else {
                                $new_otp = $otp_object->OTP;
                                Log::info('old otp1 = ' . print_r($new_otp, true));
                                UserMultiFactorAuthentication::where('id', '=', $otp_object->id)->update(
                                    array('updated_at' => date('Y-m-d H:i:s')));
                            }
                        }

                        $settings = Settings::where('key', 'admin_email_address')->first();
                        $admin_email = $settings->value;

                        //sending email to user for new otp
                        $pattern1 = array('admin_email' => $admin_email, 'otp' => $new_otp, 'web_url' => web_url(), 'otp_text' => 'You have requested a new OTP for login. Please find the OTP. It is valid for 15 minutes only.');
                        $subject = "OTP Requested.";
                        email_notification($Healthcare->id, 'healthcare', $pattern1, $subject, 'new_otp_mail', null);
                        if ($Healthcare->operator_phone != null) {
                            $pattern2 = "You have requested a new OTP for login. Please find the OTP . It is valid for 15 minutes only. OTP is $new_otp";
                            sms_notification($Healthcare->id, 'healthcare', $pattern2);
                        }
                        return $Healthcare->email;
                    }
                } else {
                    //return Redirect::to('booking/signin')->with('error', 'Invalid email and password');
                    return 3;
                }
            }
        } else{
            Log::info('user part = ' . print_r($email, true));
            $owner = Owner::where('email', '=', $email)->first();
            if ($owner && Hash::check($password, $owner->password)) {

                Log::info('user = ' . print_r($owner->email, true));
                $otp_object = UserMultiFactorAuthentication::where('email','=',$email)->where('user_type','=','4')->first();
                Log::info('otp_object = ' . print_r($otp_object, true));
                if($otp_object==null){
                    $new_otp = mt_rand(100000,999999);
                    //Generate OTP and save it to database and send email and sms to user
                    $generate_otp = new UserMultiFactorAuthentication();
                    $generate_otp->email = $owner->email;
                    $generate_otp->user_type = 4; //1=>dispatcher, 2=>EnterpriseClient 3=>walker 4=>owner 5=>consumer 6=>healtchare agent
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
                email_notification($owner->id, 'owner', $pattern1, $subject, 'new_otp_mail', null);
                if($owner->phone!=null){
                    $pattern2 = "You have requested a new OTP for login. Please find the OTP . It is valid for 15 minutes only. OTP is $new_otp";
                    sms_notification($owner->id, 'owner', $pattern2);
                }
                return $owner->email;
            }else {
                //return Redirect::to('booking/signin')->with('error', 'Invalid email and password');
                return 3;
            }
        }
    }


    public function CheckUserbyOTP(){
        $email = Input::get('email');
        $otp = Input::get('otp');

        if (Input::get('user_select') == 1) {
            if(Input::get('agent_select') == 2){
                Log::info('check otp1 = ' . print_r(Input::get('user_select'), true));
                $otp_object = UserMultiFactorAuthentication::where('email', '=', $email)->where('user_type', '=', '6')
                    ->where('OTP', '=', $otp)->first();

                if ($otp_object != null && ($otp_object->otp_expiry_time >= time())) {
                    $agent = DispatcherAgent::where('email', '=', $email)->first();
                    Session::put('user_id', $agent->id);
                    Session::put('is_agent', '1');
                    $id = $agent->healthcare_id;
                    $Healthcare = EnterpriseClient::where('id', '=', $id)->first();
                    Session::put('username', $agent->contact_name);
                    Session::put('user_pic', $Healthcare->companylogo);
                    Session::put('healthcare_email', $Healthcare->email);
                    Session::put('healthcare_company', $Healthcare->company);
                    Session::put('user_select',Input::get('user_select'));
                    $url = 'booking/myrides';
                    return $url;
                } else {
                    return 1;
                }
            }else{
                $otp_object = UserMultiFactorAuthentication::where('email', '=', $email)->where('user_type', '=', '2')
                    ->where('OTP', '=', $otp)->first();
                Log::info('check otp0 = ' . print_r(Input::get('user_select'), true));
                if ($otp_object != null && ($otp_object->otp_expiry_time >= time())) {
                    $Healthcare = EnterpriseClient::where('email', '=', $email)->first();
                    Session::put('user_id', $Healthcare->id);
                    Session::put('is_agent', '0');
                    Session::put('username', $Healthcare->contact_name);
                    Session::put('user_pic', $Healthcare->companylogo);
                    Session::put('healthcare_email', $Healthcare->email);
                    Session::put('healthcare_company', $Healthcare->company);
                    Session::put('user_select',Input::get('user_select'));
                    $url = 'booking/myrides';
                    return $url;
                } else {
                    return 1;
                }
            }
        } else {
            Log::info('check otp1 = ' . print_r(Input::get('user_select'), true));
            $otp_object = UserMultiFactorAuthentication::where('email', '=', $email)->where('user_type', '=', '4')
                ->where('OTP', '=', $otp)->first();

            if ($otp_object != null && ($otp_object->otp_expiry_time >= time())) {
                $owner = Owner::where('email', '=', $email)->first();
                Session::put('user_id', $owner->id);
                Session::put('username', $owner->contact_name);
                Session::put('email', $owner->email);
                Session::put('user_select',Input::get('user_select'));
                Session::put('is_agent', '0');
                $url = 'booking/myrides';
                return $url;
            } else {
                return 1;
            }
        }
    }

    public function ResendOTP(){
        $email = Input::get('email');
        if (Input::get('user_select') == 1) {
            if(Input::get('agent_select') == 2){
                $agent = DispatcherAgent::where('email', '=', $email)->first();
                $otp_object = UserMultiFactorAuthentication::where('email', '=', $email)->where('user_type', '=', '6')->first();
                if($otp_object==null){
                    $new_otp = mt_rand(100000,999999);
                    //Generate OTP and save it to database and send email and sms to user
                    $generate_otp = new UserMultiFactorAuthentication();
                    $generate_otp->email = $agent->email;
                    $generate_otp->user_type = 6; //1=>dispatcher, 2=>EnterpriseClient 3=>walker 4=>owner 5=>consumer 6=>healtchare agent
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
                email_notification($agent->id, 'agent', $pattern1, $subject, 'new_otp_mail', null);
                if($agent->phone!=null){
                    $pattern2 = "You have requested a new OTP for login. Please find the OTP . It is valid for 15 minutes only. OTP is $new_otp";
                    sms_notification($agent->id, 'healthcare_agent', $pattern2);
                }
                return $agent->email;
            }else{
                $EnterpriseClient = EnterpriseClient::where('email', '=', $email)->first();
                $otp_object = UserMultiFactorAuthentication::where('email', '=', $email)->where('user_type', '=', '2')->first();
                if ($otp_object == null) {
                    $new_otp = mt_rand(100000, 999999);
                    //Generate OTP and save it to database and send email and sms to user
                    $generate_otp = new UserMultiFactorAuthentication();
                    $generate_otp->email = $EnterpriseClient->email;
                    $generate_otp->user_type = 2; //1=>dispatcher, 2=>EnterpriseClient 3=>walker 4=>owner 5=>consumer
                    $generate_otp->OTP = $new_otp;
                    $generate_otp->otp_expiry_time = strtotime("+15 minutes", time());
                    $generate_otp->created_at = date("Y-m-d H:i:s");
                    $generate_otp->updated_at = date("Y-m-d H:i:s");
                    $generate_otp->save();
                } else {
                    if (time() > $otp_object->otp_expiry_time) {
                        $new_otp = mt_rand(100000, 999999);
                        UserMultiFactorAuthentication::where('id', '=', $otp_object->id)->update(
                            array('OTP' => $new_otp, 'otp_expiry_time' => strtotime("+15 minutes", time()), 'updated_at' => date('Y-m-d H:i:s')));
                    } else {
                        $new_otp = $otp_object->OTP;
                        UserMultiFactorAuthentication::where('id', '=', $otp_object->id)->update(
                            array('updated_at' => date('Y-m-d H:i:s')));
                    }
                }

                $settings = Settings::where('key', 'admin_email_address')->first();
                $admin_email = $settings->value;

                //sending email to user for new otp
                $pattern1 = array('admin_email' => $admin_email, 'otp' => $new_otp, 'web_url' => web_url(), 'otp_text' => 'You have requested a new OTP for login. Please find the OTP. It is valid for 15 minutes only.');
                $subject = "OTP Requested.";
                email_notification($EnterpriseClient->id, 'healthcare', $pattern1, $subject, 'new_otp_mail', null);
                if ($EnterpriseClient->phone != null) {
                    $pattern2 = "You have requested a new OTP for login. Please find the OTP . It is valid for 15 minutes only. OTP is $new_otp";
                    sms_notification($EnterpriseClient->id, 'healthcare', $pattern2);
                }
                return $EnterpriseClient->email;
            }
        } else{
            $owner = Owner::where('email', '=', $email)->first();
            $otp_object = UserMultiFactorAuthentication::where('email', '=', $email)->where('user_type', '=', '4')->first();
            if($otp_object==null){
                $new_otp = mt_rand(100000,999999);
                //Generate OTP and save it to database and send email and sms to user
                $generate_otp = new UserMultiFactorAuthentication();
                $generate_otp->email = $owner->email;
                $generate_otp->user_type = 4; //1=>dispatcher, 2=>EnterpriseClient 3=>walker 4=>owner 5=>consumer 6=>healtchare agent
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
            email_notification($owner->id, 'owner', $pattern1, $subject, 'new_otp_mail', null);
            if($owner->phone!=null){
                $pattern2 = "You have requested a new OTP for login. Please find the OTP . It is valid for 15 minutes only. OTP is $new_otp";
                sms_notification($owner->id, 'owner', $pattern2);
            }
            return $owner->email;
        }
    }
    public function userLogout()
    {
        Session::flush();
        return Redirect::to('/booking/signin');
    }
    public function myservices()
    {

        $is_agent = Session::get('is_agent');

        if ($is_agent == 1) {
            $agent_id = Session::get('user_id');
            $healthcare_agent = DispatcherAgent::find($agent_id);
            $user_id = $healthcare_agent->healthcare_id;
            $provider = EnterpriseClient::find($user_id);
        } else {
            $user_id = Session::get('user_id');
            if(Session::get('user_select') ==1){
                $provider = EnterpriseClient::find($user_id);
            }else{
                $provider = Owner::find($user_id);
            }
        }

        $ratecount = RateProfile::where('enterpriseclient_id', '=', $provider->id)->count();
        $fundingcount = FundingProfile::where('enterpriseclient_id', '=', $provider->id)->count();
        if($ratecount == 0 || $fundingcount == 0) {
            return View::make('enterpriseclient.needsprovisioning')
                ->with('title', 'Account Setup is incomplete');
        }

        if(Session::get('user_select') == 3){
            $walks = DB::table('request')
                ->leftJoin('ride_details', 'request.id','=','ride_details.request_id')
                ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                ->leftJoin('walker_type', 'walker_type.id', '=', 'request.service_type')
                ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
                ->leftJoin('healthcare_documents','healthcare_documents.request_id','=','request.id')
                ->leftJoin('hospital_providers', 'hospital_providers.id', '=', 'request.hospital_provider_id')
                ->leftJoin('review_walker', 'review_walker.request_id', '=', 'request.id')
                ->select('owner.contact_name as owner_contact_name', 
                    'owner.phone as user_phone_no', 'request.id as id', 'request.request_start_time as date',
                    'request.payment_mode', 'request.is_started', 'request.is_walker_arrived',
                    'request.payment_mode', 'request.is_completed', 'request.is_paid',
                    'request.is_walker_started', 'request.status', 'request.time', 'request.distance',
                    'request.total', 'request.is_cancelled','walker.id as walker_id','request.confirmed_walker',
                    'request.transfer_amount', 'walker_type.name', 'request.total as total_service_amount',
                    'request.promo_payment', 'request.additional_fee', 'hospital_providers.provider_name',
                    'walker.contact_name as walker_contact_name', 
                    'walker.phone as walker_phone', 'request.is_confirmed',
                    'ride_details.agent_contact_name','healthcare_documents.document_url',
                    'request.estimated_time','request.src_address','request.dest_address',
                    'ride_details.oxygen_mask as oxygen_mask',
                    'ride_details.height as user_height',
                    'ride_details.weight as user_weight',
                    'ride_details.condition as user_condition',
                    'ride_details.respirator as respirator',
                    'ride_details.any_tubing as any_tubing',
                    'ride_details.colostomy_bag as colostomy_bag',
                    'ride_details.any_attachments as any_attachments',
                    'request.attendant_travelling as attendant_travelling',
                    'request.is_manual','request.driver_name','request.driver_phone',
                    'review_walker.rating','review_walker.comment')
                ->orderBy('request.id', 'DESC')
                ->where('request.owner_id', '=', $user_id)
                ->paginate(10);
        } else{
            $walks = DB::table('request')
                ->leftJoin('ride_details', 'request.id','=','ride_details.request_id')
                ->leftJoin('dispatcher_assigned', 'request.dispatcher_assigned_id', '=', 'dispatcher_assigned.id')
                ->leftJoin('walker_type', 'walker_type.id', '=', 'request.service_type')
                ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
                ->leftJoin('healthcare_documents','healthcare_documents.request_id','=','request.id')
                ->leftJoin('hospital_providers', 'hospital_providers.id', '=', 'request.hospital_provider_id')
                ->leftJoin('review_walker', 'review_walker.request_id', '=', 'request.id')
                ->select('dispatcher_assigned.contact_name as owner_contact_name',
                    'dispatcher_assigned.phone as user_phone_no', 'request.id as id', 'request.request_start_time as date',
                    'request.payment_mode', 'request.is_started', 'request.is_walker_arrived',
                    'request.payment_mode', 'request.is_completed', 'request.is_paid',
                    'request.is_walker_started', 'request.status', 'request.time', 'request.distance',
                    'request.total', 'request.is_cancelled','walker.id as walker_id','request.confirmed_walker',
                    'request.transfer_amount', 'walker_type.name', 'request.total as total_service_amount',
                    'request.promo_payment', 'request.additional_fee', 'hospital_providers.provider_name',
                    'walker.contact_name as walker_contact_name', 
                    'walker.phone as walker_phone', 'request.is_confirmed',
                    'ride_details.agent_contact_name','healthcare_documents.document_url',
                    'request.estimated_time','request.src_address','request.dest_address',
                    'ride_details.oxygen_mask as oxygen_mask',
                    'ride_details.height as user_height',
                    'ride_details.weight as user_weight',
                    'ride_details.condition as user_condition',
                    'ride_details.respirator as respirator',
                    'ride_details.any_tubing as any_tubing',
                    'ride_details.colostomy_bag as colostomy_bag',
                    'ride_details.any_attachments as any_attachments',
                    'request.attendant_travelling as attendant_travelling',
                    'request.is_manual','request.driver_name','request.driver_phone',
                    'review_walker.rating','review_walker.comment')
                ->orderBy('request.id', 'DESC')
                ->where('request.healthcare_id', '=', $user_id)
                ->paginate(10);
        }

        $title = 'My Rides';
        return View::make('enterpriseclient.myservice')
            ->with('title', $title)
            ->with('page', 'walks')
            ->with('walks', $walks)
            ->with('enterprise_client', $provider);
    }
    public function servicerequest()
    {
        $owner_id = 0;  // default owner_id
        $is_agent = Session::get('is_agent');
        if ($is_agent == 1) {
            $agent_id = Session::get('user_id');
            $EnterpriseClient = DispatcherAgent::find($agent_id);
            $provider_id = $EnterpriseClient->healthcare_id;
        } else {
            if(Session::get('user_select') == 1){
                $provider_id  = Session::get('user_id');
            } else{
                $owner_id = Session::get('user_id');
            }
        }


        $query = "SELECT * FROM walker_type WHERE is_visible=1 AND id!='1'";
        $services = DB::select(DB::raw($query));
        if (Session::get('user_select') ==1) {
            $hospital_data = "SELECT * FROM hospital_providers WHERE healthcare_id=" . $provider_id;
            $hospital_provider = DB::select(DB::raw($hospital_data));
        } else{
            $hospital_provider = '';
        }
        $paymentflag = '0';
        return View::make('enterpriseclient.requestservice')
            ->with('title', 'Request Ride')
            ->with('page', 'Request service')
            ->with('services', $services)
            ->with('paymentflag', $paymentflag)
            ->with('hospital_provider', $hospital_provider)
            ->with('is_agent', $is_agent)
            ->with('user_select',Session::get('user_select'))
            ->with('user_id',$owner_id);
    }
    public function getdrivers()
    {
        $address = $_POST['address'];
        $prepAddr = str_replace(' ', '+', $address);
        $geocode = file_get_contents($this->getGeocodeURL($prepAddr));
        $output = json_decode($geocode);
        $latitude = $output->results[0]->geometry->location->lat;
        $longitude = $output->results[0]->geometry->location->lng;
        $passengerlatitude = $latitude . '00';
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
        foreach ($results as $result) {
            echo "<tr>";
            echo "<td>$result->contact_name</td>";
            echo "<td>$result->company</td>";
            $type = DB::select(DB::raw("SELECT `name`,`id` FROM `walker_type` WHERE `id`=" . $result->type));
            $passengertype = $type[0]->name;
            $passengertypeid = $type[0]->id;
            echo "<td>$passengertype</td>";
            echo "<td><input type=radio id=assignride name=assignride value='$result->id'>
							   <input type=hidden value=$passengertypeid name=type>
							   <input type=hidden value=$latitude id=lat>
							   <input type=hidden value=$longitude id=long>
							   </td>";
            echo "</tr>";
        }
    }
    public function saveuserrequest()
    {
        $payment_type = Input::get('payment_type');
        //
        // determine request owner
        //
        $owner_id = 0;
        if(Input::get('owner_id') != null) {
            $owner_id = Input::get('owner_id');
        }

        if($owner_id != 0){
            $is_agent = Session::get('is_agent');
            $onwer_details = Owner::where('id','=',$owner_id)->first();
            $passenger_contact_name = $onwer_details->contact_name;
            $passenger_phone = $onwer_details->phone;
            $passenger_email = $onwer_details->email;
        }
        else {
            $is_agent = Session::get('is_agent');
            $passenger_contact_name = Input::get('passenger_contact_name');
            $passenger_country_code = Input::get('passenger_countryCode');
            $passenger_phone = Input::get('passenger_phone');
            $passenger_email = Input::get('passenger_email');
        }
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
        $ride_type = Input::get('all_radio');
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
        if ($is_agent == 0) {
            $agent_name = Input::get('agent_name');
            if($agent_name!=''){
                $agent_array = explode(" ",$agent_name);
                if(count($agent_array)>1) {
                    $agent_firstname = $agent_array[0];
                    $agent_lastname = $agent_array[1];
                }else{
                    $agent_firstname = $agent_name;
                    $agent_lastname = '';
                }
            }
            $agent_phone = Input::get('agent_phone');
            if(strlen($agent_phone) <=10){
                $agent_phone = "+1".$agent_phone;
            }
        }
        //
        // check for attendant details
        //
        if($attendant == 1){
            $attendant_name = Input::get('attendant_name');
            $attendant_phone = Input::get('attendant_phone');
            $attendant_pickupaddress = Input::get('attendant_pickupaddress');

            if(strlen($attendant_phone) <=10){
                $attendant_phone = "+1".$attendant_phone;
            }
        }
        //
        // parameters of questionable value...
        //
        $distance = Input::get('distance_db');
        $time = Input::get('time_db');
        $total_db = Input::get('total_db');
        $total_roundtrip_amount = Input::get('total_roundtrip_amount');

        if ($wheelchair == 1) {
            $wheelchair = 1;
        } else {
            $wheelchair = 0;
        }

        if ($is_agent == 1) {
            $agent_id = Session::get('user_id');
            $dispatcher_agent = DispatcherAgent::where('id', '=', $agent_id)->first();
            $healthcare_id = $dispatcher_agent->healthcare_id;
            $agent_firstname = $dispatcher_agent->contact_name;
            $agent_lastname = "";
            $agent_phone = $dispatcher_agent->phone;
        } else {
            $healthcare_id = Session::get('user_id');
        }

        $enterprise_client = EnterpriseClient::find($healthcare_id);

        $requestpickuptime = $pickupdate . " " . $pickuptime;
        Log::info('pickupdate from input = ' . print_r($pickupdate, true));
        date_default_timezone_set($usertimezone);
        Log::info('usertimezone = ' . print_r($usertimezone, true));
        $finalpickuptime = get_UTC_time($requestpickuptime);
        Log::info('finalpickuptime = ' . print_r($finalpickuptime, true));
        date_default_timezone_set(Config::get('app.timezone'));
        
        if($roundtrip == 1){
            $round_pickup_date = Input::get('round_pickup_date');
            $round_pickup_time  = Input::get('round_pickup_time');
            $round_pickupaddress = $dropoffaddress;
            $round_dropoffaddress = $pickupaddress;
/*
//
// ridiculous, we're paying twice for looking up an address. what if we don't have an address???
//

            if ($round_pickupaddress != '') {
                $prepAddr = str_replace(' ', '+', $round_pickupaddress);
                $geocodeURL = $this->getGeocodeURL($prepAddr);
                $geocode = file_get_contents($geocodeURL);
                $output = json_decode($geocode);
                if ($output->status == "OK") {
                    $roundlatitude = $output->results[0]->geometry->location->lat;
                    $roundlongitude = $output->results[0]->geometry->location->lng;
                }else{
                    return
                        Redirect::to('booking/request-ride')
                        ->with('error', 'Return Trip Pickup Address not found. Please try again with a nearby address')
                        ->with('geocodeURL', $geocodeURL)
                        ->with('geocode', $geocode);
                }
            }
            if ($round_dropoffaddress != '') {
                $dropprepAddr = str_replace(' ', '+', $round_dropoffaddress);
                $geocodeURL = $this->getGeocodeURL($dropprepAddr);
                $geocode = file_get_contents($geocodeURL);
                $dropoutput = json_decode($geocode);
                if ($dropoutput->status == "OK") {
                    $rounddroplatitude = $dropoutput->results[0]->geometry->location->lat;
                    $rounddroplongitude = $dropoutput->results[0]->geometry->location->lng;
                }else{
                    return
                        Redirect::to('booking/request-ride')
                        ->with('error', 'Return Trip Drop Off Address not found. Please try again with a nearby address')
                        ->with('geocodeURL', $geocodeURL)
                        ->with('geocode', $geocode);
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
        if ($pickupaddress != '') {
            $prepAddr = str_replace(' ', '+', $pickupaddress);
            $url = $this->getGeocodeURL($prepAddr);
            Log::info("URL: $url");
            $geocode = file_get_contents($url);
            Log::info("geocode: $geocode");
            $output = json_decode($geocode);
            if ($output->status == "OK") {
                $latitude = $output->results[0]->geometry->location->lat;
                $longitude = $output->results[0]->geometry->location->lng;
            }else{
                return
                    Redirect::to('booking/request-ride')
                    ->with('error','Pick Up Address not found. Please try again with a nearby address')
                    ->with('geocodeURL', $geocodeURL)
                    ->with('geocode', $geocode);
            }
        }
        if ($dropoffaddress != '') {
            $dropprepAddr = str_replace(' ', '+', $dropoffaddress);
            $url = $this->getGeocodeURL($dropprepAddr);
            Log::info("URL: $url");
            $geocode = file_get_contents($url);
            Log::info("geocode: $geocode");
            $dropoutput = json_decode($geocode);
            if ($dropoutput->status == "OK") {
                $droplatitude = $dropoutput->results[0]->geometry->location->lat;
                $droplongitude = $dropoutput->results[0]->geometry->location->lng;
            }else{
                return
                    Redirect::to('booking/request-ride')
                    ->with('error','Drop Off Address not found. Please try again with a nearby address')
                    ->with('geocodeURL', $geocodeURL)
                    ->with('geocode', $geocode);
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
        if (Input::has('passenger_pickupaddress')) {
            $src_address = trim(Input::get('passenger_pickupaddress'));
        }
        $dest_address = "Address Not Available";
        if (Input::has('passenger_dropoffaddress')) {
            $dest_address = trim(Input::get('passenger_dropoffaddress'));
        }
        if($owner_id != 0){
            $validator = Validator::make(
                array(
                    'passenger_pickupaddress' => $pickupaddress,
                    'passenger_dropoffaddress' => $dropoffaddress
                ), array(
                'passenger_pickupaddress'=>'required',
                'passenger_dropoffaddress' => 'required'
            ), array(
                    'passenger_pickupaddress' => 'Pickup address is required',
                    'passenger_dropoffaddress' => 'dropoff address is required'
                )
            );
        }else {
            if ($is_agent == 1) {
                $validator = Validator::make(
                    array(
                        'contact_name' => $passenger_contact_name,
                        'phone' => $passenger_phone,
                        'passenger_dropoffaddress' => $dropoffaddress
                    ), array(
                    'phone' => 'required',
                    'contact_name' => 'required',
                    'passenger_dropoffaddress' => 'required'
                ), array(
                        'Phone' => 'Phone field is required.',
                        'contact_name' => 'Passenger Name field is required.',
                        'passenger_dropoffaddress' => 'dropoff address is required'
                    )
                );
            } else {
                $validator = Validator::make(
                    array(
                        'contact_name' => $passenger_contact_name,
                        'phone' => $passenger_phone,
                        'agent_name' => $agent_name,
                        'passenger_dropoffaddress' => $dropoffaddress,
                    ), array(
                    'phone' => 'required',
                    'contact_name' => 'required',
                    'agent_name' => 'required',
                    'passenger_dropoffaddress' => 'required'
                ), array(
                        'Phone' => 'Phone field is required.',
                        'contact_name' => 'Passenger Name field is required.',
                        'agent_name' => 'Agent Name field is required.',
                        'passenger_dropoffaddress' => 'dropoff address is required.'
                    )
                );
            }
        }

        if ($validator->fails()) {
            return Redirect::to('booking/request-ride')
                ->with('error', 'Please fill all the fields.')
                ->with('passenger_contact_name', $passenger_contact_name)
                ->with('passenger_phone', $passenger_phone)
                ->with('passenger_email', $passenger_email)
                ->with('dropoffaddress', $dropoffaddress)
                ->with('agent_name', $agent_name);
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

        $thetotal = $fare['ride_estimated_price'];
        if($fare['is_roundtrip'] == 1) {
            $thetotal = $thetotal / 2;
        }

        //check whether dispatcher_assigned already exists.
        if($owner_id != 0){
            $fullphoneno = $passenger_phone;
        }
        else {
            $fullphoneno = $passenger_country_code . $passenger_phone;
        }

        if($owner_id!=0 && Session::get('user_select') == 3){
            $DispatcherAssigned = Owner::where('id', '=', $owner_id)->first();
        }else{
            $DispatcherAssigned = DispatcherAssigned::where('phone', '=', $fullphoneno)->first();
            if ($DispatcherAssigned) {
                DispatcherAssigned::where('id', '=', $DispatcherAssigned->id)->update(array('contact_name' => $passenger_contact_name, 'email' => $passenger_email, 'updated_at' => date('Y-m-d H:i:s')));
            } else {
                $DispatcherAssigned = new DispatcherAssigned;
                $DispatcherAssigned->contact_name = $passenger_contact_name;
                $DispatcherAssigned->email = $passenger_email;
                $DispatcherAssigned->phone = $passenger_country_code . $passenger_phone;
                $DispatcherAssigned->dispatcher_id = $healthcare_id;
                $DispatcherAssigned->created_at = date('Y-m-d H:i:s');
                $DispatcherAssigned->updated_at = date('Y-m-d H:i:s');
                $DispatcherAssigned->save();
            }
        }

        $request = new RideRequest;
        $ride_detail = new RideDetails;
        $request->metadata('fare', $fare);
        $request->passenger_contact_name = $passenger_contact_name;
        $request->passenger_phone = $passenger_phone;
        $request->payment_mode = $payment_opt;
        $request->special_request = $special_request;
        $request->service_type = $services;
        $ride_detail->billing_code = $billing_code;
        $request->time_zone = $time_zone;
        $request->src_address = $src_address;
        if ($ride_type == 2) {
            $ridetype = 1;
            $request->later = '1';
        } else {
            $ridetype = 0;
            $request->later = '0';
        }

        $ride_detail->is_scheduled = $ridetype;
        $request->D_latitude = $destination_latitude;
        $request->D_longitude = $destination_longitude;

        $request->dest_address = $dest_address;
        $request->request_start_time = $finalpickuptime;
        $request->latitude = $origin_latitude;
        $request->longitude = $origin_longitude;
        $request->req_create_user_time = $user_create_time;
        $request->status = 0;
        $request->is_walker_started = '0';
        $request->is_walker_arrived = '0';
        $request->is_started = '0';
        $request->is_completed = '0';
        $request->is_dog_rated = '0';
        $request->is_walker_rated = '0';
        $request->distance = $distance;
        $request->time = $time;
        $request->total = $thetotal;
        $request->is_paid = '0.00';
        $request->card_payment = '0.00';
        $request->ledger_payment = '0.00';
        $request->is_cancelled = '0';
        $request->refund = '0.00';
        $request->transfer_amount = '0.00';
        $request->promo_code = '';
        $request->promo_id = '';
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

        if($owner_id!=0 && Session::get('user_select') == 3){
            $request->owner_id = $owner_id;
        }else{
            $request->hospital_provider_id = $payment_mode;
            $request->dispatcher_assigned_id = $DispatcherAssigned->id;
            $request->healthcare_id = $healthcare_id;
            $ride_detail->agent_contact_name = $agent_firstname;
            $ride_detail->agent_phone = $agent_phone;
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
        $reqserv->type = $services;
        $reqserv->save();

        $id = RideRequest::where('id','=',$request->id)->first();
        $ride_detail->request_id = $id->id;
        $ride_detail->save();

        if($roundtrip==1){
            $request1 = new RideRequest;
            $ride_detail_1 = new RideDetails;

            if($attendant==1){
                $round_attendant_name = Input::get('round_attendant_name');
                $round_attendant_phone = Input::get('round_attendant_phone');

                if(strlen($round_attendant_phone) <=10){
                    $round_attendant_phone = "+1".$round_attendant_phone;
                } else{
                    $round_attendant_phone = $round_attendant_phone;
                }
            }

            $request1->metadata('fare', $fare);
            $request1->passenger_contact_name = $passenger_contact_name;
            $request1->passenger_phone = $passenger_phone;
            $request1->payment_mode = $payment_opt;
            $request1->special_request = $special_request;
            $request1->service_type = $services;
            $ride_detail_1->billing_code = $billing_code;
            $request1->time_zone = $time_zone;
            $request1->src_address = $round_pickupaddress;
            if($owner_id!=0 && Session::get('user_select') == 3){
                $request1->owner_id = $owner_id;
            }else{
                $request1->hospital_provider_id = $payment_mode;
                $request1->dispatcher_assigned_id = $DispatcherAssigned->id;
                $request1->healthcare_id = $healthcare_id;
                $ride_detail_1->agent_contact_name = $agent_firstname;
                $ride_detail_1->agent_phone = $agent_phone;
            }

            if ($ride_type == 2) {
                $ridetype = 1;
                $request1->later = '1';
            } else {
                $ridetype = 0;
                $request1->later = '0';
            }
            $ride_detail_1->is_scheduled = $ridetype;
            $request1->D_latitude = $origin_latitude;
            $request1->D_longitude = $origin_longitude;
            $request1->dest_address = $round_dropoffaddress;
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
            $request1->total = $thetotal;
            $request1->is_paid = '0.00';
            $request1->card_payment = '0.00';
            $request1->ledger_payment = '0.00';
            $request1->is_cancelled = '0';
            $request1->refund = '0.00';
            $request1->transfer_amount = '0.00';
            $request1->promo_code = '';
            $request1->promo_id = '';
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

        $time_left = Settings::value('provider_timeout');
        $msg_array = array();
        $msg_array['unique_id'] = 1;
        $msg_array['request_id'] = $request->id;
        $msg_array['time_left_to_respond'] = $time_left;
        $unit = Settings::value('default_distance_unit');
        if ($unit == 0) {
            $unit_set = 'kms';
        } elseif ($unit == 1) {
            $unit_set = 'miles';
        }

        if($attendant==1) {
            $attendantName = $attendant_name;
            $attendantPhone = $attendant_phone;
        } else{
            $attendantName = '';
            $attendantPhone = '';
        }

        //sending email notifications to operator
        //get user information
        $agent_name = $request->agent_contact_name;
        if($owner_id!=0 && Session::get('user_select') == 3) {
            $passengerinfo = Owner::where('id', $request->owner_id)->first();
            $agent_name = $passengerinfo->contact_name;
        }else{
            $passengerinfo = DispatcherAssigned::where('id', $request->dispatcher_assigned_id)->first();
        }
        $admin_email = Settings::value('admin_email_address');
        $ride_assignee_phone_number = Settings::value('ride_assignee_phone_number');

        $follow_url = web_url() . "/booking/myrides";
        if ($request->driver_name) {
            $driver_name = $request->driver_name;
            $driver_phone = $request->driver_phone;
        } else {
            $driver_name = "NA";
            $driver_phone = "NA";
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

        $pattern = array('driver_name' => $driver_name, 'driver_phone' => $driver_phone,
            'passenger_name' => $passenger_name, 'passenger_phone' => $passengerinfo->phone,
            'pickup_time' => $pickuptime,'pickup_date' => $pickupdate, 'pickup_location' => $request->src_address,
            'dropoff_location' => $request->dest_address, 'butterfli_dispatcher_phno' => $ride_assignee_phone_number,
            'admin_email' => $admin_email, 'trip_id' => $request->id, 'follow_url' => $follow_url,
            'agent_name' => $agent_name, 'wheelchair_request' => $wheelchair_request,'attendant_travelling' => $attendant_travelling,
            'healthcare_email' => '', 'healthcare_company' => '', 'server' => '', 'billing_code'=>$billing_code,'all_radio'=>$ride_type);
        $subject = "We've received your ride request";

        if($owner_id == 0) {
        }else{
            $owner_id = (Session::get('user_id'));
            email_notification($owner_id, 'owner_mail', $pattern, $subject, 'new_enterprise_ride_request', null);

        }
        if ($_SERVER['HTTP_HOST'] == "ride.gobutterfli.com") {
            $server = "Development";
        } elseif ($_SERVER['HTTP_HOST'] == "app.gobutterfli.com") {
            $server = "Production";
        } elseif ($_SERVER['HTTP_HOST'] == "demo.gobutterfli.com") {
            $server = "Demo";
        } else {
            $server = "Unknown";
        }

        $healthcare_email = Session::get('healthcare_email');
        $healthcare_company = Session::get('healthcare_company');

        $params = array(
            'passenger_contact_name' =>      $passenger_contact_name,
            'passenger_phone' =>            $passenger_phone,
            'has_attendant' =>              $attendant,
            'has_wheelchair' =>             $wheelchair,
            'attendant_contact_name' =>     $attendantName,
            'attendant_phone' =>            $attendantPhone,
            'owner_id' =>                   $owner_id,
            'user_id' =>                    Session::get('user_id'),
            'user_select' =>                Session::get('user_select'),
            'pickup_time' =>                $pickuptime,
            'pickup_date' =>                $pickupdate,
            'pickup_location' =>            $request->src_address,
            'dropoff_location' =>           $request->dest_address,
            'butterfli_dispatcher_phno' =>  $ride_assignee_phone_number,
            'admin_email' =>                $admin_email,
            'trip_id' =>                    $request->id,
            'follow_url' =>                 $follow_url,
            'agent_name' =>                 $agent_name,
            'attendant_travelling' =>       $attendant_travelling,
            'healthcare_email' =>           $healthcare_email,
            'healthcare_company' =>         $healthcare_company,
            'server' =>                     $server,
            'billing_code' =>               $billing_code,
            'all_radio' =>                  $ride_type,
            'subject' =>                    $subject
        );

        send_ride_request_notifications($request, $params);

        return Redirect::to('/booking/request-ride')->with('success', "Request Sent Successfully.");
    }

    public function view_map()
    {
        $id = Request::segment(4);
        $request =RideRequest::find($id);
        $owner = Owner::where('id', $request->owner_id)->first();
        if ($owner == '') {
            $owner = DispatcherAssigned::where('id', $request->dispatcher_assigned_id)->first();
        }
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
        } elseif ($request->payment_mode == 3) {
            $pay_mode = "<span class='badge bg-green'>" . $request->provider_name . " </span>";
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
            return View::make('enterpriseclient.walk_map')
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
                ->with('full_walk', $full_walk);
        } else {
            $title = ucwords('Maps');
            return View::make('enterpriseclient.walk_map')
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
                ->with('status', $status);
        }
    }
    public function healthcareForgotPassword()
    {
        $email = Input::get('email');
        if(Input::get('user_click')==1) {
            $healthcare = EnterpriseClient::where('email', $email)->first();
            if ($healthcare) {
                $new_password = time();
                $new_password .= rand();
                $new_password = sha1($new_password);
                $new_password = substr($new_password, 0, 8);
                Log::info('new password = ' . print_r($new_password, true));
                $healthcare->password = Hash::make($new_password);
                $healthcare->save();
                $settings = Settings::where('key', 'admin_email_address')->first();
                $admin_email = $settings->value;
                $login_url = web_url() . "/booking/signin";
                $pattern = array('name' => $healthcare->contact_name, 'admin_email' => $admin_email, 'new_password' => $new_password, 'login_url' => $login_url);
                $subject = "Your New Password";
                email_notification($healthcare->id, 'healthcare', $pattern, $subject, 'forgot_password', 'imp');
                return Redirect::to('booking/signin')->with('success', 'Password reset successfully. Please check your inbox for new password.');
            } else {
                $agent = DispatcherAgent::where('email', $email)->first();
                if ($agent) {
                    $new_password = time();
                    $new_password .= rand();
                    $new_password = sha1($new_password);
                    $new_password = substr($new_password, 0, 8);
                    Log::info('new password = ' . print_r($new_password, true));
                    $agent->password = Hash::make($new_password);
                    $agent->save();
                    $settings = Settings::where('key', 'admin_email_address')->first();
                    $admin_email = $settings->value;
                    $login_url = web_url() . "/booking/signin";
                    $pattern = array('name' => $agent->contact_name, 'admin_email' => $admin_email, 'new_password' => $new_password, 'login_url' => $login_url);
                    $subject = "Your New Password";
                    email_notification($agent->id, 'agent', $pattern, $subject, 'forgot_password', 'imp');
                    return Redirect::to('booking/signin')->with('success', 'Password reset successfully. Please check your inbox for new password.');
                } else {
                    return Redirect::to('booking/signin')->with('error', 'This email ID is not registered with us');
                }
            }
        }else{
            $owner = Owner::where('email', $email)->first();
            if ($owner) {
                $new_password = time();
                $new_password .= rand();
                $new_password = sha1($new_password);
                $new_password = substr($new_password, 0, 8);
                Log::info('new password = ' . print_r($new_password, true));
                $owner->password = Hash::make($new_password);
                $owner->save();
                $settings = Settings::where('key', 'admin_email_address')->first();
                $admin_email = $settings->value;
                $login_url = web_url() . "/booking/signin";
                $pattern = array('name' => $owner->contact_name, 'admin_email' => $admin_email, 'new_password' => $new_password, 'login_url' => $login_url);
                $subject = "Your New Password";
                email_notification($owner->id, 'owner', $pattern, $subject, 'forgot_password', 'imp');
                return Redirect::to('booking/signin')->with('success', 'Password reset successfully. Please check your inbox for new password.');
            } else {
                return Redirect::to('booking/signin')->with('error', 'This email ID is not registered with us');
            }
        }
    }
    public function cancelrideHealthcare()
    {
        $request_id = $_POST['request_id'];
        $cancel_reason = $_POST['cancel_reason'];
        if ($request =RideRequest::find($request_id)) {
            if ($request->is_cancelled != 1) {
                $healthcare_email = Session::get('healthcare_email');
                $providertype = ProviderType::where('id', $request->service_type)->first();
                // request ended
              RideRequest::where('id', '=', $request_id)->update(array('status' => 0, 'is_cancelled' => 1, 'cancel_reason' => $cancel_reason, 'cancelled_by'=> $healthcare_email, 'cancellation_fee'=>$providertype->base_price));
                // Send SMS
                $pattern = "Ride has been cancelled for the request-id: " . $request->id . " with Cancel reason: " . $cancel_reason;
                sms_notification($request->healthcare_id, 'operator', $pattern);
                sms_notification($request->healthcare_id, 'ride_assignee', $pattern);
                sms_notification($request->healthcare_id, 'ride_assignee_2', $pattern);
                sms_notification($request->healthcare_id, 'ride_assignee_3', $pattern);
                //sending email notifications to operator
                //get user information
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
                $passenger_name = $passengerinfo->contact_name;
                $agent_name = $request->agent_contact_name;
                $request = RideRequest::find($request->id);
                $ride_type = $request->is_scheduled;
                $datetime = new DateTime($request->request_start_time);
                $datetime->format('Y-m-d H:i:s') . "\n";
                $user_time = new DateTimeZone($request->time_zone);
                $datetime->setTimezone($user_time);
                $newpickuptime = $datetime->format('Y-m-d H:i:s');
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
                    'cancel_reason' => $cancel_reason, 'agent_name' => $agent_name,
                    'all_radio'=>$ride_type, 'cancellation_fee'=>$providertype->base_price);
                $subject = "You've cancelled your ride request";
                email_notification($request->healthcare_id, 'operator', $pattern, $subject, 'ride_cancel', 'imp');
                email_notification($request->healthcare_id, 'ride_assignee', $pattern, $subject, 'ride_cancel', 'imp');
                email_notification($request->healthcare_id, 'ride_assignee_2', $pattern, $subject, 'ride_cancel', 'imp');
                email_notification($request->healthcare_id, 'ride_assignee_3', $pattern, $subject, 'ride_cancel', 'imp');
                return 1;
            }
        } else {
            return 2;
        }
    }
    public function saveownerpayments()
    {
        $token = $_POST['stripeToken'];
        $cardholdername = $_POST['cardholdername'];
        $cardholderphone = $_POST['cardholderphone'];
        $cardtype = $_POST['cardtype'];
        $last4 = $_POST['last4'];
        $rememberme = $_POST['rememberme'];
        $disp_assign_id = $_POST['dispatcher_owner_id'];
        $card_id = $_POST['card_id'];
        $newpayment = new PaymentServices();
        $response = $newpayment->saveUserPayment($token, $cardtype, $last4, $cardholdername, $rememberme, $disp_assign_id, $card_id);
        //Log::info('response = ' . print_r($response['error'], true));
        //die;
        if ($disp_assign_id != '' && ($response == 1 || $response == 4)) {
            if(Session::get('user_select') == 3) {
                $query = "SELECT * FROM payment WHERE customer_id IS NOT NULL AND owner_id =" . $disp_assign_id;
            }else{
                $query = "SELECT * FROM payment WHERE customer_id IS NOT NULL AND dispatcher_assigned_id =" . $disp_assign_id;
            }
            $results = DB::select(DB::raw($query));
            foreach ($results as $result) {
                if ($result->is_default == 1) {
                    $selected = 'checked';
                } else {
                    $selected = '';
                }
                echo "<tr>";
                echo "<td style='padding-bottom: 10px;'><input style='width:100%' type=text name=last_four readonly value='XXXX-XXXX-XXXX-$result->last_four'></td>";
                echo "<td><input style='width: 50px;vertical-align: -10px;margin-left: 10px;' type=radio id='is_default' name=is_default checked=$selected value='$result->id' onclick=changedefault('$result->id');>Active</td>";
                echo "</tr>";
            }
            if(Session::get('user_select') == 3) {
                echo "<input type='hidden' name='owner_id' id='owner_id' value='$disp_assign_id'>";
            }else{
                echo "<input type='hidden' name='dispatcher_assigned_id' id='dispatcher_assigned_id' value='$disp_assign_id'>";
            }
            echo "<input type='hidden' name='paymentflag' id='payment_flag' value='1'>";
        } elseif ($response['error'] != '') {
            return $response;
        } else {
            echo "<tr>";
            echo "<td style='padding-bottom: 10px;'><input style='width:100%' type=text name=last_four readonly value='XXXX-XXXX-XXXX-$last4'></td>";
            echo "<td><input style='width: 50px;' type=radio id='is_default' name=is_default checked='checked' value='1'>Active</td>";
            echo "</tr>";
            echo "<input type='hidden' name='paymentflag' id='payment_flag' value='1'>";
        }
        //return $response;
    }
    public function checkHealthcareassigned()
    {
        $phone = $_POST['phone'];
        $country_code = $_POST['country_code'];
        $fullphoneno = $country_code . $phone;
        $DispatcherAssigned = DispatcherAssigned::where('phone', '=', $fullphoneno)->first();
        if ($DispatcherAssigned) {
            return $DispatcherAssigned;
        } else {
            return 1; //when no dispatcher_assigned is exists in table.
        }
    }
    public function checkPaymentOwnerData()
    {
        $dispatcher_assign_id = $_POST['dispatch_assign_id'];
        if ($dispatcher_assign_id > 0) {
            if(Session::get('user_select') == 3) {
                $query = "SELECT * FROM payment WHERE customer_id IS NOT NULL AND owner_id =" . $dispatcher_assign_id;
            } else{
                $query = "SELECT * FROM payment WHERE customer_id IS NOT NULL AND dispatcher_assigned_id =" . $dispatcher_assign_id;
            }
            $results = DB::select(DB::raw($query));
            if (empty($results)) {
                return 1; // not data available with customer_id in payment table.
            } else {
                foreach ($results as $result) {
                    if ($result->is_default == 1) {
                        $selected = 'checked=checked';
                    } else {
                        $selected = '';
                    }
                    echo "<tr>";
                    echo "<td style='padding-bottom: 10px;'><input style='width:100%' type=text name=last_four readonly value='XXXX-XXXX-XXXX-$result->last_four'></td>";

                    if(Session::get('user_select') == 3) {
                        echo "<td><input style='width: 50px;vertical-align: -10px;margin-left: 10px;' type=radio id='is_default' name=is_default $selected value='$result->id' onclick=changedefault('$result->id');>Active</td>";
                    }else{
                        echo "<td><input style='width: 50px;vertical-align: -10px;margin-left: 10px;' type=radio id='is_default' name=is_default $selected value='$result->id' onclick=changeassigneddefault('$result->id');>Active</td>";
                    }
                    echo "</tr>";
                }
                if(Session::get('user_select') == 3) {
                    echo "<input type='hidden' name='owner_id' id='owner_id' value='$dispatcher_assign_id'>";
                }else{
                    echo "<input type='hidden' name='dispatcher_assigned_id' id='dispatcher_assigned_id' value='$dispatcher_assign_id'>";
                }
            }
        }
    }
    public function dispatchercharge_user()
    {
        $request_id = $_POST['request_id'];
        $final_amount = $_POST['final_amount'];
        $comments = $_POST['comments'];
        $additional_fee = $_POST['add_fee'];
        $approval_text = $_POST['approval_text'];
        $promotional_offer = $_POST['promo_offer'];
        if ($request = RideRequest::find($request_id)) {
            $payment_data = Payment::where('dispatcher_assigned_id', $request->dispatcher_assigned_id)->where('is_default', 1)->first();
            if ($payment_data != '') {
                $customer_id = $payment_data->customer_id;
                $token = $payment_data->card_token;
                //$setransfer = Settings::where('key', 'transfer')->first();
                //$transfer_allow = $setransfer->value;
                $newpayment = new PaymentServices();
                if ($customer_id != '') {
                    $response = $newpayment->makePayment($final_amount, $customer_id, '');
                    if ($response->paid) {
                        $request->is_paid = 1;
                    } else {
                        $request->is_paid = 0;
                    }
                } else {
                    $response = $newpayment->makePayment($final_amount, '', $token);
                    if ($response->paid) {
                        $request->is_paid = 1;
                    } else {
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
            } else {
                return 2;
            }
        } else {
            return 3;
        }
    }
    public function updatedefaultOwnerpaymentcard()
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
    public function HealthcareRideCompleted()
    {
        $request_id = $_POST['request_id'];
        //$dropoffaddress  = $_POST['dropoffaddress'];
        // $distance		 = $_POST['dist'];
        // $time            = $_POST['distancetime'];
        //$comment		 = $_POST['comment'];
        $rating         = $_POST['walker_rating'];
        $feedback       = $_POST['feedback_comment'];
        //Log::info('distance input = ' . print_r($distance, true));
        Log::info('requestid input = ' . print_r($request_id, true));
        Log::info('rating input = ' . print_r($rating, true));
        Log::info('feedback input = ' . print_r($feedback, true));

        //if($request_id>0 && $dropoffaddress!='' && $distance>0 && $time!='' && $comment!=''){
        if ($request_id > 0) {
            /*if($dropoffaddress!=''){
                $dropprepAddr = str_replace(' ','+',$dropoffaddress);
                $geocode=file_get_contents('https://maps.google.com/maps/api/geocode/json?address='.$dropprepAddr.'&sensor=false');
                $dropoutput= json_decode($geocode);
                $droplatitude = $dropoutput->results[0]->geometry->location->lat;
                $droplongitude = $dropoutput->results[0]->geometry->location->lng;
            }*/
            if ($request = RideRequest::find($request_id)) {
                if ($request->is_confirmed == 1) {
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
                    if ($request->is_wheelchair_request == 1) {
                        $actual_total = $actual_total + $request->additional_fee;
                    }
                    Log::info('total_price = ' . print_r($actual_total, true));
                    $settings = Settings::where('key', 'provider_amount_for_each_request_in_percentage')->first();
                    $provider_percentage = $settings->value;
                    $total = 0;
                    $ref_total = 0;
                    $promo_total = 0;
                    if ($request->payment_mode == 0) {
                        $walker_payment_remaining = (($total * $provider_percentage) / 100);
                    }
                    $request = RideRequest::find($request_id);
                    $request->is_completed = 1;
                    $request->is_walker_started = 1;
                    $request->is_walker_arrived = 1;
                    $request->is_started = 1;
                    //$request->security_key = NULL;
                    $request->total = $actual_total;
                    $request->card_payment = $actual_total;
                    $request->payment_remaining = $walker_payment_remaining;
                    $request->refund_remaining = $provider_refund_remaining;
                    $request->ledger_payment = $ref_total;
                    $request->promo_payment = $promo_total;
                    $request->save();

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
                    // Send SMS
                    /*$owner = DispatcherAssigned::find($request->dispatcher_assigned_id);
                    $settings = Settings::where('key', 'sms_when_provider_completes_job')->first();
                    $pattern = $settings->value;
                    $pattern = str_replace('%user%', $owner->contact_name, $pattern);
                    $pattern = str_replace('%driver%', $walker->contact_name, $pattern);
                    $pattern = str_replace('%driver_mobile%', $walker->phone, $pattern);
                    $pattern = str_replace('%amount%', $request->total, $pattern);
                    if ($request->dispatcher_assigned_id != null) {
                       // sms_notification($request->dispatcher_assigned_id, 'dispatcher_assigned', $pattern);
                    }*/
                    //sending email notifications to operator
                    //get user information
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
                    $passenger_name = $passengerinfo->contact_name;
                    $agent_name = $request->agent_contact_name;
                    $datetime = new DateTime($request->request_start_time);
                    $datetime->format('Y-m-d H:i:s') . "\n";
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
                    email_notification($request->healthcare_id, 'operator', $pattern, $subject, 'ride_complete', 'imp');
                    return 1;
                }
            } else {
                return 2;
            }
        }
    }
    public function getWalkerData($walker_id)
    {
        $walker_data = Walker::where('id', '=', $walker_id)->first();
        if (!$walker_data) {
            return false;
        }
        return $walker_data;
    }
    public function DownloadReport()
    {
        $is_agent = Session::get('is_agent');
        if ($is_agent == 1) {
            $agent_id = Session::get('user_id');
            $healthcare_agent = DispatcherAgent::find($agent_id);
            $user_id = $healthcare_agent->healthcare_id;
            $provider = EnterpriseClient::find($user_id);
        } else {
            $user_id = Session::get('user_id');
            if(Session::get('user_select') ==1){
                $provider = EnterpriseClient::find($user_id);
            }else{
                $provider = Owner::find($user_id);
            }
        }

        if(Session::get('user_select') == 3){
            $walks = DB::table('request')
                ->leftJoin('ride_details', 'request.id','=','ride_details.request_id')
                ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                ->leftJoin('walker_type', 'walker_type.id', '=', 'request.service_type')
                ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
                ->leftJoin('healthcare_documents','healthcare_documents.request_id','=','request.id')
                ->leftJoin('hospital_providers', 'hospital_providers.id', '=', 'request.hospital_provider_id')
                ->select('owner.contact_name as owner_contact_name', 
                    'owner.phone as user_phone_no', 'request.id as id', 'request.request_start_time as date',
                    'request.payment_mode', 'request.is_started', 'request.is_walker_arrived',
                    'request.payment_mode', 'request.is_completed', 'request.is_paid',
                    'request.is_walker_started', 'request.status', 'request.time', 'request.distance',
                    'request.total', 'request.is_cancelled','walker.id as walker_id','request.confirmed_walker',
                    'request.transfer_amount', 'walker_type.name', 'request.total as total_service_amount',
                    'request.promo_payment', 'request.additional_fee', 'hospital_providers.provider_name',
                    'walker.contact_name as walker_contact_name', 
                    'walker.phone as walker_phone', 'request.is_confirmed',
                    'ride_details.agent_contact_name','healthcare_documents.document_url',
                    'request.estimated_time','request.src_address','request.dest_address',
                    'ride_details.oxygen_mask as oxygen_mask',
                    'ride_details.height as user_height',
                    'ride_details.weight as user_weight',
                    'ride_details.condition as user_condition',
                    'ride_details.respirator as respirator',
                    'ride_details.any_tubing as any_tubing',
                    'ride_details.colostomy_bag as colostomy_bag',
                    'ride_details.any_attachments as any_attachments',
                    'request.attendant_travelling as attendant_travelling','request.is_manual','request.driver_name','request.driver_phone')
                ->orderBy('request.id', 'DESC')
                ->where('request.owner_id', '=', $user_id)
                ->paginate(1000);
        } else{
            $walks = DB::table('request')
                ->leftJoin('ride_details', 'request.id','=','ride_details.request_id')
                ->leftJoin('dispatcher_assigned', 'request.dispatcher_assigned_id', '=', 'dispatcher_assigned.id')
                ->leftJoin('walker_type', 'walker_type.id', '=', 'request.service_type')
                ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
                ->leftJoin('healthcare_documents','healthcare_documents.request_id','=','request.id')
                ->leftJoin('hospital_providers', 'hospital_providers.id', '=', 'request.hospital_provider_id')
                ->select('dispatcher_assigned.contact_name as owner_contact_name', 
                    'dispatcher_assigned.phone as user_phone_no', 'request.id as id', 'request.request_start_time as date',
                    'request.payment_mode', 'request.is_started', 'request.is_walker_arrived',
                    'request.payment_mode', 'request.is_completed', 'request.is_paid',
                    'request.is_walker_started', 'request.status', 'request.time', 'request.distance',
                    'request.total', 'request.is_cancelled','walker.id as walker_id','request.confirmed_walker',
                    'request.transfer_amount', 'walker_type.name', 'request.total as total_service_amount',
                    'request.promo_payment', 'request.additional_fee', 'hospital_providers.provider_name',
                    'walker.contact_name as walker_contact_name', 
                    'walker.phone as walker_phone', 'request.is_confirmed',
                    'ride_details.agent_contact_name','healthcare_documents.document_url',
                    'request.estimated_time','request.src_address','request.dest_address',
                    'ride_details.oxygen_mask as oxygen_mask',
                    'ride_details.height as user_height',
                    'ride_details.weight as user_weight',
                    'ride_details.condition as user_condition',
                    'ride_details.respirator as respirator',
                    'ride_details.any_tubing as any_tubing',
                    'ride_details.colostomy_bag as colostomy_bag',
                    'ride_details.any_attachments as any_attachments',
                    'request.attendant_travelling as attendant_travelling','request.is_manual','request.driver_name','request.driver_phone')
                ->orderBy('request.id', 'DESC')
                ->where('request.healthcare_id', '=', $user_id)
                ->paginate(1000);
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=data.csv');
        $handle = fopen('php://output', 'w');
        $settings = Settings::where('key', 'default_distance_unit')->first();
        $unit = $settings->value;
        if ($unit == 0) {
            $unit_set = 'kms';
        } elseif ($unit == 1) {
            $unit_set = 'miles';
        }
        fputcsv($handle, array('Request ID','User Name', 'User Phone #', 'Driver', 'Driver Phone #','Pickup address','Dropoff Address', 'Service',
            'ETA driver (in mins)', 'Date/Time', 'Status', 'Payment Mode', 'Payment Status'));
        //die;
        foreach ($walks as $request) {
            $pay_mode = "Card Payment";
            if ($request->payment_mode == 0) {
                $pay_mode = "Stored Cards";
            } elseif ($request->payment_mode == 1) {
                $pay_mode = "Pay by Cash";
            } elseif ($request->payment_mode == 2) {
                $pay_mode = "Paypal";
            } elseif ($request->payment_mode == 3) {
                $pay_mode = $request->provider_name;
            }
            $status = 'Yet To Start';
            if ($request->is_cancelled == 1) {
                $status = 'Cancelled';
            } elseif ($request->is_completed == 1) {
                $status = 'Completed';
            } elseif ($request->is_started == 1) {
                $status = 'Started';
            } elseif ($request->is_walker_arrived == 1) {
                $status = 'Arrived';
            } elseif ($request->is_walker_started == 1) {
                $status = 'Started';
            }
            if ($request->is_paid == 1) {
                $pay_status = "Completed";
            } elseif ($request->is_paid == 0 && $request->is_completed == 1) {
                $pay_status = "Pending";
            } else {
                $pay_status = "Request Not Completed";
            }

            if ($request->confirmed_walker) {
                $driver_name = $request->walker_contact_name;
            } else if($request->driver_name && $request->driver_phone){
                $driver_name =  $request->driver_name;
            }else{
                $driver_name =  "Un Assigned";
            }

            if (($request->is_confirmed  == 1) && ($request->is_manual == 0)) {
                $driver_phone =  $request->walker_phone;
            } else if($request->driver_phone){
                $driver_phone =  $request->driver_phone;
            } else{
                $driver_phone = "NA";
            }
            
            fputcsv($handle, array(
                $request->id,
                $request->owner_contact_name,
                $request->user_phone_no,
                $driver_name,
                $driver_phone,
                $request->src_address,
                $request->dest_address,
                $request->name,
                $request->estimated_time,
                date('l, F d Y h:i A', strtotime($request->date)) . " (UTC)",
                $status,
                $pay_mode,
                $pay_status
            ));
        }
        fclose($handle);
        $headers = array(
            'Content-Type' => 'text/csv',
        );
    }

    public function CalculateAmount() {
        $is_agent = Session::get('is_agent');
        $enterpriseClient = $this->GetEnterpriseClient($is_agent);

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

    public function MyProfile()
    {
        $is_agent = Session::get('is_agent');

        if ($is_agent == 1) {
            $agent_id = Session::get('user_id');
            $provider = DispatcherAgent::find($agent_id);
            $hospital_provider = array();
        } else {
            if(Session::get('user_select') == 3){
                $owner_id = Session::get('user_id');
                $provider = Owner::find($owner_id);
                $hospital_provider = '';
            } else{
                $healthcare_id = Session::get('user_id');
                $provider = EnterpriseClient::find($healthcare_id);
                $query = "SELECT * FROM hospital_providers WHERE healthcare_id=" . $provider->id;
                $hospital_provider = DB::select(DB::raw($query));
            }

        }
        return View::make('enterpriseclient.MyProfile')
            ->with('title', 'My Profile')
            ->with('provider', $provider)
            ->with('hospital_provider', $hospital_provider)
            ->with('is_agent', $is_agent)
            ->with('user_select', Session::get('user_select'));
    }
    public function UpdatePassword()
    {
        $current_password = Input::get('current_password');
        $new_password = Input::get('new_password');
        $confirm_password = Input::get('confirm_password');
        $is_agent = Session::get('is_agent');
        $user_select = Session::get('user_select');
        if ($is_agent == 1) {
            $agent_id = Session::get('user_id');
            $user = DispatcherAgent::find($agent_id);
        } else {
            if($user_select==1){
                $healthcare_id = Session::get('user_id');
                $user = EnterpriseClient::find($healthcare_id);
            } else{
                $owner_id = Session::get('user_id');
                $user = Owner::find($owner_id);
            }
        }
        if ($new_password === $confirm_password) {
            if (Hash::check($current_password, $user->password)) {
                $password = Hash::make($new_password);
                $user->password = $password;
                $user->save();
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
        return Redirect::to('booking/myprofile')->with('success', $message)->with('type', $type);
    }
    public function UpdateProfile()
    {
        $is_agent = Session::get('is_agent');
        $user_select = Session::get('user_select');
        $contact_name = Input::get('contact_name');
        $countrycode = Input::get('countrycode');
        $operator_phone = Input::get('operator_phone');
        if ($is_agent == 0 && $user_select!=3) {
            $company = Input::get('company');
            $operator_email = Input::get('operator_email');
        }
        if ($is_agent == 0 && $user_select!=3) {
            $validator = Validator::make(
                array(
                    'contact_name' => $contact_name,
                    'company' => $company,
                    'operator_email' => $operator_email,
                    'operator_phone' => $operator_phone,
                ), array(
                'contact_name' => 'required',
                'company' => 'required',
                'operator_email' => 'required',
                'operator_phone' => 'required',
            ), array(
                    'contact_name' => 'Contact Name field is required.',
                    'company' => 'Company Name is required.',
                    'operator_email' => 'Operator Email is required.',
                    'operator_phone' => 'Operator Phone is required.',
                )
            );
            $validator1 = Validator::make(
                array(
                    'operator_email' => $operator_email,
                ), array(
                'operator_email' => 'required|email'
            ), array(
                    'operator_email' => 'Operator Email is required'
                )
            );
            $validator2 = Validator::make(
                array(
                    'operator_phone' => $operator_phone,
                ), array(
                'operator_phone' => 'required|numeric'
            ), array(
                    'operator_phone' => 'Operator Phone must be numeric'
                )
            );
        } else {
            $validator = Validator::make(
                array(
                    'contact_name' => $contact_name,
                    'operator_phone' => $operator_phone,
                ), array(
                'contact_name' => 'required',
                'operator_phone' => 'required',
            ), array(
                    'contact_name' => 'Contact Name field is required.',
                    'operator_phone' => 'Operator Phone is required.',
                )
            );
            $validator2 = Validator::make(
                array(
                    'operator_phone' => $operator_phone,
                ), array(
                'operator_phone' => 'required|numeric'
            ), array(
                    'operator_phone' => 'Operator Phone must be numeric'
                )
            );
        }
        if ($validator->fails()) {
            $error_messages = $validator->messages();
            Log::info('Error = ' . print_r($error_messages, true));
            return Redirect::to('/booking/myprofile')->with('error', 'Please enter required fields');
        } else if ($is_agent == 0 && $user_select!=3 && $validator1->fails()) {
            $error_messages = $validator1->messages();
            Log::info('Error = ' . print_r($error_messages, true));
            return Redirect::to('booking/myprofile')->with('error', 'Please Enter email correctly.');
        } else if ($validator2->fails()) {
            $error_messages = $validator2->messages();
            Log::info('Error = ' . print_r($error_messages, true));
            return Redirect::to('booking/myprofile')->with('error', 'Please Enter phone correctly.');
        } else {
            if ($is_agent == 1) {
                $agent_id = Session::get('user_id');
                $user = DispatcherAgent::find($agent_id);
            } else {
                if($user_select==1) {
                    $healthcare_id = Session::get('user_id');
                    $user = EnterpriseClient::find($healthcare_id);
                    $companyname = strtolower($company);
                    if(EnterpriseClient::where('company', 'LIKE', $companyname)
                            ->where('id', '!=', $user->id)->count() > 0){
                        Log::info('Error = ' . print_r($user, true));
                        return Redirect::to('/booking/myprofile')->with('error', 'Same company name exists');
                    }
                } else{
                    $owner_id = Session::get('user_id');
                    $user = Owner::find($owner_id);
                }
            }
            if ($is_agent == 0 && $user_select!=3) {
                if (Input::hasFile('picture') && Config::get('app.s3_bucket') != "") {
                    $file_name = time();
                    $ext = Input::file('picture')->getClientOriginalExtension();
                    $local_url = $file_name . "." . $ext;
                    Input::file('picture')->move(public_path('image') . "/uploads", $file_name . "." . $ext);
                    $s3 = App::make('aws')->get('s3');
                    $s3->putObject(array(
                        'Bucket' => Config::get('app.s3_bucket'),
                        'Key' => "uploads/booking/" . $file_name,
                        'SourceFile' => public_path('image') . "/uploads/" . $local_url,
                    ));
                    $s3->putObjectAcl(array(
                        'Bucket' => Config::get('app.s3_bucket'),
                        'Key' => "uploads/booking/" . $file_name,
                        'ACL' => 'public-read'
                    ));
                    $final_file_name = "uploads/booking/" . $file_name;
                    $s3_url = $s3->getObjectUrl(Config::get('app.s3_bucket'), $final_file_name);
                    $localfile = public_path('image') . "/uploads/" . $local_url;
                    unlink_image($localfile);
                } else {
                    $s3_url = $user->companylogo;
                }
            }
            if ($countrycode == '+1') {
                $result = substr($operator_phone, 0, 2);
                if ($result != '+1') {
                    $new_phone_no = $countrycode . $operator_phone;
                } else {
                    $new_phone_no = $operator_phone;
                }
            } elseif ($countrycode == '+44' || $countrycode == '+91') {
                $result = substr($operator_phone, 0, 3);
                if ($result == '+91' || $result == '+44') {
                    $new_phone_no = $operator_phone;
                } else {
                    $new_phone_no = $countrycode . $operator_phone;
                }
            }
            $user->contact_name = $contact_name;
            if ($is_agent == 0 && $user_select!=3) {
                $user->companylogo = $s3_url;
                $user->company = $company;
                $user->operator_email = $operator_email;
                $user->operator_phone = $new_phone_no;
            } else {
                $user->phone = $new_phone_no;
            }
            $user->save();
            if ($is_agent == 0 && $user_select!=3) {
                Session::put('user_pic', $user->companylogo);
            }
            return Redirect::to('/booking/myprofile')->with('success', 'Your profile has been updated successfully')->with('type', 'success');
        }
    }
    public function UpdateHospitalProvider()
    {
        $provider_id = Input::get('provider_id');
        $provider_name = Input::get('provider_name');
        HospitalProviders::where('id', '=', $provider_id)->update(array('is_active' => 1, 'provider_name' => $provider_name));
        return Redirect::to('/booking/myprofile')->with('success', 'Provider name successfully updated');
    }
    public function AddHospitalProvider()
    {
        $healthcare_id = Session::get('user_id');
        $provider_name = Input::get('provider_name');
        //add data in hospital providers
        $hospital = new HospitalProviders;
        $hospital->healthcare_id = $healthcare_id;
        $hospital->provider_name = $provider_name;
        $hospital->is_active = 1;
        $hospital->save();
        return Redirect::to('/booking/myprofile')->with('success', 'Provider name successfully added');
    }
    public function DeleteHospitalProvider()
    {
        $provider_id = Input::get('provider_id');
        $Data = HospitalProviders::find($provider_id);
        $Data->delete();
        return Redirect::to('/booking/myprofile')->with('success', 'Provider name successfully deleted');
    }
    public function HealthcareReceipts()
    {
        $is_agent = Session::get('is_agent');
        if ($is_agent == 1) {
            $agent_id = Session::get('user_id');
            $enterprise_client = DispatcherAgent::find($agent_id);
            $user_id = $enterprise_client->healthcare_id;
        } else {
            $user_id = Session::get('user_id');
        }
        $docs = DB::table('healthcare_documents')
            ->leftJoin('request', 'request.id', '=', 'healthcare_documents.request_id')
            ->leftJoin('dispatcher_assigned', 'request.dispatcher_assigned_id', '=', 'dispatcher_assigned.id')
            ->leftJoin('enterprise_client', 'enterprise_client.id', '=', 'healthcare_documents.healthcare_id')
            ->leftJoin('dispatcher_agent','dispatcher_agent.id','=','healthcare_documents.agent_id')
            ->leftJoin('hospital_providers', 'hospital_providers.id', '=', 'healthcare_request.hospital_provider_id')
            ->select('dispatcher_assigned.contact_name as owner_contact_name',
                'dispatcher_assigned.phone as user_phone_no', 'healthcare_documents.id as docid',
                'request.id as requestid', 'request.request_start_time as date',
                'request.payment_mode',
                'request.payment_mode', 'request.is_completed', 'request.is_paid',
                'request.is_walker_started', 'request.status', 'request.time',
                'request.distance',
                'request.total', 'request.is_cancelled',
                'request.total as total_service_amount',
                'request.promo_payment', 'request.additional_fee', 'hospital_providers.provider_name',
                'request.driver_name', 'request.driver_phone', 'request.is_confirmed',
                'request.agent_contact_name','healthcare_documents.document_url')
            ->orderBy('request.id', 'DESC')
            ->where('request.healthcare_id', '=', $user_id)
            ->where('request.is_completed', '=', 1)
            ->paginate(10);
        $title = 'View Receipts';
        return View::make('enterpriseclient.receipts')
            ->with('title', $title)
            ->with('page', 'Receipts')
            ->with('docs', $docs);
    }
    public function SendRideInfo(){
        $code = $_POST['code'];
        $sms = $_POST['sms'];
        $email = $_POST['email'];
        $request_id = $_POST['request_id'];
        $sms_no = $code.$sms;
        if ($request = RideRequest::find($request_id)) {
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

            $walker = Walker::find($request->confirmed_walker);
            if($walker){
                $driver_name  = $walker->contact_name;
                $driver_phone = $walker->phone;
            }

            if ($sms != '') {
                $pattern = "Ride info for the request-id: " . $request->id."\n";
                $pattern .= " Passenger Name: ".$passenger_name."\n";
                $pattern .= " Passenger Phone: ".$passengerinfo->phone."\n";
                $pattern .= " Driver Name: ".$request->driver_name."\n";
                $pattern .= " Passenger Phone: ".$request->driver_phone."\n";
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

    private function getGeocodeURL($address) {
        $key = Config::get('app.google_maps_server_key');
        $key = 'AIzaSyBRpT5YM5zkopiPlvtK2E-uDCthDIzB_Jk';
        return 'https://maps.google.com/maps/api/geocode/json?sensor=false&key=' . $key . '&address=' . $address;

    }

    private function GetEnterpriseClient($is_agent) {
        $user_id = 0;
        if ($is_agent == 1) {
            $healthcare_agent = DispatcherAgent::find($agent_id);
            $user_id = $healthcare_agent->healthcare_id;
        }
        else {
            $user_id = Session::get('user_id');
        }

        return(EnterpriseClient::find($user_id));
    }

    /*public function GetFaxListing(){
        fax_listing();
    }
    public function SendFax(){
        create_fax();
    }*/
}