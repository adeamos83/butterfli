<?php

class PaymentController extends \BaseController {

    /**
     * Display a listing of the resource.
     *
     *
     * @return Response
     */
    private $_api_context;

    public function __construct() {

        $this->beforeFilter(function() {
            if (!Session::has('user_id')) {
                Session::put('pre_login_url', URL::current());
                return Redirect::to('/dispatcher/signin');
            } else {
                $user_id = Session::get('user_id');
                $Dispatcher = Dispatcher::where('id', $user_id)->first();
                Session::put('user_name', $Dispatcher->contact_name);

            }
        }, array('except' => array(
                'userLogin',
        )));
    }
	
	public function saveUserPayment() {
        $payment_token = Input::get('stripeToken');
        $owner_id = Session::get('user_id');
        $owner_data = Owner::find($owner_id);
        try {
            if (Config::get('app.default_payment') == 'stripe') {
                Stripe::setApiKey(Config::get('app.stripe_secret_key'));

                $customer = Stripe_Customer::create(array(
                            "card" => $payment_token,
                            "description" => $owner_data->email)
                );
                //Log::info('key = ' . print_r($customer, true));

                $last_four = substr(Input::get('number'), -4);
                if ($customer) {
                    $customer_id = $customer->id;
                    $payment = new Payment;
                    $payment->owner_id = $owner_id;
                    $payment->customer_id = $customer_id;
                    $payment->last_four = $last_four;
                    $payment->card_token = $customer->sources->data[0]->id;

                    $card_count = DB::table('payment')->where('owner_id', '=', $owner_id)->count();
                    if ($card_count > 0) {
                        $payment->is_default = 0;
                    } else {
                        $payment->is_default = 1;
                    }
                    $payment->save();
                    $message = "Your Card is successfully added.";
                    $type = "success";
                    return Redirect::to('/user/payments')->with('message', $message)->with('type', $type);
                } else {
                    $message = "Sorry something went wrong.";
                    $type = "danger";
                    return Redirect::to('/user/payments')->with('message', $message)->with('type', $type);
                }
            } else {
                Braintree_Configuration::environment(Config::get('app.braintree_environment'));
                Braintree_Configuration::merchantId(Config::get('app.braintree_merchant_id'));
                Braintree_Configuration::publicKey(Config::get('app.braintree_public_key'));
                Braintree_Configuration::privateKey(Config::get('app.braintree_private_key'));
                $result = Braintree_Customer::create(array(
                            "firstName" => $owner_data->contact_name,
                            "creditCard" => array(
                                "number" => Input::get('number'),
                                "expirationMonth" => Input::get('month'),
                                "expirationYear" => Input::get('year'),
                                "cvv" => Input::get('cvv'),
                            )
                ));

                if ($result->success) {
                    $num = $result->customer->creditCards[0]->maskedNumber;
                    $last_four = substr($num, -4);
                    $customer_id = $result->customer->id;
                    $payment = new Payment;
                    $payment->owner_id = $owner_id;
                    $payment->customer_id = $customer_id;
                    $payment->last_four = $last_four;
                    $payment->card_token = $result->customer->creditCards[0]->token;
                    $payment->save();

                    $message = "Your Card is successfully added.";
                    $type = "success";
                    return Redirect::to('/user/payments')->with('message', $message)->with('type', $type);
                } else {
                    $message = "Sorry something went wrong.";
                    $type = "danger";
                    return Redirect::to('/user/payments')->with('message', $message)->with('type', $type);
                }
            }
        } catch (Exception $e) {
            $message = "Sorry something went wrong.";
            $type = "danger";
            return Redirect::to('/user/payments')->with('message', $message)->with('type', $type);
        }
    }
}
