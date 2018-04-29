<?php

class AdminController extends BaseController {

    public function __construct() {
        $this->beforeFilter(function () {
            if (!Auth::check()) {
                $url = URL::current();
                $routeName = Route::currentRouteName();
                //Log::info('current route =' . print_r(Route::currentRouteName(), true));

                if ($routeName != "AdminLogin" && $routeName != 'admin') {
                    Session::put('pre_admin_login_url', $url);
                }
                return Redirect::to('/admin/login');
            }
        }, array('except' => array('login', 'verify', 'add', 'walker_xml')));
    }

    private function _braintreeConfigure() {
        Braintree_Configuration::environment(Config::get('app.braintree_environment'));
        Braintree_Configuration::merchantId(Config::get('app.braintree_merchant_id'));
        Braintree_Configuration::publicKey(Config::get('app.braintree_public_key'));
        Braintree_Configuration::privateKey(Config::get('app.braintree_private_key'));
    }

    public function add() {
        $user = new Admin;
        $user->username = Input::get('username');
        $user->password = $user->password = Hash::make(Input::get('password'));
        $user->save();
    }

    public function report() {
        /*$braintree_environment = Config::get('app.braintree_environment');
        $braintree_merchant_id = Config::get('app.braintree_merchant_id');
        $braintree_public_key = Config::get('app.braintree_public_key');
        $braintree_private_key = Config::get('app.braintree_private_key');
        $braintree_cse = Config::get('app.braintree_cse');
        $twillo_account_sid = Config::get('app.twillo_account_sid');
        $twillo_auth_token = Config::get('app.twillo_auth_token');
        $twillo_number = Config::get('app.twillo_number');
        $stripe_publishable_key = Config::get('app.stripe_publishable_key');
        $default_payment = Config::get('app.default_payment');
        $stripe_secret_key = Config::get('app.stripe_secret_key');
        $mail_driver = Config::get('mail.mail_driver');
        $email_name = Config::get('mail.from.name');
        $email_address = Config::get('mail.from.address');
        $mandrill_secret = Config::get('services.mandrill_secret');
        /* DEVICE PUSH NOTIFICATION DETAILS */
        $customer_certy_url = Config::get('app.customer_certy_url');
        $customer_certy_pass = Config::get('app.customer_certy_pass');
        $customer_certy_type = Config::get('app.customer_certy_type');
        $provider_certy_url = Config::get('app.provider_certy_url');
        $provider_certy_pass = Config::get('app.provider_certy_pass');
        $provider_certy_type = Config::get('app.provider_certy_type');
        $gcm_browser_key = Config::get('app.gcm_browser_key');
        /* DEVICE PUSH NOTIFICATION DETAILS END */
        /* $install = array(
            /*'braintree_environment' => $braintree_environment,
            'braintree_merchant_id' => $braintree_merchant_id,
            'braintree_public_key' => $braintree_public_key,
            'braintree_private_key' => $braintree_private_key,
            'braintree_cse' => $braintree_cse,
            'twillo_account_sid' => $twillo_account_sid,
            'twillo_auth_token' => $twillo_auth_token,
            'twillo_number' => $twillo_number,
            'stripe_publishable_key' => $stripe_publishable_key,
            'stripe_secret_key' => $stripe_secret_key,
            'mail_driver' => $mail_driver,
            'email_address' => $email_address,
            'email_name' => $email_name,
            'mandrill_secret' => $mandrill_secret,
            'default_payment' => $default_payment,
            /* DEVICE PUSH NOTIFICATION DETAILS */
            /* 'customer_certy_url' => $customer_certy_url,
             'customer_certy_pass' => $customer_certy_pass,
             'customer_certy_type' => $customer_certy_type,
             'provider_certy_url' => $provider_certy_url,
             'provider_certy_pass' => $provider_certy_pass,
             'provider_certy_type' => $provider_certy_type,
             'gcm_browser_key' => $gcm_browser_key,*/
                /* DEVICE PUSH NOTIFICATION DETAILS END */
            /*);*/
        $start_date = Input::get('start_date');
        Session::put('start_date', $start_date);
        $end_date = Input::get('end_date');
        Session::put('end_date', $end_date);
        $submit = Input::get('submit');
        Session::put('submit', $submit);
        $walker_id = Input::get('walker_id');
        Session::put('walker_id', $walker_id);
        $hospital_provider_id = Input::get('hospital_provider_id');
        Session::put('hospital_provider_id', $hospital_provider_id);
        $status = Input::get('status');
        Session::put('status', $status);

        $start_time = date("Y-m-d H:i:s", strtotime($start_date));
        $end_time = date("Y-m-d H:i:s", strtotime($end_date));
        //$start_date = date("Y-m-d", strtotime($start_date));
        //$end_date = date("Y-m-d", strtotime($end_date));

        $query = DB::table('request')
                ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
                ->leftJoin('walker_type', 'walker_type.id', '=', 'request.service_type')
				->leftJoin('dispatcher_assigned', 'request.dispatcher_assigned_id', '=', 'dispatcher_assigned.id')
                ->leftJoin('hospital_providers','hospital_providers.id','=','request.hospital_provider_id');

        if (Input::get('start_date') && Input::get('end_date')) {
            $query = $query->where('request_start_time', '>=', $start_time)
                    ->where('request_start_time', '<=', $end_time);
        }

        if (Input::get('walker_id') && Input::get('walker_id') != 0) {
            $query = $query->where('request.confirmed_walker', '=', $walker_id);
        }

        if (Input::get('hospital_provider_id') && Input::get('hospital_provider_id') != 0) {
            $query = $query->where('request.hospital_provider_id', '=', $hospital_provider_id);
        }

        if (Input::get('status') && Input::get('status') != 0) {
            if ($status == 1) {
                $query = $query->where('request.is_completed', '=', 1);
            } else {
                $query = $query->where('request.is_cancelled', '=', 1);
            }
        } else {

            $query = $query->where(function ($que) {
                $que->where('request.is_completed', '=', 1)
                        ->orWhere('request.is_cancelled', '=', 1);
            });
        }

        $walks = $query->select('request.request_start_time', 'walker_type.name as type', 'request.ledger_payment', 'request.card_payment',
            'owner.contact_name as owner_contact_name', 'dispatcher_assigned.contact_name as contact_name',
            'walker.contact_name as walker_contact_name', 
            'owner.id as owner_id', 'walker.id as walker_id', 'request.id as id', 'request.created_at as date', 'request.is_started',
            'request.is_walker_arrived', 'request.payment_mode', 'request.is_completed', 'request.is_paid', 'request.is_walker_started',
            'request.confirmed_walker', 'request.status', 'request.time', 'request.distance', 'request.total', 'request.is_cancelled',
            'request.promo_payment','hospital_providers.provider_name','request.service_type');
        $walks = $walks->orderBy('id', 'DESC')->paginate(10);

        $query = DB::table('request')
                ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
                ->leftJoin('walker_type', 'walker.type', '=', 'walker_type.id')
				->leftJoin('dispatcher_assigned', 'request.dispatcher_assigned_id', '=', 'dispatcher_assigned.id')
                ->leftJoin('hospital_providers','hospital_providers.id','=','request.hospital_provider_id');

        if (Input::get('start_date') && Input::get('end_date')) {
            $query = $query->where('request_start_time', '>=', $start_time)
                    ->where('request_start_time', '<=', $end_time);
        }

        if (Input::get('walker_id') && Input::get('walker_id') != 0) {
            $query = $query->where('request.confirmed_walker', '=', $walker_id);
        }

        if (Input::get('hospital_provider_id') && Input::get('hospital_provider_id') != 0) {
            $query = $query->where('request.hospital_provider_id', '=', $hospital_provider_id);
        }

        $completed_rides = $query->where('request.is_completed', 1)->count();


        $query = DB::table('request')
                ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
                ->leftJoin('walker_type', 'walker.type', '=', 'walker_type.id')
				->leftJoin('dispatcher_assigned', 'request.dispatcher_assigned_id', '=', 'dispatcher_assigned.id')
                ->leftJoin('hospital_providers','hospital_providers.id','=','request.hospital_provider_id');

        if (Input::get('start_date') && Input::get('end_date')) {
            $query = $query->where('request_start_time', '>=', $start_time)
                    ->where('request_start_time', '<=', $end_time);
        }

        if (Input::get('walker_id') && Input::get('walker_id') != 0) {
            $query = $query->where('request.confirmed_walker', '=', $walker_id);
        }

        if (Input::get('hospital_provider_id') && Input::get('hospital_provider_id') != 0) {
            $query = $query->where('request.hospital_provider_id', '=', $hospital_provider_id);
        }
        $cancelled_rides = $query->where('request.is_cancelled', 1)->count();


        $query = DB::table('request')
                ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
                ->leftJoin('walker_type', 'walker.type', '=', 'walker_type.id')
				->leftJoin('dispatcher_assigned', 'request.dispatcher_assigned_id', '=', 'dispatcher_assigned.id')
                ->leftJoin('hospital_providers','hospital_providers.id','=','request.hospital_provider_id');

        if (Input::get('start_date') && Input::get('end_date')) {
            $query = $query->where('request_start_time', '>=', $start_time)
                    ->where('request_start_time', '<=', $end_time);
        }

        if (Input::get('walker_id') && Input::get('walker_id') != 0) {
            $query = $query->where('request.confirmed_walker', '=', $walker_id);
        }

        if (Input::get('hospital_provider_id') && Input::get('hospital_provider_id') != 0) {
            $query = $query->where('request.hospital_provider_id', '=', $hospital_provider_id);
        }
        //$card_payment = $query->where('request.payment_mode', 0)->where('request.is_completed', 1)->sum('request.card_payment');
        $driver_assigned_rides = $query->where('request.confirmed_walker', '>',0)
            ->where('request.is_completed', '=',0)
            ->where('request.is_cancelled', '=',0)->count();


        $query = DB::table('request')
                ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
                ->leftJoin('walker_type', 'walker.type', '=', 'walker_type.id')
				->leftJoin('dispatcher_assigned', 'request.dispatcher_assigned_id', '=', 'dispatcher_assigned.id')
                ->leftJoin('hospital_providers','hospital_providers.id','=','request.hospital_provider_id');

        if (Input::get('start_date') && Input::get('end_date')) {
            $query = $query->where('request_start_time', '>=', $start_time)
                    ->where('request_start_time', '<=', $end_time);
        }

        if (Input::get('walker_id') && Input::get('walker_id') != 0) {
            $query = $query->where('request.confirmed_walker', '=', $walker_id);
        }

        if (Input::get('hospital_provider_id') && Input::get('hospital_provider_id') != 0) {
            $query = $query->where('request.hospital_provider_id', '=', $hospital_provider_id);
        }

        $wheelchair_rides = $query->where('request.service_type', '=',2)->count();
        $total_payment = $query->where('request.is_paid', '=',1)->sum('request.total');

        //$scheduled_rides = ScheduledRequest::count();

        $query = DB::table('request')
            ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
            ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
            ->leftJoin('walker_type', 'walker.type', '=', 'walker_type.id')
            ->leftJoin('dispatcher_assigned', 'request.dispatcher_assigned_id', '=', 'dispatcher_assigned.id')
            ->leftJoin('hospital_providers','hospital_providers.id','=','request.hospital_provider_id');

        if (Input::get('start_date') && Input::get('end_date')) {
            $query = $query->where('request_start_time', '>=', $start_time)
                ->where('request_start_time', '<=', $end_time);
        }

        if (Input::get('walker_id') && Input::get('walker_id') != 0) {
            $query = $query->where('request.confirmed_walker', '=', $walker_id);
        }

        if (Input::get('hospital_provider_id') && Input::get('hospital_provider_id') != 0) {
            $query = $query->where('request.hospital_provider_id', '=', $hospital_provider_id);
        }
        $gurney_rides = $query->where('request.service_type', '=',4)->count();

        $query = DB::table('request')
            ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
            ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
            ->leftJoin('walker_type', 'walker.type', '=', 'walker_type.id')
            ->leftJoin('dispatcher_assigned', 'request.dispatcher_assigned_id', '=', 'dispatcher_assigned.id')
            ->leftJoin('hospital_providers','hospital_providers.id','=','request.hospital_provider_id');

        if (Input::get('start_date') && Input::get('end_date')) {
            $query = $query->where('request_start_time', '>=', $start_time)
                ->where('request_start_time', '<=', $end_time);
        }

        if (Input::get('walker_id') && Input::get('walker_id') != 0) {
            $query = $query->where('request.confirmed_walker', '=', $walker_id);
        }

        if (Input::get('hospital_provider_id') && Input::get('hospital_provider_id') != 0) {
            $query = $query->where('request.hospital_provider_id', '=', $hospital_provider_id);
        }
        $ambulatory_rides = $query->where('request.service_type', '=',3)->count();


        if (Input::get('submit') && Input::get('submit') == 'Download_Report') {

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
            fputcsv($handle, array('ID', 'Hospital Providers', 'UserName','Driver', 'Type of Service','Date', 'Time', 'Status', 'Amount', 'Payment Status'));
            foreach ($walks as $request) {
                if ($request->provider_name) {
                    $provider_name =  $request->provider_name;
                } else {
                    $provider_name =  "NA";
                }

                if ($request->owner_contact_name!='') {
                    $username =  $request->owner_contact_name;
                }else {
                    $username =  $request->contact_name;
                }

                if ($request->confirmed_walker) {
                    $driver_name = $request->walker_contact_name;
                } else {
                    $driver_name = "Un Assigned";
                }

                if ($request->is_cancelled == 1) {

                    $status = "Cancelled";
                } elseif ($request->is_completed == 1) {
                    $status ="Completed";
                } elseif ($request->is_started == 1) {
                    $status ="Started";
                } elseif ($request->is_walker_arrived == 1) {
                    $status =  "Driver Arrived";
                } elseif ($request->is_walker_started == 1) {
                    $status = "Started";
                }

                if ($request->is_paid == 1) {
                    $payment_status ="Completed";
                } elseif ($request->is_paid == 0 && $request->is_completed == 1) {
                    $payment_status ="Pending";
                } else {
                    $payment_status = "Request Not Completed";
                }

                fputcsv($handle, array(
                    $request->id,
                    $provider_name,
                    $username,
                    $driver_name,
                    $request->type,
                    date("d M Y", strtotime($request->date)),
                    date("g:iA", strtotime($request->date)),
                    $status,
                    sprintf2($request->total, 2),
                    $payment_status
                ));
            }

            fputcsv($handle, array());
            fputcsv($handle, array());
            fputcsv($handle, array('Total Trips', $completed_rides + $cancelled_rides + $driver_assigned_rides));
            fputcsv($handle, array('Completed Trips', $completed_rides));
            fputcsv($handle, array('Cancelled Trips', $cancelled_rides));
            //fputcsv($handle, array('Scheduled Trips', $scheduled_rides));
            fputcsv($handle, array('Total Payments', sprintf2(($total_payment), 2)));
            /*fputcsv($handle, array('Card Payment', sprintf2($card_payment, 2)));
            fputcsv($handle, array('Referral Payment', sprintf2($credit_payment, 2)));
            fputcsv($handle, array('Cash Payment', sprintf2($cash_payment, 2)));
            fputcsv($handle, array('Promotional Payment', sprintf2($promo_payment, 2)));*/

            fclose($handle);

            $headers = array(
                'Content-Type' => 'text/csv',
            );
        } else {
            /* $currency_selected = Keywords::where('alias', 'Currency')->first();
              $currency_sel = $currency_selected->keyword; */
            $currency_sel = Config::get('app.generic_keywords.Currency');
            $walkers = Walker::get();
            $hospital_providers = HospitalProviders::get();
            $title = ucwords(trans('customize.Dashboard'));
            return View::make('dashboard')
                            ->with('title', $title)
                            ->with('page', 'dashboard')
                            ->with('walks', $walks)
                            ->with('hospital_providers', $hospital_providers)
                            ->with('walkers', $walkers)
                            ->with('completed_rides', $completed_rides)
                            ->with('cancelled_rides', $cancelled_rides)
                            ->with('driver_assigned_rides',$driver_assigned_rides)
                            ->with('wheelchair_rides',$wheelchair_rides)
                            ->with('ambulatory_rides',$ambulatory_rides)
                            ->with('gurney_rides',$gurney_rides)
                            //->with('card_payment', $card_payment)
                            //->with('install', $install)
                            ->with('currency_sel', $currency_sel)
                            /*->with('cash_payment', $cash_payment)
                            ->with('promo_payment', $promo_payment)
                            ->with('scheduled_rides', $scheduled_rides)
                            ->with('credit_payment', $credit_payment)*/
                            ->with('total_payment',$total_payment);
        }
    }

    //admin control

    public function admins() {
        Session::forget('type');
        Session::forget('valu');
        $admins = Admin::paginate(10);
        $title = ucwords(trans('customize.admin_control'));
        return View::make('admins')
                        ->with('title', $title)
                        ->with('page', 'settings')
                        ->with('admin', $admins);
    }

    public function add_admin() {
        $admin = Admin::all();
        return View::make('add_admin')
                        ->with('title', 'Add Admin')
                        ->with('page', 'add_admin')
                        ->with('admin', $admin);
    }

    public function add_admin_do() {
        $username = Input::get('username');
        $password = Input::get('password');

        $validator = Validator::make(
                        array(
                    'username' => $username,
                    'password' => $password,
                        ), array(
                    'username' => 'required',
                    'password' => 'required|min:6'
                        ), array(
                    'username' => 'User name is required',
                    'password' => 'Password must be longer then 6 digit.'
                        )
        );

        if ($validator->fails()) {
            $error_messages = $validator->messages()->first();
            Session::put('msg', $error_messages);
            $admin = Admin::all();
            return View::make('add_admin')
                            ->with('title', 'Add Admin')
                            ->with('page', 'add_admin')
                            ->with('admin', $admin);
        } else {

            $admin = new Admin;
            $password = Hash::make(Input::get('password'));
            $admin->username = $username;
            $admin->password = $admin->password = $password;
            $admin->save();
            return Redirect::to("/admin/admins?success=1");
        }
    }

    public function edit_admins() {
        $id = Request::segment(4);
        $success = Input::get('success');
        $admin = Admin::find($id);
        //Log::info("admin = " . print_r($admin, true));
        $title = ucwords('Edit Admin' . " : " . $admin->username);
        if ($admin) {
            return View::make('edit_admin')
                            ->with('title', $title)
                            ->with('page', 'settings')
                            ->with('success', $success)
                            ->with('admin', $admin);
        } else {
            return View::make('notfound')->with('title', 'Error Page Not Found')->with('page', 'Error Page Not Found');
        }
    }

    public function update_admin() {

        $admin = Admin::find(Input::get('id'));
        $username = Input::get('username');
        $old_pass = Input::get('old_password');
        $new_pass = Input::get('new_password');
        $address = Input::get('my_address');
        $latitude = Input::get('latitude');
        $longitude = Input::get('longitude');

        $validator = Validator::make(
                        array(
                    'username' => $username,
                    'old_pass' => $old_pass,
                    'new_pass' => $new_pass,
                        ), array(
                    'username' => 'required',
                    'old_pass' => 'required',
                    'new_pass' => 'required|min:6'
                        ), array(
                    'username' => 'User name is required',
                    'old_pass' => 'Invalid Current password.',
                    'new_pass' => 'Password must be longer then 6 digit.'
                        )
        );

        if ($validator->fails()) {
            $error_messages = $validator->messages()->first();
            Session::put('msg', $error_messages);
            if ($admin) {
                $title = ucwords('Edit Admin' . " : " . $admin->username);
                return View::make('edit_admin')
                                ->with('title', $title)
                                ->with('page', 'admins')
                                ->with('success', '')
                                ->with('admin', $admin);
            } else {
                return View::make('notfound')->with('title', 'Error Page Not Found')->with('page', 'Error Page Not Found');
            }
        } else {

            $admin->username = $username;
            $admin->latitude = $latitude;
            $admin->longitude = $longitude;
            $admin->address = $address;

            if ($new_pass != NULL) {
                $check_pass = Hash::check($old_pass, $admin->password);
                if ($check_pass) {
                    $admin->password = $admin->password = Hash::make($new_pass);
                    //Log::info('admin password changed');
                }
            }
            $admin->save();
            return Redirect::to("/admin/admins");
        }
    }

    public function delete_admin() {
        $id = Request::segment(4);
        $success = Input::get('success');
        $admin = Admin::find($id);
        if ($admin) {
            Admin::where('id', $id)->delete();
            return Redirect::to("/admin/admins?success=1");
        } else {
            return View::make('notfound')->with('title', 'Error Page Not Found')->with('page', 'Error Page Not Found');
        }
    }

    public function banking_provider() {
        $id = Request::segment(4);
        $success = Input::get('success');
        $provider = Walker::find($id);
        if ($provider) {
            $title = ucwords('Banking Details' . " : " . $provider->contact_name);
            if (Config::get('app.default_payment') == 'stripe') {
                return View::make('banking_provider_stripe')
                                ->with('title', $title)
                                ->with('page', 'walkers')
                                ->with('success', $success)
                                ->with('provider', $provider);
            } else {
                return View::make('banking_provider_braintree')
                                ->with('title', $title)
                                ->with('page', 'walkers')
                                ->with('success', $success)
                                ->with('provider', $provider);
            }
        } else {
            return View::make('notfound')->with('title', 'Error Page Not Found')->with('page', 'Error Page Not Found');
        }
    }

    public function providerB_bankingSubmit() {
        $this->_braintreeConfigure();
        $result = new stdClass();
        $result = Braintree_MerchantAccount::create(
                        array(
                            'individual' => array(
                                'firstName' => Input::get('contact_name'),
                                'lastName' => Input::get('lastname'),
                                'email' => Input::get('email'),
                                'phone' => Input::get('phone'),
                                'dateOfBirth' => date('Y-m-d', strtotime(Input::get('dob'))),
                                'ssn' => Input::get('ssn'),
                                'address' => array(
                                    'streetAddress' => Input::get('streetAddress'),
                                    'locality' => Input::get('locality'),
                                    'region' => Input::get('region'),
                                    'postalCode' => Input::get('postalCode')
                                )
                            ),
                            'funding' => array(
                                'descriptor' => 'UberForX',
                                'destination' => Braintree_MerchantAccount::FUNDING_DESTINATION_BANK,
                                'email' => Input::get('bankemail'),
                                'mobilePhone' => Input::get('bankphone'),
                                'accountNumber' => Input::get('accountNumber'),
                                'routingNumber' => Input::get('routingNumber')
                            ),
                            'tosAccepted' => true,
                            'masterMerchantAccountId' => Config::get('app.braintree_merchant_id'),
                            'id' => "taxinow" . Input::get('id')
                        )
        );

        //Log::info('res = ' . print_r($result, true));
        if ($result->success) {
            $pro = Walker::where('id', Input::get('id'))->first();
            $pro->merchant_id = $result->merchantAccount->id;
            $pro->save();
            //Log::info(print_r($pro, true));
            //Log::info('Adding banking details to provider from Admin = ' . print_r($result, true));
            return Redirect::to("/admin/providers");
        } else {
            //Log::info('Error in adding banking details: ' . $result->message);
            return Redirect::to("/admin/providers");
        }
    }

    public function providerS_bankingSubmit() {
        $id = Input::get('id');
        Stripe::setApiKey(Config::get('app.stripe_secret_key'));
        $token_id = Input::get('stripeToken');
        // Create a Recipient
        try {
            $recipient = Stripe_Recipient::create(array(
                        "name" => Input::get('contact_name'),
                        "type" => Input::get('type'),
                        "bank_account" => $token_id,
                        "email" => Input::get('email')
                            )
            );

            //Log::info('recipient = ' . print_r($recipient, true));

            $pro = Walker::where('id', Input::get('id'))->first();
            $pro->merchant_id = $recipient->id;
            $pro->account_id = $recipient->active_account->id;
            $pro->last_4 = $recipient->active_account->last4;
            $pro->save();

            //Log::info('recipient added = ' . print_r($recipient, true));
        } catch (Exception $e) {
            //Log::info('Error in Stripe = ' . print_r($e, true));
        }
        return Redirect::to("/admin/providers");
    }

    public function index() {
        return Redirect::to('/admin/login');
    }

    // Added to create intial admin - remember to remove production deployment
    public function admin_register() {
        return View::make('admins_register');
    }

    public function get_document_types() {
        Session::forget('type');
        Session::forget('valu');
        $types = Document::paginate(10);
        $title = ucwords(trans('customize.Documents')); /* 'Document Types' */
        return View::make('list_document_types')
                        ->with('title', $title)
                        ->with('page', 'document-type')
                        ->with('types', $types);
    }

    public function get_promo_codes() {
        Session::forget('type');
        Session::forget('valu');
        $success = Input::get('success');
        $promo_codes = PromoCodes::paginate(10);
        $title = ucwords(trans('customize.promo_codes')); /* 'Promo Codes' */
        return View::make('list_promo_codes')
                        ->with('title', $title)
                        ->with('page', 'promo_code')
                        ->with('success', $success)
                        ->with('promo_codes', $promo_codes);
    }

    public function searchdoc() {
        $valu = $_GET['valu'];
        $type = $_GET['type'];
        Session::put('valu', $valu);
        Session::put('type', $type);
        if ($type == 'docid') {
            $types = Document::where('id', $valu)->paginate(10);
        } elseif ($type == 'docname') {
            $types = Document::where('name', 'like', '%' . $valu . '%')->paginate(10);
        }
        $title = ucwords(trans('customize.Documents')); /* 'Document Types' */
        return View::make('list_document_types')
                        ->with('title', $title)
                        ->with('page', 'document-type')
                        ->with('types', $types);
    }

    public function delete_document_type() {
        $id = Request::segment(4);
        Document::where('id', $id)->delete();
        return Redirect::to("/admin/document-types");
    }

    public function edit_document_type() {
        $id = Request::segment(4);
        $success = Input::get('success');
        $document_type = Document::find($id);

        if ($document_type) {
            $id = $document_type->id;
            $name = $document_type->name;
            $title = ucwords("Edit Document Types" . " : " . $name);
        } else {
            $id = 0;
            $name = "";
            $title = "Add Document Types";
        }

        return View::make('edit_document_type')
                        ->with('title', $title)
                        ->with('page', 'document-type')
                        ->with('success', $success)
                        ->with('id', $id)
                        ->with('name', $name);
    }

    public function update_document_type() {
        $id = Input::get('id');
        $name = Input::get('name');

        if ($id == 0) {
            $document_type = new Document;
        } else {
            $document_type = Document::find($id);
        }


        $document_type->name = $name;
        $document_type->save();

        return Redirect::to("/admin/document-type/edit/$document_type->id?success=1");
    }

    public function get_provider_types() {

        $settings = Settings::where('key', 'default_distance_unit')->first();
        $success = Input::get('success');
        $unit = $settings->value;
        if ($unit == 0) {
            $unit_set = 'kms';
        } elseif ($unit == 1) {
            $unit_set = 'miles';
        }
        $types = ProviderType::paginate(10);
        $title = ucwords(trans('customize.Provider') . " " . trans('customize.Types')); /* 'Provider Types' */
        return View::make('list_provider_types')
                        ->with('title', $title)
                        ->with('page', 'provider-type')
                        ->with('unit_set', $unit_set)
                        ->with('success', $success)
                        ->with('types', $types);
    }

    public function searchpvtype() {
        $success = Input::get('success');
        $valu = $_GET['valu'];
        $type = $_GET['type'];
        Session::put('valu', $valu);
        Session::put('type', $type);
        if ($type == 'provid') {
            $types = ProviderType::where('id', $valu)->paginate(10);
        } elseif ($type == 'provname') {
            $types = ProviderType::where('name', 'like', '%' . $valu . '%')->paginate(10);
        }
        $settings = Settings::where('key', 'default_distance_unit')->first();
        $unit = $settings->value;
        if ($unit == 0) {
            $unit_set = 'kms';
        } elseif ($unit == 1) {
            $unit_set = 'miles';
        }
        $title = ucwords(trans('customize.Provider') . " " . trans('customize.Types')); /* 'Provider Types' */
        return View::make('list_provider_types')
                        ->with('title', $title)
                        ->with('page', 'provider-type')
                        ->with('success', $success)
                        ->with('unit_set', $unit_set)
                        ->with('types', $types);
    }

    public function delete_provider_type() {
        $id = Request::segment(4);
        ProviderType::where('id', $id)->where('is_default', 0)->delete();
        return Redirect::to("/admin/provider-types");
    }

    public function edit_provider_type() {
        $id = Request::segment(4);
        $success = Input::get('success');
        $providers_type = ProviderType::find($id);
        $settings = Settings::where('key', 'default_distance_unit')->first();
        $unit = $settings->value;
        if ($unit == 0) {
            $unit_set = 'kms';
        } elseif ($unit == 1) {
            $unit_set = 'miles';
        }

        if ($providers_type) {
            $id = $providers_type->id;
            $name = $providers_type->name;
            $is_default = $providers_type->is_default;
            $base_price = $providers_type->base_price;
            $base_distance = $providers_type->base_distance;
            $price_per_unit_distance = $providers_type->price_per_unit_distance;
            $price_per_unit_time = $providers_type->price_per_unit_time;
            $icon = $providers_type->icon;
            $base_price = $providers_type->base_price;
            $max_size = $providers_type->max_size;
            $is_visible = $providers_type->is_visible;
            $title = ucwords("Edit Provider Type" . " : " . $name);
        } else {
            $id = 0;
            $name = "";
            $is_default = "";
            $base_distance = 1;
            $base_price = "";
            $price_per_unit_time = "";
            $price_per_unit_distance = "";
            $icon = "";
            $base_price = '';
            $max_size = '';
            $is_visible = "";
            $title = "Add New Provider Type";
        }

        return View::make('edit_provider_type')
                        ->with('title', $title)
                        ->with('page', 'provider-type')
                        ->with('success', $success)
                        ->with('id', $id)
                        ->with('base_price', $base_price)
                        ->with('base_distance', $base_distance)
                        ->with('max_size', $max_size)
                        ->with('name', $name)
                        ->with('is_default', $is_default)
                        ->with('base_price', $base_price)
                        ->with('icon', $icon)
                        ->with('is_visible', $is_visible)
                        ->with('price_per_unit_time', $price_per_unit_time)
                        ->with('price_per_unit_distance', $price_per_unit_distance)
                        ->with('unit_set', $unit_set);
    }

    public function update_provider_type() {
        $id = Input::get('id');
        $name = ucwords(trim(Input::get('name')));
        $base_distance = trim(Input::get('base_distance'));
        if ($base_distance == "" || $base_distance == 0) {
            $base_distance = 1;
        }
        $base_price = trim(Input::get('base_price'));
        if ($base_price == "" || $base_price == 0) {
            $base_price = 0;
        }
        $distance_price = trim(Input::get('distance_price'));
        if ($distance_price == "" || $distance_price == 0) {
            $distance_price = 0;
        }
        $time_price = trim(Input::get('time_price'));
        if ($time_price == "" || $time_price == 0) {
            $time_price = 0;
        }
        $max_size = trim(Input::get('max_size'));
        if ($max_size == "" || $max_size == 0) {
            $max_size = 0;
        }
        $is_default = Input::get('is_default');
        $is_visible = trim(Input::get('is_visible'));

        if ($is_default) {
            if ($is_default == 1) {
                ProviderType::where('is_default', 1)->update(array('is_default' => 0));
            }
        } else {
            $is_default = 0;
        }


        if ($id == 0) {
            $providers_type = new ProviderType;
        } else {
            $providers_type = ProviderType::find($id);
        }
        if (Input::hasFile('icon')) {
            // Upload File
            $file_name = time();
            $file_name .= rand();
            $ext = Input::file('icon')->getClientOriginalExtension();
            list($width, $height) = getimagesize(Input::file('icon'));
            /* echo "width : " . $width;
              echo "height : " . $height; */
            if ($width == $height && $width >= 300 && $height >= 300) {
                Input::file('icon')->move(public_path() . "/uploads", $file_name . "." . $ext);
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

                if (isset($providers_type->icon)) {
                    if ($providers_type->icon != "") {
                        $icon = $providers_type->icon;
                        unlink_image($icon);
                    }
                }
                $providers_type->icon = $s3_url;
            } else {
                return Redirect::to("/admin/provider-types?success=4");
            }
        }
        if ($base_price <= 0 || $distance_price <= 0 || $time_price <= 0) {
            return Redirect::to("/admin/provider-type/edit/$providers_type->id?success=3");
        }

        $providers_type->name = $name;
        $providers_type->base_distance = $base_distance;
        $providers_type->base_price = $base_price;
        $providers_type->price_per_unit_distance = $distance_price;
        $providers_type->price_per_unit_time = $time_price;
        $providers_type->max_size = $max_size;
        $providers_type->is_default = $is_default;
        $providers_type->is_visible = $is_visible;
        $providers_type->save();

        return Redirect::to("/admin/provider-type/edit/$providers_type->id?success=1");
    }

    public function get_info_pages() {

        $informations = Information::paginate(10);
        $title = ucwords(trans('customize.Information') . " Pages"); /* 'Information Pages' */
        return View::make('list_info_pages')
                        ->with('title', $title)
                        ->with('page', 'information')
                        ->with('informations', $informations);
    }

    public function searchinfo() {
        $valu = $_GET['valu'];
        $type = $_GET['type'];
        Session::put('valu', $valu);
        Session::put('type', $type);
        if ($type == 'infoid') {
            $informations = Information::where('id', $valu)->paginate(10);
        } elseif ($type == 'infotitle') {
            $informations = Information::where('title', 'like', '%' . $valu . '%')->paginate(10);
        }
        $title = ucwords(trans('customize.Information') . " Pages | Search Result"); /* 'Information Pages | Search Result' */
        return View::make('list_info_pages')
                        ->with('title', $title)
                        ->with('page', 'information')
                        ->with('informations', $informations);
    }

    public function delete_info_page() {
        $id = Request::segment(4);
        Information::where('id', $id)->delete();
        return Redirect::to("/admin/informations");
    }

    public function skipSetting() {
        setcookie("skipInstallation", "admincookie", time() + (86400 * 30));
        return Redirect::to("/admin/report/");
    }

    public function edit_info_page() {
        $id = Request::segment(4);
        $success = Input::get('success');
        $information = Information::find($id);
        if ($information) {
            $id = $information->id;
            $title = $information->title;
            $description = $information->content;
            $icon = $information->icon;

            $title_new = str_replace(' ', '_', $title);

            $file = base_path() . "/app/views/website/" . $title . ".blade.php";

            if (file_exists($file)) {
                $fp = fopen($file, "w");
                $body = generate_generic_page_layout($description);
                fwrite($fp, $body);
                fclose($fp);
            } else {
                $success = 2;
            }
            $page_title = ucwords("Edit Information Page" . " : " . $title);
        } else {
            $id = 0;
            $title = "";
            $description = "";
            $icon = "";
            $page_title = "Add Information Page";
        }
        return View::make('edit_info_page')
                        ->with('title', $page_title)
                        ->with('page', 'information')
                        ->with('success', $success)
                        ->with('id', $id)
                        ->with('info_title', $title)
                        ->with('icon', $icon)
                        ->with('description', $description);
    }

    public function update_info_page() {
        $id = Input::get('id');
        $title = Input::get('title');
        $description = Input::get('description');
        if ($id == 0) {
            $information = new Information;
        } else {
            $information = Information::find($id);
        }

        if (Input::hasFile('icon')) {
            // Upload File
            $file_name = time();
            $file_name .= rand();
            $ext = Input::file('icon')->getClientOriginalExtension();
            Input::file('icon')->move(public_path() . "/uploads", $file_name . "." . $ext);
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

            if (isset($information->icon)) {
                if ($information->icon != "") {
                    $icon = $information->icon;
                    unlink_image($icon);
                }
            }
            $information->icon = $s3_url;
        }

        $information->title = $title;
        $information->content = $description;
        $information->save();

        $title_new = str_replace(' ', '_', $title);

        $file = base_path() . "/app/views/website/" . $title . ".blade.php";

        if (!file_exists($file)) {
            $fp = fopen($file, "w");
            $body = generate_generic_page_layout($description);
            fwrite($fp, $body);
            fclose($fp);
        }

        return Redirect::to("/admin/information/edit/$information->id?success=1");
    }

    public function map_view() {
        $settings = Settings::where('key', 'map_center_latitude')->first();
        $center_latitude = $settings->value;
        $settings = Settings::where('key', 'map_center_longitude')->first();
        $center_longitude = $settings->value;
        $title = ucwords(trans('customize.map_view')); /* 'Map View' */
        return View::make('map_view')
                        ->with('title', $title)
                        ->with('page', 'map-view')
                        ->with('center_longitude', $center_longitude)
                        ->with('center_latitude', $center_latitude);
    }

    public function DriverList() {

        $learningCategory = LearningCategory::where('is_active', '=', 1)->get();
        Session::forget('type');
        Session::forget('valu');
        Session::forget('che');
        //$query = "SELECT *,(select count(*) from request_meta where walker_id = walker.id  and status != 0 ) as total_requests,(select count(*) from request_meta where walker_id = walker.id and status=1) as accepted_requests FROM `walker`";
        //$walkers = DB::select(DB::raw($query));
        /* $walkers1 = DB::table('walker')
          ->leftJoin('request_meta', 'walker.id', '=', 'request_meta.walker_id')
          ->where('request_meta.status', '!=', 0)
          ->count();
          $walkers2 = DB::table('walker')
          ->leftJoin('request_meta', 'walker.id', '=', 'request_meta.walker_id')
          ->where('request_meta.status', '=', 1)
          ->count();

          $walkers = Walker::paginate(10); */
        $subQuery = DB::table('request_meta')
                ->select(DB::raw('count(*)'))
                ->whereRaw('walker_id = walker.id and status != 0');
        $subQuery1 = DB::table('request_meta')
                ->select(DB::raw('count(*)'))
                ->whereRaw('walker_id = walker.id and status=1');

        $walkers = DB::table('walker')
                ->leftJoin('transportation_provider','walker.transportation_provider_id','=','transportation_provider.id')
                ->select('transportation_provider.company as transportation_provider_company','walker.*', DB::raw("(" . $subQuery->toSql() . ") as 'total_requests'"), DB::raw("(" . $subQuery1->toSql() . ") as 'accepted_requests'"))->where('deleted_at', '=',NULL)
                /* ->where('walker.is_deleted', 0) */
                ->orderBy('walker.created_at', 'DESC')
                ->paginate(10);
        $title = ucwords(trans('customize.Provider') . 's'); /* 'Providers' */
        return View::make('admin.tp.drivers')
                        ->with('title', $title)
                        ->with('page', 'walkers')
                        ->with('walkers', $walkers)
                        ->with('learningCategory',$learningCategory)/*
          ->with('total_requests', $walkers1)
          ->with('accepted_requests', $walkers2) */;
    }

    //Referral Statistics
    public function referral_details() {
        $owner_id = Request::segment(4);
        $ledger = Ledger::where('owner_id', $owner_id)->first();
        $owners = Owner::where('referred_by', $owner_id)->paginate(10);
        $title = ucwords(trans('customize.User') . 's' . " | Coupon Statistics"); /* 'Owner | Coupon Statistics' */
        return View::make('referred')
                        ->with('page', 'owners')
                        ->with('title', $title)
                        ->with('owners', $owners)
                        ->with('ledger', $ledger);
    }

    // Search Walkers from Admin Panel
    public function searchpv() {
        $valu = $_GET['valu'];
        $type = $_GET['type'];
        Session::put('valu', $valu);
        Session::put('type', $type);
        if ($type == 'provid') {
            /* $walkers = Walker::where('id', $valu)->paginate(10); */
            $subQuery = DB::table('request_meta')
                    ->select(DB::raw('count(*)'))
                    ->whereRaw('walker_id = walker.id and status != 0');
            $subQuery1 = DB::table('request_meta')
                    ->select(DB::raw('count(*)'))
                    ->whereRaw('walker_id = walker.id and status=1');

            $walkers = DB::table('walker')
                ->leftJoin('transportation_provider','walker.transportation_provider_id','=','transportation_provider.id')
                ->select('transportation_provider.company as transportation_provider_company','walker.*',
                        DB::raw("(" . $subQuery->toSql() . ") as 'total_requests'"), DB::raw("(" . $subQuery1->toSql() . ") as 'accepted_requests'"))->where('deleted_at', NULL)
                    /* ->where('walker.is_deleted', 0) */
                    ->where('walker.id', $valu)
                    ->where('walker.deleted_at', '=', NULL)
                    ->paginate(10);
        } elseif ($type == 'pvname') {
            /* $walkers = Walker::where('contact_name', 'like', '%' . $valu . '%')->paginate(10); */
            $subQuery = DB::table('request_meta')
                    ->select(DB::raw('count(*)'))
                    ->whereRaw('walker_id = walker.id and status != 0');
            $subQuery1 = DB::table('request_meta')
                    ->select(DB::raw('count(*)'))
                    ->whereRaw('walker_id = walker.id and status=1');

            $walkers = DB::table('walker')
                ->leftJoin('transportation_provider','walker.transportation_provider_id','=','transportation_provider.id')
                ->select('transportation_provider.company as transportation_provider_company','walker.*',
                        DB::raw("(" . $subQuery->toSql() . ") as 'total_requests'"), DB::raw("(" . $subQuery1->toSql() . ") as 'accepted_requests'"))->where('deleted_at', NULL)
                    /* ->where('walker.is_deleted', 0) */
                    ->where('walker.contact_name', 'like', '%' . $valu . '%')
                    ->where('walker.deleted_at', '=', NULL)
                    ->paginate(10);
        } elseif ($type == 'pvemail') {
            /* $walkers = Walker::where('email', 'like', '%' . $valu . '%')->paginate(10); */
            $subQuery = DB::table('request_meta')
                    ->select(DB::raw('count(*)'))
                    ->whereRaw('walker_id = walker.id and status != 0');
            $subQuery1 = DB::table('request_meta')
                    ->select(DB::raw('count(*)'))
                    ->whereRaw('walker_id = walker.id and status=1');

            $walkers = DB::table('walker')
                ->leftJoin('transportation_provider','walker.transportation_provider_id','=','transportation_provider.id')
                ->select('transportation_provider.company as transportation_provider_company','walker.*',
                        DB::raw("(" . $subQuery->toSql() . ") as 'total_requests'"), DB::raw("(" . $subQuery1->toSql() . ") as 'accepted_requests'"))->where('deleted_at', NULL)
                    /* ->where('walker.is_deleted', 0) */
                    ->where('walker.email', 'like', '%' . $valu . '%')
                    ->where('walker.deleted_at', '=', NULL)
                    ->paginate(10);
        } elseif ($type == 'bio') {
            /* $walkers = Walker::where('bio', 'like', '%' . $valu . '%')->paginate(10); */
            $subQuery = DB::table('request_meta')
                    ->select(DB::raw('count(*)'))
                    ->whereRaw('walker_id = walker.id and status != 0');
            $subQuery1 = DB::table('request_meta')
                    ->select(DB::raw('count(*)'))
                    ->whereRaw('walker_id = walker.id and status=1');

            $walkers = DB::table('walker')
                ->leftJoin('transportation_provider','walker.transportation_provider_id','=','transportation_provider.id')
                ->select('transportation_provider.company as transportation_provider_company','walker.*',
                        DB::raw("(" . $subQuery->toSql() . ") as 'total_requests'"), DB::raw("(" . $subQuery1->toSql() . ") as 'accepted_requests'"))->where('deleted_at', NULL)
                    /* ->where('walker.is_deleted', 0) */
                    ->where('walker.bio', 'like', '%' . $valu . '%')
                    ->where('walker.deleted_at', '=', NULL)
                    ->paginate(10);
        }
        $title = ucwords(trans('customize.Provider') . 's' . " | Search Result"); /* 'Providers | Search Result' */
        return View::make('admin.tp.drivers')
                        ->with('title', $title)
                        ->with('page', 'walkers')
                        ->with('walkers', $walkers);
    }

    public function DriverProfile($id) {
        $success = Input::get("success");
        if(! is_null($success)) {
            Session::put('success', $success);
        }
        $error = Input::get("error");
        if(! is_null($error)) {
            Session::put('error', $error);
        }
        $driver = Walker::find($id);
        $title = "Driver: " . $driver->contact_name;

        return View::make('admin/tp/driver/profile')
            ->with('title', $title)
            ->with('page', 1)
            ->with('driver', $driver);
    }

    public function DriverProfileSave() {
        $id = Input::get("id");
        $driver = Walker::find($id);
        $title = "Driver: " . $driver->contact_name;
        $error = 0;
        $success = 0;

        if(null == $driver) {
            $error = "Invalid Record";
        }
        else {
            $error = $driver->Ingest();
        }

        if(! $error) {
            $ret = $driver->update($driver->IngestedAttributes());
            Log::info("ERROR: $ret");
            $success = 'Driver Profile updated successfully';
        }

        $params = "";
        if($success) {
            $params = "?success=" . $success;
        }
        if($error) {
            $params = "?error=" . $error;
        }
        return Redirect::to("/admin/driver/profile/" . $id . $params);
    }

    public function walkers_xml() {
        date_default_timezone_set('UTC');

        $query = 'SELECT * FROM walker WHERE is_authorize=1 AND is_approved=1 AND is_active=1;';
        $walkers = DB::select(DB::raw($query));

        Log::info("Count: " . sizeof($walkers));

        $response = '<markers>';
        $walker_ids = array();
        $time = time() - 300;
        foreach ($walkers as $walker) {
            if($walker->is_available == 1) {
                $lastseen = strtotime($walker->lastseen . "+0000 UTC");
                Log::info("lastseen: " . $lastseen . ", time: " .$time);
                if($lastseen < $time) {
                    continue;
                }
            }
            
            $response .= '<marker ';
            $response .= 'name="' . $walker->contact_name . '" ';
            $response .= 'client_name="' . $walker->contact_name . '" ';
            $response .= 'contact="' . $walker->phone . '" ';
            $response .= 'amount="' . 0 . '" ';
            $response .= 'angl="' . $walker->bearing . '" ';
            $response .= 'lat="' . $walker->latitude . '" ';
            $response .= 'lng="' . $walker->longitude . '" ';
            $response .= 'id="' . $walker->id . '" ';
            if ($walker->is_available == 1) {
                $response .= 'type="driver_free" ';
                array_push($walker_ids, $walker->id);
            } else if ($walker->is_available == 0) {
                $response .= 'type="driver_on_trip" ';
                array_push($walker_ids, $walker->id);
            }
            $response .= '/>';
        }
        $response .= "</markers>";
        $content = View::make('walkers_xml')->with('response', $response);
        return Response::make($content, '200')->header('Content-Type', 'text/xml');
    }

    public function owners() {
        Session::forget('type');
        Session::forget('valu');
        $owners = Owner::orderBy('id', 'DESC')->paginate(10);
        $title = ucwords(trans('customize.User') . 's'); /* 'Owners' */
        return View::make('owners')
                        ->with('title', $title)
                        ->with('page', 'owners')
                        ->with('owners', $owners);
    }

    public function searchur() {
        $valu = $_GET['valu'];
        $type = $_GET['type'];
        Session::put('valu', $valu);
        Session::put('type', $type);
        if ($type == 'userid') {
            $owners = Owner::where('id', $valu)->paginate(10);
        } elseif ($type == 'username') {
            $owners = Owner::where('contact_name', 'like', '%' . $valu . '%')->paginate(10);
        } elseif ($type == 'useremail') {
            $owners = Owner::where('email', 'like', '%' . $valu . '%')->paginate(10);
        } elseif ($type == 'useraddress') {
            $owners = Owner::where('address', 'like', '%' . $valu . '%')->orWhere('state', 'like', '%' . $valu . '%')->orWhere('country', 'like', '%' . $valu . '%')->paginate(10);
        }
        $title = ucwords(trans('customize.User') . "s" . " | Search Result"); /* 'Owners | Search Result' */
        return View::make('owners')
                        ->with('title', $title)
                        ->with('page', 'owners')
                        ->with('owners', $owners);
    }

    public function walks() {
        Session::forget('type');
        Session::forget('valu');
        $walks = DB::table('request')
                ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
                ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
				->leftJoin('dispatcher_assigned', 'request.dispatcher_assigned_id', '=', 'dispatcher_assigned.id')
                ->leftJoin('hospital_providers','hospital_providers.id','=','request.hospital_provider_id')
                ->select('owner.contact_name as owner_contact_name', 
				'dispatcher_assigned.contact_name as contact_name', 
				'walker.contact_name as walker_contact_name', 'owner.id as owner_id', 'walker.id as walker_id', 'walker.merchant_id as walker_merchant', 'request.id as id', 'request.created_at as date', 'request.payment_mode', 'request.is_started', 'request.is_walker_arrived', 'request.payment_mode', 'request.is_completed', 'request.is_paid', 'request.is_walker_started', 'request.confirmed_walker'
                        , 'request.status', 'request.time', 'request.distance', 'request.total', 'request.is_cancelled', 'request.transfer_amount','hospital_providers.provider_name')
                ->orderBy('request.created_at', 'DESC')
                ->paginate(10);
        $setting = Settings::where('key', 'paypal')->first();
        $title = ucwords(trans('customize.Request') . 's'); /* 'Request' */
        return View::make('walks')
                        ->with('title', $title)
                        ->with('page', 'walks')
                        ->with('walks', $walks)
                        ->with('setting', $setting);
    }

    public function HealthcareRequest() {
        Session::forget('type');
        Session::forget('valu');

        $walks = DB::table('request')
            ->leftJoin('ride_details', 'request.id','=','ride_details.request_id')
            ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
            ->leftJoin('dispatcher_assigned', 'request.dispatcher_assigned_id', '=', 'dispatcher_assigned.id')
            ->leftJoin('enterprise_client', 'request.healthcare_id', '=', 'enterprise_client.id')
            ->leftJoin('walker_type','walker_type.id','=','request.service_type')
            ->leftJoin('healthcare_documents','healthcare_documents.request_id','=','request.id')
            ->leftJoin('hospital_providers','hospital_providers.id','=','request.hospital_provider_id')
            ->select('dispatcher_assigned.contact_name as owner_contact_name',
                'dispatcher_assigned.phone as user_phone_no',
                'owner.contact_name as owner_contact_name',
                'owner.phone as ownerphone',
                'request.id as id', 'request.request_start_time as date',
                'request.payment_mode','request.is_started', 'request.is_walker_arrived',
                'request.is_completed', 'request.is_paid',
                'request.is_walker_started', 'request.status', 'request.time',
                'request.distance',
                'request.total', 'request.is_cancelled',
                'request.transfer_amount','walker_type.name',
                'request.promo_payment','request.additional_fee','hospital_providers.provider_name',
                'request.driver_name','request.driver_phone',
                'request.is_confirmed',
                'enterprise_client.contact_name as requester_name',
                'enterprise_client.company as provider_company',
                'ride_details.oxygen_mask as oxygen_mask',
                'ride_details.height as user_height',
                'ride_details.weight as user_weight',
                'ride_details.condition as user_condition',
                'ride_details.respirator as respirator',
                'ride_details.any_tubing as any_tubing',
                'ride_details.colostomy_bag as colostomy_bag',
                'ride_details.any_attachments as any_attachments',
                'request.attendant_travelling as attendant_travelling',
                'src_address','dest_address','is_wheelchair_request',
                'agent_contact_name','healthcare_documents.document_url','request.attendant_travelling')
            ->orderBy('request.id', 'DESC')
            ->paginate(10);

        $setting = Settings::where('key', 'paypal')->first();
        $title = "Healthcare Request";
        return View::make('healthcarewalks')
            ->with('title', $title)
            ->with('page', 'healthcare')
            ->with('walks', $walks)
            ->with('setting', $setting);
    }

    public function DispatcherRequest() {
        Session::forget('type');
        Session::forget('valu');

        $walks = DB::table('request')
            ->leftJoin('dispatcher_assigned', 'request.dispatcher_assigned_id', '=',
                'dispatcher_assigned.id')
            ->leftJoin('walker_type','walker_type.id','=','request.service_type')
            ->leftJoin('dispatcher','dispatcher.id','=','request.dispatcher_id')
            ->select('dispatcher_assigned.contact_name as owner_contact_name',
                'dispatcher_assigned.phone as user_phone_no','request.id as id', 'request.request_start_time as date',
                'request.payment_mode','request.is_started','request.is_walker_arrived',
                'request.is_completed', 'request.is_paid',
                'request.is_walker_started', 'request.status', 'request.time',
                'request.distance',
                'request.total', 'request.is_cancelled',
                'request.transfer_amount','walker_type.name',
                'request.promo_payment','request.additional_fee',
                'request.driver_name','request.driver_phone',
                'request.is_confirmed',
                'dispatcher.contact_name as dispatcher_firstname',
                'src_address','dest_address')
            ->orderBy('request.id', 'DESC')
            ->where('request.is_manual', '=', '1')
            ->paginate(10);

        $title = "Dispatcher Request";
        return View::make('dispatcherwalks')
            ->with('title', $title)
            ->with('page', 'dispatcherwalks')
            ->with('walks', $walks);
    }

    public function scheduled_walks() {
        Session::forget('type');
        Session::forget('valu');
        $schedules = DB::table('scheduled_requests')
                ->leftJoin('owner', 'scheduled_requests.owner_id', '=', 'owner.id')
                ->select('owner.contact_name as owner_contact_name', 'owner.id as owner_id', 'scheduled_requests.id as id', 'scheduled_requests.created_at as date', 'scheduled_requests.time_zone', 'scheduled_requests.src_address', 'scheduled_requests.dest_address', 'scheduled_requests.promo_code', 'scheduled_requests.server_start_time', 'scheduled_requests.start_time', 'scheduled_requests.payment_mode')
                ->orderBy('scheduled_requests.server_start_time', 'ASC')
                ->paginate(10);
        $total_schedules = ScheduledRequest::count();
        $setting = Settings::where('key', 'paypal')->first();
        $title = ucwords(trans('customize.Schedules') . " : Total = " . $total_schedules);
        return View::make('schedules')
                        ->with('title', $title)
                        ->with('page', 'schedule')
                        ->with('schedules', $schedules)
                        ->with('setting', $setting);
    }

    // Search Walkers from Admin Panel
    public function searchreq() {
        $valu = $_GET['valu'];
        $type = $_GET['type'];
        Session::put('valu', $valu);
        Session::put('type', $type);
        if ($type == 'reqid') {
            $walks = DB::table('request')
                    ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                    ->leftJoin('walker', 'request.current_walker', '=', 'walker.id')
                    ->groupBy('request.id')
                    ->select('owner.contact_name as owner_contact_name', 'walker.contact_name as walker_contact_name', 'owner.id as owner_id', 'walker.id as walker_id', 'request.id as id', 'request.created_at as date', 'request.*', 'request.is_walker_arrived', 'request.is_completed', 'request.is_paid', 'request.is_walker_started', 'request.confirmed_walker'
                            , 'request.status', 'request.time', 'request.distance', 'request.total', 'request.is_cancelled', 'request.payment_mode')
                    ->where('request.id', $valu)
                    ->orderBy('request.created_at')
                    ->paginate(10);
        } elseif ($type == 'owner') {
            $walks = DB::table('request')
                    ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                    ->leftJoin('walker', 'request.current_walker', '=', 'walker.id')
                    ->groupBy('request.id')
                    ->select('owner.contact_name as owner_contact_name', 'walker.contact_name as walker_contact_name', 'owner.id as owner_id', 'walker.id as walker_id', 'request.id as id', 'request.created_at as date', 'request.*', 'request.is_walker_arrived', 'request.is_completed', 'request.is_paid', 'request.is_walker_started', 'request.confirmed_walker'
                            , 'request.status', 'request.time', 'request.distance', 'request.total', 'request.is_cancelled', 'request.payment_mode')
                    ->where('owner.contact_name', 'like', '%' . $valu . '%')
                    ->orderBy('request.created_at')
                    ->paginate(10);
        } elseif ($type == 'walker') {
            $walks = DB::table('request')
                    ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                    ->leftJoin('walker', 'request.current_walker', '=', 'walker.id')
                    ->groupBy('request.id')
                    ->select('owner.contact_name as owner_contact_name', 'walker.contact_name as walker_contact_name', 'owner.id as owner_id', 'walker.id as walker_id', 'request.id as id', 'request.created_at as date', 'request.*', 'request.is_walker_arrived', 'request.is_completed', 'request.is_paid', 'request.is_walker_started', 'request.confirmed_walker'
                            , 'request.status', 'request.time', 'request.distance', 'request.total', 'request.is_cancelled', 'request.payment_mode')
                    ->where('walker.contact_name', 'like', '%' . $valu . '%')
                    ->orderBy('request.created_at')
                    ->paginate(10);
        } elseif ($type == 'payment') {
            if ($valu == "Stored Cards" || $valu == "cards" || $valu == "Cards" || $valu == "Card") {
                $value = 0;
            } elseif ($valu == "Pay by Cash" || $valu == "cash" || $valu == "Cash") {
                $value = 1;
            } elseif ($valu == "Paypal" || $valu == "paypal") {
                $value = 2;
            }

            $walks = DB::table('request')
                    ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                    ->leftJoin('walker', 'request.current_walker', '=', 'walker.id')
                    ->groupBy('request.id')
                    ->select('owner.contact_name as owner_contact_name', 'walker.contact_name as walker_contact_name', 'owner.id as owner_id', 'walker.id as walker_id', 'request.id as id', 'request.created_at as date', 'request.is_started', 'request.is_walker_arrived', 'request.is_completed', 'request.is_paid', 'request.is_walker_started', 'request.confirmed_walker'
                            , 'request.status', 'request.time', 'request.distance', 'request.total', 'request.is_cancelled', 'request.payment_mode')
                    ->Where('request.payment_mode', $value)
                    ->orderBy('request.created_at')
                    ->paginate(10);
        }

        $setting = Settings::where('key', 'paypal')->first();
        $title = ucwords(trans('customize.Request') . 's' . " | Search Result"); /* 'Request | Search Result' */
        return View::make('walks')
                        ->with('title', $title)
                        ->with('page', 'walks')
                        ->with('setting', $setting)
                        ->with('valu', $valu)
                        ->with('walks', $walks);
    }

    public function reviews() {
        Session::forget('type');
        Session::forget('valu');
        $provider_reviews = DB::table('review_walker')
                ->leftJoin('walker', 'review_walker.walker_id', '=', 'walker.id')
                ->leftJoin('owner', 'review_walker.owner_id', '=', 'owner.id')
                ->select('review_walker.id as review_id', 'review_walker.rating', 'review_walker.comment', 'owner.contact_name as owner_contact_name', 'walker.contact_name as walker_contact_name', 'owner.id as owner_id', 'walker.id as walker_id', 'review_walker.created_at')
                ->orderBy('review_walker.id', 'DESC')
                ->paginate(10);

        $user_reviews = DB::table('review_dog')
                ->leftJoin('walker', 'review_dog.walker_id', '=', 'walker.id')
                ->leftJoin('owner', 'review_dog.owner_id', '=', 'owner.id')
                ->select('review_dog.id as review_id', 'review_dog.rating', 'review_dog.comment', 'owner.contact_name as owner_contact_name', 'walker.contact_name as walker_contact_name', 'owner.id as owner_id', 'walker.id as walker_id', 'review_dog.created_at')
                ->orderBy('review_dog.id', 'DESC')
                ->paginate(10);
        $title = ucwords(trans('customize.Reviews')); /* 'Reviews' */
        return View::make('reviews')
                        ->with('title', $title)
                        ->with('page', 'reviews')
                        ->with('provider_reviews', $provider_reviews)
                        ->with('user_reviews', $user_reviews);
    }

    public function searchrev() {

        $valu = $_GET['valu'];
        $type = $_GET['type'];
        Session::put('valu', $valu);
        Session::put('type', $type);
        if ($type == 'owner') {
            $provider_reviews = DB::table('review_walker')
                    ->leftJoin('walker', 'review_walker.walker_id', '=', 'walker.id')
                    ->leftJoin('owner', 'review_walker.owner_id', '=', 'owner.id')
                    ->select('review_walker.id as review_id', 'review_walker.rating', 'review_walker.comment', 'owner.contact_name as owner_contact_name', 'walker.contact_name as walker_contact_name', 'owner.id as owner_id', 'walker.id as walker_id', 'review_walker.created_at')
                    ->where('owner.contact_name', 'like', '%' . $valu . '%')
                    ->paginate(10);

            $reviews = DB::table('review_dog')
                    ->leftJoin('walker', 'review_dog.walker_id', '=', 'walker.id')
                    ->leftJoin('owner', 'review_dog.owner_id', '=', 'owner.id')
                    ->select('review_dog.id as review_id', 'review_dog.rating', 'review_dog.comment', 'owner.contact_name as owner_contact_name',  'walker.contact_name as walker_contact_name',  'owner.id as owner_id', 'walker.id as walker_id', 'review_dog.created_at')
                    ->where('owner.contact_name', 'like', '%' . $valu . '%')
                    ->paginate(10);
        } elseif ($type == 'walker') {
            $provider_reviews = DB::table('review_walker')
                    ->leftJoin('walker', 'review_walker.walker_id', '=', 'walker.id')
                    ->leftJoin('owner', 'review_walker.owner_id', '=', 'owner.id')
                    ->select('review_walker.id as review_id', 'review_walker.rating', 'review_walker.comment', 'owner.contact_name as owner_contact_name',  'walker.contact_name as walker_contact_name',  'owner.id as owner_id', 'walker.id as walker_id', 'review_walker.created_at')
                    ->where('walker.contact_name', 'like', '%' . $valu . '%')
                    ->paginate(10);

            $reviews = DB::table('review_dog')
                    ->leftJoin('walker', 'review_dog.walker_id', '=', 'walker.id')
                    ->leftJoin('owner', 'review_dog.owner_id', '=', 'owner.id')
                    ->select('review_dog.id as review_id', 'review_dog.rating', 'review_dog.comment', 'owner.contact_name as owner_contact_name',  'walker.contact_name as walker_contact_name',  'owner.id as owner_id', 'walker.id as walker_id', 'review_dog.created_at')
                    ->where('walker.contact_name', 'like', '%' . $valu . '%')
                    ->paginate(10);
        }
        $title = ucwords(trans('customize.Reviews') . " | Search Result"); /* 'Reviews | Search Result' */
        return View::make('reviews')
                        ->with('title', $title)
                        ->with('page', 'reviews')
                        ->with('provider_reviews', $provider_reviews)
                        ->with('user_reviews', $reviews)
        /* ->with('reviews', $reviews) */;
    }

    public function search() {
        Session::forget('type');
        Session::forget('valu');
        $type = Input::get('type');
        $q = Input::get('q');
        if ($type == 'user') {
            $owners = Owner::where('contact_name', 'like', '%' . $q . '%')
                    ->where('deleted_at', NULL)
                    ->paginate(10);
            $title = ucwords(trans('customize.User') . 's'); /* 'Users' */
            return View::make('owners')
                            ->with('title', $title)
                            ->with('page', 'owners')
                            ->with('owners', $owners);
        } else {

            $walkers = Walker::where('deleted_at', NULL)
                    ->where('deleted_at', NULL)
                    ->where('contact_name', 'like', '%' . $q . '%')
                    ->paginate(10);
            $title = ucwords(trans('customize.Provider') . 's'); /* 'Providers' */
            return View::make('walkers')
                            ->with('title', $title)
                            ->with('page', 'walkers')
                            ->with('walkers', $walkers);
        }
    }

    public function logout() {
        Auth::logout();
        return Redirect::to('/admin/login');
    }

    public function verify() {
        $username = Input::get('username');
        $password = Input::get('password');
        if (!Admin::count()) {
            $user = new Admin;
            $user->username = Input::get('username');
            $user->password = $user->password = Hash::make(Input::get('password'));
            $user->save();
            return Redirect::to('/admin/login');
        } else {
            if (Auth::attempt(array('username' => $username, 'password' => $password))) {
                if (Session::has('pre_admin_login_url')) {
                    $url = Session::get('pre_admin_login_url');
                    Session::forget('pre_admin_login_url');
                    return Redirect::to($url);
                } else {
                    $admin = Admin::where('username', 'like', '%' . $username . '%')->first();
                    Session::put('admin_id', $admin->id);
                    return Redirect::to('/admin/report')->with('notify', 'installation Notification');
                }
            } else {
                //return Redirect::to('/admin/login?error=1');
				return Redirect::to('/admin/login')->with('error', 'Invalid email and password');
            }
        }
    }

    public function login() {
        $error = Input::get('error');
        if (Admin::count()) {

            return View::make('login')->with('title', 'Login')->with('button', 'Login')->with('error', $error);
        } else {
            return View::make('login')->with('title', 'Create Admin')->with('button', 'Create')->with('error', $error);
        }
    }

    public function edit_walker() {
        $id = Request::segment(4);
        $type = ProviderType::where('is_visible', '=', 1)->get();
        $provserv = ProviderServices::where('provider_id', $id)->get();
        $success = Input::get('success');
        $walker = Walker::find($id);
        $transportation_provider = TransportationProvider::find($walker->transportation_provider_id);
        if ($walker) {
            $title = ucwords("Edit " . trans('customize.Provider') . " : " . $walker->contact_name); /* 'Edit Provider' */
            return View::make('edit_walker')
                            ->with('title', $title)
                            ->with('page', 'walkers')
                            ->with('success', $success)
                            ->with('type', $type)
                            ->with('ps', $provserv)
                            ->with('walker', $walker)
                            ->with('transportation_provider', $transportation_provider);
        } else {
            return View::make('notfound')->with('title', 'Error Page Not Found')->with('page', 'Error Page Not Found');
        }
    }

    public function provider_availabilty() {
        $id = Request::segment(5);
        $type = ProviderType::where('is_visible', '=', 1)->get();
        $provserv = ProviderServices::where('provider_id', $id)->get();
        $success = Input::get('success');
        $walker = Walker::find($id);
        $title = ucwords("Edit " . trans('customize.Provider') . " : Availability"); /* 'Edit Provider Availability' */
        return View::make('edit_walker_availability')
                        ->with('title', $title)
                        ->with('page', 'walkers')
                        ->with('success', $success)
                        ->with('type', $type)
                        ->with('ps', $provserv)
                        ->with('walker', $walker);
    }

    public function add_walker() {
        $title = ucwords("Add " . trans('customize.Provider')); /* 'Add Provider' */
        return View::make('add_walker')
                        ->with('title', $title)
                        ->with('page', 'walkers');
    }

    public function add_promo_code() {
        $title = ucwords("Add " . trans('customize.promo_codes')); /* 'Add Promo Code' */
        return View::make('add_promo_code')
                        ->with('title', $title)
                        ->with('page', 'promo_code');
    }

    public function edit_promo_code() {
        $id = Request::segment(4);
        $promo_code = PromoCodes::where('id', $id)->first();
        $title = ucwords("Edit " . trans('customize.promo_codes')); /* 'Edit Promo Code' */
        return View::make('edit_promo_code')
                        ->with('title', $title)
                        ->with('page', 'promo_code')
                        ->with('promo_code', $promo_code);
    }

    public function deactivate_promo_code() {
        $id = Request::segment(4);
        $promo_code = PromoCodes::where('id', $id)->first();
        $promo_code->state = 2;
        $promo_code->save();
        return Redirect::route('AdminPromoCodes');
    }

    public function activate_promo_code() {
        $id = Request::segment(4);
        $promo_code = PromoCodes::where('id', $id)->first();
        $promo_code->state = 1;
        $promo_code->save();
        return Redirect::route('AdminPromoCodes');
    }

    public function update_promo_code() {
        $check = PromoCodes::where('coupon_code', '=', Input::get('code_name'))->where('id', '!=', Input::get('id'))->count();
        if ($check > 0) {
            return Redirect::to("admin/promo_code?success=1");
        }
        if (Input::get('id') != 0) {
            $promo = PromoCodes::find(Input::get('id'));
        } else {
            $promo = new PromoCodes;
        }

        $code_name = Input::get('code_name');
        $code_value = Input::get('code_value');
        $code_type = Input::get('code_type');
        $code_uses = Input::get('code_uses');
        $start_date = date("Y-m-d H:i:s", strtotime(trim(Input::get('start_date'))));
        $code_expiry = date("Y-m-d H:i:s", strtotime(trim(Input::get('code_expiry'))) + ((((23 * 60) + 59) * 60) + 59));

        $validator = Validator::make(
                        array(
                    'code_name' => $code_name,
                    'code_value' => $code_value,
                    'code_type' => $code_type,
                    'code_uses' => $code_uses,
                    'code_expiry' => $code_expiry,
                    'start_date' => $start_date,
                        ), array(
                    'code_name' => 'required',
                    'code_value' => 'required|integer',
                    'code_type' => 'required|integer',
                    'code_uses' => 'required|integer',
                    'code_expiry' => 'required',
                    'start_date' => 'required',
                        ), array(
                    'code_name' => 'Code name is required.',
                    'code_value' => 'Amount for the code is not given.',
                    'code_type' => 'Type of code is not given.',
                    'code_uses' => 'Number of user for the code is not defined',
                    'code_expiry' => 'Please set the Expiry date for the code',
                    'start_date' => 'Please set the Start date for the code',
                        )
        );

        if ($validator->fails()) {
            $error_messages = $validator->messages()->first();
            Session::put('msg', $error_messages);
            $title = ucwords("Add " . trans('customize.promo_codes')); /* 'Add Promo Code' */
            return View::make('add_promo_code')
                            ->with('title', $title)
                            ->with('page', 'promo_codes');
        } else {
            $expirydate = date("Y-m-d H:i:s", strtotime($code_expiry));

            $promo->coupon_code = $code_name;
            $promo->value = $code_value;
            $promo->type = $code_type;
            $promo->uses = $code_uses;
            $promo->start_date = $start_date;
            $promo->expiry = $expirydate;
            $promo->state = 1;
            $promo->save();
        }
        return Redirect::route('AdminPromoCodes');
    }

    public function update_walker() {

        if (Input::get('id') != 0) {
            $walker = Walker::find(Input::get('id'));
        } else {

            $findWalker = Walker::where('email', Input::get('email'))->first();

            if ($findWalker) {
                Session::put('new_walker', 0);
                $error_messages = "This Email Id is already registered.";
                Session::put('msg', $error_messages);
                $title = ucwords("Add" . trans('customize.Provider')); /* 'Add Provider' */
                return View::make('add_walker')
                                ->with('title', $title)
                                ->with('page', 'walkers');
            } else {
                Session::put('new_walker', 1);
                $walker = new Walker;
            }
        }
        if (Input::has('service') != NULL) {
            foreach (Input::get('service') as $key) {
                $serv = ProviderType::where('id', $key)->first();
                $pserv[] = $serv->name;
            }
        }

        $contact_name = Input::get('contact_name');
        $email = Input::get('email');
        $phone = Input::get('phone');
        $bio = Input::get('bio');
        $address = Input::get('address');
        $state = Input::get('state');
        $country = Input::get('country');
        $zipcode = Input::get('zipcode');
        $company_name = Input::get('company_name');

        $validator = Validator::make(
                        array(
                    'contact_name' => $contact_name,
                    'email' => $email,
                    'phone' => $phone,
                    'bio' => $bio,
                    'state' => $state,
                    'country' => $country,
                    'company_name' => $company_name
                        /* 'zipcode' => $zipcode, */
                        ), array(
                    'contact_name' => 'required',
                    'email' => 'required|email',
                    'phone' => 'required',
                    //'bio' => 'required',
                    //'state' => 'required',
                   // 'country' => 'required',
                    'company_name' => 'required'
                        /* 'zipcode' => 'required|integer' */
                        ), array(
                    'contact_name' => 'Name field is required.',
                    'email' => 'Email field is required',
                    'phone' => 'Phone number must be required.',
                    'company_name'=>'Company Name is required'
                        /* 'zipcode' => '' */
                        )
        );

        if ($validator->fails()) {
            $error_messages = $validator->messages()->first();
            Session::put('msg', $error_messages);
            $title = ucwords("Add" . trans('customize.Provider')); /* 'Add Provider' */
            return View::make('add_walker')
                            ->with('title', $title)
                            ->with('page', 'walkers');
        } else {

            $walker->contact_name = Input::get('contact_name');
            $walker->email = Input::get('email');
            $walker->phone = Input::get('phone');
            $walker->bio = Input::get('bio');
            $walker->address = Input::get('address');
            $walker->state = Input::get('state');
            $walker->type = Input::get('services');
            $walker->transportation_provider_id = $company_name;
            // adding password to new provider

            $new_password = time();
            $new_password .= rand();
            $new_password = sha1($new_password);
            $new_password = substr($new_password, 0, 8);
            /* $walker->password = Hash::make($new_password); */

            $walker->country = Input::get('country');
            $walker->zipcode = Input::get('zipcode');
            $walker->is_approved = 1;
            $walker->email_activation = 1;
            $car_number = trim(Input::get('car_number'));
            if ($car_number != "") {
                $walker->car_number = $car_number;
            }
            $car_model = trim(Input::get('car_model'));
            if ($car_model != "") {
                $walker->car_model = $car_model;
            }

            // upload image
            $file_name = time();
            $file_name .= rand();
            $file_name = sha1($file_name);
            $s3_url = "";
            if (Input::hasfile('pic')) {
                $ext = Input::file('pic')->getClientOriginalExtension();
                Input::file('pic')->move(public_path('image') . "/uploads", $file_name . "." . $ext);
                $local_url = $file_name . "." . $ext;
                Log::info('pic = ' . print_r($local_url, true));
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
            $walker->save();

            if (Session::get('new_walker') == 1) {
                // send email
                $settings = Settings::where('key', 'email_forgot_password')->first();
                $pattern = $settings->value;
                $pattern = "Hi, " . Config::get('app.website_title') . " is Created a New Account for you , Your Username is:" . Input::get('email') . " and Your Password is " . $new_password . ". Please dont forget to change the password once you log in next time.";
                $subject = "Welcome On Board";
                /* email_notification($walker->id, 'walker', $pattern, $subject); */
            }

            if (Input::has('services') != NULL) {
                $proviserv = ProviderServices::where('provider_id', $walker->id)->first();
                if ($proviserv != NULL) {
                    DB::delete("delete from walker_services where provider_id = '" . $walker->id . "';");
                }

                $base_price = '0.00';
                $service_price_distance = '0.00';
                $service_price_time = '0.00';

                $type_id = Input::get('services');
                $prserv = new ProviderServices;
                $prserv->provider_id = $walker->id;
                $prserv->type = $type_id;
                $walker->type = $type_id;
                $walker->save();
                /* Log::info('key = ' . print_r($key, true)); */
                if (Input::has('service_base_price')) {
                    $prserv->base_price = $base_price[$type_id];
                } else {
                    $prserv->base_price = 0;
                }
                if (Input::has('service_price_distance')) {
                    $prserv->price_per_unit_distance = $service_price_distance[$type_id];
                } else {
                    $prserv->price_per_unit_distance = 0;
                }
                if (Input::has('service_price_distance')) {
                    $prserv->price_per_unit_time = $service_price_time[$type_id];
                } else {
                    $prserv->price_per_unit_distance = 0;
                }
                $prserv->save();
                /* } */
                /* } */
            }

            return Redirect::to("/admin/providers");
        }
    }

    public function approve_walker() {
        $id = Request::segment(4);
        $success = Input::get('success');
        $walker = Walker::find($id);
        $walker->is_approved = 1;

        $txt_approve = "Decline";
        if ($walker->is_approved) {
            $txt_approve = "Approved";
        }
        $response_array = array(
            'unique_id' => 5,
            'success' => true,
            'id' => $walker->id,
            'contact_name' => $walker->contact_name,
            'phone' => $walker->phone,
            'email' => $walker->email,
            'picture' => $walker->picture,
            'bio' => $walker->bio,
            'address' => $walker->address,
            'state' => $walker->state,
            'country' => $walker->country,
            'zipcode' => $walker->zipcode,
            'login_by' => $walker->login_by,
            'social_unique_id' => $walker->social_unique_id,
            'device_token' => $walker->device_token,
            'device_type' => $walker->device_type,
            'token' => $walker->token,
            'type' => $walker->type,
            'is_approved' => $walker->is_approved,
            'is_approved_txt' => $txt_approve,
        );
        $title = "You are approved";
        $message = $response_array;
        send_notifications($id, "walker", $title, $message, "imp");
        /* SMS */
        $settings = Settings::where('key', 'sms_walker_approve')->first();
        $pattern = $settings->value;
        $pattern = str_replace('%name%', $pattern, $walker->contact_name);
        sms_notification($id, 'walker', $pattern);
        /* SMS END */

        $settings = Settings::where('key', 'admin_email_address')->first();
        $admin_email = $settings->value;
        $pattern = array('walker_name' => $walker->contact_name, 'admin_email' => $admin_email);
        $subject = "Welcome " . $walker->contact_name . " To " . Config::get('app.website_title') . "";
        email_notification($id, 'walker', $pattern, $subject, 'walker_approve');
        /* EMAIL END */
        $walker->save();
        return Redirect::to("/admin/providers");
    }

    public function decline_walker() {
        $id = Request::segment(4);
        $success = Input::get('success');
        $walker = Walker::find($id);
        $walker->is_approved = 0;
        $txt_approve = "Decline";
        if ($walker->is_approved) {
            $txt_approve = "Approved";
        }
        $response_array = array(
            'unique_id' => 5,
            'success' => true,
            'id' => $walker->id,
            'contact_name' => $walker->contact_name,
            'phone' => $walker->phone,
            'email' => $walker->email,
            'picture' => $walker->picture,
            'bio' => $walker->bio,
            'address' => $walker->address,
            'state' => $walker->state,
            'country' => $walker->country,
            'zipcode' => $walker->zipcode,
            'login_by' => $walker->login_by,
            'social_unique_id' => $walker->social_unique_id,
            'device_token' => $walker->device_token,
            'device_type' => $walker->device_type,
            'token' => $walker->token,
            'type' => $walker->type,
            'is_approved' => $walker->is_approved,
            'is_approved_txt' => $txt_approve,
        );
        $title = "You are Decline";
        $message = $response_array;
        send_notifications($id, "walker", $title, $message, "imp");
        /* SMS */
        $settings = Settings::where('key', 'sms_walker_decline')->first();
        $pattern = $settings->value;
        $pattern = str_replace('%name%', $pattern, $walker->contact_name);
        sms_notification($id, 'walker', $pattern);
        /* SMS END */

        $settings = Settings::where('key', 'admin_email_address')->first();
        $admin_email = $settings->value;
        $pattern = array('walker_name' => $walker->contact_name, 'admin_email' => $admin_email);
        $subject = "Welcome " . $walker->contact_name . " To " . Config::get('app.website_title') . "";
        email_notification($id, 'walker', $pattern, $subject, 'walker_decline');
        /* EMAIL END */
        $walker->save();
        return Redirect::to("/admin/providers");
    }

    public function delete_walker() {
        $id = Request::segment(4);
        $success = Input::get('success');
        RequestMeta::where('walker_id', $id)->delete();
        Walker::where('id', $id)->delete();
        return Redirect::to("/admin/providers");
    }

    public function delete_owner() {
        $id = Request::segment(4);
        $success = Input::get('success');
        Owner::where('id', $id)->delete();
        return Redirect::to("/admin/users");
    }

    public function walker_history() {
        $walker_id = Request::segment(4);
        $walks = DB::table('request')
                ->where('request.confirmed_walker', $walker_id)
                ->where('request.is_completed', 1)
                ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
                ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                ->select('owner.contact_name as owner_contact_name',  'walker.contact_name as walker_contact_name',  'owner.id as owner_id', 'walker.id as walker_id', 'request.id as id', 'request.created_at as date', 'request.is_started', 'request.is_walker_arrived', 'request.is_completed', 'request.is_paid', 'request.is_walker_started', 'request.confirmed_walker', 'request.status', 'request.time', 'request.distance', 'request.total', 'request.is_cancelled', 'request.payment_mode')
                ->orderBy('request.created_at')
                ->paginate(10);
        $title = ucwords(trans('customize.Provider') . " History"); /* 'Trip History' */
        foreach ($walks as $walk) {
            $title = ucwords(trans('customize.Provider') . ' History' . " : " . $walk->walker_contact_name);
        }
        $setting = Settings::where('key', 'transfer')->first();

        return View::make('walks')
                        ->with('title', $title)
                        ->with('page', 'walkers')
                        ->with('setting', $setting)
                        ->with('walks', $walks);
    }

    public function walker_documents() {
        $walker_id = Request::segment(4);
        $walker = Walker::find($walker_id);
        $documents = Document::all();
        $walker_document = WalkerDocument::where('walker_id', $walker_id)->get();


        return View::make('walker_document_list')
                        ->with('title', 'Driver Documents')
                        ->with('page', 'walkers')
                        ->with('walker', $walker)
                        ->with('documents', $documents)
                        ->with('walker_document', $walker_document);
    }

    public function walker_upcoming_walks() {
        $walker_id = Request::segment(4);
        $walks = DB::table('request')
                ->where('request.walker_id', $walker_id)
                ->where('request.is_completed', 0)
                ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
                ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                ->select('owner.contact_name as owner_contact_name',  'walker.contact_name as walker_contact_name',  'owner.id as owner_id', 'walker.id as walker_id', 'request.id as id', 'request.created_at as date', 'request.is_started', 'request.is_walker_arrived', 'request.is_completed', 'request.is_paid', 'request.is_walker_started', 'request.confirmed_walker', 'request.status', 'request.time', 'request.distance', 'request.total')
                ->orderBy('request.created_at')
                ->paginate(10);
        $title = ucwords(trans('customize.Provider') . " Upcoming " . trans('customize.Request') . 's'); /* 'Upcoming Walks' */
        foreach ($walks as $walk) {
            $title = ucwords(trans('customize.Provider') . " Upcoming " . trans('customize.Request') . 's' . " : " . $walk->walker_contact_name);
        }
        return View::make('walks')
                        ->with('title', $title)
                        ->with('page', 'walkers')
                        ->with('walks', $walks);
    }

    public function edit_rider() {
        $id = Request::segment(4);
        $success = Input::get('success');
        $owner = Owner::find($id);
        if ($owner) {
            $title = ucwords("Edit " . trans('customize.User') . " : " . $owner->contact_name); /* 'Edit User' */
            return View::make('admin/rider/edit_rider')
                            ->with('title', $title)
                            ->with('page', 'owners')
                            ->with('success', $success)
                            ->with('owner', $owner);
        } else {
            return View::make('notfound')
                            ->with('title', 'Error Page Not Found')
                            ->with('page', 'Error Page Not Found');
        }
    }

    public function update_owner() {
        $owner = Owner::find(Input::get('id'));
        $owner->contact_name = Input::get('contact_name');
        $owner->email = Input::get('email');
        $owner->phone = Input::get('phone');
        $owner->address = Input::get('address');
        $owner->state = Input::get('state');
        $owner->zipcode = Input::get('zipcode');
        $owner->is_tester = Input::get('is_tester');
        $owner->save();
        return Redirect::to("/admin/user/edit/$owner->id?success=1");
    }

    public function owner_history() {
        $setting = Settings::where('key', 'transfer')->first();
        $owner_id = Request::segment(4);
        $owner = Owner::find($owner_id);
        $walks = DB::table('request')
                ->where('request.owner_id', $owner->id)
                ->where('request.is_completed', 1)
                ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
                ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                ->select('owner.contact_name as owner_contact_name',  'walker.contact_name as walker_contact_name',  'owner.id as owner_id', 'walker.id as walker_id', 'request.id as id', 'request.created_at as date', 'request.is_started', 'request.is_walker_arrived', 'request.is_completed', 'request.is_paid', 'request.is_walker_started', 'request.confirmed_walker', 'request.status', 'request.time', 'request.distance', 'request.total', 'request.is_cancelled', 'request.payment_mode')
                ->orderBy('request.created_at')
                ->paginate(10);
        $title = ucwords(trans('customize.Provider') . " History"); /* 'Trip History' */
        foreach ($walks as $walk) {
            $title = ucwords(trans('customize.User') . ' History' . " : " . $walk->owner_contact_name);
        }

        return View::make('walks')
                        ->with('title', $title)
                        ->with('page', 'owners')
                        ->with('setting', $setting)
                        ->with('walks', $walks);
    }

    public function owner_upcoming_walks() {
        $owner_id = Request::segment(4);
        $owner = Owner::find($owner_id);
        $walks = DB::table('request')
                ->where('request.owner_id', $owner->id)
                ->where('request.is_completed', 0)
                ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
                ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                ->select('owner.contact_name as owner_contact_name',  'walker.contact_name as walker_contact_name',  'owner.id as owner_id', 'walker.id as walker_id', 'request.id as id', 'request.created_at as date', 'request.is_started', 'request.is_walker_arrived', 'request.is_completed', 'request.is_paid', 'request.is_walker_started', 'request.confirmed_walker', 'request.status', 'request.time', 'request.distance', 'request.total')
                ->orderBy('request.created_at')
                ->paginate(10);
        $title = ucwords(trans('customize.User') . " Upcoming " . trans('customize.Request') . 's'); /* 'Upcoming Walks' */
        foreach ($walks as $walk) {
            $title = ucwords(trans('customize.User') . " Upcoming " . trans('customize.Request') . 's' . " : " . $walk->owner_contact_name);
        }
        return View::make('walks')
                        ->with('title', $title)
                        ->with('page', 'owners')
                        ->with('walks', $walks);
    }

    public function delete_review() {
        $id = Request::segment(4);
        $walker = WalkerReview::where('id', $id)->delete();
        return Redirect::to("/admin/reviews");
    }

    public function delete_review_owner() {
        $id = Request::segment(4);
        $walker = DogReview::where('id', $id)->delete();
        return Redirect::to("/admin/reviews");
    }

    public function approve_walk() {
        $id = Request::segment(4);
        $walk = Walk::find($id);
        $walk->is_confirmed = 1;
        $walk->save();
        return Redirect::to("/admin/walks");
    }

    public function decline_walk() {
        $id = Request::segment(4);
        $walk = Walk::find($id);
        $walk->is_confirmed = 0;
        $walk->save();
        return Redirect::to("/admin/walks");
    }

    public function view_map() {
        $id = Request::segment(4);
        $request = RideRideRequest::find($id);
        $walker = Walker::where('id', $request->confirmed_walker)->first();
        $owner = Owner::where('id', $request->owner_id)->first();
		if($owner=='')
        {
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
            /* $owner_latitude = $owner->latitude;
              $owner_longitude = $owner->longitude; */
            $owner_latitude = $request->latitude;
            $owner_longitude = $request->longitude;
        }

        $request_meta = DB::table('request_meta')
                ->where('request_id', $id)
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
            return View::make('walk_map')
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
                            ->with('request_meta', $request_meta);
        } else {
            $title = ucwords('Maps');
            return View::make('walk_map')
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
                            ->with('status', $status);
        }
    }

    public function change_walker() {
        $id = Request::segment(4);
        $title = ucwords('Map View');
        return View::make('reassign_walker')
                        ->with('title', $title)
                        ->with('page', 'walks')
                        ->with('walk_id', $id);
    }

    public function alternative_walkers_xml() {
        $id = Request::segment(4);
        $walk = Walk::find($id);
        $schedule = Schedules::find($walk->schedule_id);
        $dog = Dog::find($walk->dog_id);
        $owner = Owner::find($dog->owner_id);
        $current_walker = Walker::find($walk->walker_id);
        $latitude = $owner->latitude;
        $longitude = $owner->longitude;
        $distance = 5;


        // Get Latitude
        $schedule_meta = ScheduleMeta::where('schedule_id', '=', $schedule->id)
                ->orderBy('started_on', 'DESC')
                ->get();

        $flag = 0;
        $date = "0000-00-00";
        $days = array();
        foreach ($schedule_meta as $meta) {
            if ($flag == 0) {
                $date = $meta->started_on;
                $flag++;
            }
            array_push($days, $meta->day);
        }

        $start_time = date('H:i:s', strtotime($schedule->start_time) - (60 * 60));
        $end_time = date('H:i:s', strtotime($schedule->end_time) + (60 * 60));
        $days_str = implode(',', $days);
        $settings = Settings::where('key', 'default_distance_unit')->first();
        $unit = $settings->value;
        if ($unit == 0) {
            $multiply = 1.609344;
        } elseif ($unit == 1) {
            $multiply = 1;
        }

        $query = "SELECT walker.id,walker.bio,walker.contact_name,walker.phone,walker.latitude,walker.longitude from walker where id NOT IN ( SELECT distinct schedules.walker_id FROM `schedule_meta` left join schedules on schedule_meta.schedule_id = schedules.id where schedules.is_confirmed	 != 0 and schedule_meta.day IN ($days_str) and schedule_meta.ends_on >= '$date' and schedule_meta.started_on <= '$date' and ((schedules.start_time > '$start_time' and schedules.start_time < '$end_time') OR ( schedules.end_time > '$start_time' and schedules.end_time < '$end_time' )) ) and "
                . "ROUND((" . $multiply . " * 3956 * acos( cos( radians('$latitude') ) * "
                . "cos( radians(latitude) ) * "
                . "cos( radians(longitude) - radians('$longitude') ) + "
                . "sin( radians('$latitude') ) * "
                . "sin( radians(latitude) ) ) ) ,8) <= $distance ";

        $walkers = DB::select(DB::raw($query));
        $response = "";
        $response .= '<markers>';

        foreach ($walkers as $walker) {
            $response .= '<marker ';
            $response .= 'name="' . $walker->contact_name . '" ';
            $response .= 'client_name="' . $walker->contact_name . '" ';
            $response .= 'contact="' . $walker->phone . '" ';
            $response .= 'amount="' . 0 . '" ';
            $response .= 'lat="' . $walker->latitude . '" ';
            $response .= 'lng="' . $walker->longitude . '" ';
            $response .= 'id="' . $walker->id . '" ';
            $response .= 'type="client" ';
            $response .= '/>';
        }

        // Add Current walker
        if ($current_walker) {
            $response .= '<marker ';
            $response .= 'name="' . $current_walker->contact_name . '" ';
            $response .= 'client_name="' . $current_walker->contact_name . '" ';
            $response .= 'contact="' . $current_walker->phone . '" ';
            $response .= 'amount="' . 0 . '" ';
            $response .= 'lat="' . $current_walker->latitude . '" ';
            $response .= 'lng="' . $current_walker->longitude . '" ';
            $response .= 'id="' . $current_walker->id . '" ';
            $response .= 'type="driver" ';
            $response .= '/>';
        }

        // Add Owner
        $response .= '<marker ';
        $response .= 'name="' . $owner->contact_name . '" ';
        $response .= 'client_name="' . $owner->contact_name . '" ';
        $response .= 'contact="' . $owner->phone . '" ';
        $response .= 'amount="' . 0 . '" ';
        $response .= 'lat="' . $owner->latitude . '" ';
        $response .= 'lng="' . $owner->longitude . '" ';
        $response .= 'id="' . $owner->id . '" ';
        $response .= 'type="client_pay_done" ';
        $response .= '/>';

        // Add Busy Walkers

        $walkers = DB::table('request')
                ->where('walk.is_started', 1)
                ->where('walk.is_completed', 0)
                ->join('walker', 'walk.walker_id', '=', 'walker.id')
                ->select('walker.id', 'walker.phone', 'walker.contact_name', 'walker.latitude', 'walker.longitude')
                ->distinct()
                ->get();


        foreach ($walkers as $walker) {
            $response .= '<marker ';
            $response .= 'name="' . $walker->contact_name . '" ';
            $response .= 'client_name="' . $walker->contact_name . '" ';
            $response .= 'contact="' . $walker->phone . '" ';
            $response .= 'amount="' . 0 . '" ';
            $response .= 'lat="' . $walker->latitude . '" ';
            $response .= 'lng="' . $walker->longitude . '" ';
            $response .= 'id="' . $owner->id . '" ';
            $response .= 'type="client_no_pay" ';
            $response .= '/>';
        }


        $response .= '</markers>';

        $content = View::make('walkers_xml')->with('response', $response);
        return Response::make($content, '200')->header('Content-Type', 'text/xml');
    }

    public function save_changed_walker() {
        $walk_id = Input::get('walk_id');
        $type = Input::get('type');
        $walker_id = Input::get('walker_id');
        $walk = Walk::find($walk_id);
        if ($type == 1) {
            $walk->walker_id = $walker_id;
            $walk->save();
        } else {
            Walk::where('schedule_id', $walk->schedule_id)->where('is_started', 0)->update(array('walker_id' => $walker_id));
            Schedules::where('id', $walk->schedule_id)->update(array('walker_id' => $walker_id));
        }
        return Redirect::to('/admin/walk/change_walker/' . $walk_id);
    }

    public function pay_walker() {
        $walk_id = Input::get('walk_id');
        $amount = Input::get('amount');
        $walk = Walk::find($walk_id);
        $walk->is_paid = 1;
        $walk->amount = $amount;
        $walk->save();

        return Redirect::to('/admin/walk/map/' . $walk_id);
    }

//settings
    public function get_settings() {
        $braintree_environment = Config::get('app.braintree_environment');
        $braintree_merchant_id = Config::get('app.braintree_merchant_id');
        $braintree_public_key = Config::get('app.braintree_public_key');
        $braintree_private_key = Config::get('app.braintree_private_key');
        $braintree_cse = Config::get('app.braintree_cse');
        $twillo_account_sid = Config::get('app.twillo_account_sid');
        $twillo_auth_token = Config::get('app.twillo_auth_token');
        $twillo_number = Config::get('app.twillo_number');
        $timezone = Config::get('app.timezone');
        $stripe_publishable_key = Config::get('app.stripe_publishable_key');
        $url = Config::get('app.url');
        $website_title = Config::get('app.website_title');
        $s3_bucket = Config::get('app.s3_bucket');
        $default_payment = Config::get('app.default_payment');
        $stripe_secret_key = Config::get('app.stripe_secret_key');
        $mail_driver = Config::get('mail.mail_driver');
        $email_name = Config::get('mail.from.name');
        $email_address = Config::get('mail.from.address');
        $mandrill_secret = Config::get('services.mandrill_secret');
        $host = Config::get('mail.host');
        /* DEVICE PUSH NOTIFICATION DETAILS */
        $customer_certy_url = Config::get('app.customer_certy_url');
        $customer_certy_pass = Config::get('app.customer_certy_pass');
        $customer_certy_type = Config::get('app.customer_certy_type');
        $provider_certy_url = Config::get('app.provider_certy_url');
        $provider_certy_pass = Config::get('app.provider_certy_pass');
        $provider_certy_type = Config::get('app.provider_certy_type');
        $gcm_browser_key = Config::get('app.gcm_browser_key');
        /* DEVICE PUSH NOTIFICATION DETAILS END */
        $install = array(
            'braintree_environment' => $braintree_environment,
            'braintree_merchant_id' => $braintree_merchant_id,
            'braintree_public_key' => $braintree_public_key,
            'braintree_private_key' => $braintree_private_key,
            'braintree_cse' => $braintree_cse,
            'twillo_account_sid' => $twillo_account_sid,
            'twillo_auth_token' => $twillo_auth_token,
            'twillo_number' => $twillo_number,
            'stripe_publishable_key' => $stripe_publishable_key,
            'stripe_secret_key' => $stripe_secret_key,
            'mail_driver' => $mail_driver,
            'email_address' => $email_address,
            'email_name' => $email_name,
            'mandrill_secret' => $mandrill_secret,
            'default_payment' => $default_payment,
            /* DEVICE PUSH NOTIFICATION DETAILS */
            'customer_certy_url' => $customer_certy_url,
            'customer_certy_pass' => $customer_certy_pass,
            'customer_certy_type' => $customer_certy_type,
            'provider_certy_url' => $provider_certy_url,
            'provider_certy_pass' => $provider_certy_pass,
            'provider_certy_type' => $provider_certy_type,
            'gcm_browser_key' => $gcm_browser_key,
                /* DEVICE PUSH NOTIFICATION DETAILS END */
        );
        $success = Input::get('success');
        $settings = Settings::all();
        /* $theme = Theme::all(); */
        $theme = Theme::first();
        if (isset($theme->id)) {
            $theme = Theme::first();
        } else {
            $theme = array();
        }
        $title = ucwords(trans('customize.Settings')); /* 'Settings' */
        return View::make('settings')
                        ->with('title', $title)
                        ->with('page', 'settings')
                        ->with('settings', $settings)
                        ->with('success', $success)
                        ->with('install', $install)
                        ->with('theme', $theme);
    }

    public function edit_keywords() {
        $success = Input::get('success');
        /* $keywords = Keywords::all(); */
        $icons = Icons::all();

        $UIkeywords = array();

        $UIkeywords['keyProvider'] = Lang::get('customize.Provider');
        $UIkeywords['keyUser'] = Lang::get('customize.User');
        $UIkeywords['keyTaxi'] = Lang::get('customize.Taxi');
        $UIkeywords['keyTrip'] = Lang::get('customize.Trip');
        $UIkeywords['keyWalk'] = Lang::get('customize.Walk');
        $UIkeywords['keyRequest'] = Lang::get('customize.Request');
        $UIkeywords['keyDashboard'] = Lang::get('customize.Dashboard');
        $UIkeywords['keyMap_View'] = Lang::get('customize.map_view');
        $UIkeywords['keyReviews'] = Lang::get('customize.Reviews');
        $UIkeywords['keyInformation'] = Lang::get('customize.Information');
        $UIkeywords['keyTypes'] = Lang::get('customize.Types');
        $UIkeywords['keyDocuments'] = Lang::get('customize.Documents');
        $UIkeywords['keyPromo_Codes'] = Lang::get('customize.promo_codes');
        $UIkeywords['keyCustomize'] = Lang::get('customize.Customize');
        $UIkeywords['keyPayment_Details'] = Lang::get('customize.payment_details');
        $UIkeywords['keySettings'] = Lang::get('customize.Settings');
        $UIkeywords['keyAdmin'] = Lang::get('customize.Admin');
        $UIkeywords['keyAdmin_Control'] = Lang::get('customize.admin_control');
        $UIkeywords['keyLog_Out'] = Lang::get('customize.log_out');
        $UIkeywords['keySchedules'] = Lang::get('customize.Schedules');
        $UIkeywords['keyWeekStatement'] = Lang::get('customize.WeekStatement');
        $title = ucwords(trans('customize.Customize')); /* 'Customize' */
        return View::make('keywords')
                        ->with('title', $title)
                        ->with('page', 'keywords')
                        /* ->with('keywords', $keywords) */
                        ->with('icons', $icons)
                        ->with('Uikeywords', $UIkeywords)
                        ->with('success', $success);
    }

    public function save_keywords() {
        $braintree_cse = $stripe_publishable_key = $url = $timezone = $website_title = $s3_bucket = $twillo_account_sid = $twillo_auth_token = $twillo_number = $default_payment = $stripe_secret_key = $braintree_environment = $braintree_merchant_id = $braintree_public_key = $braintree_private_key = $customer_certy_url = $customer_certy_pass = $customer_certy_type = $provider_certy_url = $provider_certy_pass = $provider_certy_type = $gcm_browser_key = $key_provider = $key_user = $key_taxi = $key_trip = $key_currency = $total_trip = $cancelled_trip = $total_payment = $completed_trip = $card_payment = $credit_payment = $key_ref_pre = $android_client_app_url = $android_provider_app_url = $ios_client_app_url = $ios_provider_app_url = NULL;
        $braintree_environment = Config::get('app.braintree_environment');
        $braintree_merchant_id = Config::get('app.braintree_merchant_id');
        $braintree_public_key = Config::get('app.braintree_public_key');
        $braintree_private_key = Config::get('app.braintree_private_key');
        $braintree_cse = Config::get('app.braintree_cse');
        $twillo_account_sid = Config::get('app.twillo_account_sid');
        $twillo_auth_token = Config::get('app.twillo_auth_token');
        $twillo_number = Config::get('app.twillo_number');
        $timezone = Config::get('app.timezone');
        $stripe_publishable_key = Config::get('app.stripe_publishable_key');
        $url = Config::get('app.url');
        $website_title = Config::get('app.website_title');
        $s3_bucket = Config::get('app.s3_bucket');
        $default_payment = Config::get('app.default_payment');
        $stripe_secret_key = Config::get('app.stripe_secret_key');
        $mail_driver = Config::get('mail.driver');
        $email_name = Config::get('mail.from.name');
        $email_address = Config::get('mail.from.address');
        $mandrill_secret = Config::get('services.mandrill.secret');
        $host = Config::get('mail.host');
        /* DEVICE PUSH NOTIFICATION DETAILS */
        $customer_certy_url = Config::get('app.customer_certy_url');
        $customer_certy_pass = Config::get('app.customer_certy_pass');
        $customer_certy_type = Config::get('app.customer_certy_type');
        $provider_certy_url = Config::get('app.provider_certy_url');
        $provider_certy_pass = Config::get('app.provider_certy_pass');
        $provider_certy_type = Config::get('app.provider_certy_type');
        $gcm_browser_key = Config::get('app.gcm_browser_key');
        /* DEVICE PUSH NOTIFICATION DETAILS END */
        $install = array(
            'braintree_environment' => $braintree_environment,
            'braintree_merchant_id' => $braintree_merchant_id,
            'braintree_public_key' => $braintree_public_key,
            'braintree_private_key' => $braintree_private_key,
            'braintree_cse' => $braintree_cse,
            'twillo_account_sid' => $twillo_account_sid,
            'twillo_auth_token' => $twillo_auth_token,
            'twillo_number' => $twillo_number,
            'stripe_publishable_key' => $stripe_publishable_key,
            'stripe_secret_key' => $stripe_secret_key,
            'mail_driver' => $mail_driver,
            'email_address' => $email_address,
            'email_name' => $email_name,
            'mandrill_secret' => $mandrill_secret,
            'default_payment' => $default_payment,
            /* DEVICE PUSH NOTIFICATION DETAILS */
            'customer_certy_url' => $customer_certy_url,
            'customer_certy_pass' => $customer_certy_pass,
            'customer_certy_type' => $customer_certy_type,
            'provider_certy_url' => $provider_certy_url,
            'provider_certy_pass' => $provider_certy_pass,
            'provider_certy_type' => $provider_certy_type,
            'gcm_browser_key' => $gcm_browser_key,
                /* DEVICE PUSH NOTIFICATION DETAILS END */
        );        // Modifying Database Config
        /* $keywords = Keywords::all();
          foreach ($keywords as $keyword) {
          // Log::info('keyword = ' . print_r(Input::get($keyword->id), true));
          if (Input::get($keyword->id) != NULL) {
          // Log::info('keyword = ' . print_r(Input::get($keyword->id), true));
          $temp = Input::get($keyword->id);
          $temp_setting = Keywords::find($keyword->id);
          $temp_setting->keyword = Input::get($keyword->id);
          $temp_setting->save();
          }
          } */

        if (Input::has('key_provider')) {
            $key_provider = trim(Input::get('key_provider'));
            if ($key_provider != "") {
                /* $keyword = Keywords::find(1);
                  $keyword->keyword = Input::get('key_provider');
                  // $keyword->alias = Input::get('key_provider');
                  $keyword->save(); */
            } else {
                $key_provider = null;
            }
        }
        if (Input::has('key_user')) {
            $key_user = trim(Input::get('key_user'));
            if ($key_user != "") {
                /* $keyword = Keywords::find(2);
                  $keyword->keyword = Input::get('key_user');
                  // $keyword->alias = Input::get('key_user');
                  $keyword->save(); */
            } else {
                $key_user = null;
            }
        }
        if (Input::has('key_taxi')) {
            $key_taxi = trim(Input::get('key_taxi'));
            if ($key_taxi != "") {
                /* $keyword = Keywords::find(3);
                  $keyword->keyword = Input::get('key_taxi');
                  // $keyword->alias = Input::get('key_taxi');
                  $keyword->save(); */
            } else {
                $key_taxi = null;
            }
        }
        if (Input::has('key_trip')) {
            $key_trip = trim(Input::get('key_trip'));
            if ($key_trip != "") {
                /* $keyword = Keywords::find(4);
                  $keyword->keyword = Input::get('key_trip');
                  // $keyword->alias = Input::get('key_trip');
                  $keyword->save(); */
            } else {
                $key_trip = null;
            }
        }
        if (Input::has('key_currency')) {
            $key_currency = trim(Input::get('key_currency'));
            if ($key_currency != '$' || $key_currency != "usd" || $key_currency != "USD") {
                $setransfer = Settings::where('key', 'transfer')->first();
                $setransfer->value = 2;
                $setransfer->save();
            }
            if ($key_currency != "") {
                /* $keyword = Keywords::find(5);
                  $keyword->keyword = Input::get('key_currency');
                  // $keyword->alias = Input::get('key_currency');
                  $keyword->save(); */
            } else {
                $key_currency = null;
            }
        }
        if (Input::has('total_trip')) {
            $total_trip = trim(Input::get('total_trip'));
            if ($total_trip != "") {
                /* $keyword = Keywords::find(6);
                  $keyword->alias = Input::get('total_trip');
                  $keyword->save(); */
            } else {
                $total_trip = null;
            }
        }
        if (Input::has('cancelled_trip')) {
            $cancelled_trip = trim(Input::get('cancelled_trip'));
            if ($cancelled_trip != "") {
                /* $keyword = Keywords::find(7);
                  $keyword->alias = Input::get('cancelled_trip');
                  $keyword->save(); */
            } else {
                $cancelled_trip = null;
            }
        }
        if (Input::has('total_payment')) {
            $total_payment = trim(Input::get('total_payment'));
            if ($total_payment != "") {
                /* $keyword = Keywords::find(8);
                  $keyword->alias = Input::get('total_payment');
                  $keyword->save(); */
            } else {
                $total_payment = null;
            }
        }
        if (Input::has('completed_trip')) {
            $completed_trip = trim(Input::get('completed_trip'));
            if ($completed_trip != "") {
                /* $keyword = Keywords::find(9);
                  $keyword->alias = Input::get('completed_trip');
                  $keyword->save(); */
            } else {
                $completed_trip = null;
            }
        }
        if (Input::has('card_payment')) {
            $card_payment = trim(Input::get('card_payment'));
            if ($card_payment != "") {
                /* $keyword = Keywords::find(10);
                  $keyword->alias = Input::get('card_payment');
                  $keyword->save(); */
            } else {
                $card_payment = null;
            }
        }
        if (Input::has('credit_payment')) {
            $credit_payment = trim(Input::get('credit_payment'));
            if ($credit_payment != "") {
                /* $keyword = Keywords::find(11);
                  $keyword->alias = Input::get('credit_payment');
                  $keyword->save(); */
            } else {
                $credit_payment = null;
            }
        }
        if (Input::has('key_ref_pre')) {
            $key_ref_pre = trim(Input::get('key_ref_pre'));
            if ($key_ref_pre != "") {
                /* $keyword = Keywords::find(11);
                  $keyword->alias = Input::get('credit_payment');
                  $keyword->save(); */
            } else {
                $key_ref_pre = null;
            }
        }
        if (Input::has('cash_payment')) {
            $cash_payment = trim(Input::get('cash_payment'));
            if ($cash_payment != "") {
                /* $keyword = Keywords::find(11);
                  $keyword->alias = Input::get('credit_payment');
                  $keyword->save(); */
            } else {
                $cash_payment = null;
            }
        }
        if (Input::has('promotional_payment')) {
            $promotional_payment = trim(Input::get('promotional_payment'));
            if ($promotional_payment != "") {
                /* $keyword = Keywords::find(11);
                  $keyword->alias = Input::get('credit_payment');
                  $keyword->save(); */
            } else {
                $promotional_payment = null;
            }
        }
        if (Input::has('schedules_icon')) {
            $schedules_icon = trim(Input::get('schedules_icon'));
            if ($schedules_icon != "") {
                /* $keyword = Keywords::find(11);
                  $keyword->alias = Input::get('credit_payment');
                  $keyword->save(); */
            } else {
                $schedules_icon = null;
            }
        }
        /* $key_provider $key_user $key_taxi $key_trip $key_currency $total_trip $cancelled_trip $total_payment $completed_trip $card_payment $credit_payment */
        $appfile = fopen(app_path() . "/config/app.php", "w") or die("Unable to open file!");
        /* $appfile_config = generate_app_config($braintree_cse, $stripe_publishable_key, $url, $timezone, $website_title, $s3_bucket, $twillo_account_sid, $twillo_auth_token, $twillo_number, $default_payment, $stripe_secret_key, $braintree_environment, $braintree_merchant_id, $braintree_public_key, $braintree_private_key, $customer_certy_url, $customer_certy_pass, $customer_certy_type, $provider_certy_url, $provider_certy_pass, $provider_certy_type, $gcm_browser_key, $key_provider, $key_user, $key_taxi, $key_trip, $key_currency, $total_trip, $cancelled_trip, $total_payment, $completed_trip, $card_payment, $credit_payment, $key_ref_pre); */
        $appfile_config = generate_app_config($braintree_cse, $stripe_publishable_key, $url, $timezone, $website_title, $s3_bucket, $twillo_account_sid, $twillo_auth_token, $twillo_number, $default_payment, $stripe_secret_key, $braintree_environment, $braintree_merchant_id, $braintree_public_key, $braintree_private_key, $customer_certy_url, $customer_certy_pass, $customer_certy_type, $provider_certy_url, $provider_certy_pass, $provider_certy_type, $gcm_browser_key, $key_provider, $key_user, $key_taxi, $key_trip, $key_currency, $total_trip, $cancelled_trip, $total_payment, $completed_trip, $card_payment, $credit_payment, $key_ref_pre, $android_client_app_url, $android_provider_app_url, $ios_client_app_url, $ios_provider_app_url, $cash_payment, $promotional_payment, $schedules_icon);
        fwrite($appfile, $appfile_config);
        fclose($appfile);

        return Redirect::to('/admin/edit_keywords?success=1');
    }

    public function save_developer_details() {
        $braintree_cse = $stripe_publishable_key = $url = $timezone = $website_title = $s3_bucket = $twillo_account_sid = $twillo_auth_token = $twillo_number = $default_payment = $stripe_secret_key = $braintree_environment = $braintree_merchant_id = $braintree_public_key = $braintree_private_key = $customer_certy_url = $customer_certy_pass = $customer_certy_type = $provider_certy_url = $provider_certy_pass = $provider_certy_type = $gcm_browser_key = $key_provider = $key_user = $key_taxi = $key_trip = $key_currency = $total_trip = $cancelled_trip = $total_payment = $completed_trip = $card_payment = $credit_payment = $key_ref_pre = $android_client_app_url = $android_provider_app_url = $ios_client_app_url = $ios_provider_app_url = $dev_twit_link = $dev_fb_link = $dev_email = $dev_web_link = $dev_comp_name = NULL;
        $braintree_environment = Config::get('app.braintree_environment');
        $braintree_merchant_id = Config::get('app.braintree_merchant_id');
        $braintree_public_key = Config::get('app.braintree_public_key');
        $braintree_private_key = Config::get('app.braintree_private_key');
        $braintree_cse = Config::get('app.braintree_cse');
        $twillo_account_sid = Config::get('app.twillo_account_sid');
        $twillo_auth_token = Config::get('app.twillo_auth_token');
        $twillo_number = Config::get('app.twillo_number');
        $timezone = Config::get('app.timezone');
        $stripe_publishable_key = Config::get('app.stripe_publishable_key');
        $url = Config::get('app.url');
        $website_title = Config::get('app.website_title');
        $s3_bucket = Config::get('app.s3_bucket');
        $default_payment = Config::get('app.default_payment');
        $stripe_secret_key = Config::get('app.stripe_secret_key');
        $mail_driver = Config::get('mail.driver');
        $email_name = Config::get('mail.from.name');
        $email_address = Config::get('mail.from.address');
        $mandrill_secret = Config::get('services.mandrill.secret');
        $host = Config::get('mail.host');
        /* DEVICE PUSH NOTIFICATION DETAILS */
        $customer_certy_url = Config::get('app.customer_certy_url');
        $customer_certy_pass = Config::get('app.customer_certy_pass');
        $customer_certy_type = Config::get('app.customer_certy_type');
        $provider_certy_url = Config::get('app.provider_certy_url');
        $provider_certy_pass = Config::get('app.provider_certy_pass');
        $provider_certy_type = Config::get('app.provider_certy_type');
        $gcm_browser_key = Config::get('app.gcm_browser_key');
        /* DEVICE PUSH NOTIFICATION DETAILS END */
        $install = array(
            'braintree_environment' => $braintree_environment,
            'braintree_merchant_id' => $braintree_merchant_id,
            'braintree_public_key' => $braintree_public_key,
            'braintree_private_key' => $braintree_private_key,
            'braintree_cse' => $braintree_cse,
            'twillo_account_sid' => $twillo_account_sid,
            'twillo_auth_token' => $twillo_auth_token,
            'twillo_number' => $twillo_number,
            'stripe_publishable_key' => $stripe_publishable_key,
            'stripe_secret_key' => $stripe_secret_key,
            'mail_driver' => $mail_driver,
            'email_address' => $email_address,
            'email_name' => $email_name,
            'mandrill_secret' => $mandrill_secret,
            'default_payment' => $default_payment,
            /* DEVICE PUSH NOTIFICATION DETAILS */
            'customer_certy_url' => $customer_certy_url,
            'customer_certy_pass' => $customer_certy_pass,
            'customer_certy_type' => $customer_certy_type,
            'provider_certy_url' => $provider_certy_url,
            'provider_certy_pass' => $provider_certy_pass,
            'provider_certy_type' => $provider_certy_type,
            'gcm_browser_key' => $gcm_browser_key,
                /* DEVICE PUSH NOTIFICATION DETAILS END */
        );        // Modifying Database Config
        /* $keywords = Keywords::all();
          foreach ($keywords as $keyword) {
          // Log::info('keyword = ' . print_r(Input::get($keyword->id), true));
          if (Input::get($keyword->id) != NULL) {
          // Log::info('keyword = ' . print_r(Input::get($keyword->id), true));
          $temp = Input::get($keyword->id);
          $temp_setting = Keywords::find($keyword->id);
          $temp_setting->keyword = Input::get($keyword->id);
          $temp_setting->save();
          }
          } */
        $key_provider = trim(Config::get('app.generic_keywords.Provider'));
        $key_user = trim(Config::get('app.generic_keywords.User'));
        $key_taxi = trim(Config::get('app.generic_keywords.Services'));
        $key_trip = trim(Config::get('app.generic_keywords.Trip'));
        $key_currency = trim(Config::get('app.generic_keywords.Currency'));
        $total_trip = trim(Config::get('app.generic_keywords.total_trip'));
        $cancelled_trip = trim(Config::get('app.generic_keywords.cancelled_trip'));
        $total_payment = trim(Config::get('app.generic_keywords.total_payment'));
        $completed_trip = trim(Config::get('app.generic_keywords.completed_trip'));
        $card_payment = trim(Config::get('app.generic_keywords.card_payment'));
        $credit_payment = trim(Config::get('app.generic_keywords.credit_payment'));
        $key_ref_pre = trim(Config::get('app.referral_prefix'));
        $cash_payment = trim(Config::get('app.generic_keywords.cash_payment'));
        $promotional_payment = trim(Config::get('app.generic_keywords.promotional_payment'));
        $schedules_icon = trim(Config::get('app.generic_keywords.schedules_icon'));

        if (Input::has('company_name')) {
            $dev_comp_name = trim(Input::get('company_name'));
            if ($dev_comp_name != "") {
                
            } else {
                $dev_comp_name = null;
            }
        }
        if (Input::has('web_link')) {
            $dev_web_link = trim(Input::get('web_link'));
            if ($dev_web_link != "") {
                
            } else {
                $dev_web_link = null;
            }
        }
        if (Input::has('email')) {
            $dev_email = trim(Input::get('email'));
            if ($dev_email != "") {
                
            } else {
                $dev_email = null;
            }
        }
        if (Input::has('fb_link')) {
            $dev_fb_link = trim(Input::get('fb_link'));
            if ($dev_fb_link != "") {
                
            } else {
                $dev_fb_link = null;
            }
        }
        if (Input::has('twit_link')) {
            $dev_twit_link = trim(Input::get('twit_link'));
            if ($dev_twit_link != "") {
                
            } else {
                $dev_twit_link = null;
            }
        }

        $appfile = fopen(app_path() . "/config/app.php", "w") or die("Unable to open file!");
        $appfile_config = generate_app_config($braintree_cse, $stripe_publishable_key, $url, $timezone, $website_title, $s3_bucket, $twillo_account_sid, $twillo_auth_token, $twillo_number, $default_payment, $stripe_secret_key, $braintree_environment, $braintree_merchant_id, $braintree_public_key, $braintree_private_key, $customer_certy_url, $customer_certy_pass, $customer_certy_type, $provider_certy_url, $provider_certy_pass, $provider_certy_type, $gcm_browser_key, $key_provider, $key_user, $key_taxi, $key_trip, $key_currency, $total_trip, $cancelled_trip, $total_payment, $completed_trip, $card_payment, $credit_payment, $key_ref_pre, $android_client_app_url, $android_provider_app_url, $ios_client_app_url, $ios_provider_app_url, $cash_payment, $promotional_payment, $schedules_icon, $dev_comp_name, $dev_web_link, $dev_email, $dev_fb_link, $dev_twit_link);
        fwrite($appfile, $appfile_config);
        fclose($appfile);

        return Redirect::to('/admin/settings/installation?success=1');
    }

    public function save_keywords_UI() {
        $dashboard = trim(Input::get('val_dashboard'));
        $map_view = trim(Input::get('val_map_view'));
        $provider = trim(Input::get('val_provider'));
        $user = trim(Input::get('val_user'));
        $taxi = trim(Input::get('val_taxi'));
        $trip = trim(Input::get('val_trip'));
        $walk = trim(Input::get('val_walk'));
        $request = trim(Input::get('val_request'));
        $reviews = trim(Input::get('val_reviews'));
        $information = trim(Input::get('val_information'));
        $types = trim(Input::get('val_types'));
        $documents = trim(Input::get('val_documents'));
        $promo_codes = trim(Input::get('val_promo_codes'));
        $customize = trim(Input::get('val_customize'));
        $payment_details = trim(Input::get('val_payment_details'));
        $settings = trim(Input::get('val_settings'));
        $val_admin = trim(Input::get('val_admin'));
        $admin_control = trim(Input::get('val_admin_control'));
        $log_out = trim(Input::get('val_log_out'));
        $schedule = trim(Input::get('val_schedules'));
        $weekstatement = trim(Input::get('val_weekstatement'));

        if ($weekstatement == null || $weekstatement == "") {
            $weekstatement = Lang::get('customize.Schedules');
        } else {
            $weekstatement = $weekstatement;
        }
        if ($schedule == null || $schedule == "") {
            $schedule = Lang::get('customize.Schedules');
        } else {
            $schedule = $schedule;
        }
        if ($dashboard == null || $dashboard == "") {
            $dashboard = Lang::get('customize.Dashboard');
        } else {
            $dashboard = $dashboard;
        }
        if ($map_view == null || $map_view == "") {
            $map_view = Lang::get('customize.map_view');
        } else {
            $map_view = $map_view;
        }
        if ($provider == null || $provider == "") {
            $provider = Lang::get('customize.Provider');
        } else {
            $provider = $provider;
        }
        if ($user == null || $user == "") {
            $user = Lang::get('customize.User');
        } else {
            $user = $user;
        }
        if ($taxi == null || $taxi == "") {
            $taxi = Lang::get('customize.Taxi');
        } else {
            $taxi = $taxi;
        }
        if ($trip == null || $trip == "") {
            $trip = Lang::get('customize.Trip');
        } else {
            $trip = $trip;
        }
        if ($walk == null || $walk == "") {
            $walk = Lang::get('customize.Walk');
        } else {
            $walk = $walk;
        }
        if ($request == null || $request == "") {
            $request = Lang::get('customize.Request');
        } else {
            $request = $request;
        }
        if ($reviews == null || $reviews == "") {
            $reviews = Lang::get('customize.Reviews');
        } else {
            $reviews = $reviews;
        }
        if ($information == null || $information == "") {
            $information = Lang::get('customize.Information');
        } else {
            $information = $information;
        }
        if ($types == null || $types == "") {
            $types = Lang::get('customize.Types');
        } else {
            $types = $types;
        }
        if ($documents == null || $documents == "") {
            $documents = Lang::get('customize.Documents');
        } else {
            $documents = $documents;
        }
        if ($promo_codes == null || $promo_codes == "") {
            $promo_codes = Lang::get('customize.promo_codes');
        } else {
            $promo_codes = $promo_codes;
        }
        if ($customize == null || $customize == "") {
            $customize = Lang::get('customize.Customize');
        } else {
            $customize = $customize;
        }
        if ($payment_details == null || $payment_details == "") {
            $payment_details = Lang::get('customize.payment_details');
        } else {
            $payment_details = $payment_details;
        }
        if ($settings == null || $settings == "") {
            $settings = Lang::get('customize.Settings');
        } else {
            $settings = $settings;
        }
        if ($val_admin == null || $val_admin == "") {
            $val_admin = Lang::get('customize.Admin');
        } else {
            $val_admin = $val_admin;
        }
        if ($admin_control == null || $admin_control == "") {
            $admin_control = Lang::get('customize.admin_control');
        } else {
            $admin_control = $admin_control;
        }
        if ($log_out == null || $log_out == "") {
            $log_out = Lang::get('customize.log_out');
        } else {
            $log_out = $log_out;
        }
        $appfile = fopen(app_path() . "/lang/en/customize.php", "w") or die("Unable to open file!");
        $appfile_config = generate_custome_key($dashboard, $map_view, $provider, $user, $taxi, $trip, $walk, $request, $reviews, $information, $types, $documents, $promo_codes, $customize, $payment_details, $settings, $val_admin, $admin_control, $log_out, $schedule, $weekstatement);
        fwrite($appfile, $appfile_config);
        fclose($appfile);

        return Redirect::to('/admin/edit_keywords?success=1');
    }

    public function adminCurrency() {
        $currency_selected = $_POST['currency_selected'];
        /* $keycurrency = Keywords::find(5);
          $original_selection = $keycurrency->keyword; */
        $original_selection = Config::get('app.generic_keywords.Currency');
        if ($original_selection == '$') {
            $original_selection = "USD";
        }
        if ($currency_selected == '$') {
            $currency_selected = "USD";
        }
        if ($currency_selected == $original_selection) {
            // same currency
            $data['success'] = false;
            $data['error_message'] = 'Same Currency.';
        } else {
            $httpAdapter = new \Ivory\HttpAdapter\FileGetContentsHttpAdapter();
            // Create the Yahoo Finance provider
            $yahooProvider = new \Swap\Provider\YahooFinanceProvider($httpAdapter);
            // Create Swap with the provider
            $swap = new \Swap\Swap($yahooProvider);
            $rate = $swap->quote($original_selection . "/" . $currency_selected);
            $rate = json_decode($rate, true);
            $data['success'] = true;
            $data['rate'] = $rate;
        }
        return $data;
    }

    public function save_settings() {
        $settings = Settings::all();
        foreach ($settings as $setting) {
            if (Input::get($setting->id) != NULL) {
                $temp_setting = Settings::find($setting->id);
                $temp_setting->value = Input::get($setting->id);
                $temp_setting->save();
            }
        }
        return Redirect::to('/admin/settings?success=1');
    }

//Installation Settings
    public function installation_settings() {
        $braintree_environment = Config::get('app.braintree_environment');
        $braintree_merchant_id = Config::get('app.braintree_merchant_id');
        $braintree_public_key = Config::get('app.braintree_public_key');
        $braintree_private_key = Config::get('app.braintree_private_key');
        $braintree_cse = Config::get('app.braintree_cse');
        $twillo_account_sid = Config::get('app.twillo_account_sid');
        $twillo_auth_token = Config::get('app.twillo_auth_token');
        $twillo_number = Config::get('app.twillo_number');
        $timezone = Config::get('app.timezone');
        $stripe_publishable_key = Config::get('app.stripe_publishable_key');
        $url = Config::get('app.url');
        $website_title = Config::get('app.website_title');
        $s3_bucket = Config::get('app.s3_bucket');
        $default_payment = Config::get('app.default_payment');
        $stripe_secret_key = Config::get('app.stripe_secret_key');
        $mail_driver = Config::get('mail.driver');
        $email_name = Config::get('mail.from.name');
        $email_address = Config::get('mail.from.address');
        $mandrill_secret = Config::get('services.mandrill.secret');
        $mandrill_username = Config::get('services.mandrill.username');
        $host = Config::get('mail.host');
        /* DEVICE PUSH NOTIFICATION DETAILS */
        $customer_certy_url = Config::get('app.customer_certy_url');
        $customer_certy_pass = Config::get('app.customer_certy_pass');
        $customer_certy_type = Config::get('app.customer_certy_type');
        $provider_certy_url = Config::get('app.provider_certy_url');
        $provider_certy_pass = Config::get('app.provider_certy_pass');
        $provider_certy_type = Config::get('app.provider_certy_type');
        $gcm_browser_key = Config::get('app.gcm_browser_key');
        /* DEVICE PUSH NOTIFICATION DETAILS END */
        $install = array(
            'braintree_environment' => $braintree_environment,
            'braintree_merchant_id' => $braintree_merchant_id,
            'braintree_public_key' => $braintree_public_key,
            'braintree_private_key' => $braintree_private_key,
            'braintree_cse' => $braintree_cse,
            'twillo_account_sid' => $twillo_account_sid,
            'twillo_auth_token' => $twillo_auth_token,
            'twillo_number' => $twillo_number,
            'stripe_publishable_key' => $stripe_publishable_key,
            'stripe_secret_key' => $stripe_secret_key,
            'mail_driver' => $mail_driver,
            'email_address' => $email_address,
            'mandrill_username' => $mandrill_username,
            'email_name' => $email_name,
            'host' => $host,
            'mandrill_secret' => $mandrill_secret,
            'default_payment' => $default_payment,
            /* DEVICE PUSH NOTIFICATION DETAILS */
            'customer_certy_url' => $customer_certy_url,
            'customer_certy_pass' => $customer_certy_pass,
            'customer_certy_type' => $customer_certy_type,
            'provider_certy_url' => $provider_certy_url,
            'provider_certy_pass' => $provider_certy_pass,
            'provider_certy_type' => $provider_certy_type,
            'gcm_browser_key' => $gcm_browser_key,
                /* DEVICE PUSH NOTIFICATION DETAILS END */                );
        $success = Input::get('success');
        $cert_def = 0;
        $cer = Certificates::where('file_type', 'certificate')->where('client', 'apple')->get();
        foreach ($cer as $key) {
            if ($key->default == 1) {
                $cert_def = $key->type;
            }
        }
        $title = ucwords("Installation " . trans('customize.Settings')); /* 'Installation Settings' */
        return View::make('install_settings')
                        ->with('title', $title)
                        ->with('success', $success)
                        ->with('page', 'settings')
                        ->with('cert_def', $cert_def)
                        ->with('install', $install);
    }

    public function finish_install() {
        $braintree_cse = $stripe_publishable_key = $url = $timezone = $website_title = $s3_bucket = $twillo_account_sid = $twillo_auth_token = $twillo_number = $default_payment = $stripe_secret_key = $braintree_environment = $braintree_merchant_id = $braintree_public_key = $braintree_private_key = $customer_certy_url = $customer_certy_pass = $customer_certy_type = $provider_certy_url = $provider_certy_pass = $provider_certy_type = $gcm_browser_key = $key_provider = $key_user = $key_taxi = $key_trip = $key_currency = $total_trip = $cancelled_trip = $total_payment = $completed_trip = $card_payment = $credit_payment = $key_ref_pre = $android_client_app_url = $android_provider_app_url = $ios_client_app_url = $ios_provider_app_url = NULL;
        $braintree_environment = Config::get('app.braintree_environment');
        $braintree_merchant_id = Config::get('app.braintree_merchant_id');
        $braintree_public_key = Config::get('app.braintree_public_key');
        $braintree_private_key = Config::get('app.braintree_private_key');
        $braintree_cse = Config::get('app.braintree_cse');
        $twillo_account_sid = Config::get('app.twillo_account_sid');
        $twillo_auth_token = Config::get('app.twillo_auth_token');
        $twillo_number = Config::get('app.twillo_number');
        $timezone = Config::get('app.timezone');
        $stripe_publishable_key = Config::get('app.stripe_publishable_key');
        $url = Config::get('app.url');
        $website_title = Config::get('app.website_title');
        $s3_bucket = Config::get('app.s3_bucket');
        $default_payment = Config::get('app.default_payment');
        $stripe_secret_key = Config::get('app.stripe_secret_key');
        $mail_driver = Config::get('mail.driver');
        $email_name = Config::get('mail.from.name');
        $email_address = Config::get('mail.from.address');
        $mandrill_secret = Config::get('services.mandrill.secret');
        $host = Config::get('mail.host');
        /* DEVICE PUSH NOTIFICATION DETAILS */
        $customer_certy_url = Config::get('app.customer_certy_url');
        $customer_certy_pass = Config::get('app.customer_certy_pass');
        $customer_certy_type = Config::get('app.customer_certy_type');
        $provider_certy_url = Config::get('app.provider_certy_url');
        $provider_certy_pass = Config::get('app.provider_certy_pass');
        $provider_certy_type = Config::get('app.provider_certy_type');
        $gcm_browser_key = Config::get('app.gcm_browser_key');
        /* DEVICE PUSH NOTIFICATION DETAILS END */
        $install = array(
            'braintree_environment' => $braintree_environment,
            'braintree_merchant_id' => $braintree_merchant_id,
            'braintree_public_key' => $braintree_public_key,
            'braintree_private_key' => $braintree_private_key,
            'braintree_cse' => $braintree_cse,
            'twillo_account_sid' => $twillo_account_sid,
            'twillo_auth_token' => $twillo_auth_token,
            'twillo_number' => $twillo_number,
            'stripe_publishable_key' => $stripe_publishable_key,
            'stripe_secret_key' => $stripe_secret_key,
            'mail_driver' => $mail_driver,
            'email_address' => $email_address,
            'email_name' => $email_name,
            'mandrill_secret' => $mandrill_secret,
            'default_payment' => $default_payment,
            /* DEVICE PUSH NOTIFICATION DETAILS */
            'customer_certy_url' => $customer_certy_url,
            'customer_certy_pass' => $customer_certy_pass,
            'customer_certy_type' => $customer_certy_type,
            'provider_certy_url' => $provider_certy_url,
            'provider_certy_pass' => $provider_certy_pass,
            'provider_certy_type' => $provider_certy_type,
            'gcm_browser_key' => $gcm_browser_key,
                /* DEVICE PUSH NOTIFICATION DETAILS END */
        );        // Modifying Database Config
        if (isset($_POST['sms'])) {
            $twillo_account_sid = Input::get('twillo_account_sid');
            $twillo_auth_token = Input::get('twillo_auth_token');
            $twillo_number = Input::get('twillo_number');

            $appfile = fopen(app_path() . "/config/app.php", "w") or die("Unable to open file!");
            /* $appfile_config = generate_app_config($braintree_cse, $stripe_publishable_key, $url, $timezone, $website_title, $s3_bucket, $twillo_account_sid, $twillo_auth_token, $twillo_number, $default_payment, $stripe_secret_key, $braintree_environment, $braintree_merchant_id, $braintree_public_key, $braintree_private_key); */
            $appfile_config = generate_app_config($braintree_cse, $stripe_publishable_key, $url, $timezone, $website_title, $s3_bucket, $twillo_account_sid, $twillo_auth_token, $twillo_number, $default_payment, $stripe_secret_key, $braintree_environment, $braintree_merchant_id, $braintree_public_key, $braintree_private_key, $customer_certy_url, $customer_certy_pass, $customer_certy_type, $provider_certy_url, $provider_certy_pass, $provider_certy_type, $gcm_browser_key, $key_provider, $key_user, $key_taxi, $key_trip, $key_currency, $total_trip, $cancelled_trip, $total_payment, $completed_trip, $card_payment, $credit_payment, $key_ref_pre, $android_client_app_url, $android_provider_app_url, $ios_client_app_url, $ios_provider_app_url);
            fwrite($appfile, $appfile_config);
            fclose($appfile);
        }

        if (isset($_POST['payment'])) {
            $default_payment = Input::get('default_payment');

            if ($default_payment == 'stripe') {
                if ($stripe_secret_key != trim(Input::get('stripe_secret_key')) || $stripe_publishable_key != trim(Input::get('stripe_publishable_key'))) {
                    /* DELETE CUSTOMER CARDS FROM DATABASE */
                    $delete_un_rq = DB::delete("DELETE FROM payment WHERE 1;");
                    /* DELETE CUSTOMER CARDS FROM DATABASE END */
                    $stripe_secret_key = Input::get('stripe_secret_key');
                    $stripe_publishable_key = Input::get('stripe_publishable_key');
                    $braintree_environment = '';
                    $braintree_merchant_id = '';
                    $braintree_public_key = '';
                    $braintree_private_key = '';
                    $braintree_cse = '';
                    $appfile = fopen(app_path() . "/config/app.php", "w") or die("Unable to open file!");
                    /* $appfile_config = generate_app_config($braintree_cse, $stripe_publishable_key, $url, $timezone, $website_title, $s3_bucket, $twillo_account_sid, $twillo_auth_token, $twillo_number, $default_payment, $stripe_secret_key, $braintree_environment, $braintree_merchant_id, $braintree_public_key, $braintree_private_key); */
                    $appfile_config = generate_app_config($braintree_cse, $stripe_publishable_key, $url, $timezone, $website_title, $s3_bucket, $twillo_account_sid, $twillo_auth_token, $twillo_number, $default_payment, $stripe_secret_key, $braintree_environment, $braintree_merchant_id, $braintree_public_key, $braintree_private_key, $customer_certy_url, $customer_certy_pass, $customer_certy_type, $provider_certy_url, $provider_certy_pass, $provider_certy_type, $gcm_browser_key, $key_provider, $key_user, $key_taxi, $key_trip, $key_currency, $total_trip, $cancelled_trip, $total_payment, $completed_trip, $card_payment, $credit_payment, $key_ref_pre, $android_client_app_url, $android_provider_app_url, $ios_client_app_url, $ios_provider_app_url);
                    fwrite($appfile, $appfile_config);
                    fclose($appfile);
                }
            } else {
                if ($braintree_environment != trim(Input::get('braintree_environment')) || $braintree_merchant_id != trim(Input::get('braintree_merchant_id')) || $braintree_public_key != trim(Input::get('braintree_public_key')) || $braintree_private_key != trim(Input::get('braintree_private_key')) || $braintree_cse != trim(Input::get('braintree_cse'))) {
                    /* DELETE CUSTOMER CARDS FROM DATABASE */
                    $delete_un_rq = DB::delete("DELETE FROM payment WHERE 1;");
                    /* DELETE CUSTOMER CARDS FROM DATABASE END */
                    $stripe_secret_key = '';
                    $stripe_publishable_key = '';
                    $braintree_environment = Input::get('braintree_environment');
                    $braintree_merchant_id = Input::get('braintree_merchant_id');
                    $braintree_public_key = Input::get('braintree_public_key');
                    $braintree_private_key = Input::get('braintree_private_key');
                    $braintree_cse = Input::get('braintree_cse');
                    $appfile = fopen(app_path() . "/config/app.php", "w") or die("Unable to open file!");
                    /* $appfile_config = generate_app_config($braintree_cse, $stripe_publishable_key, $url, $timezone, $website_title, $s3_bucket, $twillo_account_sid, $twillo_auth_token, $twillo_number, $default_payment, $stripe_secret_key, $braintree_environment, $braintree_merchant_id, $braintree_public_key, $braintree_private_key); */
                    $appfile_config = generate_app_config($braintree_cse, $stripe_publishable_key, $url, $timezone, $website_title, $s3_bucket, $twillo_account_sid, $twillo_auth_token, $twillo_number, $default_payment, $stripe_secret_key, $braintree_environment, $braintree_merchant_id, $braintree_public_key, $braintree_private_key, $customer_certy_url, $customer_certy_pass, $customer_certy_type, $provider_certy_url, $provider_certy_pass, $provider_certy_type, $gcm_browser_key, $key_provider, $key_user, $key_taxi, $key_trip, $key_currency, $total_trip, $cancelled_trip, $total_payment, $completed_trip, $card_payment, $credit_payment, $key_ref_pre, $android_client_app_url, $android_provider_app_url, $ios_client_app_url, $ios_provider_app_url);
                    fwrite($appfile, $appfile_config);
                    fclose($appfile);
                }
            }
        }

        // Modifying Mail Config File

        if (isset($_POST['mail'])) {
            $mail_driver = Input::get('mail_driver');
            $email_name = Input::get('email_name');
            $email_address = Input::get('email_address');
            $mandrill_secret = Input::get('mandrill_secret');
            $mandrill_hostname = "";
            if ($mail_driver == 'mail') {
                $mandrill_hostname = "localhost";
            } elseif ($mail_driver == 'mandrill') {
                $mandrill_hostname = Input::get('host_name');
            }
            $mailfile = fopen(app_path() . "/config/mail.php", "w") or die("Unable to open file!");
            $mailfile_config = generate_mail_config($mandrill_hostname, $mail_driver, $email_name, $email_address);
            fwrite($mailfile, $mailfile_config);
            fclose($mailfile);

            if ($mail_driver == 'mandrill') {
                $mandrill_username = Input::get('user_name');
                $servicesfile = fopen(app_path() . "/config/services.php", "w") or die("Unable to open file!");
                $servicesfile_config = generate_services_config($mandrill_secret, $mandrill_username);
                fwrite($servicesfile, $servicesfile_config);
                fclose($servicesfile);
            }
        }
        $install = array(
            'braintree_environment' => $braintree_environment,
            'braintree_merchant_id' => $braintree_merchant_id,
            'braintree_public_key' => $braintree_public_key,
            'braintree_private_key' => $braintree_private_key,
            'braintree_cse' => $braintree_cse,
            'twillo_account_sid' => $twillo_account_sid,
            'twillo_auth_token' => $twillo_auth_token,
            'twillo_number' => $twillo_number,
            'stripe_publishable_key' => $stripe_publishable_key,
            'stripe_secret_key' => $stripe_secret_key,
            'mail_driver' => $mail_driver,
            'email_address' => $email_address,
            'email_name' => $email_name,
            'mandrill_secret' => $mandrill_secret,
            'default_payment' => $default_payment,
            /* DEVICE PUSH NOTIFICATION DETAILS */
            'customer_certy_url' => $customer_certy_url,
            'customer_certy_pass' => $customer_certy_pass,
            'customer_certy_type' => $customer_certy_type,
            'provider_certy_url' => $provider_certy_url,
            'provider_certy_pass' => $provider_certy_pass,
            'provider_certy_type' => $provider_certy_type,
            'gcm_browser_key' => $gcm_browser_key,
                /* DEVICE PUSH NOTIFICATION DETAILS END */
        );
        return Redirect::to('/admin/settings?success=1')
                        ->with('install', $install);
    }

    public function addcerti() {
        $braintree_cse = $stripe_publishable_key = $url = $timezone = $website_title = $s3_bucket = $twillo_account_sid = $twillo_auth_token = $twillo_number = $default_payment = $stripe_secret_key = $braintree_environment = $braintree_merchant_id = $braintree_public_key = $braintree_private_key = $customer_certy_url = $customer_certy_pass = $customer_certy_type = $provider_certy_url = $provider_certy_pass = $provider_certy_type = $gcm_browser_key = $key_provider = $key_user = $key_taxi = $key_trip = $key_currency = $total_trip = $cancelled_trip = $total_payment = $completed_trip = $card_payment = $credit_payment = $key_ref_pre = $android_client_app_url = $android_provider_app_url = $ios_client_app_url = $ios_provider_app_url = NULL;
        $is_certy_change = 0;
        $braintree_environment = Config::get('app.braintree_environment');
        $braintree_merchant_id = Config::get('app.braintree_merchant_id');
        $braintree_public_key = Config::get('app.braintree_public_key');
        $braintree_private_key = Config::get('app.braintree_private_key');
        $braintree_cse = Config::get('app.braintree_cse');
        $twillo_account_sid = Config::get('app.twillo_account_sid');
        $twillo_auth_token = Config::get('app.twillo_auth_token');
        $twillo_number = Config::get('app.twillo_number');
        $timezone = Config::get('app.timezone');
        $stripe_publishable_key = Config::get('app.stripe_publishable_key');
        $url = Config::get('app.url');
        $website_title = Config::get('app.website_title');
        $s3_bucket = Config::get('app.s3_bucket');
        $default_payment = Config::get('app.default_payment');
        $stripe_secret_key = Config::get('app.stripe_secret_key');
        $mail_driver = Config::get('mail.driver');
        $email_name = Config::get('mail.from.name');
        $email_address = Config::get('mail.from.address');
        $mandrill_secret = Config::get('services.mandrill.secret');
        $host = Config::get('mail.host');
        /* DEVICE PUSH NOTIFICATION DETAILS */
        $customer_certy_url = Config::get('app.customer_certy_url');
        $customer_certy_pass = Config::get('app.customer_certy_pass');
        $customer_certy_type = Config::get('app.customer_certy_type');
        $provider_certy_url = Config::get('app.provider_certy_url');
        $provider_certy_pass = Config::get('app.provider_certy_pass');
        $provider_certy_type = Config::get('app.provider_certy_type');
        $gcm_browser_key = Config::get('app.gcm_browser_key');
        /* DEVICE PUSH NOTIFICATION DETAILS END */
        $install = array(
            'braintree_environment' => $braintree_environment,
            'braintree_merchant_id' => $braintree_merchant_id,
            'braintree_public_key' => $braintree_public_key,
            'braintree_private_key' => $braintree_private_key,
            'braintree_cse' => $braintree_cse,
            'twillo_account_sid' => $twillo_account_sid,
            'twillo_auth_token' => $twillo_auth_token,
            'twillo_number' => $twillo_number,
            'stripe_publishable_key' => $stripe_publishable_key,
            'stripe_secret_key' => $stripe_secret_key,
            'mail_driver' => $mail_driver,
            'email_address' => $email_address,
            'email_name' => $email_name,
            'mandrill_secret' => $mandrill_secret,
            'default_payment' => $default_payment,
            /* DEVICE PUSH NOTIFICATION DETAILS */
            'customer_certy_url' => $customer_certy_url,
            'customer_certy_pass' => $customer_certy_pass,
            'customer_certy_type' => $customer_certy_type,
            'provider_certy_url' => $provider_certy_url,
            'provider_certy_pass' => $provider_certy_pass,
            'provider_certy_type' => $provider_certy_type,
            'gcm_browser_key' => $gcm_browser_key,
                /* DEVICE PUSH NOTIFICATION DETAILS END */
        );
        $count = 0;

        // apple user
        if (Input::hasFile('user_certi_a') && Input::has('user_pass_a') && Input::has('cert_type_a')) {
            // Upload File
            $certy_password_client = $customer_certy_pass = trim(Input::get('user_pass_a'));
            $customer_certy_type = Input::get('cert_type_a');
            if ($customer_certy_type) {
                $client_certy_type = "ssl";
            } else {
                $client_certy_type = "sandboxSsl";
            }
            $file_name = "Client_certy";
            $ext = Input::file('user_certi_a')->getClientOriginalExtension();
            if ($ext == "PEM" || $ext == "pem") {
                /* Input::file('user_certi_a')->move(app_path() . "/ios_push/iph_cert/", $file_name . "." . $ext); */
                Input::file('user_certi_a')->move(public_path() . "/apps/ios_push/iph_cert", $file_name . "." . $ext);

                /* chmod(app_path() . "/ios_push/iph_cert/" . $file_name . "." . $ext, 0777); */

                $local_url = $file_name . "." . $ext;

                // Upload to S3
                if (Config::get('app.s3_bucket') != "") {
                    $s3 = App::make('aws')->get('s3');
                    $pic = $s3->putObject(array(
                        'Bucket' => Config::get('app.s3_bucket'),
                        'Key' => $file_name,
                        'SourceFile' => app_path() . "/ios_push/iph_cert/" . $local_url,
                    ));

                    $s3->putObjectAcl(array(
                        'Bucket' => Config::get('app.s3_bucket'),
                        'Key' => $file_name,
                        'ACL' => 'public-read'
                    ));

                    $customer_certy_url = $s3->getObjectUrl(Config::get('app.s3_bucket'), $file_name);
                } else {
                    /* $customer_certy_url = app_path() . '/ios_push/iph_cert/' . $local_url; */
                    /* $customer_certy_url = app_path() . "/ios_push/iph_cert/" . $local_url; */
                }
                /* $customer_certy_url = public_path() . "/apps/ios_push/iph_cert" . $local_url; */
                $customer_certy_url = asset_url() . '/apps/ios_push/iph_cert/' . $local_url;
                /* if (isset($theme->logo)) {
                  $icon = asset_url() . '/uploads/' . $theme->logo;
                  unlink_image($icon);
                  }
                  $theme->logo = $local_url; */
                $update_client_certy = "<?php

//session_start();

//require_once  'database.php';
//error_reporting(false);

class ClientApns {

    public \$ctx;
    public \$fp;
    private \$ssl = 'ssl://gateway.push.apple.com:2195';
    private \$passphrase = '" . $certy_password_client . "';
    private \$sandboxCertificate = 'iph_cert/" . $local_url . "';
    private \$sandboxSsl = 'ssl://gateway.sandbox.push.apple.com:2195';
    private \$sandboxFeedback = 'ssl://feedback.sandbox.push.apple.com:2196';
    private \$message = 'ManagerMaster';

    public function __construct() {
        \$this->initialize_apns();
    }

    private function getCertificatePath() {
        /*return app_path() . '/ios_push/' . \$this->sandboxCertificate;*/
        return public_path().'/apps/ios_push/'.\$this->sandboxCertificate;
    }
    
    public function initialize_apns() {
        try {
            \$this->ctx = stream_context_create();

            //stream_context_set_option(\$ctx, 'ssl', 'cafile', 'entrust_2048_ca.cer');
            stream_context_set_option(\$this->ctx, 'ssl', 'local_cert', \$this->getCertificatePath());
            stream_context_set_option(\$this->ctx, 'ssl', 'passphrase', \$this->passphrase); // use this if you are using a passphrase
            // Open a connection to the APNS servers
            \$this->fp = @stream_socket_client(\$this->" . $client_certy_type . ", \$err, \$errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, \$this->ctx);

            if (\$this->fp) {
                //Log::info('Successfully connected to server of APNS');
                //echo 'Successfully connected to server of APNS ckUberForXOwner.pem';
            } else {
                //Log::error('Error in connection while trying to connect to APNS');
                //echo 'Error in connection while trying to connect to APNS ckUberForXOwner.pem';
            }
        } catch (Exception \$e) {
            //Log::error(\$e);
        }
    }

    public function send_notification(\$devices, \$message) {
        try {
            \$errCounter = 0;
            \$payload = json_encode(array('aps' => \$message));
            \$result = 0;
            \$bodyError = '';
            foreach (\$devices as \$key => \$value) {
                \$msg = chr(0) . pack('n', 32) . pack('H*', str_replace(' ', '', \$value)) . pack('n', (strlen(\$payload))) . \$payload;
                \$result = fwrite(\$this->fp, \$msg);
                \$bodyError .= 'result: ' . \$result . ', devicetoken: ' . \$value;
                if (!\$result) {
                    \$errCounter = \$errCounter + 1;
                }
            }
			//echo 'Result :- '.\$result;
            if (\$result) {
                //Log::info('Delivered Message to APNS' . PHP_EOL);
                //echo 'Delivered Message to APNS' . PHP_EOL;
                \$bool_result = true;
            } else {
                //Log::info('Could not Deliver Message to APNS' . PHP_EOL);
                //echo 'Could not Deliver Message to APNS' . PHP_EOL;
                \$bool_result = false;
            }

            @socket_close(\$this->fp);
            @fclose(\$this->fp);
            return \$bool_result;
        } catch (Exception \$e) {
            //Log::error(\$e);
        }
    }

}
";
                $t = file_put_contents(app_path() . '/ios_push/apns.php', $update_client_certy);
                /* chmod(app_path() . '/ios_push/apns.php', 0777); */
                $is_certy_change ++;
            } else {
                return Redirect::to('/admin/settings/installation?success=3')
                                ->with('install', $install);
            }
        }
        if (Input::hasFile('prov_certi_a') && Input::has('prov_pass_a') && Input::has('cert_type_a')) {
            $certy_password_driver = $provider_certy_pass = trim(Input::get('prov_pass_a'));

            $provider_certy_type = Input::get('cert_type_a');
            if ($provider_certy_type) {
                $driver_certy_type = "ssl";
            } else {
                $driver_certy_type = "sandboxSsl";
            }
            // Upload File
            $file_name = "Walker_certy";
            $ext = Input::file('prov_certi_a')->getClientOriginalExtension();
            if ($ext == "PEM" || $ext == "pem") {
                /* Input::file('prov_certi_a')->move(app_path() . "/ios_push/walker/iph_cert/", $file_name . "." . $ext); */
                Input::file('prov_certi_a')->move(public_path() . "/apps/ios_push/walker/iph_cert", $file_name . "." . $ext);

                $local_url = $file_name . "." . $ext;

                /* chmod(app_path() . "/ios_push/walker/iph_cert/" . $file_name . "." . $ext, 0777); */

                // Upload to S3
                if (Config::get('app.s3_bucket') != "") {
                    $s3 = App::make('aws')->get('s3');
                    $pic = $s3->putObject(array(
                        'Bucket' => Config::get('app.s3_bucket'),
                        'Key' => $file_name,
                        'SourceFile' => app_path() . "/ios_push/walker/iph_cert/" . $local_url,
                    ));

                    $s3->putObjectAcl(array(
                        'Bucket' => Config::get('app.s3_bucket'),
                        'Key' => $file_name,
                        'ACL' => 'public-read'
                    ));

                    $provider_certy_url = $s3->getObjectUrl(Config::get('app.s3_bucket'), $file_name);
                } else {
                    /* $provider_certy_url = app_path() . '/ios_push/walker/iph_cert/' . $local_url; */
                    /* $provider_certy_url = app_path() . "/ios_push/walker/iph_cert/" . $local_url; */
                }
                $provider_certy_url = asset_url() . '/apps/ios_push/walker/iph_cert/' . $local_url;
                /* if (isset($theme->logo)) {
                  $icon = asset_url() . '/uploads/' . $theme->logo;
                  unlink_image($icon);
                  }
                  $theme->logo = $local_url; */
                $update_client_certy = "<?php

//session_start();

//require_once  'database.php';
//error_reporting(false);

class ProviderApns {

    public \$ctx;
    public \$fp;
    private \$ssl = 'ssl://gateway.push.apple.com:2195';
    private \$passphrase = '" . $certy_password_driver . "';
    private \$sandboxCertificate = 'walker/iph_cert/" . $local_url . "';
    private \$sandboxSsl = 'ssl://gateway.sandbox.push.apple.com:2195';
    private \$sandboxFeedback = 'ssl://feedback.sandbox.push.apple.com:2196';
    private \$message = 'ManagerMaster';

    public function __construct() {
        \$this->initialize_apns();
    }

    private function getCertificatePath() {
        /*return app_path() . '/ios_push/' . \$this->sandboxCertificate;*/
        return public_path().'/apps/ios_push/'.\$this->sandboxCertificate;
    }
    
    public function initialize_apns() {
        try {
            \$this->ctx = stream_context_create();

            //stream_context_set_option(\$ctx, 'ssl', 'cafile', 'entrust_2048_ca.cer');
            stream_context_set_option(\$this->ctx, 'ssl', 'local_cert', \$this->getCertificatePath());
            stream_context_set_option(\$this->ctx, 'ssl', 'passphrase', \$this->passphrase); // use this if you are using a passphrase
            // Open a connection to the APNS servers
            \$this->fp = @stream_socket_client(\$this->" . $driver_certy_type . ", \$err, \$errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, \$this->ctx);

            if (\$this->fp) {
                //Log::info('Successfully connected to server of APNS');
                /*echo 'Successfully connected to server of APNS ckUberForXProvider.pem';*/
            } else {
                //Log::error('Error in connection while trying to connect to APNS');
                /*echo 'Error in connection while trying to connect to APNS ckUberForXProvider.pem';*/
            }
        } catch (Exception \$e) {
            //Log::error(\$e);
        }
    }

    public function send_notification(\$devices, \$message) {
        try {
            \$errCounter = 0;
            \$payload = json_encode(array('aps' => \$message));
            \$result = 0;
            \$bodyError = '';
			/*print_r(\$devices);*/
            foreach (\$devices as \$key => \$value) {
				/*echo \$value;*/
                \$msg = chr(0) . pack('n', 32) . pack('H*', str_replace(' ', '', \$value)) . pack('n', (strlen(\$payload))) . \$payload;
                \$result = fwrite(\$this->fp, \$msg);
                \$bodyError .= 'result: ' . \$result . ', devicetoken: ' . \$value;
                if (!\$result) {
                    \$errCounter = \$errCounter + 1;
                }
            }
			/*echo 'Result :- '.\$result;*/
            if (\$result) {
                //Log::info('Delivered Message to APNS' . PHP_EOL);
                /*echo 'Delivered Message to APNS' . PHP_EOL;*/
                \$bool_result = true;
            } else {
                //Log::info('Could not Deliver Message to APNS' . PHP_EOL);
                /*echo 'Could not Deliver Message to APNS' . PHP_EOL;*/
                \$bool_result = false;
            }

            @socket_close(\$this->fp);
            @fclose(\$this->fp);
            return \$bool_result;
        } catch (Exception \$e) {
            //Log::error(\$e);
        }
    }

}
";
                $t = file_put_contents(app_path() . '/ios_push/walker/apns.php', $update_client_certy);
                /* chmod(app_path() . '/ios_push/walker/apns.php', 0777); */
                $is_certy_change ++;
            } else {
                return Redirect::to('/admin/settings/installation?success=3')
                                ->with('install', $install);
            }
        }
        if (Input::has('gcm_key')) {
            /* "AIzaSyAKe3XmUV93WvHJvII4Qzpf0R052mxb0KI" */
            $app_gcm_key = $gcm_browser_key = trim(Input::get('gcm_key'));
            if ($app_gcm_key != "") {
                $update_client_certy = "<?php

/*array(
    'GOOGLE_API_KEY' => '" . $app_gcm_key . "',
);*/
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of GCM
 *
 * @author Ravi Tamada
 */
define('GOOGLE_API_KEY', '" . $app_gcm_key . "');
/*define('GOOGLE_API_KEY', 'AIzaSyAKe3XmUV93WvHJvII4Qzpf0R052mxb0KI');*/
/*define('GOOGLE_API_KEY', 'AIzaSyC0JjF-O72-gUvUmUm_dsHHvG5o3aWosp8');*/

class GCM {

    //put your code here
    // constructor
    function __construct() {
        
    }

    /**
     * Sending Push Notification
     */
    public function send_notification(\$registatoin_ids, \$message) {
        // include config
        // include_once 'const.php';
        /* include_once 'config.php'; */
        // Set POST variables
        \$url = 'https://android.googleapis.com/gcm/send';

        \$fields = array(
            'registration_ids' => \$registatoin_ids,
            'data' => \$message,
        );

        \$headers = array(
            'Authorization: key=' . GOOGLE_API_KEY,
            'Content-Type: application/json'
        );
        // Open connection
        \$ch = curl_init();

        // Set the url, number of POST vars, POST data
        curl_setopt(\$ch, CURLOPT_URL, \$url);

        curl_setopt(\$ch, CURLOPT_POST, true);
        curl_setopt(\$ch, CURLOPT_HTTPHEADER, \$headers);
        curl_setopt(\$ch, CURLOPT_RETURNTRANSFER, true);

        // Disabling SSL Certificate support temporarly
        curl_setopt(\$ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt(\$ch, CURLOPT_POSTFIELDS, json_encode(\$fields));

        // Execute post
        \$result = curl_exec(\$ch);
        if (\$result === FALSE) {
            //die('Curl failed: ' . curl_error(\$ch));
            //Log::error('Curl failed: ' . curl_error(\$ch));
        }
        else{
            //echo \$result;
            //Log::error(\$result);
        }

        // Close connection
        /*curl_close(\$ch);
         echo \$result/*.'\n\n'.json_encode(\$fields); */
    }

}
?>
";
                $t = file_put_contents(app_path() . '/gcm/GCM_1.php', $update_client_certy);
                $is_certy_change ++;
            } else {
                return Redirect::to('/admin/settings/installation?success=4')
                                ->with('install', $install);
            }
        }
        /* if (Input::hasFile('user_certi_a')) {
          $certi_user_a = Certificates::where('client', 'apple')->where('user_type', 0)->where('file_type', 'certificate')->where('type', Input::get('cert_type_a'))->first();
          if ($certi_user_a != NULL) {
          //user
          $path = $certi_user_a->name;
          //Log::info($path);
          $filename = basename($path);
          //Log::info($filename);
          if (file_exists($path)) {
          try {
          unlink(public_path() . "/apps/ios_push/iph_cert/" . $filename);
          } catch (Exception $e) {

          }
          }
          $key = Certificates::where('client', 'apple')->where('user_type', 0)->where('file_type', 'certificate')->first();
          } else {
          $key = new Certificates();
          $key->client = 'apple';
          $key->type = Input::get('cert_type_a');
          $key->user_type = 0;
          $key->file_type = 'certificate';
          }
          // upload image
          $file_name = time();
          $file_name .= rand();
          $file_name = sha1($file_name);

          //Log::info(Input::file('user_certi_a'));

          $ext = Input::file('user_certi_a')->getClientOriginalExtension();
          Input::file('user_certi_a')->move(public_path() . "/apps/ios_push/iph_cert", $file_name . "." . $ext);
          $local_url = $file_name . "." . $ext;

          // Upload to S3
          if (Config::get('app.s3_bucket') != "") {
          $s3 = App::make('aws')->get('s3');
          $pic = $s3->putObject(array(
          'Bucket' => Config::get('app.s3_bucket'),
          'Key' => $file_name,
          'SourceFile' => public_path() . "/apps/ios_push/iph_cert/" . $local_url,
          ));
          $s3->putObjectAcl(array(
          'Bucket' => Config::get('app.s3_bucket'),
          'Key' => $file_name,
          'ACL' => 'public-read'
          ));
          $s3_url = $s3->getObjectUrl(Config::get('app.s3_bucket'), $file_name);
          }
          //Log::info('path = ' . print_r($local_url, true));
          $key->name = $local_url;
          $count = $count + 1;
          $key->save();
          }

          // User passphrase file.
          if (Input::has('user_pass_a')) {
          $user_key_db = Certificates::where('client', 'apple')->where('user_type', 0)->where('file_type', 'passphrase')->where('type', Input::get('cert_type_a'))->first();
          if ($user_key_db == NULL) {
          $key = new Certificates();
          $key->client = 'apple';
          $key->type = Input::get('cert_type_a');
          $key->user_type = 0;
          $key->file_type = 'passphrase';
          } else {
          $key = Certificates::where('client', 'apple')->where('user_type', 0)->where('file_type', 'passphrase')->first();
          }
          $key->name = Input::get('user_pass_a');
          $count = $count + 1;
          $key->save();
          }

          // apple provider
          if (Input::hasFile('prov_certi_a')) {
          $certi_prov_a = Certificates::where('client', 'apple')->where('user_type', 1)->where('file_type', 'certificate')->where('type', Input::get('cert_type_a'))->first();
          if ($certi_prov_a != NULL) {
          //user
          $path = $certi_prov_a->name;
          //Log::info($path);
          $filename = basename($path);
          //Log::info($filename);
          try {
          unlink(public_path() . "/apps/ios_push/walker/iph_cert/" . $filename);
          } catch (Exception $e) {

          }
          $key = Certificates::where('client', 'apple')->where('user_type', 1)->where('file_type', 'certificate')->first();
          } else {
          $key = new Certificates();
          $key->client = 'apple';
          $key->type = Input::get('cert_type_a');
          $key->user_type = 1;
          $key->file_type = 'certificate';
          }
          // upload image
          $file_name = time();
          $file_name .= rand();
          $file_name = sha1($file_name);

          $ext = Input::file('prov_certi_a')->getClientOriginalExtension();
          Input::file('prov_certi_a')->move(public_path() . "/apps/ios_push/walker/iph_cert", $file_name . "." . $ext);
          $local_url = $file_name . "." . $ext;

          // Upload to S3
          if (Config::get('app.s3_bucket') != "") {
          $s3 = App::make('aws')->get('s3');
          $pic = $s3->putObject(array(
          'Bucket' => Config::get('app.s3_bucket'),
          'Key' => $file_name,
          'SourceFile' => public_path() . "/apps/ios_push/walker/iph_cert/" . $local_url,
          ));
          $s3->putObjectAcl(array(
          'Bucket' => Config::get('app.s3_bucket'),
          'Key' => $file_name,
          'ACL' => 'public-read'
          ));
          }
          //Log::info('path = ' . print_r($local_url, true));
          $key->name = $local_url;
          $count = $count + 1;
          $key->save();
          }

          // Provider passphrase file.
          if (Input::has('prov_pass_a')) {
          $user_key_db = Certificates::where('client', 'apple')->where('user_type', 1)->where('file_type', 'passphrase')->where('type', Input::get('cert_type_a'))->first();
          if ($user_key_db == NULL) {
          $key = new Certificates();
          $key->client = 'apple';
          $key->type = Input::get('cert_type_a');
          $key->user_type = 1;
          $key->file_type = 'passphrase';
          } else {
          $key = Certificates::where('client', 'apple')->where('user_type', 1)->where('file_type', 'passphrase')->first();
          }
          $key->name = Input::get('prov_pass_a');
          $count = $count + 1;
          $key->save();
          }

          // gcm key file.
          if (Input::has('gcm_key')) {
          $gcm_key_db = Certificates::where('client', 'gcm')->first();
          if ($gcm_key_db == NULL) {
          $key = new Certificates();
          $key->client = 'gcm';
          $key->type = Input::get('cert_type_a');
          $key->user_type = 0;
          $key->file_type = 'browser_key';
          } else {
          $key = Certificates::where('client', 'gcm')->first();
          }
          $key->name = Input::get('gcm_key');
          $count = $count + 1;
          $key->save();
          }

          //Log::info("count = " . print_r($count, true));

          $cert_def = Input::get('cert_default');
          $certa = Certificates::where('client', 'apple')->get();
          foreach ($certa as $ca) {
          $def = Certificates::where('id', $ca->id)->first();
          $def->default = 0;
          $def->save();
          }
          $certs = Certificates::where('client', 'apple')->where('type', $cert_def)->get();
          foreach ($certs as $defc) {
          $def = Certificates::where('id', $defc->id)->first();
          //Log::info('def = ' . print_r($def, true));
          $def->default = 1;
          $def->save();
          } */
        $android_client_app_url = NULL;
        if (Input::has('android_client_app_url')) {
            $android_client_app_url = Input::get('android_client_app_url');
        }
        $android_provider_app_url = NULL;
        if (Input::has('android_provider_app_url')) {
            $android_provider_app_url = Input::get('android_provider_app_url');
        }
        $ios_client_app_url = NULL;
        if (Input::has('ios_client_app_url')) {
            $ios_client_app_url = Input::get('ios_client_app_url');
        }
        $ios_provider_app_url = NULL;
        if (Input::has('ios_provider_app_url')) {
            $ios_provider_app_url = Input::get('ios_provider_app_url');
        }
        $appfile = fopen(app_path() . "/config/app.php", "w") or die("Unable to open file!");
        /* $appfile_config = generate_app_config($braintree_cse, $stripe_publishable_key, $url, $timezone, $website_title, $s3_bucket, $twillo_account_sid, $twillo_auth_token, $twillo_number, $default_payment, $stripe_secret_key, $braintree_environment, $braintree_merchant_id, $braintree_public_key, $braintree_private_key, $customer_certy_url, $customer_certy_pass, $customer_certy_type, $provider_certy_url, $provider_certy_pass, $provider_certy_type, $gcm_browser_key, $android_client_app_url, $android_provider_app_url, $ios_client_app_url, $ios_provider_app_url); */
        $appfile_config = generate_app_config($braintree_cse, $stripe_publishable_key, $url, $timezone, $website_title, $s3_bucket, $twillo_account_sid, $twillo_auth_token, $twillo_number, $default_payment, $stripe_secret_key, $braintree_environment, $braintree_merchant_id, $braintree_public_key, $braintree_private_key, $customer_certy_url, $customer_certy_pass, $customer_certy_type, $provider_certy_url, $provider_certy_pass, $provider_certy_type, $gcm_browser_key, $key_provider, $key_user, $key_taxi, $key_trip, $key_currency, $total_trip, $cancelled_trip, $total_payment, $completed_trip, $card_payment, $credit_payment, $key_ref_pre, $android_client_app_url, $android_provider_app_url, $ios_client_app_url, $ios_provider_app_url);
        fwrite($appfile, $appfile_config);
        fclose($appfile);

        $install = array(
            'braintree_environment' => $braintree_environment,
            'braintree_merchant_id' => $braintree_merchant_id,
            'braintree_public_key' => $braintree_public_key,
            'braintree_private_key' => $braintree_private_key,
            'braintree_cse' => $braintree_cse,
            'twillo_account_sid' => $twillo_account_sid,
            'twillo_auth_token' => $twillo_auth_token,
            'twillo_number' => $twillo_number,
            'stripe_publishable_key' => $stripe_publishable_key,
            'stripe_secret_key' => $stripe_secret_key,
            'mail_driver' => $mail_driver,
            'email_address' => $email_address,
            'email_name' => $email_name,
            'mandrill_secret' => $mandrill_secret,
            'default_payment' => $default_payment,
            /* DEVICE PUSH NOTIFICATION DETAILS */
            'customer_certy_url' => $customer_certy_url,
            'customer_certy_pass' => $customer_certy_pass,
            'customer_certy_type' => $customer_certy_type,
            'provider_certy_url' => $provider_certy_url,
            'provider_certy_pass' => $provider_certy_pass,
            'provider_certy_type' => $provider_certy_type,
            'gcm_browser_key' => $gcm_browser_key,
                /* DEVICE PUSH NOTIFICATION DETAILS END */
        );
        /* echo asset_url();
          echo "<br>";
          echo $provider_certy_url;
          echo $customer_certy_url; */
        if ($is_certy_change > 0) {
            return Redirect::to('/admin/settings/installation?success=1')->with('install', $install);
        } else {
            return Redirect::to('/admin/settings/installation?success=5')
                            ->with('install', $install);
        }
    }

    //Sort Owners
    public function sortur() {
        $valu = $_GET['valu'];
        $type = $_GET['type'];
        Session::put('valu', $valu);
        Session::put('type', $type);
        if ($type == 'userid') {
            $typename = "Owner ID";
            $users = Owner::orderBy('id', $valu)->paginate(10);
        } elseif ($type == 'username') {
            $typename = "Owner Name";
            $users = Owner::orderBy('contact_name', $valu)->paginate(10);
        } elseif ($type == 'useremail') {
            $typename = "Owner Email";
            $users = Owner::orderBy('email', $valu)->paginate(10);
        }
        $title = ucwords(trans('customize.User') . 's' . " | Sorted by " . $typename . " in " . $valu); /* 'Owners | Sorted by ' . $typename . ' in ' . $valu */
        return View::make('owners')
                        ->with('title', $title)
                        ->with('page', 'owners')
                        ->with('owners', $users);
    }

    public function sortpv() {
        $valu = $_GET['valu'];
        $type = $_GET['type'];
        Session::put('valu', $valu);
        Session::put('type', $type);
        if ($type == 'provid') {
            $typename = "Providers ID";
            /* $providers = Walker::orderBy('id', $valu)->paginate(10); */
            $subQuery = DB::table('request_meta')
                    ->select(DB::raw('count(*)'))
                    ->whereRaw('walker_id = walker.id and status != 0');
            $subQuery1 = DB::table('request_meta')
                    ->select(DB::raw('count(*)'))
                    ->whereRaw('walker_id = walker.id and status=1');

            $providers = DB::table('walker')
                ->leftJoin('transportation_provider','walker.transportation_provider_id','=','transportation_provider.id')
                ->select('transportation_provider.company as transportation_provider_company','walker.*',
                    DB::raw("(" . $subQuery->toSql() . ") as 'total_requests'"),
                    DB::raw("(" . $subQuery1->toSql() . ") as 'accepted_requests'"))->where('deleted_at', NULL)
                    /* ->where('walker.is_deleted', 0) */
                    ->where('walker.deleted_at', '=', NULL)
                    ->orderBy('walker.id', $valu)
                    ->paginate(10);
        } elseif ($type == 'pvname') {
            $typename = "Providers Name";
            /* $providers = Walker::orderBy('contact_name', $valu)->paginate(10); */
            $subQuery = DB::table('request_meta')
                    ->select(DB::raw('count(*)'))
                    ->whereRaw('walker_id = walker.id and status != 0');
            $subQuery1 = DB::table('request_meta')
                    ->select(DB::raw('count(*)'))
                    ->whereRaw('walker_id = walker.id and status=1');

            $providers = DB::table('walker')
                ->leftJoin('transportation_provider','walker.transportation_provider_id','=','transportation_provider.id')
                ->select('transportation_provider.company as transportation_provider_company','walker.*',
                        DB::raw("(" . $subQuery->toSql() . ") as 'total_requests'"), DB::raw("(" . $subQuery1->toSql() . ") as 'accepted_requests'"))->where('deleted_at', NULL)
                    /* ->where('walker.is_deleted', 0) */
                    ->where('walker.deleted_at', '=', NULL)
                    ->orderBy('walker.contact_name', $valu)
                    ->paginate(10);
        } elseif ($type == 'pvemail') {
            $typename = "Providers Email";
            /* $providers = Walker::orderBy('email', $valu)->paginate(10); */
            $subQuery = DB::table('request_meta')
                    ->select(DB::raw('count(*)'))
                    ->whereRaw('walker_id = walker.id and status != 0');
            $subQuery1 = DB::table('request_meta')
                    ->select(DB::raw('count(*)'))
                    ->whereRaw('walker_id = walker.id and status=1');

            $providers = DB::table('walker')
                ->leftJoin('transportation_provider','walker.transportation_provider_id','=','transportation_provider.id')
                ->select('transportation_provider.company as transportation_provider_company','walker.*',
                        DB::raw("(" . $subQuery->toSql() . ") as 'total_requests'"),
                        DB::raw("(" . $subQuery1->toSql() . ") as 'accepted_requests'"))->where('deleted_at', NULL)
                    /* ->where('walker.is_deleted', 0) */
                    ->where('walker.deleted_at', '=', NULL)
                    ->orderBy('walker.email', $valu)
                    ->paginate(10);
        } elseif ($type == 'pvaddress') {
            $typename = "Providers Address";
            /* $providers = Walker::orderBy('address', $valu)->paginate(10); */
            $subQuery = DB::table('request_meta')
                    ->select(DB::raw('count(*)'))
                    ->whereRaw('walker_id = walker.id and status != 0');
            $subQuery1 = DB::table('request_meta')
                    ->select(DB::raw('count(*)'))
                    ->whereRaw('walker_id = walker.id and status=1');

            $providers = DB::table('walker')
                ->leftJoin('transportation_provider','walker.transportation_provider_id','=','transportation_provider.id')
                ->select('transportation_provider.company as transportation_provider_company','walker.*',
                        DB::raw("(" . $subQuery->toSql() . ") as 'total_requests'"), DB::raw("(" . $subQuery1->toSql() . ") as 'accepted_requests'"))->where('deleted_at', NULL)
                    /* ->where('walker.is_deleted', 0) */
                    ->where('walker.deleted_at', '=', NULL)
                    ->orderBy('walker.address', $valu)
                    ->paginate(10);
        }
        $title = ucwords(trans('customize.Provider') . 's' . " | Sorted by " . $typename . " in " . $valu); /* 'Providers | Sorted by ' . $typename . ' in ' . $valu */
        return View::make('walkers')
                        ->with('title', $title)
                        ->with('page', 'walkers')
                        ->with('walkers', $providers);
    }

    public function sortpvtype() {
        $success = Input::get('success');
        $valu = $_GET['valu'];
        $type = $_GET['type'];
        Session::put('valu', $valu);
        Session::put('type', $type);
        if ($type == 'provid') {
            $typename = "Providers Type ID";
            $providers = ProviderType::orderBy('id', $valu)->paginate(10);
        } elseif ($type == 'pvname') {
            $typename = "Providers Name";
            $providers = ProviderType::orderBy('name', $valu)->paginate(10);

        }
        $settings = Settings::where('key', 'default_distance_unit')->first();
        $unit = $settings->value;
        if ($unit == 0) {
            $unit_set = 'kms';
        } elseif ($unit == 1) {
            $unit_set = 'miles';
        }
        $title = ucwords(trans('customize.Provider') . " Types" . " | Sorted by " . $typename . " in " . $valu); /* 'Provider Types | Sorted by ' . $typename . ' in ' . $valu */
        return View::make('list_provider_types')
                        ->with('title', $title)
                        ->with('page', 'provider-type')
                        ->with('success', $success)
                        ->with('unit_set', $unit_set)
                        ->with('types', $providers);
    }

    public function sortreq() {
        $valu = $_GET["valu"];
        $type = $_GET["type"];
        Session::put('valu', $valu);
        Session::put('type', $type);
        if ($type == 'reqid') {
            $typename = "Request ID";
            $requests = DB::table('request')
                    ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                    ->leftJoin('walker', 'request.current_walker', '=', 'walker.id')
                    ->groupBy('request.id')
                    ->select('owner.contact_name as owner_contact_name',  'walker.contact_name as walker_contact_name',  'owner.id as owner_id', 'walker.id as walker_id', 'request.id as id', 'request.created_at as date', 'request.is_started', 'request.is_walker_arrived', 'request.is_completed', 'request.is_paid', 'request.is_walker_started', 'request.confirmed_walker'
                            , 'request.status', 'request.time', 'request.distance', 'request.total', 'request.is_cancelled', 'request.transfer_amount', 'request.payment_mode')
                    ->orderBy('request.id', $valu)
                    ->paginate(10);
        } elseif ($type == 'owner') {
            $typename = "Owner Name";
            $requests = DB::table('request')
                    ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                    ->leftJoin('walker', 'request.current_walker', '=', 'walker.id')
                    ->groupBy('request.id')
                    ->select('owner.contact_name as owner_contact_name',  'walker.contact_name as walker_contact_name',  'owner.id as owner_id', 'walker.id as walker_id', 'request.id as id', 'request.created_at as date', 'request.is_started', 'request.is_walker_arrived', 'request.is_completed', 'request.is_paid', 'request.is_walker_started', 'request.confirmed_walker'
                            , 'request.status', 'request.time', 'request.distance', 'request.total', 'request.is_cancelled', 'request.transfer_amount', 'request.payment_mode')
                    ->orderBy('owner.contact_name', $valu)
                    ->paginate(10);
        } elseif ($type == 'walker') {
            $typename = "Provider Name";
            $requests = DB::table('request')
                    ->leftJoin('walker', 'request.current_walker', '=', 'walker.id')
                    ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                    ->groupBy('request.id')
                    ->select('owner.contact_name as owner_contact_name',  'walker.contact_name as walker_contact_name',  'owner.id as owner_id', 'walker.id as walker_id', 'request.id as id', 'request.created_at as date', 'request.is_started', 'request.is_walker_arrived', 'request.is_completed', 'request.is_paid', 'request.is_walker_started', 'request.confirmed_walker'
                            , 'request.status', 'request.time', 'request.distance', 'request.total', 'request.is_cancelled', 'request.transfer_amount', 'request.payment_mode')
                    ->orderBy('walker.contact_name', $valu)
                    ->paginate(10);
        } elseif ($type == 'payment') {
            $typename = "Payment Mode";
            $requests = DB::table('request')
                    ->leftJoin('walker', 'request.current_walker', '=', 'walker.id')
                    ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                    ->groupBy('request.id')
                    ->select('owner.contact_name as owner_contact_name',  'walker.contact_name as walker_contact_name',  'owner.id as owner_id', 'walker.id as walker_id', 'request.id as id', 'request.created_at as date', 'request.is_started', 'request.is_walker_arrived', 'request.is_completed', 'request.is_paid', 'request.is_walker_started', 'request.confirmed_walker'
                            , 'request.status', 'request.time', 'request.distance', 'request.total', 'request.is_cancelled', 'request.transfer_amount', 'request.payment_mode')
                    ->orderBy('request.payment_mode', $valu)
                    ->paginate(10);
        }
        $setting = Settings::where('key', 'paypal')->first();
        $title = ucwords(trans('customize.Request') . "s" . " | Sorted by " . $typename . " in " . $valu); /* 'Request | Sorted by ' . $typename . ' in ' . $valu */
        return View::make('walks')
                        ->with('title', $title)
                        ->with('page', 'walks')
                        ->with('walks', $requests)
                        ->with('setting', $setting);
    }

    public function sortpromo() {
        $valu = $_GET["valu"];
        $type = $_GET["type"];
        $success = Input::get('success');
        Session::put('valu', $valu);
        Session::put('type', $type);
        if ($type == 'promoid') {
            $typename = "Promo Code ID";
            $promo_codes = DB::table('promo_codes')
                    ->orderBy('id', $valu)
                    ->paginate(10);
        } elseif ($type == 'promo') {
            $typename = "Promo Code";
            $promo_codes = DB::table('promo_codes')
                    ->orderBy('coupon_code', $valu)
                    ->paginate(10);
        } elseif ($type == 'uses') {
            $typename = "No Of Uses";
            $promo_codes = DB::table('promo_codes')
                    ->orderBy('uses', $valu)
                    ->paginate(10);
        }
        $setting = Settings::where('key', 'paypal')->first();
        $title = ucwords(trans('customize.promo_codes') . " | Sorted by " . $typename . " in " . $valu); /* 'Promocodes | Sorted by ' . $typename . ' in ' . $valu */
        return View::make('list_promo_codes')
                        ->with('title', $title)
                        ->with('page', 'promo_code')
                        ->with('success', $success)
                        ->with('promo_codes', $promo_codes)
                        ->with('setting', $setting);
    }

    public function searchpromo() {
        $valu = $_GET['valu'];
        $type = $_GET['type'];
        $success = Input::get('success');
        Session::put('valu', $valu);
        Session::put('type', $type);
        if ($type == 'promo_id') {
            $promo_codes = PromoCodes::where('id', $valu)->paginate(10);
        } elseif ($type == 'promo_name') {
            $promo_codes = PromoCodes::where('coupon_code', 'like', '%' . $valu . '%')->paginate(10);
        } elseif ($type == 'promo_type') {
            if ($valu == '%') {
                $promo_codes = PromoCodes::where('type', 1)->paginate(10);
            } elseif ($val = '$') {
                $promo_codes = PromoCodes::where('type', 2)->paginate(10);
            }
        } elseif ($type == 'promo_state') {
            if ($valu == 'active' || $valu == 'Active') {
                $promo_codes = PromoCodes::where('state', 1)->paginate(10);
            } elseif ($val = 'Deactivated' || $val = 'deactivated') {
                $promo_codes = PromoCodes::where('state', 2)->paginate(10);
            }
        }
        $title = ucwords(trans('customize.promo_codes') . " | Search Result"); /* 'Promo Codes | Search Result' */
        return View::make('list_promo_codes')
                        ->with('title', $title)
                        ->with('page', 'promo_code')
                        ->with('success', $success)
                        ->with('promo_codes', $promo_codes);
    }

// Provider Availability

    public function allow_availability() {
        Settings::where('key', 'allowcal')->update(array('value' => 1));
        return Redirect::to("/admin/providers");
    }

    public function disable_availability() {
        Settings::where('key', 'allowcal')->update(array('value' => 0));
        return Redirect::to("/admin/providers");
    }

    public function availability_provider() {
        $id = Request::segment(4);
        $provider = Walker::where('id', $id)->first();
        if ($provider) {
            $success = Input::get('success');
            $pavail = ProviderAvail::where('provider_id', $id)->paginate(10);
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
            $title = ucwords(trans('customize.Provider') . " Availability"); /* 'Provider Availability' */
            return View::make('availability_provider')
                            ->with('title', $title)
                            ->with('page', 'walkers')
                            ->with('success', $success)
                            ->with('pvjson', $pvjson)
                            ->with('provider', $provider);
        } else {
            return View::make('admin.notfound')->with('title', 'Error Page Not Found')->with('page', 'Error Page Not Found');
        }
    }

    public function provideravailabilitySubmit() {
        $id = Request::segment(4);
        $proavis = $_POST['proavis'];
        $proavie = $_POST['proavie'];
        $length = $_POST['length'];
        //Log::info('Start end time Array Length = ' . print_r($length, true));
        DB::delete("delete from provider_availability where provider_id = '" . $id . "';");
        for ($l = 0; $l < $length; $l++) {
            $pv = new ProviderAvail;
            $pv->provider_id = $id;
            $pv->start = $proavis[$l];
            $pv->end = $proavie[$l];
            $pv->save();
        }
        //Log::info('providers availability start = ' . print_r($proavis, true));
        //Log::info('providers availability end = ' . print_r($proavie, true));
        return Response::json(array('success' => true));
    }

    public function view_documents_provider() {
        $id = Request::segment(4);
        $provider = Walker::where('id', $id)->first();
        $provider_documents = WalkerDocument::where('walker_id', $id)->paginate(10);
        if ($provider) {
            $title = ucwords(trans('customize.Provider') . " View Documents : " . $provider->contact_name); /* 'Provider View Documents' */
            return View::make('view_documents')
                            ->with('title', $title)
                            ->with('page', 'walkers')
                            ->with('docs', $provider_documents)
                            ->with('provider', $provider);
        } else {
            return View::make('admin.notfound')->with('title', 'Error Page Not Found')->with('page', 'Error Page Not Found');
        }
    }

    //Providers Who currently walking
    public function current() {
        Session::put('che', 'current');

        $walks = DB::table('request')
                ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
                ->select('walker.id as id', 'walker.contact_name as contact_name', 'walker.phone as phone', 'walker.email as email', 'walker.picture as picture', 'walker.merchant_id as merchant_id', 'walker.bio as bio', 'request.total as total_requests', 'walker.is_approved as is_approved')
                ->where('deleted_at', NULL)
                ->where('request.is_started', 1)
                ->where('request.is_completed', 0)
                ->paginate(10);
        $title = ucwords(trans('customize.Provider') . "s" . " | Currently Providing"); /* 'Providers | Currently Providing' */
        return View::make('walkers')
                        ->with('title', $title)
                        ->with('page', 'walkers')
                        ->with('walkers', $walks);
    }

    public function theme() {
        $th = Theme::all()->count();

        if ($th == 1) {
            $theme = Theme::first();
        } else {
            $theme = new Theme;
        }

        $theme->theme_color = '#' . Input::get('color1');
        $theme->secondary_color = '#' . Input::get('color3');
        $theme->primary_color = '#' . Input::get('color2');
        $theme->hover_color = '#' . Input::get('color4');
        $theme->active_color = '#' . Input::get('color5');

        $css_msg = ".btn-default {
  color: #ffffff;
  background-color: $theme->theme_color;
}
.navbar-nav > li {
  float: left;
}
.btn-info{
    color: #000;
    background: #fff;
    border-radius: 0px;
    border:1px solid $theme->theme_color;
}
.nav-admin .dropdown :hover, .nav-admin .dropdown :hover {
    background: $theme->hover_color;
    color: #000;
}
.navbar-nav > li > a {
  border-radius: 0px;
}
.navbar-nav > li + li {
  margin-left: 2px;
}
.navbar-nav > li.active > a,
.navbar-nav> li.active > a:hover,
.navbar-nav > li.active > a:focus {
  color: #ffffff;
  background-color: $theme->active_color!important;
}
.logo_img_login{
border-radius: 30px;border: 4px solid $theme->theme_color;
}
.btn-success {
  color: #ffffff;
  background-color: $theme->theme_color;
  border-color: $theme->theme_color;
}
.btn-success:hover,
.btn-success:focus,
.btn-success:active,
.btn-success.active,
.open .dropdown-toggle.btn-success {
  color: #ffffff;
  background-color: $theme->theme_color;
  border-color: $theme->theme_color;

}


.btn-success.disabled,
.btn-success[disabled],
fieldset[disabled] .btn-success,
.btn-success.disabled:hover,
.btn-success[disabled]:hover,
fieldset[disabled] .btn-success:hover,
.btn-success.disabled:focus,
.btn-success[disabled]:focus,
fieldset[disabled] .btn-success:focus,
.btn-success.disabled:active,
.btn-success[disabled]:active,
fieldset[disabled] .btn-success:active,
.btn-success.disabled.active,
.btn-success[disabled].active,
fieldset[disabled] .btn-success.active {

  background-color: $theme->theme_color;
  border-color: $theme->theme_color;
}
.btn-success .badge {
  color: $theme->theme_color;
  background-color: #ffffff;
}
.btn-info {
  color: #ffffff;
  background-color: $theme->theme_color;
  border-color: $theme->theme_color;
}
.btn-info:hover,
.btn-info:focus,
.btn-info:active,
.btn-info.active,
.open .dropdown-toggle.btn-info {
  color: #000;
  background-color: #FFFF;
  border-color: $theme->theme_color;
}
.btn-info:active,
.btn-info.active,
.open .dropdown-toggle.btn-info {
  background-image: none;
}
.btn-info.disabled,
.btn-info[disabled],
fieldset[disabled] .btn-info,
.btn-info.disabled:hover,
.btn-info[disabled]:hover,
fieldset[disabled] .btn-info:hover,
.btn-info.disabled:focus,
.btn-info[disabled]:focus,
fieldset[disabled] .btn-info:focus,
.btn-info.disabled:active,
.btn-info[disabled]:active,
fieldset[disabled] .btn-info:active,
.btn-info.disabled.active,
.btn-info[disabled].active,
fieldset[disabled] .btn-info.active {
  background-color: $theme->theme_color;
  border-color: $theme->theme_color;
}
.btn-info .badge {
  color: $theme->theme_color;
  background-color: #029acf;
  border-color: #029acf;
}
.btn-success,
.btn-success:hover {
  background-image: -webkit-linear-gradient($theme->theme_color $theme->theme_color 6%, $theme->theme_color);
  background-image: linear-gradient($theme->theme_color, $theme->theme_color 6%, $theme->theme_color);
  background-repeat: no-repeat;
  filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='$theme->theme_color', endColorstr='$theme->theme_color', GradientType=0);
  filter: none;
  border: 1px solid $theme->theme_color;
}
.btn-info,
.btn-info:hover {
  background-image: -webkit-linear-gradient($theme->theme_color, $theme->theme_color 6%, $theme->theme_color);
  background-image: linear-gradient($theme->theme_color, $theme->theme_color 6%, $theme->theme_color);
  background-repeat: no-repeat;
  filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='$theme->theme_color', endColorstr='$theme->theme_color', GradientType=0);
  filter: none;
  border: 1px solid $theme->theme_color;
}
.logo h3{
    margin: 0px;
    color: $theme->theme_color;
}

.second-nav{
    background: $theme->theme_color;
}
.login_back{background-color: $theme->theme_color;}
.no_radious:hover{background-image: -webkit-linear-gradient($theme->theme_color, $theme->theme_color 6%, $theme->theme_color);background-image: linear-gradient(#5d4dd1, #5d4dd1 6%, #5d4dd1);background-repeat: no-repeat;filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#5d4dd1', endColorstr='#5d4dd1', GradientType=0);filter: none;border: 1px solid #5d4dd1;}
.navbar-nav li:nth-child(1) a{
    background: $theme->primary_color;
}

.navbar-nav li:nth-child(2) a{
    background: $theme->secondary_color;
}

.navbar-nav li:nth-child(3) a{
    background: $theme->primary_color;
}

.navbar-nav li:nth-child(4) a{
    background: $theme->secondary_color;
}

.navbar-nav li:nth-child(5) a{
    background: $theme->primary_color;
}

.navbar-nav li:nth-child(6) a{
    background: $theme->secondary_color;
}

.navbar-nav li:nth-child(7) a{
    background: $theme->primary_color;
}

.navbar-nav li:nth-child(8) a{
    background: $theme->secondary_color;
}

.navbar-nav li:nth-child(9) a{
    background: $theme->primary_color;
}

.navbar-nav li:nth-child(10) a{
    background: $theme->secondary_color;
}

.navbar-nav li a:hover{
    background: $theme->hover_color;
}
.btn-green{

    background: $theme->theme_color;
    color: #fff;
}
.btn-green:hover{
    background: $theme->hover_color;
    color: #fff;
}
";
        $t = file_put_contents(public_path() . '/stylesheet/theme_cus.css', $css_msg);
        /* chmod(public_path() . '/stylesheet/theme_cus.css', 0777); */

        if (Input::hasFile('logo')) {
            // Upload File
            $file_name = time();
            $file_name .= rand();
            $ext = Input::file('logo')->getClientOriginalExtension();

            Input::file('logo')->move(public_path() . "/uploads", $file_name . "." . $ext);
            $local_url = $file_name . "." . $ext;

            /* $new = Image::make(public_path() . "/uploads/" . $local_url)->resize(70, 70)->save(); */

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
            if (isset($theme->logo)) {
                $icon = asset_url() . '/uploads/' . $theme->logo;
                unlink_image($icon);
            }
            $theme->logo = $local_url;
        }

        if (Input::hasFile('icon')) {
            // Upload File
            $file_name1 = time();
            $file_name1 .= rand();
            $file_name1 .= 'icon';
            $ext1 = Input::file('icon')->getClientOriginalExtension();
            Input::file('icon')->move(public_path() . "/uploads", $file_name1 . "." . $ext1);
            $local_url1 = $file_name1 . "." . $ext1;

            // Upload to S3
            if (Config::get('app.s3_bucket') != "") {
                $s3 = App::make('aws')->get('s3');
                $pic = $s3->putObject(array(
                    'Bucket' => Config::get('app.s3_bucket'),
                    'Key' => $file_name1,
                    'SourceFile' => public_path() . "/uploads/" . $local_url1,
                ));

                $s3->putObjectAcl(array(
                    'Bucket' => Config::get('app.s3_bucket'),
                    'Key' => $file_name1,
                    'ACL' => 'public-read'
                ));

                $s3_url1 = $s3->getObjectUrl(Config::get('app.s3_bucket'), $file_name1);
            } else {
                $s3_url1 = asset_url() . '/uploads/' . $local_url1;
            }
            if (isset($theme->favicon)) {
                $icon = asset_url() . '/uploads/' . $theme->favicon;
                unlink_image($icon);
            }
            $theme->favicon = $local_url1;
        }
        $theme->save();
        return Redirect::to("/admin/settings");
    }

    public function transfer_amount() {
        $request = RideRideRequest::where('id', Input::get('request_id'))->first();
        $walker = Walker::where('id', $request->confirmed_walker)->first();
        $amount = Input::get("amount");

        if (($amount + $request->transfer_amount) <= $request->total && ($amount + $request->transfer_amount) > 0) {
            if (Config::get('app.default_payment') == 'stripe') {
                Stripe::setApiKey(Config::get('app.stripe_secret_key'));
                // dd($amount$request->transfer_amount);
                $transfer = Stripe_Transfer::create(array(
                            "amount" => $amount * 100, // amount in cents
                            "currency" => "usd",
                            "recipient" => $walker->merchant_id)
                );
            } else {
                Braintree_Configuration::environment(Config::get('app.braintree_environment'));
                Braintree_Configuration::merchantId(Config::get('app.braintree_merchant_id'));
                Braintree_Configuration::publicKey(Config::get('app.braintree_public_key'));
                Braintree_Configuration::privateKey(Config::get('app.braintree_private_key'));
                $payment_data = Payment::where('owner_id', $request->owner_id)->first();
                $customer_id = $payment_data->customer_id;
                $result = Braintree_Transaction::sale(
                                array(
                                    'merchantAccountId' => $walker->merchant_id,
                                    'paymentMethodNonce' => $customer_id,
                                    'options' => array(
                                        'submitForSettlement' => true,
                                        'holdInEscrow' => true,
                                    ),
                                    'amount' => $amount
                                )
                );
            }
            $request->transfer_amount += $amount;
            $request->save();
            return Redirect::to("/admin/requests");
        } else {
            Session::put('error', "Amount exceeds the total amount to be paid");
            $title = ucwords("Transfer amount");
            return View::make('transfer_amount')
                            ->with('request', $request)
                            ->with('title', $title)
                            ->with('page', 'walkers');
        }
    }

    public function pay_provider($id) {
        $request = RideRideRequest::find($id);
        if (Config::get('app.default_payment') == 'stripe') {
            $title = ucwords("Transfer amount");
            return View::make('transfer_amount')
                            ->with('request', $request)
                            ->with('title', $title)
                            ->with('page', 'walkers');
        } else {
            $this->_braintreeConfigure();
            $clientToken = Braintree_ClientToken::generate();
            Session::put('error', 'Manual Transfer is not available in braintree.');
            $title = ucwords("Transfer amount");
            return View::make('transfer_amount')
                            ->with('request', $request)
                            ->with('clientToken', $clientToken)
                            ->with('title', $title)
                            ->with('page', 'walks');
        }
    }

    public function charge_user($id) {
        $request = RideRequest::find($id);
        //Log::info('Charge User from admin');
        $total = $request->total;
        $payment_data = Payment::where('owner_id', $request->owner_id)->first();
        $customer_id = $payment_data->customer_id;
        $setransfer = Settings::where('key', 'transfer')->first();
        $transfer_allow = $setransfer->value;
        if (Config::get('app.default_payment') == 'stripe') {
            //dd($customer_id);
            Stripe::setApiKey(Config::get('app.stripe_secret_key'));
            try {
                $charge = Stripe_Charge::create(array(
                            "amount" => $total * 100,
                            "currency" => "usd",
                            "customer" => $customer_id)
                );
                //Log::info('charge stripe = ' . print_r($charge, true));
            } catch (Stripe_InvalidRequestError $e) {
                // Invalid parameters were supplied to Stripe's API
                $ownr = Owner::find($request->owner_id);
                $ownr->debt = $total;
                $ownr->save();
                $response_array = array('error' => $e->getMessage());
                $response_code = 200;
                $response = Response::json($response_array, $response_code);
                return $response;
            }
            $request->is_paid = 1;
            $settng = Settings::where('key', 'service_fee')->first();
            $settng_mode = Settings::where('key', 'payment_mode')->first();
            if ($settng_mode->value == 2 and $transfer_allow == 1) {
                $transfer = Stripe_Transfer::create(array(
                            "amount" => ($total - $settng->value) * 100, // amount in cents
                            "currency" => "usd",
                            "recipient" => $walker_data->merchant_id)
                );
                $request->transfer_amount = ($total - $settng->value);
            }
        } else {
            try {
                Braintree_Configuration::environment(Config::get('app.braintree_environment'));
                Braintree_Configuration::merchantId(Config::get('app.braintree_merchant_id'));
                Braintree_Configuration::publicKey(Config::get('app.braintree_public_key'));
                Braintree_Configuration::privateKey(Config::get('app.braintree_private_key'));
                if ($settng_mode->value == 2 and $transfer_allow == 1) {
                    $sevisett = Settings::where('key', 'service_fee')->first();
                    $service_fee = $sevisett->value;
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
                //Log::info('result of braintree = ' . print_r($result, true));
                if ($result->success) {
                    $request->is_paid = 1;
                } else {
                    $request->is_paid = 0;
                }
            } catch (Exception $e) {
                //Log::info('error in braintree payment = ' . print_r($e, true));
            }
        }
        $request->card_payment = $total;
        $request->ledger_payment = $request->total - $total;
        $request->save();
        return Redirect::to('/admin/requests');
    }

    public function add_request() {
        //Log::info('add request from admin panel.');
        $owner_id = Request::segment(3);
        $owner = Owner::find($owner_id);
        $services = ProviderType::where('is_visible', '=', 1)->get();
        $total_services = ProviderType::where('is_visible', '=', 1)->count();
        // Payment options allowed
        $payment_options = array();

        $payments = Payment::where('owner_id', $owner_id)->count();

        if ($payments) {
            $payment_options['stored_cards'] = 1;
        } else {
            $payment_options['stored_cards'] = 0;
        }
        $codsett = Settings::where('key', 'cod')->first();
        if ($codsett->value == 1) {
            $payment_options['cod'] = 1;
        } else {
            $payment_options['cod'] = 0;
        }

        $paypalsett = Settings::where('key', 'paypal')->first();
        if ($paypalsett->value == 1) {
            $payment_options['paypal'] = 1;
        } else {
            $payment_options['paypal'] = 0;
        }

        //Log::info('payment_options = ' . print_r($payment_options, true));
        // Promo code allowed
        $promosett = Settings::where('key', 'promo_code')->first();
        if ($promosett->value == 1) {
            $promo_allow = 1;
        } else {
            $promo_allow = 0;
        }
        $settdestination = Settings::where('key', 'get_destination')->first();
        $settdestination = $settdestination->value;
        $title = ucwords("Add" . trans('customize.Request')); /* 'Add Request' */
        return View::make('add_request')
                        ->with('owner', $owner)
                        ->with('services', $services)
                        ->with('total_services', $total_services)
                        ->with('payment_option', $payment_options)
                        ->with('settdestination', $settdestination)
                        ->with('title', $title)
                        ->with('page', 'walks');
    }

    //create manual request from admin panel

   public function create_manual_request() {
        $latitude = Input::get('latitude');
        $longitude = Input::get('longitude');
        $d_latitude = Input::get('d_latitude');
        $d_longitude = Input::get('d_longitude');
        $type = Input::get('type');
        $provider = Input::get('walker');
        $user_id = Input::get('owner_id');
        $payment_opt = 0;
        if (Input::has('payment_type')) {
            $payment_opt = Input::get('payment_type');
        }

        $time = date("Y-m-d H:i:s");

        $provider_details = Walker::where('id', '=', $provider)->first();

        $user = Owner::where('id', '=', $user_id)->first();

        $request = new RideRequest;
        $request->owner_id = $user_id;
        $request->request_start_time = $time;
        //if(isset($provider))
        $request->confirmed_walker = $provider;
        if ($d_longitude != '' && $d_latitude != '') {
            $request->D_latitude = $d_latitude;
            $request->D_longitude = $d_longitude;
        }
        //if(isset($provider))
        $request->current_walker = $provider;

       // $request->status = 1;

        if ($payment_opt != 1) {
            $request->payment_mode = 0;
        } else {
            $request->payment_mode = 1;
        }

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

            $current_request = RideRideRequest::where('id', '=', $reqid)->first();
            Session::put('msg', 'A New Request is Created Successfully');
            return Redirect::to('/admin/users');
        }
    }

    public function payment_details() {
        $braintree_environment = Config::get('app.braintree_environment');
        $braintree_merchant_id = Config::get('app.braintree_merchant_id');
        $braintree_public_key = Config::get('app.braintree_public_key');
        $braintree_private_key = Config::get('app.braintree_private_key');
        $braintree_cse = Config::get('app.braintree_cse');
        $twillo_account_sid = Config::get('app.twillo_account_sid');
        $twillo_auth_token = Config::get('app.twillo_auth_token');
        $twillo_number = Config::get('app.twillo_number');
        $stripe_publishable_key = Config::get('app.stripe_publishable_key');
        $default_payment = Config::get('app.default_payment');
        $stripe_secret_key = Config::get('app.stripe_secret_key');
        $mail_driver = Config::get('mail.mail_driver');
        $email_name = Config::get('mail.from.name');
        $email_address = Config::get('mail.from.address');
        $mandrill_secret = Config::get('services.mandrill_secret');
        /* DEVICE PUSH NOTIFICATION DETAILS */
        $customer_certy_url = Config::get('app.customer_certy_url');
        $customer_certy_pass = Config::get('app.customer_certy_pass');
        $customer_certy_type = Config::get('app.customer_certy_type');
        $provider_certy_url = Config::get('app.provider_certy_url');
        $provider_certy_pass = Config::get('app.provider_certy_pass');
        $provider_certy_type = Config::get('app.provider_certy_type');
        $gcm_browser_key = Config::get('app.gcm_browser_key');
        /* DEVICE PUSH NOTIFICATION DETAILS END */
        $install = array(
            'braintree_environment' => $braintree_environment,
            'braintree_merchant_id' => $braintree_merchant_id,
            'braintree_public_key' => $braintree_public_key,
            'braintree_private_key' => $braintree_private_key,
            'braintree_cse' => $braintree_cse,
            'twillo_account_sid' => $twillo_account_sid,
            'twillo_auth_token' => $twillo_auth_token,
            'twillo_number' => $twillo_number,
            'stripe_publishable_key' => $stripe_publishable_key,
            'stripe_secret_key' => $stripe_secret_key,
            'mail_driver' => $mail_driver,
            'email_address' => $email_address,
            'email_name' => $email_name,
            'mandrill_secret' => $mandrill_secret,
            'default_payment' => $default_payment,
            /* DEVICE PUSH NOTIFICATION DETAILS */
            'customer_certy_url' => $customer_certy_url,
            'customer_certy_pass' => $customer_certy_pass,
            'customer_certy_type' => $customer_certy_type,
            'provider_certy_url' => $provider_certy_url,
            'provider_certy_pass' => $provider_certy_pass,
            'provider_certy_type' => $provider_certy_type,
            'gcm_browser_key' => $gcm_browser_key,
                /* DEVICE PUSH NOTIFICATION DETAILS END */
        );
        $start_date = Input::get('start_date');
        Session::put('start_date', $start_date);
        $end_date = Input::get('end_date');
        Session::put('end_date', $end_date);
        $submit = Input::get('submit');
        Session::put('submit', $submit);
        $walker_id = Input::get('walker_id');
        Session::put('walker_id', $walker_id);
        $owner_id = Input::get('owner_id');
        Session::put('owner_id', $owner_id);
        $status = Input::get('status');
        Session::put('status', $status);

        $start_time = date("Y-m-d H:i:s", strtotime($start_date));
        $end_time = date("Y-m-d H:i:s", strtotime($end_date));
        $start_date = date("Y-m-d", strtotime($start_date));
        $end_date = date("Y-m-d", strtotime($end_date));

        $query = DB::table('request')
                ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
                ->leftJoin('walker_type', 'walker.type', '=', 'walker_type.id');

        if (Input::get('start_date') && Input::get('end_date')) {
            $query = $query->where('request_start_time', '>=', $start_time)
                    ->where('request_start_time', '<=', $end_time);
        }

        if (Input::get('walker_id') && Input::get('walker_id') != 0) {
            $query = $query->where('request.confirmed_walker', '=', $walker_id);
        }

        if (Input::get('owner_id') && Input::get('owner_id') != 0) {
            $query = $query->where('request.owner_id', '=', $owner_id);
        }

        if (Input::get('status') && Input::get('status') != 0) {
            if ($status == 1) {
                $query = $query->where('request.is_completed', '=', 1);
            } else {
                $query = $query->where('request.is_cancelled', '=', 1);
            }
        } else {

            $query = $query->where(function ($que) {
                $que->where('request.is_completed', '=', 1)
                        ->orWhere('request.is_cancelled', '=', 1);
            });
        }

        $walks = $query->select('request.request_start_time', 'walker_type.name as type', 'request.ledger_payment', 'request.card_payment', 'owner.contact_name as owner_contact_name',  'walker.contact_name as walker_contact_name',  'owner.id as owner_id', 'walker.id as walker_id', 'request.id as id', 'request.created_at as date', 'request.is_started', 'request.is_walker_arrived', 'request.payment_mode', 'request.is_completed', 'request.is_paid', 'request.is_walker_started', 'request.confirmed_walker', 'request.promo_id', 'request.promo_code'
                , 'request.status', 'request.time', 'request.distance', 'request.total', 'request.is_cancelled', 'request.promo_payment');
        $walks = $walks->orderBy('id', 'DESC')->paginate(10);

        $query = DB::table('request')
                ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
                ->leftJoin('walker_type', 'walker.type', '=', 'walker_type.id');

        if (Input::get('start_date') && Input::get('end_date')) {
            $query = $query->where('request_start_time', '>=', $start_time)
                    ->where('request_start_time', '<=', $end_time);
        }

        if (Input::get('walker_id') && Input::get('walker_id') != 0) {
            $query = $query->where('request.confirmed_walker', '=', $walker_id);
        }

        if (Input::get('owner_id') && Input::get('owner_id') != 0) {
            $query = $query->where('request.owner_id', '=', $owner_id);
        }

        $completed_rides = $query->where('request.is_completed', 1)->count();


        $query = DB::table('request')
                ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
                ->leftJoin('walker_type', 'walker.type', '=', 'walker_type.id');

        if (Input::get('start_date') && Input::get('end_date')) {
            $query = $query->where('request_start_time', '>=', $start_time)
                    ->where('request_start_time', '<=', $end_time);
        }

        if (Input::get('walker_id') && Input::get('walker_id') != 0) {
            $query = $query->where('request.confirmed_walker', '=', $walker_id);
        }

        if (Input::get('owner_id') && Input::get('owner_id') != 0) {
            $query = $query->where('request.owner_id', '=', $owner_id);
        }
        $cancelled_rides = $query->where('request.is_cancelled', 1)->count();


        $query = DB::table('request')
                ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
                ->leftJoin('walker_type', 'walker.type', '=', 'walker_type.id');

        if (Input::get('start_date') && Input::get('end_date')) {
            $query = $query->where('request_start_time', '>=', $start_time)
                    ->where('request_start_time', '<=', $end_time);
        }

        if (Input::get('walker_id') && Input::get('walker_id') != 0) {
            $query = $query->where('request.confirmed_walker', '=', $walker_id);
        }

        if (Input::get('owner_id') && Input::get('owner_id') != 0) {
            $query = $query->where('request.owner_id', '=', $owner_id);
        }
        $card_payment = $query->where('request.payment_mode', 0)->where('request.is_completed', 1)->sum('request.card_payment');


        $query = DB::table('request')
                ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
                ->leftJoin('walker_type', 'walker.type', '=', 'walker_type.id');

        if (Input::get('start_date') && Input::get('end_date')) {
            $query = $query->where('request_start_time', '>=', $start_time)
                    ->where('request_start_time', '<=', $end_time);
        }

        if (Input::get('walker_id') && Input::get('walker_id') != 0) {
            $query = $query->where('request.confirmed_walker', '=', $walker_id);
        }

        if (Input::get('owner_id') && Input::get('owner_id') != 0) {
            $query = $query->where('request.owner_id', '=', $owner_id);
        }
        $promo_payment = $query->where('request.is_completed', 1)->sum('request.promo_payment');
        $credit_payment = $query->where('request.is_completed', 1)->sum('request.ledger_payment');
        $cash_payment = $query->where('request.payment_mode', 1)->sum('request.total');
        $scheduled_rides = ScheduledRequest::count();


        if (Input::get('submit') && Input::get('submit') == 'Download_Report') {

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
            fputcsv($handle, array('ID', 'Date', 'Type of Service', 'Provider', 'Owner', 'Distance (' . $unit_set . ')', 'Time (Minutes)', 'Payment Mode', 'Earning', 'Referral Bonus', 'Promotional Bonus', 'Card Payment'));
            foreach ($walks as $request) {
                $pay_mode = "Card Payment";
                if ($request->payment_mode == 1) {
                    $pay_mode = "Cash Payment";
                }
                fputcsv($handle, array(
                    $request->id,
                    date('l, F d Y h:i A', strtotime($request->request_start_time)),
                    $request->type,
                    $request->walker_contact_name,
                    $request->owner_contact_name,
                    sprintf2($request->distance, 2),
                    sprintf2($request->time, 2),
                    $pay_mode,
                    sprintf2($request->total, 2),
                    sprintf2($request->ledger_payment, 2),
                    sprintf2($request->promo_payment, 2),
                    sprintf2($request->card_payment, 2),
                ));
            }

            fputcsv($handle, array());
            fputcsv($handle, array());
            fputcsv($handle, array('Total Trips', $completed_rides + $cancelled_rides));
            fputcsv($handle, array('Completed Trips', $completed_rides));
            fputcsv($handle, array('Cancelled Trips', $cancelled_rides));
            fputcsv($handle, array('Scheduled Trips', $scheduled_rides));
            fputcsv($handle, array('Total Payments', sprintf2(($credit_payment + $card_payment), 2)));
            fputcsv($handle, array('Card Payment', sprintf2($card_payment, 2)));
            fputcsv($handle, array('Referral Payment', $credit_payment));
            fputcsv($handle, array('Cash Payment', sprintf2($cash_payment, 2)));
            fputcsv($handle, array('Promotional Payment', sprintf2($promo_payment, 2)));

            fclose($handle);

            $headers = array(
                'Content-Type' => 'text/csv',
            );
        } else {
            /* $currency_selected = Keywords::where('alias', 'Currency')->first();
              $currency_sel = $currency_selected->keyword; */
            $currency_sel = Config::get('app.generic_keywords.Currency');
            $walkers = Walker::paginate(10);
            $owners = Owner::paginate(10);
            $payment_default = ucfirst(Config::get('app.default_payment'));
            $title = ucwords(trans('customize.payment_details')); /* 'Payments' */
            return View::make('payment')
                            ->with('title', $title)
                            ->with('page', 'payments')
                            ->with('walks', $walks)
                            ->with('owners', $owners)
                            ->with('walkers', $walkers)
                            ->with('completed_rides', $completed_rides)
                            ->with('cancelled_rides', $cancelled_rides)
                            ->with('card_payment', $card_payment)
                            ->with('install', $install)
                            ->with('currency_sel', $currency_sel)
                            ->with('cash_payment', $cash_payment)
                            ->with('credit_payment', $credit_payment)
                            ->with('payment_default', $payment_default);
        }
    }

    public function walks_payment() {
        $walks = DB::table('request')
                ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
                ->select(DB::raw('SUM(request.card_payment)as total,SUM(request.payment_remaining) as pay_to_provider,SUM(request.refund_remaining) as take_from_provider,COUNT(request.id)as trips,request.created_at,request.id, WEEK(request.created_at) as payoutweek'))
                ->where('request.status', '=', 1)
                ->where('request.is_completed', '=', 1)
                ->groupBy('payoutweek')
                ->orderBy('request.created_at', 'desc')
                ->paginate(10);
        $response = Response::json($walks);


        // return $response;
        return View::make('walks_payment')
                        ->with('title', 'Payment Statement')
                        ->with('page', 'week_statement')
                        ->with('walks', $walks);
    }

    public function admin_week_pdf() {
        $id = Input::get('id');
        $total = Input::get('total');
        $trips = Input::get('trips');
        $weekend = Input::get('weekend');
        $pay_to_provider = Input::get('pay_to_provider');
        $take_from_provider = Input::get('take_from_provider');
        //  return  $response = Response::json($walks);
        $pdf = App::make('dompdf');
        $parameterr = array();
        $parameter['title'] = 'Weekly Report';
        $parameter['date'] = date('Y-m-d');
        $parameter['page'] = 'dashboard';
        $parameter['id'] = $id;
        $parameter['total'] = $total;
        $parameter['weekend'] = $weekend;
        $parameter['trips'] = $trips;
        $parameter['pay_to_provider'] = $pay_to_provider;
        $parameter['take_from_provider'] = $take_from_provider;
        /* return Response::json($parameter); */
        /* return View::make('invoice_pdf')
          ->with('title', 'Payment Statement')
          ->with('page', 'week_statement')
          ->with('title', $parameter['title'])
          ->with('date', $parameter['date'])
          ->with('page', $parameter['page'])
          ->with('id', $parameter['id'])
          ->with('total', $parameter['total'])
          ->with('weekend', $parameter['weekend'])
          ->with('trips', $parameter['trips']); */
        /* $pdf = PDF::loadView('invoice_pdf', $parameter)->setPaper('legal')->setOrientation('landscape')->setWarnings(false); */
        $pdf = PDF::loadView('invoice_pdf', $parameter)->setPaper('legal')->setOrientation('landscape')->setWarnings(false);
        return $pdf->download($weekend . " " . 'weekly_report.pdf');
    }

    public function AdminCancelRide() {
        $request_id    = $_POST['request_id'];
        $cancel_reason = $_POST['cancel_reason'];
        if ($request = RideRideRequest::find($request_id))
        {
            if ($request->is_cancelled != 1) {

                $admin_id = Session::get('admin_id');
                $admin = Admin::where('id', '=', $admin_id)->first();
                $admin_email = $admin->username." (Admin)";
                // request ended
                RideRideRequest::where('id', '=', $request_id)->update(array('status' => 0, 'is_cancelled' => 1,'cancel_reason' => $cancel_reason,'cancelled_by'=> $admin_email));
                // Send SMS
                $pattern = "Ride has been cancelled for the request-id".$request->id." with Cancel reason: ".$cancel_reason;
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
                $follow_url = web_url() . "/healthcare/myrides";
                if($request->driver_name){
                    $driver_name = $request->driver_name;
                    $driver_phone = $request->driver_phone;
                } else{
                    $driver_name = "NA";
                    $driver_phone = "NA";
                }

                $datetime = new DateTime($request->request_start_time);
                $datetime->format('Y-m-d H:i:s') . "\n";
                $user_time = new DateTimeZone($request->time_zone);
                $datetime->setTimezone($user_time);
                $newpickuptime = $datetime->format('Y-m-d H:i:s');

                $passenger_name = $passengerinfo->contact_name;
                $agent_name = $request->agent_contact_name;
                $pattern = array('driver_name'=> $driver_name, 'driver_phone'=> $driver_phone,
                    'passenger_name'=>$passenger_name, 'passenger_phone'=> $passengerinfo->phone,
                    'pickup_time'=>$newpickuptime, 'pickup_location'=> $request->src_address,
                    'dropoff_location'=> $request->dest_address,'butterfli_dispatcher_phno'=>$ride_assignee_phone_number,
                    'admin_email' => $admin_email,'trip_id' => $request->id, 'follow_url' => $follow_url,
                    'cancel_reason'=>$cancel_reason,'agent_name'=>$agent_name);
                $subject = "Your ride request can't be fulfilled";
                email_notification($request->healthcare_id, 'operator', $pattern, $subject, 'request_not_accepted', 'imp');
                email_notification($request->healthcare_id, 'ride_assignee', $pattern, $subject, 'request_not_accepted', 'imp');
                email_notification($request->healthcare_id, 'ride_assignee_2', $pattern, $subject, 'request_not_accepted', 'imp');
                email_notification($request->healthcare_id, 'ride_assignee_3', $pattern, $subject, 'request_not_accepted', 'imp');
                return 1;
            }
        } else{
            return 2;
        }

    }

    public function DispatcherCancelRide() {
        $request_id    = $_POST['request_id'];
        $cancel_reason = $_POST['cancel_reason'];
        if ($request = RideRideRequest::find($request_id))
        {
            if ($request->is_cancelled != 1) {
                // request ended
                RideRideRequest::where('id', '=', $request_id)->update(array('status' => 0, 'is_cancelled' => 1,'cancel_reason' => $cancel_reason));
                // Send SMS
                $pattern = "Ride has been cancelled for the request-id".$request->id." with Cancel reason: ".$cancel_reason;

                sms_notification($request->healthcare_id, 'operator', $pattern);
                sms_notification($request->dispatcher_id, 'ride_assignee', $pattern);
                sms_notification($request->dispatcher_id, 'ride_assignee_2', $pattern);
                sms_notification($request->dispatcher_id, 'ride_assignee_3', $pattern);


                //get user information
                $passengerinfo = DispatcherAssigned::where('id', $request->dispatcher_assigned_id)->first();
                $settings = Settings::where('key', 'admin_email_address')->first();
                $admin_email = $settings->value;
                $settings = Settings::where('key', 'ride_assignee_phone_number')->first();
                $ride_assignee_phone_number = $settings->value;
                $follow_url = web_url() . "/dispatcher/manualrides";

                $Dispatcher = Dispatcher::find($request->dispatcher_id);

                $datetime = new DateTime($request->request_start_time);
                $datetime->format('Y-m-d H:i:s') . "\n";
                $user_time = new DateTimeZone($request->time_zone);
                $datetime->setTimezone($user_time);
                $newpickuptime = $datetime->format('Y-m-d H:i:s');

                $passenger_name = $passengerinfo->contact_name;
                $dispatcher_name = $Dispatcher->contact_name;

                if($request->driver_name){
                    $driver_name = $request->driver_name;
                    $driver_phone = $request->driver_phone;
                } else{
                    $driver_name = "NA";
                    $driver_phone = "NA";
                }

                $pattern = array('driver_name'=> $driver_name, 'driver_phone'=> $driver_phone,
                    'passenger_name'=>$passenger_name, 'passenger_phone'=> $passengerinfo->phone,
                    'pickup_time'=>$newpickuptime, 'pickup_location'=> $request->src_address,
                    'dropoff_location'=> $request->dest_address,'butterfli_dispatcher_phno'=>$ride_assignee_phone_number,
                    'admin_email' => $admin_email,'trip_id' => $request->id, 'follow_url' => $follow_url,
                    'cancel_reason'=>$cancel_reason,'agent_name'=>$dispatcher_name);
                $subject = "Your ride request can't be fulfilled";

                email_notification($request->healthcare_id, 'operator', $pattern, $subject, 'request_not_accepted', 'imp');
                email_notification($request->dispatcher_id, 'ride_assignee', $pattern, $subject, 'request_not_accepted', 'imp');
                email_notification($request->dispatcher_id, 'ride_assignee_2', $pattern, $subject, 'request_not_accepted', 'imp');
                email_notification($request->dispatcher_id, 'ride_assignee_3', $pattern, $subject, 'request_not_accepted', 'imp');
                return 1;
            }
        } else{
            return 2;
        }

    }

    public function AdminConfirmRide() {
        $request_id    = $_POST['request_id'];
        $driver_name   = $_POST['driver_name'];
        $driver_phone  = $_POST['driver_phone'];
        $code          = $_POST['code'];
        $total_cost    = $_POST['total_cost'];
        $est_time      = $_POST['est_time'];
        $phone_no_driver = $code.$driver_phone;


        if ($request = RideRequest::find($request_id))
        {
            if ($request->is_confirmed != 1) {

                $settings = Settings::where('key', 'admin_email_address')->first();
                $admin_email = $settings->value;
                $passengerinfo = DispatcherAssigned::where('id', $request->dispatcher_assigned_id)->first();
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
                $request = RideRequest::find($request->id);
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
                $HealthCare = EnterpriseClient::find($request->healthcare_id);

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

                $file_name = $HealthCare->company.'_'.$request->id.'_'.uniqid().'.pdf';
                $ext = 'pdf';
                $file_path = public_path('image'). '/uploads/' .$file_name . "." . $ext;

                try {
                    $pdf = PDF::loadView('driver_invoice', $parameter)->setPaper('legal')->setOrientation('portrait')->setWarnings(false);
                    $output = $pdf->output();
                }
                catch (Exception $exception) {
                    echo $exception;
                }
                file_put_contents($file_path, $output);
                /* PDF GENERATOR CODE  END*/

                /* Uplaod this file to s3bucket */
                if (Config::get('app.s3_bucket') != "") {

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
                    $localfile = $file_path;
                    unlink_image($localfile);
                } else {
                    $s3_url = $file_path;
                }

                $agent = DispatcherAgent::where('healthcare_id', '=', $request->healthcare_id)->first();

                //Add data to HealthCareDocuments
                $healthcaredocuments = new HealthCareDocuments;
                $healthcaredocuments->request_id = $request->id;
                $healthcaredocuments->healthcare_id = $request->healthcare_id;
                if($agent){
                    $healthcaredocuments->agent_id = $agent->id;
                }
                $healthcaredocuments->document_url = $s3_url;
                $healthcaredocuments->save();

                // Send SMS
                $pattern = "Ride has been confirmed for the request-id: ".$request->id." with driver: ".$driver_name." Phone: ".$code.$driver_phone;

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
                $pattern = array('driver_name'=> $driver_name, 'driver_phone'=> $driver_phone,
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

                return 1;
            }
        } else {
            return 2;
        }

    }

    public function AdminCompleteRide(){
        $request_id   	 = $_POST['request_id'];
        //$dropoffaddress  = $_POST['dropoffaddress'];
        //$distance		 = $_POST['dist'];
        //$time            = $_POST['distancetime'];
        //$comment		 = $_POST['comment'];

        //Log::info('distance input = ' . print_r($distance, true));
        Log::info('rquest id: input = ' . print_r($request_id, true));

        //if($request_id>0 && $dropoffaddress!='' && $distance>0 && $time!='' && $comment!=''){
        if($request_id>0){
            /*if($dropoffaddress!=''){
                $dropprepAddr = str_replace(' ','+',$dropoffaddress);
                $geocode=file_get_contents('https://maps.google.com/maps/api/geocode/json?address='.$dropprepAddr.'&sensor=false');
                $dropoutput= json_decode($geocode);
                $droplatitude = $dropoutput->results[0]->geometry->location->lat;
                $droplongitude = $dropoutput->results[0]->geometry->location->lng;
            }*/
            if ($request = RideRequest::find($request_id)) {
                if ($request->is_confirmed==1) {
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
                    if($request->is_wheelchair_request ==1){
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
                    $request->total = $actual_total;
                    $request->card_payment = $actual_total;
                    $request->payment_remaining = $walker_payment_remaining;
                    $request->refund_remaining = $provider_refund_remaining;
                    $request->ledger_payment = $ref_total;
                    $request->promo_payment = $promo_total;
                    $request->save();

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
                    $follow_url = web_url() . "/healthcare/myrides";
                    if($request->driver_name){
                        $driver_name = $request->driver_name;
                        $driver_phone = $request->driver_phone;
                    } else{
                        $driver_name = "NA";
                        $driver_phone = "NA";
                    }

                    $datetime = new DateTime($request->request_start_time);
                    $datetime->format('Y-m-d H:i:s') . "\n";
                    $user_time = new DateTimeZone($request->time_zone);
                    $datetime->setTimezone($user_time);
                    $newpickuptime = $datetime->format('Y-m-d H:i:s');


                    $passenger_name = $passengerinfo->contact_name;
                    $agent_name = $request->agent_contact_name;

                    $pattern = array('driver_name'=> $driver_name, 'driver_phone'=> $driver_phone,
                        'passenger_name'=>$passenger_name, 'passenger_phone'=> $passengerinfo->phone,
                        'pickup_time'=>$newpickuptime, 'pickup_location'=> $request->src_address,
                        'dropoff_time'=> $request->updated_at,'dropoff_location'=> $request->dest_address,
                        'butterfli_dispatcher_phno'=>$ride_assignee_phone_number,
                        'admin_email' => $admin_email,'trip_id' => $request->id,
                        'follow_url' => $follow_url,'agent_name'=>$agent_name);
                    $subject = "Thanks for Riding with ButterFLi";
                    email_notification($request->healthcare_id, 'operator', $pattern, $subject, 'ride_complete', 'imp');
                    email_notification($request->healthcare_id, 'ride_assignee', $pattern, $subject, 'ride_complete', 'imp');
                    email_notification($request->healthcare_id, 'ride_assignee_2', $pattern, $subject, 'ride_complete', 'imp');
                    email_notification($request->healthcare_id, 'ride_assignee_3', $pattern, $subject, 'ride_complete', 'imp');
                    return 1;
                }
            } else{
                return 2;
            }
        }
    }

    public function DispatcherManualRideCompleted()
    {
        $request_id = $_POST['request_id'];
        $dropoffaddress  = $_POST['dropoffaddress'];
        $distance		 = $_POST['dist'];
        $time            = $_POST['distancetime'];
        $comment		 = $_POST['comment'];

        Log::info('distance input = ' . print_r($distance, true));
        Log::info('requestid input = ' . print_r($request_id, true));

        if($request_id>0 && $dropoffaddress!='' && $distance>0 && $time!='' && $comment!=''){

            if($dropoffaddress!=''){
                $dropprepAddr = str_replace(' ','+',$dropoffaddress);
                $geocode=file_get_contents('https://maps.google.com/maps/api/geocode/json?address='.$dropprepAddr.'&sensor=false');
                $dropoutput= json_decode($geocode);
                $droplatitude = $dropoutput->results[0]->geometry->location->lat;
                $droplongitude = $dropoutput->results[0]->geometry->location->lng;
            }
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
                    $request->comments = $comment;
                    $request->distance = $distance;
                    $request->time = $time;
                    $request->dest_address = $dropoffaddress;
                    if (isset($droplatitude)) {
                        $request->D_latitude = $droplatitude;
                    }
                    $request->D_longitude = 0;
                    if (isset($droplongitude)) {
                        $request->D_longitude = $droplongitude;
                    }
                    $request->total = $actual_total;
                    $request->card_payment = $actual_total;
                    $request->payment_remaining = $walker_payment_remaining;
                    $request->refund_remaining = $provider_refund_remaining;
                    $request->ledger_payment = $ref_total;
                    $request->promo_payment = $promo_total;
                    $request->save();

                    // Send SMS
                    $owner = DispatcherAssigned::find($request->dispatcher_assigned_id);
                    $settings = Settings::where('key', 'sms_when_provider_completes_job')->first();
                    $pattern = $settings->value;
                    $pattern = str_replace('%user%', $owner->contact_name, $pattern);
                    $pattern = str_replace('%driver%', $request->driver_name, $pattern);
                    $pattern = str_replace('%driver_mobile%', $request->driver_phone, $pattern);
                    $pattern = str_replace('%amount%', $request->total, $pattern);
                    if ($request->dispatcher_assigned_id != null) {
                        sms_notification($request->dispatcher_assigned_id, 'dispatcher_assigned', $pattern);
                    }

                    //get user information
                    $passengerinfo = DispatcherAssigned::where('id', $request->dispatcher_assigned_id)->first();
                    $settings = Settings::where('key', 'admin_email_address')->first();
                    $admin_email = $settings->value;
                    $settings = Settings::where('key', 'ride_assignee_phone_number')->first();
                    $ride_assignee_phone_number = $settings->value;
                    $follow_url = web_url() . "/dispatcher/manualrides";

                    $Dispatcher = Dispatcher::find($request->dispatcher_id);

                    $datetime = new DateTime($request->request_start_time);
                    $datetime->format('Y-m-d H:i:s') . "\n";
                    $user_time = new DateTimeZone($request->time_zone);
                    $datetime->setTimezone($user_time);
                    $newpickuptime = $datetime->format('Y-m-d H:i:s');

                    $passenger_name = $passengerinfo->contact_name;
                    $dispatcher_name = $Dispatcher->contact_name;

                    if($request->driver_name){
                        $driver_name = $request->driver_name;
                        $driver_phone = $request->driver_phone;
                    } else{
                        $driver_name = "NA";
                        $driver_phone = "NA";
                    }

                    $pattern = array('driver_name'=> $driver_name, 'driver_phone'=> $driver_phone,
                        'passenger_name'=>$passenger_name, 'passenger_phone'=> $passengerinfo->phone,
                        'pickup_time'=>$newpickuptime, 'pickup_location'=> $request->src_address,
                        'dropoff_time'=> $request->updated_at,'dropoff_location'=> $request->dest_address,
                        'butterfli_dispatcher_phno'=>$ride_assignee_phone_number,
                        'admin_email' => $admin_email,'trip_id' => $request->id,
                        'follow_url' => $follow_url,'agent_name'=>$dispatcher_name);
                    $subject = "Thanks for Riding with ButterFLi";

                    email_notification($request->healthcare_id, 'ride_assignee', $pattern, $subject, 'ride_complete', 'imp');
                    email_notification($request->healthcare_id, 'ride_assignee_2', $pattern, $subject, 'ride_complete', 'imp');
                    email_notification($request->healthcare_id, 'ride_assignee_3', $pattern, $subject, 'ride_complete', 'imp');
                    return 1;
                    return 1;
                }
            } else {
                return 2;
            }
        }
    }

    public function DispatcherConfirmRide() {
        $request_id    = $_POST['request_id'];
        $driver_name   = $_POST['driver_name'];
        $driver_phone  = $_POST['driver_phone'];
        $code          = $_POST['code'];

        $phone_no_driver = $code.$driver_phone;
        if ($request = RideRequest::find($request_id))
        {
            if ($request->is_confirmed != 1) {
                // request ended
                RideRequest::where('id', '=', $request_id)->update(array('is_confirmed' => 1,'driver_name' => $driver_name, 'driver_phone' => $code.$driver_phone));
                // Send SMS
                $pattern = "Ride has been confirmed for the request-id: ".$request->id." with driver: ".$driver_name." Phone: ".$code.$driver_phone;

                sms_notification($request->dispatcher_id, 'ride_assignee', $pattern);
                sms_notification($request->dispatcher_id, 'ride_assignee_2', $pattern);
                sms_notification($request->dispatcher_id, 'ride_assignee_3', $pattern);

                //get user information
                $passengerinfo = DispatcherAssigned::where('id', $request->dispatcher_assigned_id)->first();
                $settings = Settings::where('key', 'admin_email_address')->first();
                $admin_email = $settings->value;
                $settings = Settings::where('key', 'ride_assignee_phone_number')->first();
                $ride_assignee_phone_number = $settings->value;
                $follow_url = web_url() . "/dispatcher/manualrides";

                $Dispatcher = Dispatcher::find($request->dispatcher_id);

                $datetime = new DateTime($request->request_start_time);
                $datetime->format('Y-m-d H:i:s') . "\n";
                $user_time = new DateTimeZone($request->time_zone);
                $datetime->setTimezone($user_time);
                $newpickuptime = $datetime->format('Y-m-d H:i:s');

                $passenger_name = $passengerinfo->contact_name;
                $dispatcher_name = $Dispatcher->contact_name;
                $pattern = array('driver_name'=> $driver_name, 'driver_phone'=> $driver_phone,
                    'passenger_name'=>$passenger_name, 'passenger_phone'=> $passengerinfo->phone,
                    'pickup_time'=>$newpickuptime, 'pickup_location'=> $request->src_address,
                    'dropoff_location'=> $request->dest_address,'butterfli_dispatcher_phno'=>$ride_assignee_phone_number,
                    'admin_email' => $admin_email,'trip_id' => $request->id, 'follow_url' => $follow_url,
                    'agent_name'=>$dispatcher_name);
                $subject = "Your ride is confirmed";

                email_notification($request->dispatcher_id, 'ride_assignee', $pattern, $subject, 'ride_confirm', 'imp');
                email_notification($request->dispatcher_id, 'ride_assignee_2', $pattern, $subject, 'ride_confirm', 'imp');
                email_notification($request->dispatcher_id, 'ride_assignee_3', $pattern, $subject, 'ride_confirm', 'imp');

                return 1;
            }
        } else {
            return 2;
        }

    }

	public function EnterpriseClientProfile($id) {
        $success = Input::get("success");
        if(! is_null($success)) {
            Session::put('success', $success);
        }
        $error = Input::get("error");
        if(! is_null($error)) {
            Session::put('error', $error);
        }
		$client = EnterpriseClient::find($id);
		$title = "Enterprise Client: " . $client->company;

		return View::make('admin/enterprise/profile')
			->with('title', $title)
			->with('page', 1)
			->with('client', $client);
	}

    public function EnterpriseRateProfile($index, $enterpriseclient_id, $service_type) {
        $rateprofile = RateProfile::DefaultProfileForType($service_type);
        $rateprofile->enterpriseclient_id = $enterpriseclient_id;
        $rateprofile->id = -1;

        return View::make('admin/enterprise/rateprofile')
            ->with('i', $index)
            ->with('rateprofile', $rateprofile);
    }

    public function EnterpriseFundingProfile($index, $enterpriseclient_id, $funding_rule_type) {
        $fundingprofile = FundingProfile::DefaultProfileForType($funding_rule_type);
        $fundingprofile->enterpriseclient_id = $enterpriseclient_id;
        $fundingprofile->bill_enterpriseclient_id = $enterpriseclient_id;
        $fundingprofile->id = -1;

        return View::make('admin/enterprise/fundingprofile')
            ->with('i', $index)
            ->with('fundingprofile', $fundingprofile);
    }

	public function EnterpriseClientProfileSave() {
		$id = Input::get("id");
		$client = EnterpriseClient::find($id);
		$title = "Enterprise Client: " . $client->company;
		$error = 0;
		$success = 0;

		if(null == $client) {
			$error = "Invalid Record";
		}
		else {
			$error = $client->Ingest([
				'operator_phone' => 'required'
			]);
		}

		if(! $error) {
			$ret = $client->update($client->IngestedAttributes());
            Log::info("ERROR: $ret");
			$success = 'Enterprise Client Profile updated successfully';
		}

        $params = "";
        if($success) {
            $params = "?success=" . $success;
        }
        if($error) {
            $params = "?error=" . $error;
        }
        return Redirect::to("/admin/enterpriseclient/profile/" . $id . $params);
	}

	public function EnterpriseClients() {
		$healthproviders = DB::table('enterprise_client')
			->select('enterprise_client.*')
			->orderBy('enterprise_client.created_at', 'DESC')
			->paginate(10);

		$title = "Enterprise Clients";

		return View::make('admin/enterprise/clients')
			->with('title', $title)
			->with('page', 'healthproviders')
			->with('healthproviders', $healthproviders);
	}

    public function approve_EnterpriseClient() {
        $id = Request::segment(4);
        $success = Input::get('success');
        $client = EnterpriseClient::find($id);
        $client->is_active = 1;
        $client->save();

        /*$settings = Settings::where('key', 'admin_email_address')->first();
        $admin_email = $settings->value;
        $follow_url = web_url() . "/healthcare/signin";
        $pattern = array('username' => $HealthCare->contact_name, 'admin_email' => $admin_email,
            'follow_url'=>$follow_url);
        $subject = "Request Approved by Admin.";
        email_notification($HealthCare->id, 'healthcare', $pattern, $subject, 'healthcare_approve');*/
        return Redirect::to("/admin/enterpriseclients");
    }

    public function decline_EnterpriseClient() {
        $id = Request::segment(4);
        $success = Input::get('success');
        $HealthCare = EnterpriseClient::find($id);
        $HealthCare->is_active = 0;
        $HealthCare->save();

        $settings = Settings::where('key', 'admin_email_address')->first();
        $admin_email = $settings->value;
        $follow_url = web_url() . "/healthcare/signin";
        $pattern = array('username' => $HealthCare->contact_name, 'admin_email' => $admin_email,
            'follow_url'=>$follow_url);
        $subject = "Request Declined by Admin.";
        email_notification($HealthCare->id, 'healthcare', $pattern, $subject, 'healthcare_decline');
        return Redirect::to("/admin/EnterpriseClients");
    }
    public function HealthcareAgents() {
        Session::forget('type');
        Session::forget('valu');
        Session::forget('che');

        $healthagents = DB::table('dispatcher_agent')
            ->leftJoin('enterprise_client', 'enterprise_client.id', '=', 'dispatcher_agent.healthcare_id')
            ->select('dispatcher_agent.*','enterprise_client.company','enterprise_client.companylogo')
            ->orderBy('dispatcher_agent.created_at', 'DESC')
            ->paginate(10);
        $title = "Healthcare Agents";
        return View::make('healthcareagents')
            ->with('title', $title)
            ->with('page', 'healthcareagents')
            ->with('healthagents', $healthagents);
    }

    public function approve_healthcareagent() {
        $id = Request::segment(4);
        $success = Input::get('success');
        $HealthCare = DispatcherAgent::find($id);
        $HealthCare->is_active = 1;
        $HealthCare->save();

        /*$settings = Settings::where('key', 'admin_email_address')->first();
        $admin_email = $settings->value;
        $follow_url = web_url() . "/healthcare/signin";
        $pattern = array('username' => $HealthCare->contact_name , 'admin_email' => $admin_email,
            'follow_url'=>$follow_url);
        $subject = "Request Approved by Admin.";
        email_notification($HealthCare->id, 'agent', $pattern, $subject, 'healthcare_approve');*/
        return Redirect::to("/admin/healthcareagents");
    }

    public function decline_healthcareagent() {
        $id = Request::segment(4);
        $success = Input::get('success');
        $HealthCare = DispatcherAgent::find($id);
        $HealthCare->is_active = 0;
        $HealthCare->save();

        $settings = Settings::where('key', 'admin_email_address')->first();
        $admin_email = $settings->value;
        $follow_url = web_url() . "/healthcare/signin";
        $pattern = array('username' => $HealthCare->contact_name, 'admin_email' => $admin_email,
            'follow_url'=>$follow_url);
        $subject = "Request Declined by Admin.";
        email_notification($HealthCare->id, 'agent', $pattern, $subject, 'healthcare_decline');
        return Redirect::to("/admin/healthcareagents");
    }

    public function AdminDeleteRide()
    {
        $request_id    = $_POST['request_id'];

        if ($request = RideRequest::find($request_id))
        {
            $request->delete();
            return 1;
        } else{
            return 2;
        }
    }

    public function AddBalance()
    {
        $healthcare_id    = $_POST['healthcare_id'];
        $new_amount       = $_POST['new_amount'];

        if($enterprise_client = EnterpriseClient::find($healthcare_id)){
            if($new_amount >0){
                $enterprise_client->total_amount = $enterprise_client->total_amount + $new_amount;
                $enterprise_client->account_balance = $enterprise_client->account_balance + $new_amount;
                $enterprise_client->save();
                return 1;
            }
        }else{
            return 2;
        }
    }

    public function AdminSendRideInfo(){
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
                    $pattern .= " Wheelchair Requested";
                }

                sms_notification(1, 'ride_info', $pattern, $sms_no);
            }

            if ($email != '') {
                $pattern = array('driver_name'=> $request->driver_name, 'driver_phone'=> $request->driver_phone,
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

    public function GetDispatchers() {
        Session::forget('type');
        Session::forget('valu');
        Session::forget('che');

        $Dispatchers = DB::table('dispatcher')
            ->leftJoin('transportation_provider', 'transportation_provider.id', '=', 'dispatcher.transportation_provider_id')
            ->select('dispatcher.*','transportation_provider.company as transportation_company')
            ->orderBy('dispatcher.created_at', 'DESC')
            ->paginate(10);
        $title = "Dispatchers";

        return View::make('dispatchers')
            ->with('title', $title)
            ->with('page', 'dispatchers')
            ->with('Dispatchers', $Dispatchers);
    }

    function DispatcherProfile($id) {
        $success = Input::get("success");
        if(! is_null($success)) {
            Session::put('success', $success);
        }
        $error = Input::get("error");
        if(! is_null($error)) {
            Session::put('error', $error);
        }
        $dispatcher = Dispatcher::find($id);

        return
            View::make('admin/tp/dispatcher/profile')
                ->with('title', "Dispatcher " . $dispatcher->contact_name)
                ->with('page', 'Dispatcher Profile')
                ->with('dispatcher', $dispatcher);
    }

    public function DispatcherProfileSave() {
        $array = Input::all();
        $id = Input::get("id");
        $dispatcher = NULL;
        if($id == NULL) {
            $dispatcher = new Dispatcher();
        }
        else {
            $dispatcher = Dispatcher::find($id);
        }

        $title = "Dispatcher: " . $dispatcher->contact_name;
        $error = 0;
        $success = 0;

        if(null == $dispatcher) {
            $error = "Invalid Record";
        }
        else {
            $error = $dispatcher->Ingest([
                'contact_name' => 'required'
            ]);
        }

        if(! $error) {
            $ret = $dispatcher->update($dispatcher->IngestedAttributes());
            Log::info("ERROR: $ret");
            $success = 'Dispatcher Profile updated successfully';
        }

        $params = "";
        if($success) {
            $params = "?success=" . $success;
        }
        if($error) {
            $params = "?error=" . $error;
        }
        return Redirect::to("/admin/dispatcher/profile/" . $id . $params);
    }

    public function approve_dispatcher() {
        $id = Request::segment(4);
        $success = Input::get('success');
        $Dispatchers = Dispatcher::find($id);
        $Dispatchers->is_active = 1;
        $Dispatchers->save();

        /*$settings = Settings::where('key', 'admin_email_address')->first();
        $admin_email = $settings->value;
        $follow_url = web_url() . "/healthcare/signin";
        $pattern = array('username' => $HealthCare->contact_name, 'admin_email' => $admin_email,
            'follow_url'=>$follow_url);
        $subject = "Request Approved by Admin.";
        email_notification($HealthCare->id, 'healthcare', $pattern, $subject, 'healthcare_approve');*/
        return Redirect::to("/admin/dispatchers");
    }

    public function decline_dispatcher() {
        $id = Request::segment(4);
        $success = Input::get('success');
        $Dispatchers = Dispatcher::find($id);
        $Dispatchers->is_active = 0;
        $Dispatchers->save();

        $settings = Settings::where('key', 'admin_email_address')->first();
        $admin_email = $settings->value;
        $follow_url = web_url() . "/dispatcher/signin";
        $pattern = array('username' => $Dispatchers->contact_name, 'admin_email' => $admin_email,
            'follow_url'=>$follow_url);
        $subject = "Request Declined by Admin.";
        email_notification($Dispatchers->id, 'dispatcher', $pattern, $subject, 'healthcare_decline');
        return Redirect::to("/admin/dispatchers");
    }

    public function MakeDispatcherAdmin() {
        $id = Request::segment(4);
        $Dispatchers = Dispatcher::find($id);
        $Dispatchers->is_admin = 1;
        $Dispatchers->is_active = 1;
        $Dispatchers->save();

        return Redirect::to("/admin/dispatchers");
    }

    public function RemoveDispatcherAdmin () {
        $id = Request::segment(4);
        $Dispatchers = Dispatcher::find($id);
        $Dispatchers->is_admin = 0;
        $Dispatchers->save();
        return Redirect::to("/admin/dispatchers");
    }

    public function AdminProviderAuthorize() {

        $id = Request::segment(4);
        $walker = Walker::find($id);
        $walker->is_authorize = 1;
        $walker->save();

        return Redirect::to("/admin/providers");
    }

    public function AdminProviderAccessDLC() {

        $id = Request::segment(4);
        $walker = Walker::find($id);
        $walker->is_authorize = 0;
        $walker->save();

        return Redirect::to("/admin/providers");
    }

    public function TransportationProvider() {
        $transproviders = DB::table('transportation_provider')
            ->select('transportation_provider.*')
            ->orderBy('transportation_provider.created_at', 'DESC')
            ->paginate(10);
        $title = "Transportation Providers";
        return View::make('admin/tp/transportationproviders')
            ->with('title', $title)
            ->with('page', 'transportationproviders')
            ->with('transportationproviders', $transproviders);
    }

    public function TransportationProviderProfile($id) {
        $tp = TransportationProvider::find($id);
        $title = "Transportation Provider Profile";
        return View::make('admin/tp/profile')
            ->with('title', $title)
            ->with('tp', $tp)
            ->with('page', 'addnewtransportationproviders');
    }

    public function OldAddNewTransportationProvider() {
        $title = "Add New Transportation Providers";
        return View::make('addnewtransportationproviders')
            ->with('title', $title)
            ->with('page', 'addnewtransportationproviders');
    }


    public function AddNewTransportationProvider() {

        $tp = new TransportationProvider();
        $title = "Add New Transportation Providers";
        return View::make('admin/tp/profile')
            ->with('title', $title)
            ->with('tp', $tp)
            ->with('page', 'addnewtransportationproviders');
    }

    public function TransportationProviderInsertUpdate(){
        $id      = $_POST['id'];
        $email   = $_POST['email'];
        $company = $_POST['company'];
        $phone   = $_POST['phone'];
        $rate    = $_POST['rate'];
        $contact = $_POST['contact'];
        $status  = $_POST['status'];
        $comment = $_POST['comment'];
        $device  = $_POST['device'];
        $tp_address             = $_POST['tp_address'];
        $tps_vehicles           = $_POST['tps_vehicles'];
        $service_area           = $_POST['service_area'];
        $available_after_hours  = $_POST['available_after_hours'];
        $service_hours          = $_POST['service_hours'];
        $wheelchair_vehicles    = $_POST['wheelchair_vehicles'];

        if($id>0){
            if(TransportationProvider::where('id', '=', $id)->count() == 0){
                Log::info('id error = ' . print_r('Id not exists', true));
                return Redirect::to('/admin/transportationproviders')->with('error', 'Id Not Exists');
            }else{
                
            }
        } else{

            $validator = Validator::make(
                array(
                    'phone' => $phone,
                    'company' => $company
                ), array(
                'phone' => 'required',
                'company' => 'required',
            ), array(
                    'phone' => 'Phone is required.',
                    'company_name' => 'Company is required.'
                )
            );
            /*$validator1 = Validator::make(
                array(
                    'email' => $email,
                ), array(
                'email' => 'required|email'
            ), array(
                    'email' => 'Email field is required'
                )
            );*/
            $validator2 = Validator::make(
                array(
                    'phone' => $phone,
                ), array(
                'phone' => 'required'
            ), array(
                    'phone' => 'Phone is required'
                )
            );
            if ($validator->fails()) {
                $error_messages = $validator->messages()->first();
                return Redirect::to('/admin/transportationproviders/new')->with('error', 'Please fill all the fields.');
            } else if ($validator2->fails()) {
                $error_messages = $validator2->messages();
                Log::info('Error = ' . print_r($error_messages, true));
                return Redirect::to('/admin/transportationproviders/new')->with('error', 'Please Enter phone no.');
            } else if (TransportationProvider::where('email', '=', $email)->count() > 0) {
                Log::info('Email Error = ' . print_r('email already exists', true));
                return Redirect::to('/admin/transportationproviders/new')->with('error', 'Email Already Exists');
            } else if (TransportationProvider::where('phone', '=', $phone)->count() > 0) {
                Log::info('Phone Error = ' . print_r('phone already exists', true));
                return Redirect::to('/admin/transportationproviders/new')->with('error', 'Phoneno Already Exists');
            } else if (TransportationProvider::where('company', '=', $company)->count() > 0) {
                Log::info('company Error = ' . print_r('Company Name already exists', true));
                return Redirect::to('/admin/transportationproviders/new')->with('error', 'Company Name Already Exists');
            }else{
                $newtrans = new TransportationProvider;
                $newtrans->email = $email;
                $newtrans->company = $company;
                $result = substr($phone, 0, 2);
                if ($result != '+1') {
                    //$new_phone_no = "+1" . $phone;
                    $new_phone_no = $phone;
                } else {
                    $new_phone_no = $phone;
                }
                $newtrans->phone = $new_phone_no;
                $newtrans->rate = $rate;
                $newtrans->contact = $contact;
                $newtrans->status = $status;
                $newtrans->service_area = $service_area;
                $newtrans->available_after_hours = $available_after_hours;
                $newtrans->service_hours = $service_hours;
                $newtrans->wheelchair_vehicles = $wheelchair_vehicles;
                $newtrans->comment = $comment;
                $newtrans->tp_address = $tp_address;
                $newtrans->tps_vehicles = $tps_vehicles;
                $newtrans->device = $device;
                $newtrans->save();
                return Redirect::to('/admin/transportationproviders')->with('success', 'Record Successfully Inserted.');
            }
        }
    }

    public function DriverCertificateStatus(){
        $walker_id = Request::segment(4);

        $walker = Walker::find($walker_id);

        if($walker){
            $walker_certificate_status = json_decode($walker->certificate_status);

            $title = "Training Certificate Status";
            return View::make('training_certificate_status')
                ->with('title', $title)
                ->with('walker_id',$walker_id)
                ->with('page', 'training_certificate_status')
                ->with('walker_timezone',$walker->timezone)
                ->with('walker_certificate_status', $walker_certificate_status);
        }
    }

    public function AddCertificateJson(){

        $walker = Walker::all();

        foreach($walker as $walk){

            $learningContents = LearningContent::where('category_id', '=', '1')
                ->where('quiz_id', '>', 0)
                ->where('deleted_at', '=', NULL)->get();
            $learning_quiz_results = LearningQuizResults::where('walker_id','=',$walk->id)->where('deleted_at', '=', NULL)->get();
            $check_content = 0;
            $confirm_checked_content = 0;

            foreach ($learningContents as $content) {
                if ($content->category_id == 1) {
                    $check_content++;
                    foreach ($learning_quiz_results as $result) {
                        if ($result->content_id == $content->id && $result->result == 'Pass') {
                            $confirm_checked_content++;
                        }
                    }
                }
            }
            Log::info('result:pass check content= ' . print_r($check_content, true));
            Log::info('result:pass confirmed check content= ' . print_r($confirm_checked_content, true));

            if ($confirm_checked_content == $check_content) {
                Log::info('inner result:pass check content= ' . print_r($check_content, true));
                Log::info('inner result:pass confirmed check content= ' . print_r($confirm_checked_content, true));

                if ($walk->certificate_status != '' && $walk->certificate_status != null) {
                    $certificate_status_json['certificate'] = array();
                } else {
                    $certificate_status_json['certificate'] = array();
                }

                $newcertificateobj = array();
                $newcertificateobj['category_id'] = 1;
                $newcertificateobj['certificate_sent'] = "YES";
                $newcertificateobj['certificate_generate_time'] = date('Y-m-d H:i:s');

                array_push($certificate_status_json['certificate'], $newcertificateobj);
                $certificate_status_json = json_encode($certificate_status_json);
                Log::info('$certificate_status_json= ' . print_r($certificate_status_json, true));

                Walker::where('id', '=', $walk->id)->update(array('certificate_status' => $certificate_status_json));
                echo "Json inserted.";
            }
        }
    }

    public function SendCertificate(){
        $walker_id = $_POST['walker_id'];

        $walker = Walker::find($walker_id);

        if($walker){
            $learningContents = LearningContent::where('category_id', '=', '1')
                ->where('quiz_id', '>', 0)
                ->where('deleted_at', '=', NULL)->get();
            $learning_quiz_results = LearningQuizResults::where('walker_id','=',$walker->id)
                ->where('deleted_at', '=', NULL)->get();
            $check_content = 0;
            $confirm_checked_content = 0;

            foreach ($learningContents as $content) {
                if ($content->category_id == 1) {
                    $check_content++;
                    foreach ($learning_quiz_results as $result) {
                        if ($result->content_id == $content->id && $result->result == 'Pass') {
                            $confirm_checked_content++;
                        }
                    }
                }
            }
            Log::info('result:pass check content= ' . print_r($check_content, true));
            Log::info('result:pass confirmed check content= ' . print_r($confirm_checked_content, true));

            if ($confirm_checked_content == $check_content) {
                Log::info('inner result:pass check content= ' . print_r($check_content, true));
                Log::info('inner result:pass confirmed check content= ' . print_r($confirm_checked_content, true));

                if ($walker->certificate_status != '' && $walker->certificate_status != null) {
                    Walker::where('id', '=', $walker->id)->update(array('certificate_status' => ''));
                    $certificate_status_json['certificate'] = array();
                } else {
                    $certificate_status_json['certificate'] = array();
                }

                $newcertificateobj = array();
                $newcertificateobj['category_id'] = 1;
                $newcertificateobj['certificate_sent'] = "YES";
                $newcertificateobj['certificate_generate_time'] = date('Y-m-d H:i:s');

                array_push($certificate_status_json['certificate'], $newcertificateobj);
                $certificate_status_json = json_encode($certificate_status_json);
                Log::info('certificate_status_json= ' . print_r($certificate_status_json, true));

                Walker::where('id', '=', $walker->id)->update(array('certificate_status' => $certificate_status_json));
                $learningCategory = LearningCategory::where('id','=',1)->first();

                $test_parameter['category_name'] = $learningCategory->category;
                $test_parameter['driver_contact_name'] = $walker->contact_name;
                $test_parameter['date'] = date("Y-m-d");

                $file_name = $test_parameter['driver_contact_name'] . '_' . $test_parameter['category_name'] . '_' . uniqid() . '.pdf';
                $ext = 'pdf';
                $file_path = public_path('image') . '/uploads/' . $file_name . "." . $ext;


                try {
                    $pdf = PDF::loadView('testResultpdf', $test_parameter)->setPaper('legal')->setOrientation('portrait')->setWarnings(false);
                    $output = $pdf->output();
                } catch (Exception $exception) {
                    echo $exception;
                }
                file_put_contents($file_path, $output);

                /* Uplaod this file to s3bucket */
                if (Config::get('app.s3_bucket') != "") {

                    $s3 = App::make('aws')->get('s3');

                    $s3->putObject(array(
                        'Bucket' => Config::get('app.s3_bucket'),
                        'Key' => "uploads/learningcenter/documents/" . $file_name,
                        'SourceFile' => $file_path,
                    ));


                    $s3->putObjectAcl(array(
                        'Bucket' => Config::get('app.s3_bucket'),
                        'Key' => "uploads/learningcenter/documents/" . $file_name,
                        'ACL' => 'public-read'
                    ));

                    $final_file_name = "uploads/learningcenter/documents/" . $file_name;

                    $s3->getObjectUrl(Config::get('app.s3_bucket'), $final_file_name);
                    $localfile = $file_path;
                    $driver_name = array('driver_name' => $test_parameter['driver_contact_name']);
                    email_notification($walker->id, 'certificate', $driver_name, "Test Certificate", 'Certificate', null, $file_path);
                    unlink_image($localfile);
                }
                return 1;
            } else{
                return 2;
            }
        }
    }

    public function FeeCalculator(){

        return View::make('feecalculator')
            ->with('title', 'Fee Calculator')
            ->with('page', 'feecalc');
    }

    public function GenerateFeeReceipt(){
        $miles = $_POST['miles'];
        $waiting_time = $_POST['waiting_time'];
        $deadhead_miles = $_POST['deadhead_miles'];
        $total = $_POST['total'];
        $email = $_POST['email'];
        $pickupfee = $_POST['pickup_fee'];

        $settings = Settings::where('key', 'admin_email_address')->first();
        $admin_email = $settings->value;

        $test_parameter['pickupfee'] = $pickupfee;
        $test_parameter['miles'] = $miles;
        $test_parameter['waiting_time'] = $waiting_time;
        $test_parameter['deadhead_miles'] = $deadhead_miles;
        $test_parameter['total'] = $total;
        $test_parameter['admin_email_address'] = $admin_email;

        $file_name = 'RideReceipt'.'_' . uniqid();
        $ext = 'pdf';
        $file_path = public_path('image') . '/uploads/' . $file_name . "." . $ext;

        try {
            $pdf = PDF::loadView('ridefeereceipt', $test_parameter)->setPaper('legal')->setOrientation('portrait')->setWarnings(false);
            $output = $pdf->output();
            file_put_contents($file_path, $output);
            $generate_pdf = array('pdf_generate' => "Generated pdf is attached with this email");
            email_notification(1, 'send_pdf', $generate_pdf, "Ride Fee", 'sending_pdf', 'imp',$file_path,$email);
            unlink_image($file_path);
            return 1;
        } catch (Exception $exception) {
            echo $exception;
        }

    }

    public function TrainingModuleAdmin(){

        $learningCategory = LearningCategory::where('is_active', '=', 1)->get();

        $title = 'Training Modules';
        return View::make('training_module_admin')
            ->with('title', $title)
            ->with('page', 'module')
            ->with('learningmodules', $learningCategory);
    }

    public function EnrollDriversAdmin(){
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

            $query = "SELECT walker.* FROM walker where is_approved = 1 AND deleted_at IS NULL ";
            if($driverlist!=''){
                $query .= " AND walker.id NOT IN ($driverlist) ";
            }
            $query .=  " order by id DESC";
            $results = DB::select(DB::raw($query));

            $title = 'Training Module: '.$learningCategory->category;
            return View::make('enrolldrivers_admin')
                ->with('title', $title)
                ->with('page', 'module')
                ->with('drivers', $results)
                ->with('module_id',$learningCategory->id);

        }else{
            return Redirect::to('admin/trainingmodule')->with('error', 'Please try again.');
        }
    }

    public function AddDriversAdmin(){
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

            $query = "SELECT walker.* FROM walker where is_approved = 1 AND deleted_at IS NULL ";
            if($driverlist!=''){
                $query .= " AND walker.id NOT IN ($driverlist)";
            }
            $query .=  " order by id DESC ";
            $results = DB::select(DB::raw($query));

            echo "<table class=\"table table-bordered\"><tbody><tr><th>Driver Name</th><th>Driver Phone</th><th>Enroll</th></tr>";
            foreach($results as $result)
            {
                echo "<tr>";
                echo "<td style='text-align:center'>$result->contact_name</td>";
                echo "<td>$result->phone </td>";
                echo "<td>";
                echo "<input type=checkbox name=checkbox onclick=enroll_driver($result->id,$module_id); id=driver_id value='$result->id'>";
                echo "</td></tr>";
            }
            echo "</tbody></table>";
        }
    }

    public function DriverListingAdmin(){
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

            $query = "SELECT walker.* FROM walker where is_approved = 1 AND deleted_at IS NULL AND walker.id IN ($driverlist) ";
            $query .=  " order by id DESC";
            $results = DB::select(DB::raw($query));

        } else{
            $results = array();
        }

        $title = 'Enrolled Drivers for the Module: '.$learningCategory->category;
        return View::make('driverlisting_admin')
            ->with('title', $title)
            ->with('page', 'module')
            ->with('drivers', $results)
            ->with('module_id',$learningCategory->id);
    }

    public function DeleteDriverAdmin(){

        $module_id = Request::segment(4);
        $driver_id = Request::segment(5);

        if($module_id>0 && $driver_id>0){
            TrainingSessions::where('module_id','=',$module_id)
                ->where('driver_id','=',$driver_id)->delete();
            return Redirect::to('admin/trainingmodule/driverlisting/'.$module_id)->with('success', 'Driver deleted successfully.');
        }
    }

    public function DownloadDriverReport()
    {
        $learningCategory = LearningCategory::where('is_active', '=', 1)->get();

        $subQuery = DB::table('request_meta')
            ->select(DB::raw('count(*)'))
            ->whereRaw('walker_id = walker.id and status != 0');
        $subQuery1 = DB::table('request_meta')
            ->select(DB::raw('count(*)'))
            ->whereRaw('walker_id = walker.id and status=1');

        $walkers = DB::table('walker')
            ->leftJoin('transportation_provider','walker.transportation_provider_id','=','transportation_provider.id')
            ->select('transportation_provider.company as transportation_provider_company','walker.*', DB::raw("(" . $subQuery->toSql() . ") as 'total_requests'"), DB::raw("(" . $subQuery1->toSql() . ") as 'accepted_requests'"))->where('deleted_at', '=',NULL)
            /* ->where('walker.is_deleted', 0) */
            ->orderBy('walker.created_at', 'DESC')
            ->paginate(1000);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=drivers.csv');
        $handle = fopen('php://output', 'w');

        fputcsv($handle, array('ID','Name', 'Email','Phone', 'Bio', 'Total Request','Acceptance Rate %','Status', 'Company', 'Training Status'));
        //die;
        foreach ($walkers as $walker) {

            if ($walker->bio) {
                $walker_bio =  $walker->bio;
            } else {
                $walker_bio = Config::get('app.blank_fiend_val');
            }

            if ($walker->total_requests != 0) {
                $accept_request = round(($walker->accepted_requests / $walker->total_requests) * 100, 2);
            } else {
                $accept_request = 0;
            }

            if ($walker->is_approved == 1) {
                $walker_approve = "Approved";
            } else {
                $walker_approve = "Pending";
            }

            if ($walker->transportation_provider_id != null) {
                $walker_company = $walker->transportation_provider_company ;
            } else {
                $walker_company = "NA";
            }

            $certificate = array();
            if ($walker->certificate_status != '' && $walker->certificate_status != null) {
                $certificate_status_json = json_decode($walker->certificate_status, true);
            } else {
                $certificate_status_json['certificate'] = array();
            }

            if(count($certificate_status_json['certificate']) >0) {
                foreach ($certificate_status_json['certificate'] as $status) {

                    Log::info('status= ' . print_r($status['certificate_sent'], true));

                    $datetime = new DateTime($status['certificate_generate_time']);
                    $datetime->format('Y-m-d H:i:s') . "\n";
                    if ($walker->timezone != '' && $walker->timezone != NULL) {
                        $user_time = new DateTimeZone($walker->timezone);
                    } else {
                        $user_time = new DateTimeZone(Config::get('app.timezone'));
                    }

                    $datetime->setTimezone($user_time);
                    $certificate_generate_time = $datetime->format('Y-m-d H:i:s');
                    $learningcategory = LearningCategory::find($status['category_id']);

                    $certificate[0] = $learningcategory->category;
                    $certificate[1] = $status['certificate_sent'];
                    $certificate[2] = $certificate_generate_time;
                }
                Log::info('certificate= ' . print_r($certificate, true));
            } else{
                $certificate[0] = 'NA';
                $certificate[1] = 'NA';
                $certificate[2] = 'NA';
            }

            fputcsv($handle, array(
                $walker->id,
                $walker->contact_name,
                $walker->email,
                $walker->phone,
                $walker_bio,
                $walker->total_requests,
                $accept_request,
                $walker_approve,
                $walker_company,
                $certificate[0] ." - ". $certificate[1] . " - ". $certificate[2]
            ));
        }
        fclose($handle);
        $headers = array(
            'Content-Type' => 'text/csv',
        );
    }

    public function InvoiceSent(){
        $walker_id = $_POST['driver_id'];
        $module_id = $_POST['module_id'];
        $invoice_status = $_POST['invoice_status'];

        $walker = Walker::find($walker_id);

        if($walker){
            Log::info('module_id = ' . print_r($module_id, true));
            Log::info('walker_id = ' . print_r($walker_id, true));

            if ($walker->certificate_status != '' && $walker->certificate_status != null) {
                $certificate_status_json = json_decode($walker->certificate_status, true);
            }

            if($invoice_status==1){
                $certificate_status_json['certificate'][0]['invoice_sent']  = "YES";
            } else{
                $certificate_status_json['certificate'][0]['invoice_sent']  = "NO";
            }

            $certificate_status_json = json_encode($certificate_status_json);
            Log::info('certificate_status_json= ' . print_r($certificate_status_json, true));

            Walker::where('id', '=', $walker->id)->update(array('certificate_status' => $certificate_status_json));
            return Redirect::to('admin/driver/certificate_status/'.$module_id)->with('success', 'Invoice Sent.');
        }
    }

    public function InvoicePaid(){
        $walker_id = $_POST['driver_id'];
        $module_id = $_POST['module_id'];
        $invoice_status = $_POST['invoice_status'];

        $walker = Walker::find($walker_id);

        if($walker){
            Log::info('module_id = ' . print_r($module_id, true));
            Log::info('walker_id = ' . print_r($walker_id, true));

            if ($walker->certificate_status != '' && $walker->certificate_status != null) {
                $certificate_status_json = json_decode($walker->certificate_status, true);
            }

            if($invoice_status==1){
                $certificate_status_json['certificate'][0]['invoice_paid']  = "YES";
            } else{
                $certificate_status_json['certificate'][0]['invoice_paid']  = "NO";
            }

            $certificate_status_json = json_encode($certificate_status_json);
            Log::info('certificate_status_json= ' . print_r($certificate_status_json, true));

            Walker::where('id', '=', $walker->id)->update(array('certificate_status' => $certificate_status_json));
            return Redirect::to('admin/driver/certificate_status/'.$module_id)->with('success', 'Invoice Paid.');
        }
    }

    public function RatingTransportationProvider(){
        $Dispatchers = DB::table('dispatcher')
            ->leftJoin('transportation_provider', 'transportation_provider.id', '=', 'dispatcher.transportation_provider_id')
            ->leftJoin('rating_transportation_provider', 'rating_transportation_provider.tp_id', '=', 'dispatcher.transportation_provider_id')
            ->select('dispatcher.*','transportation_provider.company as transportation_company','rating_transportation_provider.rating',
                'rating_transportation_provider.comment')
            ->where('dispatcher.transportation_provider_id', '>', 0)
            ->orderBy('dispatcher.created_at', 'DESC')
            ->paginate(10);
        $title = "Transportation Provider Ratings";

        return View::make('transportationproviderrating')
            ->with('title', $title)
            ->with('page', 'dispatchers')
            ->with('Dispatchers', $Dispatchers);
    }
}


