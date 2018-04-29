<?php
namespace helpers;
//use Stripe\Stripe;

class PaymentServices {

    // constructor
    function __construct() {
        
    }

	public function saveUserPayment($token,$cardtype,$last4,$cardholdername,$rememberme,$dispatcher_assigned_id='',$card_id) {
        if($token!='' && $cardtype!='' && $last4!='' && $cardholdername!=''){
			/* Create a payment entry with card token */
			$payment = new \Payment;				
			$payment->card_type  = $cardtype;
			$payment->created_at = date('Y-m-d H:i:s');
			$payment->updated_at = date('Y-m-d H:i:s');
			$payment->last_four  = $last4;
			$payment->card_token = $token;
			$payment->card_id    = $card_id;
			$payment->is_default = 1;
			
			
			if($rememberme==1){
				if (\Config::get('app.default_payment') == 'stripe') {
					\Stripe::setApiKey(\Config::get('app.stripe_secret_key'));
					if($dispatcher_assigned_id>0){
                        if(\Session::get('user_select') == 3) {
                            $query = "SELECT * FROM payment LEFT JOIN owner ON owner.id = payment.owner_id WHERE customer_id IS NOT NULL AND owner_id =" . $dispatcher_assigned_id . " ORDER BY payment.id DESC LIMIT 1";
                        } else{
                            $query = "SELECT * FROM payment LEFT JOIN dispatcher_assigned ON dispatcher_assigned.id = payment.dispatcher_assigned_id WHERE customer_id IS NOT NULL AND dispatcher_assigned_id =" . $dispatcher_assigned_id . " ORDER BY payment.id DESC LIMIT 1";
                        }    
						$results = \DB::select(\DB::raw($query));
						if($results){
							\Log::info('results for dispatcher_assigned_id = ' . print_r($results[0], true));
							if($results[0]->customer_id!=''){
								try {
									$customer = \Stripe_Customer::retrieve($results[0]->customer_id);
									$customer->sources->create(array("source" => $token));
									\Log::info('retrivefunction = ' . print_r($customer, true));
									$customer->save();
									$customer = \Stripe_Customer::retrieve($results[0]->customer_id);
									$customer->description = $results[0]->contact_name;
									$customer->default_source = $card_id; 
									$customer->save();
									\Log::info('card update = ' . print_r($customer, true));
									$payment->save();
									if ($customer) {
										$customer_id = $customer->id;
                                        if(\Session::get('user_select') == 3) {
                                            \Payment::where('card_token', '=', $token)->update(array('customer_id' => $customer_id, 'updated_at' => date('Y-m-d H:i:s'), 'owner_id' => $dispatcher_assigned_id));

                                            \Payment::where('owner_id', '=', $dispatcher_assigned_id)->update(array('is_default' => 0, 'updated_at' => date('Y-m-d H:i:s')));
                                        }else{
                                            \Payment::where('card_token', '=', $token)->update(array('customer_id' => $customer_id, 'updated_at' => date('Y-m-d H:i:s'), 'dispatcher_assigned_id' => $dispatcher_assigned_id));

                                            \Payment::where('dispatcher_assigned_id', '=', $dispatcher_assigned_id)->update(array('is_default' => 0, 'updated_at' => date('Y-m-d H:i:s')));
                                        }
										
										\Payment::where('card_token', '=', $token)->update(array('is_default' => 1, 'updated_at' => date('Y-m-d H:i:s')));
										$message = "Your Card is successfully added.";
										$type = "success";
										return 1;
									}									
								}catch (\Stripe_RateLimitError $e) {
									// Invalid parameters were supplied to Stripe's API
									$response_array = array('error' => $e->getMessage());
									\Log::info('Stripe_RateLimitError = ' . print_r($response_array, true));
									$response = $response_array;
									return $response;
								} catch (\Stripe_InvalidRequestError $e) {
									// Invalid parameters were supplied to Stripe's API
									$response_array = array('error' => $e->getMessage());
									\Log::info('InvalidRequestError = ' . print_r($response_array, true));
									$response = $response_array;
									return $response;
								} catch (\Stripe_AuthenticationError $e) {								
									// Invalid parameters were supplied to Stripe's API
									$response_array = array('error' => $e->getMessage());
									\Log::info('Stripe_AuthenticationError = ' . print_r($response_array, true));
									$response = $response_array;
									return $response;
								} catch (\Stripe_CardError $e) {
									// Invalid parameters were supplied to Stripe's API
									$response_array = array('error' => $e->getMessage());
									\Log::info('Stripe_CardError = ' . print_r($response_array, true));
									$response = $response_array;
									return $response;
								} catch (\Stripe_ApiError $e) {
									// Invalid parameters were supplied to Stripe's API
									$response_array = array('error' => $e->getMessage());
									\Log::info('Stripe_ApiError = ' . print_r($response_array, true));
									$response = $response_array;
									return $response;
								}
							}
						} else{
							try {
								$customer = \Stripe_Customer::create(array(
											"source" => $token,
											"description" => $cardholdername)
								);
								\Log::info('key = ' . print_r($customer, true));
								$payment->save();
								if ($customer) {
									$customer_id = $customer->id;

                                    if(\Session::get('user_select') == 3) {
                                        \Payment::where('card_token', '=', $token)->update(array('customer_id' => $customer_id, 'updated_at' => date('Y-m-d H:i:s'), 'owner_id' => $dispatcher_assigned_id));

                                        \Payment::where('owner_id', '=', $dispatcher_assigned_id)->update(array('is_default' => 0, 'updated_at' => date('Y-m-d H:i:s')));
                                    }else{
                                        \Payment::where('card_token', '=', $token)->update(array('customer_id' => $customer_id, 'updated_at' => date('Y-m-d H:i:s'), 'dispatcher_assigned_id' => $dispatcher_assigned_id));

                                        \Payment::where('dispatcher_assigned_id', '=', $dispatcher_assigned_id)->update(array('is_default' => 0, 'updated_at' => date('Y-m-d H:i:s')));
                                    }

									\Payment::where('card_token', '=', $token)->update(array('is_default' => 1, 'updated_at' => date('Y-m-d H:i:s')));
									$message = "Your Card is successfully added.";
									$type = "success";
									return 1;
								}
							}catch (\Stripe_RateLimitError $e) {
								// Invalid parameters were supplied to Stripe's API
								$response_array = array('error' => $e->getMessage());
								\Log::info('Stripe_RateLimitError = ' . print_r($response_array, true));
								$response = $response_array;
								return $response;
							} catch (\Stripe_InvalidRequestError $e) {
								// Invalid parameters were supplied to Stripe's API
								$response_array = array('error' => $e->getMessage());
								\Log::info('InvalidRequestError = ' . print_r($response_array, true));
								$response = $response_array;
								return $response;
							} catch (\Stripe_AuthenticationError $e) {								
								// Invalid parameters were supplied to Stripe's API
								$response_array = array('error' => $e->getMessage());
								\Log::info('Stripe_AuthenticationError = ' . print_r($response_array, true));
								$response = $response_array;
								return $response;
							} catch (\Stripe_CardError $e) {
								// Invalid parameters were supplied to Stripe's API
								$response_array = array('error' => $e->getMessage());
								\Log::info('Stripe_CardError = ' . print_r($response_array, true));
								$response = $response_array;
								return $response;
							} catch (\Stripe_ApiError $e) {
								// Invalid parameters were supplied to Stripe's API
								$response_array = array('error' => $e->getMessage());
								\Log::info('Stripe_ApiError = ' . print_r($response_array, true));
								$response = $response_array;
								return $response;
							} catch (\Exception $e) {
								// Invalid parameters were supplied to Stripe's API
								$response_array = array('error' => $e->getMessage());
								\Log::info('Exception = ' . print_r($response_array, true));
								$response = $response_array;
								return $response;								
							}
						}
					} else{
						try {
							$customer = \Stripe_Customer::create(array(
										"source" => $token,
										"description" => $cardholdername)
							);
							\Log::info('no dispatcher_assigned_id = ' . print_r($customer, true));
							$payment->save();
							if ($customer) {
								$customer_id = $customer->id;
								\Payment::where('card_token', '=', $token)->update(array('customer_id' => $customer_id, 'updated_at' => date('Y-m-d H:i:s')));
								return 1;
							}
						}catch (\Stripe_RateLimitError $e) {
							// Invalid parameters were supplied to Stripe's API
							$response_array = array('error' => $e->getMessage());
							\Log::info('Stripe_RateLimitError = ' . print_r($response_array, true));
							$response = $response_array;
							return $response;
						} catch (\Stripe_InvalidRequestError $e) {
							// Invalid parameters were supplied to Stripe's API
							$response_array = array('error' => $e->getMessage());
							\Log::info('InvalidRequestError = ' . print_r($response_array, true));
							$response = $response_array;
							return $response;
						} catch (Stripe_AuthenticationError $e) {								
							// Invalid parameters were supplied to Stripe's API
							$response_array = array('error' => $e->getMessage());
							\Log::info('Stripe_AuthenticationError = ' . print_r($response_array, true));
							$response = $response_array;
							return $response;
						} catch (Stripe_CardError $e) {
							// Invalid parameters were supplied to Stripe's API
							$response_array = array('error' => $e->getMessage());
							\Log::info('Stripe_CardError = ' . print_r($response_array, true));
							$response_code = 200;
							$response = \Response::json($response_array, $response_code);
							return $response;
						} catch (Stripe_ApiError $e) {
							// Invalid parameters were supplied to Stripe's API
							$response_array = array('error' => $e->getMessage());
							\Log::info('Stripe_ApiError = ' . print_r($response_array, true));
							$response = $response_array;
							return $response;
						}
					}
				}
			} elseif($payment->save()) {
				return 4;
			}
		}
    }
	public function makePayment($total,$customer_id,$token) {
		\Stripe::setApiKey(\Config::get('app.stripe_secret_key'));
		if($total>0 && $customer_id!=''){
			try {
				$charge = \Stripe_Charge::create(array(
							"amount" => $total * 100,
							"currency" => "usd",
							"customer" => $customer_id)
				);
				\Log::info('charge stripe = ' . print_r($charge, true));
				return $charge;
			} catch (Exception $e) {
				// Invalid parameters were supplied to Stripe's API
				$response_array = array('error' => $e->getMessage());
				\Log::info('Exception = ' . print_r($response_array, true));
			}
		}elseif($total>0 && $token!=''){
			// Charge the user's card:
			try {
				$charge = \Stripe_Charge::create(array(
							"amount" => $total * 100,
							"currency" => "usd",
							"description" => "OneTime charge",
							"source" => $token,
				));
                \Payment::where('card_token', '=', $token)->update(array('is_default'=> 0, 'updated_at' => date('Y-m-d H:i:s')));
				return $charge;
			} catch (Exception $e) {
				// Invalid parameters were supplied to Stripe's API
				$response_array = array('error' => $e->getMessage());
				\Log::info('Exception = ' . print_r($response_array, true));
			}
		}
	}
	
	public function updateDefaultCard ($card_id, $customer_id,$customer_name) {
		\Stripe::setApiKey(\Config::get('app.stripe_secret_key'));
		try {
			$customer = \Stripe_Customer::retrieve($customer_id);
			$customer->description = $customer_name;
			$customer->default_source = $card_id; 
			$customer->save();
			\Log::info('card update = ' . print_r($customer, true));
			return 1;									
		}catch (\Stripe_RateLimitError $e) {
			// Invalid parameters were supplied to Stripe's API
			$response_array = array('error' => $e->getMessage());
			\Log::info('Stripe_RateLimitError = ' . print_r($response_array, true));
			$response = $response_array;
			return $response;
		} catch (\Stripe_InvalidRequestError $e) {
			// Invalid parameters were supplied to Stripe's API
			$response_array = array('error' => $e->getMessage());
			\Log::info('InvalidRequestError = ' . print_r($response_array, true));
			$response = $response_array;
			return $response;
		} catch (\Stripe_AuthenticationError $e) {								
			// Invalid parameters were supplied to Stripe's API
			$response_array = array('error' => $e->getMessage());
			\Log::info('Stripe_AuthenticationError = ' . print_r($response_array, true));
			$response = $response_array;
			return $response;
		} catch (\Stripe_CardError $e) {
			// Invalid parameters were supplied to Stripe's API
			$response_array = array('error' => $e->getMessage());
			\Log::info('Stripe_CardError = ' . print_r($response_array, true));
			$response = $response_array;
			return $response;
		} catch (\Stripe_ApiError $e) {
			// Invalid parameters were supplied to Stripe's API
			$response_array = array('error' => $e->getMessage());
			\Log::info('Stripe_ApiError = ' . print_r($response_array, true));
			$response = $response_array;
			return $response;
		}
	}
}
?>