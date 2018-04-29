<?php

class ConsumerController extends \BaseController {

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
            if (!Session::has('user_id')) {
                return Redirect::to('/consumer/signin');
            } else {
                $consumer_id = Session::get('user_id');
                $consumer = Consumer::find($consumer_id);
                Session::put('consumer_email', $consumer->email);
                Session::put('consumer_name', $consumer->contact_name);
                Session::put('consumer_pic', $consumer->picture);
            }
        }, array('except' => array(
                'ConsumerLogin',
                'ConsumerVerify',
                'ConsumerForgotPassword',
                'ConsumerRegister',
                'ConsumerSave',
                'ConsumerActivation'
        )));
    }

    public function index() {
        return Redirect::to('/consumer/signin');
    }

    public function ConsumerLogin() {
        if (Session::has('user_id')) {
            $consumer_id = Session::get('user_id');
            $consumer = Consumer::find($consumer_id);
            Session::put('consumer_email', $consumer->email);
            Session::put('consumer_name', $consumer->contact_name);
            Session::put('consumer_pic', $consumer->picture);
            return Redirect::to('consumer/myprofile');
        }
        return View::make('consumer.ConsumerLogin');
    }

    public function ConsumerRegister() {
        return View::make('consumer.ConsumerSignup');
    }

    public function ConsumerSave() {
        $contact_name = Input::get('contact_name');
        $email = Input::get('email');
        $phone = Input::get('phone');
        $password = Input::get('password');
        $company  = Input::get('company');

        $validator = Validator::make(
            array(
                'contact_name' => $contact_name,
                'email' => $email,
                'password' => $password,
                'phone'=> $phone,
                'company'=>$company
            ), array(
            'password' => 'required',
            'email' => 'required',
            'contact_name' => 'required',
            'phone' =>'required',
            'company'=>'required'
        ), array(
                'password' => 'Password field is required.',
                'email' => 'Email field is required',
                'contact_name' => 'Name field is required.'
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
            return Redirect::to('consumer/signup')->with('error', 'Please Fill all the fields.');
        } else if ($validator1->fails()) {
            return Redirect::to('consumer/signup')->with('error', 'Please Enter email correctly.');
        } else if ($validatorPhone->fails()) {
            return Redirect::to('consumer/signup')->with('error', 'Invalid Phone Number Format');
        } else {
            if (Consumer::where('email', $email)->count() == 0) {
                $consumer = new Consumer;
                $consumer->contact_name = $contact_name;
                $consumer->email = $email;
                if(strlen($phone) <=10){
                    $consumer->phone = "+1".$phone;
                } else{
                    $consumer->phone = $phone;
                }
                if ($password != "") {
                    $consumer->password = Hash::make($password);
                }

                $consumer->client_id = generate_client_keys(40);
                $consumer->client_secret = generate_client_keys(40);

                $consumer->company = $company;
                $s3_url = "";
                /*if (Input::hasfile('picture')) {
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
                }*/

                $consumer->save();
                //Add client_id and client_secret in oauth_clients table

                $oauth_clients = new OAuthClients;
                $oauth_clients->id = $consumer->client_id;
                $oauth_clients->secret = $consumer->client_secret;
                $oauth_clients->name = $consumer->company;
                $oauth_clients->created_at = date("Y-m-d H:i:s");;
                $oauth_clients->updated_at = date("Y-m-d H:i:s");
                $oauth_clients->save();

                $login_url = web_url() . "/consumer/signin";
                $settings = Settings::where('key', 'admin_email_address')->first();
                $admin_email = $settings->value;
                $pattern = array('admin_email' => $admin_email, 'name' => ucwords($consumer->contact_name),
                    'web_url' => $login_url,'provider'=>'Consumer');
                $subject = "Welcome to " . ucwords(Config::get('app.website_title')) . ", " . ucwords($consumer->contact_name) . "";
                //email_notification($consumer->id, 'consumer', $pattern, $subject, 'consumer_register', "imp");

                return Redirect::to('consumer/signin')->with('success', 'You have successfully registered.');
            } else {
                return Redirect::to('consumer/signup')->with('error', 'This email ID is already registered.');
            }
        }
    }

    public function ConsumerForgotPassword() {
        $email = Input::get('email');
        $consumer = Consumer::where('email', $email)->first();
        if ($consumer) {
            $new_password = time();
            $new_password .= rand();
            $new_password = sha1($new_password);
            $new_password = substr($new_password, 0, 8);
            $consumer->password = Hash::make($new_password);
            $consumer->save();

            $settings = Settings::where('key', 'admin_email_address')->first();
            $admin_email = $settings->value;
            $login_url = web_url() . "/consumer/signin";
            $pattern = array('name' => ucwords($consumer->contact_name), 'admin_email' => $admin_email, 'new_password' => $new_password, 'login_url' => $login_url);
            $subject = "Your New Password";
            email_notification($consumer->id, 'consumer', $pattern, $subject, 'forgot_password', 'imp');

            // echo $pattern;
            return Redirect::to('consumer/signin')->with('success', 'password reseted successfully. Please check your inbox for new password.');
        } else {
            return Redirect::to('consumer/signin')->with('error', 'This email ID is not registered with us');
        }
    }

    public function ConsumerVerify() {
        $email = Input::get('email');
        $password = Input::get('password');
        $consumer = Consumer::where('email', '=', $email)->first();

        if ($consumer) {
            if ($consumer->is_active == 1) {
                if ($consumer && Hash::check($password, $consumer->password)) {
                    Session::put('user_id', $consumer->id);
                    Session::put('consumer_email', $consumer->email);
                    Session::put('consumer_name', $consumer->contact_name);
                    Session::put('consumer_pic', $consumer->picture);
                    return Redirect::to('consumer/myprofile');
                } else {
                    return Redirect::to('consumer/signin')->with('error', 'Invalid email and password');
                }
            } else {
                return Redirect::to('consumer/signin')->with('error', 'Please Activate your Email');
            }
        } else {
            return Redirect::to('consumer/signin')->with('error', 'Invalid email');
        }
    }

    public function ConsumerLogout() {
        Session::flush();
        return Redirect::to('/consumer/signin');
    }

    public function MyProfile()
    {
        $user_id = Session::get('user_id');
        $consumer = Consumer::find($user_id);

        return View::make('consumer.MyProfile')
            ->with('title', 'My Profile')
            ->with('provider', $consumer);
    }

    public function UpdateConsumerProfile() {
        $user_id = Session::get('user_id');
        $contact_name = Input::get('contact_name');
        $phone = Input::get('phone');
        $company = Input::get('company');

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
            'phone' => 'phone'
        ), array(
                'phone' => 'Phone number must be required.'
            )
        );

        if ($validator->fails()) {
            $error_messages = $validator->messages();
            return Redirect::to('consumer/myprofile')->with('error', 'Please Fill all the fields.');
        }else if ($validatorPhone->fails()) {
            return Redirect::to('consumer/myprofile')->with('error', 'Invalid Phone Number Format');
        } else {
            $consumer = Consumer::find($user_id);

            $consumer->contact_name = $contact_name;
            if(strlen($phone) <=10){
                $consumer->phone = "+1".$phone;
            } else{
                $consumer->phone = $phone;
            }

            $consumer->company = $company;
            $consumer->save();

            return Redirect::to('/consumer/myprofile')->with('success', 'Your profile has been updated successfully');
        }
    }

    public function UpdateConsumerPassword() {
        $current_password = Input::get('current_password');
        $new_password = Input::get('new_password');
        $confirm_password = Input::get('confirm_password');

        $user_id = Session::get('user_id');
        $consumer = Consumer::find($user_id);


        if ($new_password === $confirm_password) {

            if (Hash::check($current_password, $consumer->password)) {
                $password = Hash::make($new_password);
                $consumer->password = $password;
                $consumer->save();

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
        return Redirect::to('/consumer/myprofile')->with('success', $message);
    }

}
