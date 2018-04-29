<?php

class HelloController extends \BaseController {

    /**
     * Display a listing of the resource.
     *
     *
     * @return Response
     */
    public function index() {
        echo distanceGeoPoints(22, 50.0000001, 22, 50.000001);
    }

    public function test() {
        test_ios_noti("357eacdb0f2c1fb2e0196f41282b8b9f6e9fbd83ced7e0cf3d1cb68e732ba599", "walker", "my title", "my_message");
        test_ios_noti("77C892043F137BC4A8AF2A324BF73AD9C6B7859F7CC3116E837BAA2EC4731666", "owner", "my title", "my_message");
    }

    public function passes() {
        return View::make('passes')->with('title', "demo");
    }

    public function arr() {
        /* $arrs = array(); */
        $customer_msgs = array();
        /* for ($i = 1; $i <= 10; $i++) {
          /* $msgs = array(); */
        /* $msgs[$i] = "Message " . $i;
          /* array_push($arrs, $msgs); */
        /* } */
        $customer_msgs['1'] = "This Email is not Registered";
        $customer_msgs['2'] = "Name is required";
        $customer_msgs['3'] = "Age is required";
        $customer_msgs['4'] = "Breed is required";
        $customer_msgs['5'] = "Seassion token was expired."; /* token */
        $customer_msgs['6'] = "Your unique id is missing";
        $customer_msgs['7'] = "Image is required";
        $customer_msgs['8'] = "Invalid Input";
        $customer_msgs['9'] = "Token Expired";
        $customer_msgs['10'] = "Owner ID not Found";
        $customer_msgs['11'] = "Not a valid token";
        $customer_msgs['12'] = "No Dogs Found";
        $customer_msgs['13'] = "Driver not Found";
        $customer_msgs['14'] = 'PayPal unique id is missing.';
        $customer_msgs['15'] = 'Contact numbers are required.';
        $customer_msgs['16'] = 'Your ETA is required.';
        $customer_msgs['17'] = 'No Credit Found';
        $customer_msgs['18'] = 'Successfully Log-Out';
        $customer_msgs['19'] = "Id of Request is required";
        $customer_msgs['20'] = "Already Rated";
        $customer_msgs['21'] = "Walk is not completed";
        $customer_msgs['22'] = "Walk ID doesnot matches with Dog ID";
        $customer_msgs['23'] = "Walk ID Not Found";
        $customer_msgs['24'] = 'Invalid Phone Number';
        $customer_msgs['25'] = 'Phone number must be required.';
        $customer_msgs['26'] = 'Social Login unique id must be required.';
        $customer_msgs['27'] = 'Email ID already Registred';
        $customer_msgs['28'] = 'Password field is required.';
        $customer_msgs['29'] = 'Email field is required';
        $customer_msgs['30'] = 'Name field is required.';
        $customer_msgs['31'] = 'Last Name field is required.';
        $customer_msgs['32'] = 'Push notification token is required.';
        $customer_msgs['33'] = 'Device type must be android or ios';
        $customer_msgs['34'] = 'Login type is required.';
        $customer_msgs['35'] = 'Login by mismatch';
        $customer_msgs['36'] = 'Invalid Username and Password';
        $customer_msgs['37'] = 'Not a Registered User';
        $customer_msgs['38'] = 'Not a valid social registration User';
        $customer_msgs['39'] = 'Card number\'s last four digits are missing.';
        $customer_msgs['40'] = 'Unique payment token is missing.';
        $customer_msgs['41'] = 'Could not create client ID';
        $customer_msgs['42'] = 'Unique card ID is missing.';
        $customer_msgs['43'] = 'Card ID and ' . Config::get('app.generic_keywords.User') . ' ID Doesnot matches';
        $customer_msgs['44'] = 'Card not found';
        $customer_msgs['45'] = 'This user does not have a referral code';
        $customer_msgs['46'] = 'No Card Found';
        $customer_msgs['47'] = 'Invalid Old Password';
        $customer_msgs['48'] = 'Old Password must not be blank';
        $customer_msgs['49'] = "location points are missing";
        $customer_msgs['50'] = '' . Config::get('app.generic_keywords.User') . 'ID not Found';
        $customer_msgs['51'] = 'Request ID doesnot matches with' . Config::get('app.generic_keywords.User') . ' ID';
        $customer_msgs['52'] = 'Request ID Not Found';
        $customer_msgs['53'] = '' . Config::get('app.generic_keywords.User') . ' ID not Found';
        $customer_msgs['54'] = 'No walker found';
        $customer_msgs['55'] = 'No ' . Config::get('app.generic_keywords.Provider') . ' found matching the service type'; /* remaining from here */
        $customer_msgs['56'] = 'No ' . Config::get('app.generic_keywords.Provider') . ' found';
        $customer_msgs['57'] = 'Selected provider\'s unique id is missing.';
        $customer_msgs['58'] = 'Your previous Request is Pending.';
        $customer_msgs['59'] = 'Please add card first for payment.';
        $customer_msgs['60'] = 'No ' . Config::get('app.generic_keywords.Provider') . ' found matching the service type for scheduled request.';
        $customer_msgs['61'] = 'Invalid Promo Code';
        $customer_msgs['62'] = 'You can not apply multiple code for single trip.';
        $customer_msgs['63'] = 'Promotional code successfully applied.';
        $customer_msgs['64'] = 'Promotional code is not available';
        $customer_msgs['65'] = 'Promotional code already used.';
        $customer_msgs['66'] = 'Promotion feature is not active on card payment.';
        $customer_msgs['67'] = 'Promotion feature is not active on cash payment.';
        $customer_msgs['68'] = 'Promotion feature is not active.';
        $customer_msgs['69'] = 'You can\'t apply promotional code without creating request.';
        $customer_msgs['70'] = 'Payment mode is paypal';
        $customer_msgs['71'] = 'No ' . Config::get('app.generic_keywords.Provider') . ' found for the selected service in your area currently';
        $customer_msgs['72'] = 'You are already in debt';
        $customer_msgs['73'] = 'Distance is required.';
        $customer_msgs['74'] = 'Time is required.';
        $customer_msgs['75'] = 'Request ID doesnot matches with ' . Config::get('app.generic_keywords.User') . ' ID';
        $customer_msgs['76'] = 'On going ' . Config::get('app.generic_keywords.Trip') . '.';
        $customer_msgs['77'] = 'No on going ' . Config::get('app.generic_keywords.Trip') . ' found.';
        $customer_msgs['78'] = 'Searching for ' . Config::get('app.generic_keywords.Provider') . 's.';
        $customer_msgs['79'] = 'No ' . Config::get('app.generic_keywords.Provider') . 's are available currently. Please try after sometime.';
        $customer_msgs['80'] = '' . Config::get('app.generic_keywords.Provider') . ' not Confirmed yet';
        $customer_msgs['81'] = 'No ' . Config::get('app.generic_keywords.Provider') . ' found around you.';
        $customer_msgs['82'] = 'Time-Zone is required';
        $customer_msgs['83'] = 'Schedule date and time must be required.';
        $customer_msgs['84'] = "Sorry, You can't create schedule morethen two week faar from today.";
        $customer_msgs['85'] = 'update successfully';
        $customer_msgs['86'] = 'Payment mode not updated';
        $customer_msgs['87'] = "Destination Set Successfully";
        $customer_msgs['88'] = 'Sorry You can\'t create event earlier then 14 days.';
        $customer_msgs['89'] = 'Yo have invited maximum members for event.';
        $customer_msgs['90'] = 'Please change braintree as default gateway';
        $customer_msgs['91'] = "Sorry, You have apready apply the refereel code.";
        $customer_msgs['92'] = 'Referral process successfully completed.';
        $customer_msgs['93'] = "Sorry, You can't apply your refereel code.";
        $customer_msgs['94'] = 'Invalid referral code';
        $customer_msgs['95'] = 'You have skipped for referral process';
        $customer_msgs['96'] = 'No Dog Found';
        $customer_msgs['97'] = 'Payment type must be required.';
        $customer_msgs['98'] = 'Event Unique Id is missing.';
        $customer_msgs['99'] = 'Sorry you can\'t delte past or ongoing event.';
        $customer_msgs['100'] = 'Event not found.';
        $customer_msgs['101'] = 'Event successfully deleted.';
        /* print_r($customer_msgs); */
        $provider_msgs = array();
        $provider_msgs['1'] = "This Email is not Registered";
        $provider_msgs['2'] = 'Password field is required.';
        $provider_msgs['3'] = 'Email field is required';
        $provider_msgs['4'] = 'Name field is required.';
        $provider_msgs['5'] = 'Last Name field is required.';
        $provider_msgs['6'] = 'Image is required';
        $provider_msgs['7'] = 'Push notification token is required.';
        $provider_msgs['8'] = 'Device type must be android or ios';
        $provider_msgs['9'] = 'Login type is required.';
        $provider_msgs['10'] = 'Phone number must be required.';
        $provider_msgs['11'] = 'Social Login unique id must be required.';
        $provider_msgs['12'] = 'Invalid Input';
        $provider_msgs['13'] = 'Invalid Phone Number';
        $provider_msgs['14'] = 'Email ID already Registred';
        $provider_msgs['15'] = 'Login by mismatch';
        $provider_msgs['16'] = 'Invalid Username and Password';
        $provider_msgs['17'] = 'Not a Registered User';
        $provider_msgs['18'] = 'Not a valid social registration User';
        $provider_msgs['19'] = 'Id of Request is required.';
        $provider_msgs['20'] = 'Your unique id is missing.';
        $provider_msgs['21'] = 'Already Rated';
        $provider_msgs['22'] = 'Service ID doesnot matches with ' . Config::get('app.generic_keywords.Provider') . ' ID';
        $provider_msgs['23'] = 'Service ID Not Found';
        $provider_msgs['24'] = 'Token Expired';
        $provider_msgs['25'] = '' . Config::get('app.generic_keywords.Provider') . ' ID not Found';
        $provider_msgs['26'] = 'Not a valid token';
        $provider_msgs['27'] = 'Service Already Started';
        $provider_msgs['28'] = 'location points are missing.';
        $provider_msgs['29'] = 'accept or reject must be required.';
        $provider_msgs['30'] = 'Request ID does not matches' . Config::get('app.generic_keywords.Provider') . ' ID';
        $provider_msgs['31'] = 'Request Canceled.';
        $provider_msgs['32'] = 'Request ID Not Found';
        $provider_msgs['33'] = '' . Config::get('app.generic_keywords.Provider') . ' not yet confirmed';
        $provider_msgs['34'] = 'Service not yet started';
        $provider_msgs['35'] = '' . Config::get('app.generic_keywords.Provider') . ' not yet arrived';
        $provider_msgs['36'] = 'Distance is required.';
        $provider_msgs['37'] = 'Time is required.';
        $provider_msgs['38'] = 'Invalid Old Password';
        $provider_msgs['39'] = 'Old Password must not be blank';
        $provider_msgs['40'] = 'Successfully Log-Out';


        foreach ($customer_msgs as $key => $value) {
            echo '<string name="error_' . $key . '">' . $value . '</string>';
        }
        echo "\n ################################################################################ \n";
        foreach ($provider_msgs as $key => $value) {
            echo '<string name="error_' . $key . '">' . $value . '</string>';
        }
        /* $i = 0;
          foreach ($customer_msgs as $customer_msg) {

          echo '<string name="error_' . $i . '">' . $customer_msg . '</string>';
          $i++;
          } */
        exit;
        /* $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages_client' => $customer_msgs, 'error_messages_provider' => $provider_msgs);
          $response_code = 200;
          $response = Response::json($response_array, $response_code);
          return $response; */
    }

}
