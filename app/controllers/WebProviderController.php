<?php
class WebProviderController extends \BaseController {
    /**
     * Display a listing of the resource.
     *
     *
     * @return Response
     */
    public function __construct() {
        if (Config::get('app.production')) {
            echo "Something cool is going to be here soon.";
            die();
        }
        $this->beforeFilter(function() {
            if (!Session::has('walker_id')) {
                return Redirect::to('/provider/signin');
            } else {
                $walker_id = Session::get('walker_id');
                $walker = Walker::find($walker_id);
                Session::put('is_approved', $walker->is_approved);
                Session::put('walker_name', $walker->contact_name);
                Session::put('walker_pic', $walker->picture);
            }
        }, array('except' => array(
            'providerLogin',
            'providerVerify',
            'providerForgotPassword',
            'providerRegister',
            'providerSave',
            'providerActivation',
            'surroundingCars',
        )));
    }
    public function index() {
        return Redirect::to('/provider/signin');
    }
    public function toggle_availability() {
        $walker_id = Session::get('walker_id');
        $walker = Walker::find($walker_id);
        $walker->is_active = ($walker->is_active + 1 ) % 2;
        $walker->save();
    }
    public function set_location() {
        $walker_id = Session::get('walker_id');
        $walker = Walker::find($walker_id);
        $location = get_location(Input::get('lat'), Input::get('lng'));
        $latitude = $location['lat'];
        $longitude = $location['long'];
        $angle = get_angle($walker->latitude, $walker->longitude, $latitude, $longitude);
        $walker->old_latitude = $walker->latitude;
        $walker->old_longitude = $walker->longitude;
        $walker->latitude = $latitude;
        $walker->longitude = $longitude;
        $walker->bearing = $angle;
        $walker->save();
    }
    public function providerRequestPing() {
        Session::forget('skipReviewProvider');
        $walker_id = Session::get('walker_id');
        $time = date("Y-m-d H:i:s");
        $query = "SELECT id,latitude,longitude,owner_id,TIMESTAMPDIFF(SECOND,request_start_time, '$time') as diff from request where is_cancelled = 0 and status = 0 and current_walker=$walker_id and TIMESTAMPDIFF(SECOND,request_start_time, '$time') <= 600 limit 1";
        $requests = DB::select(DB::raw($query));
        $request_data = array();
        foreach ($requests as $request) {
            $request_data['success'] = "true";
            $request_data['request_id'] = $request->id;
            $request_data['time_left_to_respond'] = 600 - $request->diff;
            $owner = Owner::find($request->owner_id);
            $request_data['owner'] = array();
            $request_data['owner']['name'] = $owner->contact_name;
            $request_data['owner']['picture'] = $owner->picture;
            $request_data['owner']['phone'] = $owner->phone;
            $request_data['owner']['address'] = $owner->address;
            $request_data['owner']['latitude'] = $request->latitude;
            $request_data['owner']['longitude'] = $request->longitude;
            $request_data['owner']['rating'] = $owner->rate;
            $request_data['owner']['num_rating'] = $owner->rate_count;
            /* $request_data['owner']['rating'] = DB::table('review_dog')->where('owner_id', '=', $owner->id)->avg('rating') ? : 0;
              $request_data['owner']['num_rating'] = DB::table('review_dog')->where('owner_id', '=', $owner->id)->count(); */
            $request_data['dog'] = array();
            if ($dog = Dog::find($owner->dog_id)) {
                $request_data['dog']['name'] = $dog->name;
                $request_data['dog']['age'] = $dog->age;
                $request_data['dog']['breed'] = $dog->breed;
                $request_data['dog']['likes'] = $dog->likes;
                $request_data['dog']['picture'] = $dog->image_url;
            }
        }
        $response_code = 200;
        $response = Response::json($request_data, $response_code);
        return $response;
    }
    public function providerLogin() {
        if (Session::has('walker_id')) {
            $walker_id = Session::get('walker_id');
            $walker = Walker::find($walker_id);
            Session::put('is_approved', $walker->is_approved);
            Session::put('walker_name', $walker->contact_name);
            Session::put('walker_pic', $walker->picture);
            return Redirect::to('learningcenter/learning');
        }
        return View::make('webprovider.providerLogin');
    }
    public function providerActivation($act) {
        //verify the email activation
        if ($act) {
            $get_token = Walker::where('activation_code', '=', $act)->first();
            if ($get_token) {
                if ($get_token->email_activation == 1) {
                    return View::make('webprovider.providerLogin')->with('success', 'Your Email already activated, Please Login');
                } else {
                    $walker = Walker::find($get_token->id);
                    $walker->email_activation = 1;
                    $walker->save();
                    if ($walker->save()) {
                        return View::make('web.providerLogin')->with('success', 'Your Email is activated, Please Login');
                    } else {
                        return View::make('web.providerLogin')->with('error', 'Something Went Wrong');
                    }
                }
            } else {
                return View::make('web.providerLogin')->with('error', 'Something Went Wrong');
            }
        } else {
            return Redirect::to('provider/signup');
        }
    }
    public function providerRegister() {
        $types = '2';
        return View::make('webprovider.providerSignup')->with('types', $types);
    }
    public function providerSave() {
        $contact_name = Input::get('contact_name');
        $email = Input::get('email');
        $phone = Input::get('phone');
        $password = Input::get('password');
        $type = Input::get('type');
        $company_name = Input::get('company_name');
        $device_token = 0;
        $bio = "";
        error_log($company_name);
        $validator = Validator::make(
            array(
                'contact_name' => $contact_name,
                'email' => $email,
                'type' => $type,
                'password' => $password,
                'company_name' => $company_name
            ), array(
            'password' => 'required',
            'email' => 'required',
            'contact_name' => 'required',
            'company_name' => 'required'
        ), array(
                'password' => 'Password field is required.',
                'email' => 'Email field is required',
                'contact_name' => 'Name field is required.',
                'company_name' => 'Company Name field is required.'
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
        if ($validator->fails()) {
            $error_messages = $validator->messages();
            return Redirect::to('provider/signup')->with('error', 'Please Fill all the fields.');
        } else if ($validator1->fails()) {
            return Redirect::to('provider/signup')->with('error', 'Please Enter email correctly.');
        } else if ($validatorPhone->fails()) {
            return Redirect::to('user/signup')->with('error', 'Invalid Phone Number Format');
        } else {
            if (Walker::where('email', $email)->count() == 0) {
                $activation_code = uniqid();
                $walker = new Walker;
                $walker->contact_name = $contact_name;
                $walker->email = $email;
                $walker->phone = "+1".$phone;
                $walker->activation_code = $activation_code;
                $walker->email_activation = 1;
                $walker->transportation_provider_id = $company_name;
                if ($password != "") {
                    $walker->password = Hash::make($password);
                }
                $walker->token = generate_token();
                $walker->token_expiry = generate_expiry();
                $walker->type = $type;
                // upload image
                $file_name = time();
                $file_name .= rand();
                $file_name = sha1($file_name);
                $s3_url = "";
                if (Input::hasfile('picture')) {
                    $ext = Input::file('picture')->getClientOriginalExtension();
                    Input::file('picture')->move(public_path('image') . "/uploads", $file_name . "." . $ext);
                    $local_url = $file_name . "." . $ext;
                    Log::info('picture = ' . print_r($local_url, true));
                    // Upload to S3
                    if (Config::get('app.s3_bucket') != "") {
                        $s3 = App::make('aws')->get('s3');
                        $s3->putObject(array(
                            'Bucket' => Config::get('app.s3_bucket'),
                            'Key' => "uploads/driver_profile_images/" . $file_name,
                            'SourceFile' => public_path('image') . "/uploads/" . $local_url,
                        ));
                        $s3->putObjectAcl(array(
                            'Bucket' => Config::get('app.s3_bucket'),
                            'Key' => "uploads/driver_profile_images/" . $file_name,
                            'ACL' => 'public-read'
                        ));
                        $final_file_name = "uploads/driver_profile_images/" . $file_name;
                        $s3_url = $s3->getObjectUrl(Config::get('app.s3_bucket'), $final_file_name);
                        $localfile = public_path('image') . "/uploads/" . $local_url;
                        unlink_image($localfile);
                        Log::info('s3url = ' . print_r($s3_url, true));
                    } else {
                        $s3_url = asset_url() . '/uploads/' . $local_url;
                    }
                } else{
                    $s3_url = "NA";
                }
                $walker->picture = $s3_url;
                $walker->device_token = $device_token;
                $walker->bio = $bio;
                $walker->is_available = 1;
                $walker->is_active = 0;
                $walker->is_approved = 0;
                if (Input::has('timezone')) {
                    $walker->timezone = Input::get('timezone');
                }
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
                $login_url = web_url() . "/provider/signin";
                $settings = Settings::where('key', 'admin_email_address')->first();
                $admin_email = $settings->value;
                $pattern = array('admin_email' => $admin_email, 'name' => ucwords($walker->contact_name),
                    'web_url' => $login_url);
                $subject = "Welcome to " . ucwords(Config::get('app.website_title')) . ", " . ucwords($walker->contact_name) . "";
                email_notification($walker->id, 'walker', $pattern, $subject, 'walker_register', "imp");
                return Redirect::to('provider/signin')->with('success', 'You have successfully registered.');
            } else {
                return Redirect::to('provider/signup')->with('error', 'This email ID is already registered.');
            }
        }
    }
    public function providerForgotPassword() {
        $email = Input::get('email');
        $walker = Walker::where('email', $email)->first();
        if ($walker) {
            $new_password = time();
            $new_password .= rand();
            $new_password = sha1($new_password);
            $new_password = substr($new_password, 0, 8);
            $walker->password = Hash::make($new_password);
            $walker->save();
            // send email
            /* $subject = "Your New Password";
              $email_data = array();
              $email_data['password'] = $new_password;
              send_email($walker->id, 'walker', $email_data, $subject, 'forgotpassword'); */
            $settings = Settings::where('key', 'admin_email_address')->first();
            $admin_email = $settings->value;
            $login_url = web_url() . "/provider/signin";
            $pattern = array('name' => ucwords($walker->contact_name), 'admin_email' => $admin_email, 'new_password' => $new_password, 'login_url' => $login_url);
            $subject = "Your New Password";
            email_notification($walker->id, 'walker', $pattern, $subject, 'forgot_password', 'imp');
            // echo $pattern;
            return Redirect::to('provider/signin')->with('success', 'password reseted successfully. Please check your inbox for new password.');
        } else {
            return Redirect::to('provider/signin')->with('error', 'This email ID is not registered with us');
        }
    }
    public function providerVerify() {
        $email = Input::get('email');
        $password = Input::get('password');
        $walker = Walker::where('email', '=', $email)->first();
        Session::forget('admin_id');
        if ($walker) {
            if ($walker->is_approved == 1) {
                if ($walker && Hash::check($password, $walker->password)) {
                    Session::put('walker_id', $walker->id);
                    Session::put('is_approved', $walker->is_approved);
                    Session::put('walker_name', $walker->contact_name);
                    Session::put('walker_pic', $walker->picture);
                    return Redirect::to('learningcenter/learningstep1');
                } else {
                    return Redirect::to('provider/signin')->with('error', 'Invalid email and password');
                }
            } else {
                return Redirect::to('provider/signin')->with('error', 'Your Account is in pending approval');
            }
        } else {
            return Redirect::to('provider/signin')->with('error', 'Invalid email');
        }
    }
    public function providerLogout() {
        Session::flush();
        return Redirect::to('/provider/signin');
    }
    public function providerTripChangeState() {
        $date = date("Y-m-d H:i:s");
        $time_limit = date("Y-m-d H:i:s", strtotime($date) - (3 * 60 * 60));
        $walker_id = Session::get('walker_id');
        $state = $request_id = Request::segment(4);
        $current_request = RideRequest::where('confirmed_walker', $walker_id)
            ->where('is_cancelled', 0)
            ->where('is_dog_rated', 0)
            ->where('created_at', '>', $time_limit)
            ->orderBy('created_at', 'desc')
            ->where(function($query) {
                $query->where('status', 0)->orWhere(function($query_inner) {
                    $query_inner->where('status', 1)
                        ->where('is_dog_rated', 0);
                });
            })
            ->first();
        if ($current_request && $state) {
            if ($state == 2) {
                $current_request->is_walker_started = 1;
                $owner = Owner::find($current_request->owner_id);
                $walker = Walker::find($walker_id);
                $location = get_location($current_request->latitude, $current_request->longitude);
                $latitude = $location['lat'];
                $longitude = $location['long'];
                $angle = get_angle($walker->latitude, $walker->longitude, $latitude, $longitude);
                $walker->old_latitude = $walker->latitude;
                $walker->old_longitude = $walker->longitude;
                $walker->latitude = $latitude;
                $walker->longitude = $longitude;
                $walker->bearing = $angle;
                $walker->save();
                $walk_location = new WalkLocation;
                $walk_location->request_id = $current_request->id;
                $walk_location->latitude = $latitude;
                $walk_location->longitude = $longitude;
                $walk_location->distance = 0;
                $walk_location->save();
            }
            if ($state == 3) {
                $current_request->is_walker_arrived = 1;
            }
            if ($state == 4) {
                $current_request->is_started = 1;
            }
            if ($state == 6) {
                $rating = 0;
                if (Input::has('rating')) {
                    $rating = Input::get('rating');
                }
                $current_request->is_dog_rated = 1;
                $current_request->save();
                $review_dog = new DogReview;
                $review_dog->walker_id = $current_request->confirmed_walker;
                $review_dog->comment = Input::get('review');
                $review_dog->rating = $rating;
                $review_dog->owner_id = $current_request->owner_id;
                $review_dog->request_id = $current_request->id;
                $review_dog->save();
                if ($rating) {
                    if ($owner = Owner::find($current_request->owner_id)) {
                        $old_rate = $owner->rate;
                        $old_rate_count = $owner->rate_count;
                        $new_rate_counter = ($owner->rate_count + 1);
                        $new_rate = (($owner->rate * $owner->rate_count) + $rating) / $new_rate_counter;
                        $owner->rate_count = $new_rate_counter;
                        $owner->rate = $new_rate;
                        $owner->save();
                    }
                }
                $message = "You has successfully rated the owner.";
                $type = "success";
                return Redirect::to('/provider/trips')->with('message', $message)->with('type', $type);
            }
            if ($state == 5) {
                $request_services = RequestServices::where('request_id', $current_request->id)->first();
                $request_typ = ProviderType::where('id', '=', $request_services->type)->first();
                $address = urlencode(Input::get('address'));
                $end_address = json_decode(file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?address=$address"), TRUE);
                $end_location = $end_address['results'][0]['geometry'];
                $latitude = $end_location['location']['lat'];
                $longitude = $end_location['location']['lng'];
                $location = get_location($latitude, $longitude);
                $latitude = $location['lat'];
                $longitude = $location['long'];
                $request_id = $current_request->id;
                $walk_location_last = WalkLocation::where('request_id', $request_id)->orderBy('created_at', 'desc')->first();
                if ($walk_location_last) {
                    $distance_old = $walk_location_last->distance;
                    $distance_new = distanceGeoPoints($walk_location_last->latitude, $walk_location_last->longitude, $latitude, $longitude);
                    $distance = $distance_old + $distance_new;
                    $settings = Settings::where('key', 'default_distance_unit')->first();
                    $unit = $settings->value;
                    $distance = $distance;
                } else {
                    $distance = 0;
                }
                $walker = Walker::find($walker_id);
                $angle = get_angle($walker->latitude, $walker->longitude, $latitude, $longitude);
                $walker->old_latitude = $walker->latitude;
                $walker->old_longitude = $walker->longitude;
                $walker->latitude = $latitude;
                $walker->longitude = $longitude;
                $walker->bearing = $angle;
                $walker->save();
                $walk_location = new WalkLocation;
                $walk_location->request_id = $request_id;
                $walk_location->latitude = $latitude;
                $walk_location->longitude = $longitude;
                $walk_location->distance = $distance;
                $walk_location->save();
                Walker::where('id', '=', $walker_id)->update(array('is_available' => 1));
                // Calculate Rerquest Stats
                $time = 0;
                $time_query = "SELECT TIMESTAMPDIFF(SECOND,MIN(created_at),MAX(created_at)) as diff
				FROM walk_location where request_id = $current_request->id
				GROUP BY request_id limit 1 ";
                $time_data = DB::select(DB::raw($time_query));
                foreach ($time_data as $time_diff) {
                    $time = $time_diff->diff;
                }
                $time = $time / 60;
                /* TIME CALCULATION REDIRECTED */
                $time = 0;
                /* TIME CALCULATION REDIRECTED END */
                $walker_data = Walker::find($current_request->confirmed_walker);
                $provider_type = ProviderServices::where('type', $walker_data->type)->where('provider_id', $walker_id)->first();
                if ($provider_type == NULL) {
                    /* $settings = Settings::where('key', 'price_per_unit_distance')->first();
                      $price_per_unit_distance = $settings->value;
                      $settings = Settings::where('key', 'price_per_unit_time')->first();
                      $price_per_unit_time = $settings->value;
                      $settings = Settings::where('key', 'base_price')->first();
                      $base_price = $settings->value; */
                    $setbase_distance = $request_typ->base_distance;
                    $base_price = $request_typ->base_price;
                    $price_per_unit_distance = $request_typ->price_per_unit_distance;
                    $price_per_unit_time = $request_typ->price_per_unit_time;
                } else {
                    $setbase_distance = $request_typ->base_distance;
                    $provider_type = ProviderServices::where('type', $walker_data->type)->where('provider_id', $walker_id)->first();
                    $base_price = $provider_type->base_price;
                    $price_per_unit_distance = $provider_type->price_per_unit_distance;
                    $price_per_unit_time = $provider_type->price_per_unit_time;
                }
                $settings = Settings::where('key', 'default_charging_method_for_users')->first();
                $pricing_type = $settings->value;
                $settings = Settings::where('key', 'default_distance_unit')->first();
                $unit = $settings->value;
                $distance = convert($distance, $unit);
                if ($pricing_type == 1) {
                    if ($distance <= $setbase_distance) {
                        $distance_cost = 0;
                    } else {
                        $distance_cost = $price_per_unit_distance * ($distance - $setbase_distance);
                    }
                    $time_cost = $price_per_unit_time * $time;
                    $total = $base_price + $distance_cost + $time_cost;
                } else {
                    $distance_cost = 0;
                    $time_cost = 0;
                    $total = $base_price;
                }
                $current_request->is_completed = 1;
                $current_request->distance = $distance;
                $current_request->time = $time;
                $request_services->base_price = $base_price;
                $request_services->distance_cost = $distance_cost;
                $request_services->time_cost = $time_cost;
                $request_services->total = $total;
                $current_request->total = $total;
                $request_services->save();
                // charge client
                // charge client
                $ledger = Ledger::where('owner_id', $current_request->owner_id)->first();
                if ($ledger) {
                    $balance = $ledger->amount_earned - $ledger->amount_spent;
                    if ($balance > 0) {
                        if ($total > $balance) {
                            $ledger_temp = Ledger::find($ledger->id);
                            $ledger_temp->amount_spent = $ledger_temp->amount_spent + $balance;
                            $ledger_temp->save();
                            $total = $total - $balance;
                        } else {
                            $ledger_temp = Ledger::find($ledger->id);
                            $ledger_temp->amount_spent = $ledger_temp->amount_spent + $total;
                            $ledger_temp->save();
                            $total = 0;
                        }
                    }
                }
                $promo_discount = 0;
                if ($pcode = PromoCodes::where('id', $current_request->promo_code)->where('type', 1)->first()) {
                    $discount = ($pcode->value) / 100;
                    $promo_discount = $total * $discount;
                    $total = $total - $promo_discount;
                    if ($total < 0) {
                        $total = 0;
                    }
                }
                $current_request->total = $total;
                $current_request->save();
                $cod_sett = Settings::where('key', 'cod')->first();
                $allow_cod = $cod_sett->value;
                if ($current_request->payment_mode == 1 and $allow_cod == 1) {
                    // Pay by Cash
                    $current_request->is_paid = 1;
                    //Log::info('allow_cod');
                } elseif ($current_request->payment_mode == 2) {
                    // paypal
                    //Log::info('paypal payment');
                } else {
                    //Log::info('normal payment. Stored cards');
                    // stored cards
                    if ($total == 0) {
                        $current_request->is_paid = 1;
                    } else {
                        $payment_data = Payment::where('owner_id', $current_request->owner_id)->where('is_default', 1)->first();
                        if (!$payment_data)
                            $payment_data = Payment::where('owner_id', $current_request->owner_id)->first();
                        if ($payment_data) {
                            $customer_id = $payment_data->customer_id;
                            $setransfer = Settings::where('key', 'transfer')->first();
                            $transfer_allow = $setransfer->value;
                            if (Config::get('app.default_payment') == 'stripe') {
                                //dd($customer_id);
                                Stripe::setApiKey(Config::get('app.stripe_secret_key'));
                                try {
                                    $charge = Stripe_Charge::create(array(
                                            "amount" => ceil($total * 100),
                                            "currency" => "usd",
                                            "customer" => $customer_id)
                                    );
                                    //Log::info($charge);
                                } catch (Stripe_InvalidRequestError $e) {
                                    // Invalid parameters were supplied to Stripe's API
                                    $ownr = Owner::find($current_request->owner_id);
                                    $ownr->debt = $total;
                                    $ownr->save();
                                    $message = array('error' => $e->getMessage());
                                    $type = "success";
                                    //Log::info($message);
                                    return Redirect::to('/provider/tripinprogress')->with('message', $message)->with('type', $type);
                                }
                                $current_request->is_paid = 1;
                                $settng = Settings::where('key', 'service_fee')->first();
                                $settng_mode = Settings::where('key', 'payment_mode')->first();
                                if ($settng_mode->value == 2 and $transfer_allow == 1) {
                                    $transfer = Stripe_Transfer::create(array(
                                            "amount" => ($total - ($settng->value * $total / 100)) * 100, // amount in cents
                                            "currency" => "usd",
                                            "recipient" => $walker_data->merchant_id)
                                    );
                                    $current_request->transfer_amount = ($total - ($settng->value * $total / 100));
                                }
                            } else {
                                try {
                                    Braintree_Configuration::environment(Config::get('app.braintree_environment'));
                                    Braintree_Configuration::merchantId(Config::get('app.braintree_merchant_id'));
                                    Braintree_Configuration::publicKey(Config::get('app.braintree_public_key'));
                                    Braintree_Configuration::privateKey(Config::get('app.braintree_private_key'));
                                    if ($settng_mode->value == 2 and $transfer_allow == 1) {
                                        $sevisett = Settings::where('key', 'service_fee')->first();
                                        $service_fee = $sevisett->value * $total / 100;
                                        $result = Braintree_Transaction::sale(array(
                                            'amount' => $total - $service_fee,
                                            'paymentMethodNonce' => $customer_id,
                                            'merchantAccountId' => $walker_data->merchant_id,
                                            'options' => array(
                                                'submitForSettlement' => true,
                                                'holdInEscrow' => true,
                                            ),
                                            'serviceFeeAmount' => $service_fee
                                        ));
                                    } else {
                                        $result = Braintree_Transaction::sale(array(
                                            'amount' => $total,
                                            'paymentMethodNonce' => $customer_id
                                        ));
                                    }
                                    if ($result->success) {
                                        $request->is_paid = 1;
                                    } else {
                                        $request->is_paid = 0;
                                    }
                                } catch (Exception $e) {
                                    $message = "Something went wrong in the payment. Please try again.";
                                    $type = "success";
                                    return Redirect::to('/provider/tripinprogress')->with('message', $message)->with('type', $type);
                                }
                            }
                            $current_request->card_payment = $total;
                            $current_request->ledger_payment = $current_request->total - $total;
                        }
                    }
                }
                $current_request->save();
            }
            $current_request->save();
        }
        return Redirect::to('/provider/tripinprogress');
    }
    public function providerTripInProgress() {
        $date = date("Y-m-d H:i:s");
        $time_limit = date("Y-m-d H:i:s", strtotime($date) - (3 * 60 * 60));
        $walker_id = Session::get('walker_id');
        $current_request = RideRequest::where('confirmed_walker', $walker_id)
            ->where('is_cancelled', 0)
            ->where('created_at', '>', $time_limit)
            ->orderBy('created_at', 'desc')
            ->where(function($query) {
                $query->where('status', 0)->orWhere(function($query_inner) {
                    $query_inner->where('status', 1);
                });
            })
            ->first();
        if (!$current_request or Session::has('skipReviewProvider') or $current_request->is_dog_rated == 1) {
            /* $var = Keywords::where('id', 4)->first();
              $message = "You don't have any " . $var->keyword . "s currently in progress."; */
            $message = "You don't have any " . Config::get('app.generic_keywords.Trip') . "s currently in progress.";
            $type = "danger";
            $status = 6;
            return Redirect::to('/provider/trips')->with('message', $message)->with('type', $type)->with('status', $status);
        } else {
            $request_services = RequestServices::where('request_id', $current_request->id)->first();
            $owner = Owner::find($current_request->owner_id);
            $type = ProviderType::find($request_services->type);
            $status = 0;
            if ($current_request->is_dog_rated) {
                $status = 6;
            } elseif ($current_request->is_completed) {
                $status = 5;
            } elseif ($current_request->is_started) {
                $status = 4;
            } elseif ($current_request->is_walker_arrived) {
                $status = 3;
            } elseif ($current_request->is_walker_started) {
                $status = 2;
            } elseif ($current_request->confirmed_walker) {
                $status = 1;
            }
            if ($current_request->confirmed_walker) {
                $walker = Walker::find($current_request->confirmed_walker);
                /* $rating = DB::table('review_dog')->where('owner_id', '=', $current_request->owner_id)->avg('rating') ? : 0; */
                $rating = $owner->rate;
                /* $var = Keywords::where('id', 4)->first(); */
                return View::make('webprovider.providerRequestTripStatus')
                    /* ->with('title', '' . $var->keyword . ' Status')
                      ->with('page', '' . $var->keyword . '-status') */
                    ->with('title', '' . Config::get('app.generic_keywords.Trip') . ' Status')
                    ->with('page', '' . Config::get('app.generic_keywords.Trip') . '-status')
                    ->with('request', $current_request)
                    ->with('user', $owner)
                    ->with('walker', $walker)
                    ->with('type', $type)
                    ->with('status', $status)
                    ->with('rating', $rating);
            }
        }
    }
    public function providerSkipReview() {
        $request_id = Request::segment(3);
        Session::put('skipReviewProvider', 1);
        return Redirect::to('/provider/tripinprogress');
    }
    public function approve_request() {
        $request_id = Request::segment(4);
        $walker_id = Session::get('walker_id');
        $request =RideRequest::find($request_id);
        if ($request->current_walker == $walker_id) {
            // request ended
          RideRequest::where('id', '=', $request_id)->update(array('confirmed_walker' => $walker_id, 'status' => 1, 'request_start_time' => date('Y-m-d H:i:s')));
            // confirm walker
            RequestMeta::where('request_id', '=', $request_id)->where('walker_id', '=', $walker_id)->update(array('status' => 1));
            // Update Walker availability
            Walker::where('id', '=', $walker_id)->update(array('is_available' => 0));
            // remove other schedule_meta
            RequestMeta::where('request_id', '=', $request_id)->where('status', '=', 0)->delete();
            // Send Notification
            $walker = Walker::find($walker_id);
            $walker_data = array();
            $walker_data['contact_name'] = $walker->contact_name;
            $walker_data['phone'] = $walker->phone;
            $walker_data['bio'] = $walker->bio;
            $walker_data['picture'] = $walker->picture;
            $walker_data['latitude'] = $walker->latitude;
            $walker_data['longitude'] = $walker->longitude;
            $walker_data['rating'] = $walker->rate;
            $walker_data['num_rating'] = $walker->rate_count;
            $walker_data['car_model'] = $walker->car_model;
            $walker_data['car_number'] = $walker->car_number;
            /* $walker_data['rating'] = DB::table('review_walker')->where('walker_id', '=', $walker->id)->avg('rating') ? : 0;
              $walker_data['num_rating'] = DB::table('review_walker')->where('walker_id', '=', $walker->id)->count(); */
            $settings = Settings::where('key', 'default_distance_unit')->first();
            $unit = $settings->value;
            $bill = array();
            if ($request->is_completed == 1) {
                $bill['distance'] = convert($request->distance, $unit);
                $bill['time'] = $request->time;
                $bill['base_price'] = $request->base_price;
                $bill['distance_cost'] = $request->distance_cost;
                $bill['time_cost'] = $request->time_cost;
                $bill['total'] = $request->total;
                $bill['is_paid'] = $request->is_paid;
            }
            $response_array = array(
                'success' => true,
                'request_id' => $request_id,
                'status' => $request->status,
                'confirmed_walker' => $request->confirmed_walker,
                'is_walker_started' => $request->is_walker_started,
                'is_walker_arrived' => $request->is_walker_arrived,
                'is_walk_started' => $request->is_started,
                'is_completed' => $request->is_completed,
                'is_walker_rated' => $request->is_walker_rated,
                'walker' => $walker_data,
                'bill' => $bill,
            );
            /* $var = Keywords::where('id', 1)->first();
              $title = "" . $var->keyword . " Accepted"; */
            $title = "" . Config::get('app.generic_keywords.Provider') . " Accepted";
            $message = $response_array;
            send_notifications($request->owner_id, "owner", $title, $message);
            // Send SMS
            $owner = Owner::find($request->owner_id);
            $settings = Settings::where('key', 'sms_when_provider_accepts')->first();
            $pattern = $settings->value;
            $pattern = str_replace('%user%', $owner->contact_name, $pattern);
            $pattern = str_replace('%driver%', $walker->contact_name, $pattern);
            $pattern = str_replace('%driver_mobile%', $walker->phone, $pattern);
            sms_notification($request->owner_id, 'owner', $pattern);
            // Send SMS
            $settings = Settings::where('key', 'sms_request_completed')->first();
            $pattern = $settings->value;
            $pattern = str_replace('%user%', $owner->contact_name, $pattern);
            $pattern = str_replace('%id%', $request->id, $pattern);
            $pattern = str_replace('%user_mobile%', $owner->phone, $pattern);
            sms_notification(1, 'admin', $pattern);
        }
        return Redirect::to('/provider/tripinprogress');
    }
    public function decline_request() {
        $request_id = Request::segment(4);
        $walker_id = Session::get('walker_id');
        $request =RideRequest::find($request_id);
        if ($request->current_walker == $walker_id) {
            // Archiving Old Walker
            RequestMeta::where('request_id', '=', $request_id)->where('walker_id', '=', $walker_id)->update(array('status' => 3));
            $request_meta = RequestMeta::where('request_id', '=', $request_id)->where('status', '=', 0)->orderBy('created_at')->first();
            // update request
            if (isset($request_meta->walker_id)) {
                // assign new walker
              RideRequest::where('id', '=', $request_id)->update(array('current_walker' => $request_meta->walker_id, 'request_start_time' => date("Y-m-d H:i:s")));
                // Send Notification
                $walker = Walker::find($request_meta->walker_id);
                $owner_data = Owner::find($request->owner_id);
                $msg_array = array();
                $msg_array['request_id'] = $request->id;
                $msg_array['id'] = $request_meta->walker_id;
                if ($walker) {
                    $msg_array['token'] = $walker->token;
                }
                $msg_array['client_profile'] = array();
                $msg_array['client_profile']['name'] = $owner_data->contact_name;
                $msg_array['client_profile']['picture'] = $owner_data->picture;
                $msg_array['client_profile']['bio'] = $owner_data->bio;
                $msg_array['client_profile']['address'] = $owner_data->address;
                $msg_array['client_profile']['phone'] = $owner_data->phone;
                $title = "New Request";
                $message = $msg_array;
                send_notifications($request_meta->walker_id, "walker", $title, $message);
            } else {
                // request ended
              RideRequest::where('id', '=', $request_id)->update(array('current_walker' => 0, 'status' => 1));
            }
        }
        return Redirect::to('/provider/trips');
    }
    public function providerTrips() {
        $start_date = Input::get('start_date');
        $end_date = Input::get('end_date');
        $submit = Input::get('submit');
        $start_time = date("Y-m-d H:i:s", strtotime($start_date));
        $end_time = date("Y-m-d H:i:s", strtotime($end_date));
        $start_date = date("Y-m-d", strtotime($start_date));
        $end_date = date("Y-m-d", strtotime($end_date));
        if (!Input::get('start_date') && !Input::get('end_date')) {
            $walker_id = Session::get('walker_id');
            $requests = RideRequest::where('confirmed_walker', $walker_id)
                ->where('is_completed', 1)
                ->leftJoin('walker', 'walker.id', '=', 'request.confirmed_walker')
                ->leftJoin('walker_type', 'walker_type.id', '=', 'walker.type')
                ->leftJoin('owner', 'owner.id', '=', 'request.owner_id')
                ->orderBy('request_start_time', 'desc')
                ->select('request.id', 'request_start_time', 'owner.contact_name', 'request.total as total', 'walker_type.name as type', 'request.distance', 'request.time', 'request.owner_id')
                ->get();
            $total_rides = RideRequest::where('confirmed_walker', $walker_id)
                ->where('is_completed', 1)
                ->count();
            $total_distance = RideRequest::where('confirmed_walker', $walker_id)
                ->where('is_completed', 1)
                ->sum('distance');
            $settings = Settings::where('key', 'default_distance_unit')->first();
            $unit = $settings->value;
            $total_earnings = RideRequest::where('confirmed_walker', $walker_id)
                ->where('is_completed', 1)
                ->sum('total');
            /* $average_rating = DogReview::where('walker_id', $walker_id)
              ->avg('rating'); */
            $rating_avg = Walker::where('id', $walker_id)->first();
            $average_rating = $rating_avg->rate;
        } else {
            $walker_id = Session::get('walker_id');
            $requests = RideRequest::where('confirmed_walker', $walker_id)
                ->where('is_completed', 1)
                ->where('request_start_time', '>=', $start_time)
                ->where('request_start_time', '<=', $end_time)
                ->leftJoin('walker', 'walker.id', '=', 'request.confirmed_walker')
                ->leftJoin('walker_type', 'walker_type.id', '=', 'walker.type')
                ->leftJoin('owner', 'owner.id', '=', 'request.owner_id')
                ->orderBy('request_start_time', 'desc')
                ->select('request.id', 'request_start_time', 'owner.contact_name', 'request.total as total', 'walker_type.name as type', 'request.distance', 'request.time', 'request.owner_id')
                ->get();
            $total_rides = RideRequest::where('confirmed_walker', $walker_id)
                ->where('is_completed', 1)
                ->where('request_start_time', '>=', $start_time)
                ->where('request_start_time', '<=', $end_time)
                ->count();
            $total_distance = RideRequest::where('confirmed_walker', $walker_id)
                ->where('is_completed', 1)
                ->where('request_start_time', '>=', $start_time)
                ->where('request_start_time', '<=', $end_time)
                ->sum('distance');
            $total_earnings = RideRequest::where('confirmed_walker', $walker_id)
                ->where('is_completed', 1)
                ->where('request_start_time', '>=', $start_time)
                ->where('request_start_time', '<=', $end_time)
                ->sum('total');
            /* $average_rating = DogReview::where('walker_id', $walker_id)
              ->where('created_at', '>=', $start_time)
              ->where('created_at', '<=', $end_time)
              ->avg('rating'); */
            $rating_avg = Walker::where('id', $walker_id)->first();
            $average_rating = $rating_avg->rate;
        }
        if (!Input::get('submit') || Input::get('submit') == 'filter') {
            /* $var = Keywords::where('id', 4)->first(); */
            /* $currency = Keywords::where('id', 5)->first(); */
            return View::make('webprovider.providerTrips')
                /* ->with('title', 'My ' . $var->keyword . 's') */
                ->with('title', 'My ' . Config::get('app.generic_keywords.Trip') . 's')
                ->with('requests', $requests)
                ->with('total_rides', $total_rides)
                /* ->with('currency', $currency->keyword) */
                ->with('currency', Config::get('app.generic_keywords.Currency'))
                ->with('total_distance', $total_distance)
                ->with('total_earnings', $total_earnings)
                ->with('average_rating', $average_rating);
        } else {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=data.csv');
            $handle = fopen('php://output', 'w');
            fputcsv($handle, array('Date', 'Customer name', 'Type of Service', 'Distance (Miles)', 'Time (Minutes)', 'Earning'));
            foreach ($requests as $request) {
                fputcsv($handle, array(date('l, F d Y h:i A', strtotime($request->request_start_time)), $request->contact_name, $request->type, $request->distance, $request->time, $request->total));
            }
            fputcsv($handle, array());
            fputcsv($handle, array());
            fputcsv($handle, array('Total Rides', $total_rides));
            fputcsv($handle, array('Total Distance Covered (Miles)', $total_distance));
            fputcsv($handle, array('Average Rating', $average_rating));
            fputcsv($handle, array('Total Earning', $total_earnings));
            fclose($handle);
        }
    }
    public function providerTripDetail() {
        $id = Request::segment(3);
        $walker_id = Session::get('walker_id');
        $request =RideRequest::find($id);
        $request_services = RequestServices::where('request_id', $request->id)->first();
        if ($request->confirmed_walker == $walker_id) {
            $locations = WalkLocation::where('request_id', $id)
                ->orderBy('id')
                ->get();
            $count = round(count($locations) / 50);
            $start = WalkLocation::where('request_id', $id)
                ->orderBy('id')
                ->first();
            $end = WalkLocation::where('request_id', $id)
                ->orderBy('id', 'desc')
                ->first();
            $map = "https://maps-api-ssl.google.com/maps/api/staticmap?size=249x249&style=feature:landscape|visibility:off&style=feature:poi|visibility:off&style=feature:transit|visibility:off&style=feature:road.highway|element:geometry|lightness:39&style=feature:road.local|element:geometry|gamma:1.45&style=feature:road|element:labels|gamma:1.22&style=feature:administrative|visibility:off&style=feature:administrative.locality|visibility:on&style=feature:landscape.natural|visibility:on&scale=2&markers=shadow:false|scale:2|icon:https://d1a3f4spazzrp4.cloudfront.net/receipt-new/marker-start@2x.png|$start->latitude,$start->longitude&markers=shadow:false|scale:2|icon:https://d1a3f4spazzrp4.cloudfront.net/receipt-new/marker-finish@2x.png|$end->latitude,$end->longitude&path=color:0x2dbae4ff|weight:4";
            $skip = 0;
            foreach ($locations as $location) {
                if ($skip == $count) {
                    $map .= "|$location->latitude,$location->longitude";
                    $skip = 0;
                }
                $skip ++;
            }
            $start_location = json_decode(file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?latlng=$start->latitude,$start->longitude"), TRUE);
            $start_address = $start_location['results'][0]['formatted_address'];
            $end_location = json_decode(file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?latlng=$end->latitude,$end->longitude"), TRUE);
            $end_address = $end_location['results'][0]['formatted_address'];
            $owner = Owner::find($request->owner_id);
            $owner_review = DogReview::where('request_id', $id)->first();
            if ($owner_review) {
                $rating = round($owner_review->rating);
            } else {
                $rating = 0;
            }
            /* $var = Keywords::where('id', 4)->first(); */
            /* $currency = Keywords::where('id', 5)->first(); */
            return View::make('webprovider.providerTripDetail')
                /* ->with('title', 'My ' . $var->keyword . 's') */
                ->with('title', 'My ' . Config::get('app.generic_keywords.Trip') . 's')
                ->with('request', $request)
                ->with('request_services', $request_services)
                ->with('start_address', $start_address)
                ->with('end_address', $end_address)
                /* ->with('currency', $currency->keyword) */
                ->with('currency', Config::get('app.generic_keywords.Currency'))
                ->with('start', $start)
                ->with('end', $end)
                ->with('map_url', $map)
                ->with('owner', $owner)
                ->with('rating', $rating);
        } else {
            echo "false";
        }
    }
    public function providerProfile() {
        $walker_id = Session::get('walker_id');
        $user = Walker::find($walker_id);
        $type = ProviderType::where('is_visible', '=', 1)->get();
        return View::make('webprovider.providerProfile')
            ->with('title', 'My Profile')
            ->with('user', $user)
            ->with('type', $type);
    }
    public function updateProviderProfile() {
        $walker_id = Session::get('walker_id');
        $contact_name = Input::get('contact_name');
        $phone = Input::get('phone');
        $picture = Input::file('picture');
        $bio = Input::get('bio');
        $address = Input::get('address');
        $state = Input::get('state');
        $country = Input::get('country');
        $zipcode = Input::get('zipcode');
        $timezone = Input::get('timezone');
        $car_number = trim(Input::get('car_number'));
        $car_model = trim(Input::get('car_model'));
        $validator = Validator::make(
            array(
                'picture' => $picture
            ), array(
            /* 'picture' => 'mimes:jpeg,bmp,png' */
            'picture' => ''
        ), array(
                /* 'picture' => 'mimes:jpeg,bmp,png' */
                'picture' => 'Image is required'
            )
        );
        if ($validator->fails()) {
            $error_messages = $validator->messages();
            //Log::info('picture type not valid. Error = ' . print_r($error_messages, true));
            return Redirect::to('/provider/profile')->with('error', 'Invalid image format (Allowed formats jpeg,bmp and png)');
        } else {
            $walker = Walker::find($walker_id);
            // upload image
            $file_name = time();
            $file_name .= rand();
            $file_name = sha1($file_name);
            $s3_url = "";
            if (Input::hasfile('picture')) {
                $ext = Input::file('picture')->getClientOriginalExtension();
                Input::file('picture')->move(public_path('image') . "/uploads", $file_name . "." . $ext);
                $local_url = $file_name . "." . $ext;
                Log::info('picture = ' . print_r($local_url, true));
                // Upload to S3
                if (Config::get('app.s3_bucket') != "") {
                    $s3 = App::make('aws')->get('s3');
                    $s3->putObject(array(
                        'Bucket' => Config::get('app.s3_bucket'),
                        'Key' => "uploads/driver_profile_images/" . $file_name,
                        'SourceFile' => public_path('image') . "/uploads/" . $local_url,
                    ));
                    $s3->putObjectAcl(array(
                        'Bucket' => Config::get('app.s3_bucket'),
                        'Key' => "uploads/driver_profile_images/" . $file_name,
                        'ACL' => 'public-read'
                    ));
                    $final_file_name = "uploads/driver_profile_images/" . $file_name;
                    $s3_url = $s3->getObjectUrl(Config::get('app.s3_bucket'), $final_file_name);
                    $localfile = public_path('image') . "/uploads/" . $local_url;
                    unlink_image($localfile);
                    Log::info('s3url = ' . print_r($s3_url, true));
                } else {
                    $s3_url = asset_url() . '/uploads/' . $local_url;
                }
            } else{
                $s3_url = "NA";
            }
            $walker->picture = $s3_url;
            if ($car_number != "") {
                $walker->car_number = $car_number;
            }
            if ($car_model != "") {
                $walker->car_model = $car_model;
            }
            $walker->contact_name = $contact_name;
            $walker->phone = $phone;
            $walker->bio = $bio;
            $walker->address = $address;
            $walker->state = $state;
            $walker->country = $country;
            $walker->zipcode = $zipcode;
            $walker->timezone = $timezone;
            $walker->save();
            if (Input::has('services')) {
                $proviserv = ProviderServices::where('provider_id', $walker->id)->first();
                if ($proviserv != NULL) {
                    DB::delete("delete from walker_services where provider_id = '" . $walker->id . "';");
                }
            }
            $base_price = '0.00';
            $service_price= '0.00';
            $service_price_time = '0.00';
            $type_id = Input::get('services');
            $walker->type = $type_id;
            $walker->save();
            $key = Input::get('service');
            $prserv = new ProviderServices;
            $prserv->provider_id = $walker->id;
            $prserv->type = $type_id;
            /* Log::info('key = ' . print_r($key, true)); */
            if (Input::has('service_base_price')) {
                $prserv->base_price = $base_price[$type_id];
            } else {
                $prserv->base_price = 0;
            }
            if (Input::has('service_price_distance')) {
                $prserv->price_per_unit_distance = 0;
            } else {
                $prserv->price_per_unit_distance = 0;
            }
            if (Input::has('service_price_time')) {
                $prserv->price_per_unit_time = $service_price_time[$type_id];
            } else {
                $prserv->price_per_unit_time = 0;
            }
            $prserv->save();
            return Redirect::to('/provider/profile')->with('success', 'Your profile has been updated successfully');
        }
    }
    public function updateProviderPassword() {
        $current_password = Input::get('current_password');
        $new_password = Input::get('new_password');
        $confirm_password = Input::get('confirm_password');
        $walker_id = Session::get('walker_id');
        $walker = Walker::find($walker_id);
        if ($new_password === $confirm_password) {
            if (Hash::check($current_password, $walker->password)) {
                $password = Hash::make($new_password);
                $walker->password = $password;
                $walker->save();
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
        return Redirect::to('/provider/profile')->with('success', $message);
    }
    public function providerDocuments() {
        $walker_id = Session::get('walker_id');
        $documents = Document::all();
        $walker_document = WalkerDocument::where('walker_id', $walker_id)->get();
        $walker = Walker::find($walker_id);
        $status = 0;
        foreach ($documents as $document) {
            if (!$document) {
                $status = -1;
            } else {
                $status = 0;
            }
        }
        if ($walker->is_approved) {
            $status = 1;
        }
        return View::make('webprovider.providerDocuments')
            ->with('title', 'My Documents')
            ->with('documents', $documents)
            ->with('walker_document', $walker_document)
            ->with('status', $status);
    }
    public function providerUpdateDocuments() {
        $inputs = Input::all();
        $walker_id = Session::get('walker_id');
        foreach ($inputs as $key => $input) {
            $walker_document = WalkerDocument::where('walker_id', $walker_id)->where('document_id', $key)->first();
            if (!$walker_document) {
                $walker_document = new WalkerDocument;
            }
            $walker_document->walker_id = $walker_id;
            $walker_document->document_id = $key;
            if ($input) {
                $file_name = time();
                $file_name .= rand();
                $file_name = sha1($file_name);
                $ext = $input->getClientOriginalExtension();
                $input->move(public_path() . "/uploads", $file_name . "." . $ext);
                $local_url = $file_name . "." . $ext;
                // Upload to S3
                if (Config::get('app.s3_bucket') != "") {
                    $s3 = App::make('aws')->get('s3');
                    $pic = $s3->putObject(array(
                        'Bucket' => Config::get('app.s3_bucket'),
                        'Key' => $file_name,
                        'SourceFile' => public_path() . "/uploads/" . $local_url,
                    ));
                    $s3->putObjectAcl(array(
                        'Bucket' => Config::get('app.s3_bucket'),
                        'Key' => $file_name,
                        'ACL' => 'public-read'
                    ));
                    $s3_url = $s3->getObjectUrl(Config::get('app.s3_bucket'), $file_name);
                } else {
                    $s3_url = asset_url() . '/uploads/' . $local_url;
                }
                // send email
                $get = Walker::where('id', '=', $walker_id)->first();
                $pattern = "Hi, " . $get->contact_name . ", ID " . $walker_id . " Uploaded his/her Document and waiting for the admin Approval.";
                $subject = "Waiting for an Approval";
                /* email_notification('', 'admin', $pattern, $subject); */
                if (isset($walker_document->url)) {
                    if ($walker_document->url != "") {
                        $icon = $walker_document->url;
                        unlink_image($icon);
                    }
                }
                $walker_document->url = $s3_url;
                $walker_document->save();
                /* if ($walker_document->save()) {
                  echo 'asdasd';
                  } */
            }
        }
        $message = "Your documents are successfully updated.";
        $type = "success";
        return Redirect::to('/provider/documents')->with('message', $message)->with('type', $type);
    }
    public function provideravailabilitySubmit() {
        $proavis = $_POST['proavis'];
        $proavie = $_POST['proavie'];
        $length = $_POST['length'];
        $provid = Session::get('walker_id');
        //Log::info('Start end time Array Length = ' . print_r($length, true));
        DB::delete("delete from provider_availability where provider_id = '" . $provid . "';");
        for ($l = 0; $l < $length; $l++) {
            $pv = new ProviderAvail;
            $pv->provider_id = $provid;
            $pv->start = $proavis[$l];
            $pv->end = $proavie[$l];
            $pv->save();
        }
        //Log::info('providers availability start = ' . print_r($proavis, true));
        //Log::info('providers availability end = ' . print_r($proavie, true));
        return Response::json(array('success' => true));
    }
    public function provideravailability() {
        if (Session::has('walker_id')) {
            $pavail = ProviderAvail::where('provider_id', Session::get('walker_id'))->get();
            $prvi = array();
            foreach ($pavail as $pv) {
                $prv = array();
                $prv['title'] = 'available';
                $prv['start'] = date('Y-m-d', strtotime($pv->start)) . "T" . date('H:i:s', strtotime($pv->start));
                $prv['end'] = date('Y-m-d', strtotime($pv->end)) . "T" . date('H:i:s', strtotime($pv->end));
                ;
                array_push($prvi, $prv);
            }
            $pvjson = json_encode($prvi);
            //Log::info('Provider availability json = ' . print_r($pvjson, true));
            return View::make('webprovider.provideravailability')->with('pvjson', $pvjson)->with('title', 'Calendar')->with('page', 'yo');
        }
    }
    //create manual request
    public function create_manual_request() {
        $latitude = Input::get('latitude');
        $longitude = Input::get('longitude');
        $d_latitude = Input::get('d_latitude');
        $d_longitude = Input::get('d_longitude');
        $type = Input::get('type');
        $provider = Input::get('provider');
        $user_id = Session::get('user_id');
        $time = date("Y-m-d H:i:s");
        $provider_details = Walker::where('id', '=', $provider)->first();
        $user = Owner::where('id', '=', $user_id)->first();
        $request = new RideRequest;
        $request->owner_id = $user_id;
        $request->request_start_time = $time;
        $request->confirmed_walker = $provider;
        if ($d_longitude != '' && $d_latitude != '') {
            $request->D_latitude = $d_latitude;
            $request->D_longitude = $d_longitude;
        }
        $request->current_walker = $provider;
        $request->status = 1;
        $request->latitude = $latitude;
        $request->longitude = $longitude;
        $request->save();
        $reqid = $request->id;
        $request_service = new RequestServices;
        $request_service->type = $type;
        $request_service->request_id = $request->id;
        $request_service->save();
        $owner = Owner::find($user_id);
        $owner->latitude = $latitude;
        $owner->longitude = $longitude;
        $owner->save();
        $walkerlocation = new WalkLocation;
        $walkerlocation->request_id = $request->id;
        $walkerlocation->distance = 0.00;
        $walkerlocation->latitude = $latitude;
        $walkerlocation->longitude = $longitude;
        $walkerlocation->save();
        if ($request->save()) {
            $current_request = RideRequest::where('id', '=', $reqid)->first();
            return Redirect::to('/user/request-trip');
        }
    }
    // getting near by users
    public function get_nearby() {
        $latitude = Input::get('latitude');
        $longitude = Input::get('longitude');
        $typestring = Input::get('type');
        $settings = Settings::where('key', 'default_search_radius')->first();
        $distance = $settings->value;
        $settings = Settings::where('key', 'default_distance_unit')->first();
        $unit = $settings->value;
        if ($unit == 0) {
            $multiply = 1.609344;
        } elseif ($unit == 1) {
            $multiply = 1;
        }
        if ($typestring == "") {
            $query = "SELECT "
                . "walker.id, "
                . "walker.contact_name, "
                . "walker.latitude, "
                . "walker.longitude, "
                . "ROUND(" . $multiply . " * 3956 * acos( cos( radians('$latitude') ) * "
                . "cos( radians(latitude) ) * "
                . "cos( radians(longitude) - radians('$longitude') ) + "
                . "sin( radians('$latitude') ) * "
                . "sin( radians(latitude) ) ) ,8) as distance "
                . "from walker "
                . "where is_available = 1 and "
                . "is_active = 1 and "
                . "is_approved = 1 and "
                . "ROUND((" . $multiply . " * 3956 * acos( cos( radians('$latitude') ) * "
                . "cos( radians(latitude) ) * "
                . "cos( radians(longitude) - radians('$longitude') ) + "
                . "sin( radians('$latitude') ) * "
                . "sin( radians(latitude) ) ) ) ,8) <= $distance "
                . "order by distance";
        } else {
            $query = "SELECT "
                . "walker.id, "
                . "walker.contact_name, "
                . "walker.latitude, "
                . "walker.longitude, "
                . "ROUND(" . $multiply . " * 3956 * acos( cos( radians('$latitude') ) * "
                . "cos( radians(walker.latitude) ) * "
                . "cos( radians(walker.longitude) - radians('$longitude') ) + "
                . "sin( radians('$latitude') ) * "
                . "sin( radians(walker.latitude) ) ) ,8) as distance "
                . "from walker "
                . "JOIN walker_services "
                . "where walker.is_available = 1 and "
                . "walker.is_active = 1 and "
                . "walker.is_approved = 1 and "
                . "ROUND((" . $multiply . " * 3956 * acos( cos( radians('$latitude') ) * "
                . "cos( radians(walker.latitude) ) * "
                . "cos( radians(walker.longitude) - radians('$longitude') ) + "
                . "sin( radians('$latitude') ) * "
                . "sin( radians(walker.latitude) ) ) ) ,8) <= $distance and "
                . "walker.id = walker_services.provider_id and "
                . "walker_services.type = $typestring "
                . "order by distance";
        }
        $walkers = DB::select(DB::raw($query));
        // return $walkers;
        foreach ($walkers as $key) {
            echo "<option value=" . $key->id . ">" . $key->contact_name . "</option>";
        }
    }
    public function walks_payment() {
        $walker_id = Session::get('walker_id');
        $walks = DB::table('request')
            ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
            ->select(DB::raw('SUM(request.card_payment)as total,COUNT(request.id)as trips,request.created_at,request.id, WEEK(request.created_at,1) as payoutweek'))
            ->where('request.status', '=', 1)
            ->where('request.is_completed', '=', 1)
            ->where('walker.id', '=', $walker_id)
            ->groupBy('payoutweek')
            ->orderBy('request.id', 'desc')
            ->paginate(10);
        $response = Response::json($walks);
        // return $response;
        return View::make('webprovider.providerPayment')
            ->with('title', 'Payment Statement')
            ->with('page', 'payment')
            ->with('walks', $walks);
    }
    public function walkers_payout() {
        Session::forget('che');
        $start = Input::get('start');
        $end = Input::get('end');
        $weekend = Input::get('weekend');
        $walker_id = Session::get('walker_id');
        if ($end == 0) {
            $request = DB::table('request')
                ->where('is_completed', '=', 1)
                ->where('status', '=', 1)
                ->where('id', '>=', $start)->get();
        } else {
            $request = DB::table('request')
                ->whereBetween('id', array($start, $end))
                ->where('is_completed', '=', 1)
                ->where('status', '=', 1)
                ->where('confirmed_walker', '=', $walker_id)
                ->get();
        }
        $driver = '';
        foreach ($request as $walk) {
            $driver.=$walk->confirmed_walker . ',';
        }
        $driver = rtrim($driver, ",");
        $driver = explode(',', $driver);
        if ($end == 0) {
            $subQuery = DB::table('request')
                ->select(DB::raw('count(*)'))
                ->whereRaw('confirmed_walker = walker.id and request.id >="' . $start . '" and status=1 and is_completed=1');
        } else {
            $subQuery = DB::table('request')
                ->select(DB::raw('count(*)'))
                ->whereRaw('id BETWEEN "' . $start . '" AND "' . $end . '"')
                ->whereRaw('confirmed_walker = walker.id and status=1 and is_completed=1');
        }
        $subQuery1 = DB::table('request_meta')
            ->select(DB::raw('count(*)'))
            ->whereRaw('request_id BETWEEN "' . $start . '" AND "' . $end . '"')
            ->whereRaw('walker_id = walker.id and status=1 ');
        if ($end == 0) {
            $subQuery2 = DB::table('request')
                ->select(DB::raw('sum(total)'))
                ->whereRaw('confirmed_walker = walker.id and request.id >="' . $start . '"  and status=1 ');
            $subQuery_card = DB::table('request')
                ->select(DB::raw('sum(card_payment)'))
                ->whereRaw('confirmed_walker = walker.id and request.id >="' . $start . '"  and status=1 ');
            $subQuery_ref = DB::table('request')
                ->select(DB::raw('sum(ledger_payment)'))
                ->whereRaw('confirmed_walker = walker.id and request.id >="' . $start . '"  and status=1 ');
            $subQuery_promo = DB::table('request')
                ->select(DB::raw('sum(promo_payment)'))
                ->whereRaw('confirmed_walker = walker.id and request.id >="' . $start . '"  and status=1 ');
        } else {
            $subQuery2 = DB::table('request')
                ->select(DB::raw('sum(total)'))
                ->whereRaw('id BETWEEN "' . $start . '" AND "' . $end . '"')
                ->whereRaw('confirmed_walker = walker.id and status=1 ');
            $subQuery_card = DB::table('request')
                ->select(DB::raw('sum(card_payment)'))
                ->whereRaw('id BETWEEN "' . $start . '" AND "' . $end . '"')
                ->whereRaw('confirmed_walker = walker.id and status=1 ');
            $subQuery_ref = DB::table('request')
                ->select(DB::raw('sum(ledger_payment)'))
                ->whereRaw('id BETWEEN "' . $start . '" AND "' . $end . '"')
                ->whereRaw('confirmed_walker = walker.id and status=1 ');
            $subQuery_promo = DB::table('request')
                ->select(DB::raw('sum(promo_payment)'))
                ->whereRaw('id BETWEEN "' . $start . '" AND "' . $end . '"')
                ->whereRaw('confirmed_walker = walker.id and status=1 ');
        }
        $subQuery3 = DB::table('request')
            ->select(DB::raw('sum(payment_remaining)'))
            ->whereRaw('id BETWEEN "' . $start . '" AND "' . $end . '"')
            ->whereRaw('confirmed_walker = walker.id and status=1 ');
        $subQuery4 = DB::table('request')
            ->select(DB::raw('sum(refund_remaining)'))
            ->whereRaw('id BETWEEN "' . $start . '" AND "' . $end . '"')
            ->whereRaw('confirmed_walker = walker.id and status=1 ');
        $request = DB::table('request')
            ->select(DB::raw('*'))
            ->whereRaw('id BETWEEN "' . $start . '" AND "' . $end . '"')
            ->whereRaw('confirmed_walker = walker.id and status=1');
        if ($end == 0) {
            $walks = DB::table('request')
                ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
                ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                ->select('owner.contact_name as owner_contact_name', 'walker.contact_name as walker_contact_name', 'owner.id as owner_id', 'walker.id as walker_id', 'request.id as id', 'request.created_at as date', 'request.is_started', 'request.is_walker_arrived', 'request.is_completed', 'request.is_paid', 'request.is_walker_started', 'request.confirmed_walker'
                    , 'request.status', 'walker.type', 'request.request_start_time', 'request.card_payment', 'request.status', 'request.time', 'request.payment_mode as cash_or_card', 'request.distance', 'request.total', 'request.is_cancelled', 'request.payment_remaining', 'request.refund_remaining')
                ->where('walker.id', '=', $walker_id)
                ->where('request.id', '>=', $start)
                ->where('request.status', '=', 1)
                ->orderBy('request.created_at', 'desc')
                ->get();
        } else {
            $walks = DB::table('request')
                ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
                ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                ->select('owner.contact_name as owner_contact_name', 'walker.type', 'walker.contact_name as walker_contact_name', 'owner.id as owner_id', 'walker.id as walker_id', 'request.id as id', 'request.created_at as date', 'request.is_started', 'request.is_walker_arrived', 'request.is_completed', 'request.is_paid', 'request.is_walker_started', 'request.confirmed_walker'
                    , 'request.status', 'walker.type', 'request.request_start_time', 'request.card_payment', 'request.time', 'request.payment_mode as cash_or_card', 'request.distance', 'request.total', 'request.is_cancelled', 'request.payment_remaining', 'request.refund_remaining')
                ->where('walker.id', '=', $walker_id)
                ->where('request.id', '>=', $start)
                ->where('request.id', '<=', $end)
                ->where('request.status', '=', 1)
                ->where('request.is_completed', '=', 1)
                ->orderBy('request.created_at', 'desc')
                ->get();
        }
        $walkers = DB::table('walker')
            ->select('walker.*',
                DB::raw("(" . $subQuery->toSql() . ") as 'total_requests'"),
                DB::raw("(" . $subQuery1->toSql() . ") as 'accepted_requests'"),
                DB::raw("(" . $subQuery2->toSql() . ") as 'totalpayment'"),
                DB::raw("(" . $subQuery_card->toSql() . ") as 'card_pay'"),
                DB::raw("(" . $subQuery_ref->toSql() . ") as 'ref_pay'"),
                DB::raw("(" . $subQuery_promo->toSql() . ") as 'pro_pay'"),
                DB::raw("(" . $subQuery3->toSql() . ") as 'payment_remaining'"),
                DB::raw("(" . $subQuery4->toSql() . ") as 'refund_remaining'"))
            ->whereIn('id', $driver)
            ->where('walker.id', '=', $walker_id)
            ->orderBy('walker.created_at')
            ->get();
        /*echo "<pre>";
        print_r($walkers);
        exit;*/
        if (Input::get('submit1') && Input::get('submit1') == 'Download Report') {
            $pdf = App::make('dompdf');
            $parameterr = array();
            $parameter['title'] = 'Drivers List';
            $parameter['page'] = 'Drivers';
            $parameter['walkers'] = $walkers;
            $parameter['total_requests'] = $walkers;
            $parameter['accepted_requests'] = $walkers;
            $parameter['walks'] = $walks;
            $pdf = PDF::loadView('webprovider.walkers_payout_newpdf', $parameter)->setPaper('legal')->setOrientation('landscape')->setWarnings(false);
            return $pdf->download(ucwords(Config::get('app.website_title')) . '_week_ending' . $weekend . '.pdf');
        }
        return View::make('webprovider.walkers_payout')
            ->with('title', 'Drivers List')
            ->with('page', 'Drivers')
            ->with('walkers', $walkers)
            ->with('total_requests', $walkers)
            ->with('accepted_requests', $walkers)
            ->with('walks', $walks);
    }
}