@extends('enterpriseclient.layout')
@section('content')
	<?php date_default_timezone_set("America/Los_Angeles"); ?>
    <div class="row">
        <div class="col-md-12 col-sm-12">
            <div id="map_canvas" style="width:100%;height:400px;"></div>
        </div>
        <div class="col-md-12 col-sm-12">
            @if(Session::has('error'))
                <div class="alert alert-danger">
                    <b>{{ Session::get('error') }}</b>

                </div>
            @endif
            @if(Session::has('geocode'))
                <div class="alert alert-danger">
                    <b>{{ Session::get('geocode') }}</b>

                </div>
            @endif
            @if(Session::has('geocodeURL'))
                <div class="alert alert-danger">
                    <b>{{ Session::get('geocodeURL') }}</b>

                </div>
            @endif
            @if(Session::has('success'))
                <div class="alert alert-success">
                    <b>{{ Session::get('success') }}</b>
                </div>
            @endif

            <form name="dispatcher" id="dispatcher_form" class="theme-form" method="POST"
                  action="{{route('saverequest')}}">
                <div class="col-md-12 col-sm-12">
                    <div class="form-group row">
                        <div class="col-md-12 col-sm-12">
                            <input style="vertical-align: -5px;margin-bottom: 5px;width:15px;height:16px;" type="radio" name="all_radio" id="ondemand" value="1" onclick="demand_ride()">On Demand
                            <input style="vertical-align: -5px;margin-bottom: 5px;width:15px;height:16px;;margin-left: 25px;" type="radio" name="all_radio" id="scheduled" onclick="schedule_ride()" value="2" checked="checked"> <span style="margin-left:-3px;">Scheduled</span>
                            <span id="oxygen_mask_span">
                            <input style="vertical-align: -5px;margin-bottom: 5px;width:15px;height:16px;;margin-left: 25px;" type="checkbox" onclick="check_oxygen_mask();" name="oxygen_mask" id="oxygen_mask" value="1"> <span style="margin-left:-3px;vertical-align:1px;">Oxygen Mask</span>
                            <input style="vertical-align: -5px;margin-bottom: 5px;width:15px;height:16px;;margin-left: 25px;" type="checkbox" name="respirator" id="respirator" value="1"> <span style="margin-left:-3px;">Respirator</span>
                            <input style="vertical-align: -5px;margin-bottom: 5px;width:15px;height:16px;;margin-left: 25px;" type="checkbox" name="any_tubing" id="any_tubing" value="1"> <span style="margin-left:-3px;">Any Tubing</span>
                            <input style="vertical-align: -5px;margin-bottom: 5px;width:15px;height:16px;;margin-left: 25px;" type="checkbox" name="colostomy_bag" id="colostomy_bag" value="1"> <span style="margin-left:-3px;">Colostomy Bag</span>
                            </span>
                        </div>
                        <div class="col-md-7 col-sm-7" style="padding-bottom: 30px;">
                            <input style="width:15px;height:15px;margin-left:0; border-radius:4px;" type="checkbox" name="checkbox" onclick="check_wheelchair();" id="wheelchair" value="1"> <span style="vertical-align: 2px;padding-left: 2px;"> Wheelchair Request ($10; for patients 200 lbs or less)</span><br>
                            <input style="width:15px;height:15px;margin-left:0; border-radius:4px;" type="checkbox" name="attendant" id="attendant" value="1" onclick="add_attendant_data();"><span style="vertical-align: 2px;padding-left: 1px;"> Attendant Traveling</span><br>

                            <input style="width:15px;height:15px;margin-left:0; border-radius:4px;" onclick="check_roundtrip();" type="checkbox" name="roundtrip" id="roundtrip" value="1"> Round Trip
                            <?php   if($user_select!=3){ ?>
                            <br/>
                            <input style="vertical-align: -5px;margin-bottom: 5px;width:15px;height:16px;" type="radio" name="payment_type" id="payment_type" value="1" onclick="invoice()" checked="checked">Invoice
                            <input style="vertical-align: -5px;margin-bottom: 5px;width:15px;height:16px;;margin-left: 25px;" type="radio" name="payment_type" id="payment_type" onclick="creditcard(2)" value="2"> <span style="margin-left:-3px;">Credit Card</span>
                            <?php   } ?>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-6 col-xs-12" style="font-size:12px;padding-right: 4px;">
                            Service Type
                            <span class=" bg_arrow">
                            <select style="height:30px;width: 100%;background-color:#ffffff; border:1px solid rgba(10,120,177,0.4); " name="services" id="services">
                                <?php foreach ($services as $service){?>
                                <option value="<?php echo $service->id ?>"><?php echo $service->name ?></option>
                                <?php } ?>
						    </select>
                        </span>
                        </div>
                        <?php   if($user_select!=3){ ?>
                        <div id="provider_name" class="col-sm-6 col-xs-12" style="font-size:12px;padding-right: 4px;">Provider Name
                            <span class=" bg_arrow">
                                                        <select style="height:30px;background-color:#ffffff;width: 100%; border:1px solid rgba(10,120,177,0.4); " name="payment_mode" id="payment_mode">
                                                            <?php foreach ($hospital_provider as $prov){?>
                                                            <option value="<?php echo $prov->id ?>"><?php echo $prov->provider_name ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </span>
                        </div>
                        <?php   } ?>
                    </div>
                    <?php   if($is_agent==0 && $user_select==1){ ?>
                    <div class="form-group row">
                        <div class="col-sm-6 col-xs-12" style="font-size:12px;">
                            Agent Name

                            <input type="text" name="agent_name" id="agent_name"
                                   placeholder="" value="{{ Session::get('agent_name') }}">
                        </div>
                        <div class="col-sm-6 col-xs-12" style="font-size:12px;">
                            Agent Phone
                            <br>
                            <span style="vertical-align: -9px;font-size: 15px;">+1</span> <input type="text" name="agent_phone" id="agent_phone" style="width: 92%;position: absolute;"
                                      placeholder="" value="{{ Session::get('agent_phone') }}">
                        </div>
                    </div>
                    <?php   } ?>
                    <?php   if($user_select!=3){ ?>
                    <div class="form-group row">
                        <div class="col-sm-6 col-xs-12" style="font-size:12px;">
                            Passenger Name
                            <br>
                            <input type="text" name="passenger_contact_name" id="passenger_contact_name"
                                   placeholder="" value="{{ Session::get('passengerfirstname') }}">
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-sm-6 col-xs-12" style="margin-bottom:-30px;text-align:center;">
                            <div class="input-append datetimepicker" style="padding:0;">
                                <p style="margin-bottom:0;text-align:left;font-size:12px;">Passenger Phone Number</p>
                                <span class=" bg_arrow">
                                            <select style="background-color:#ffffff;height:31px;max-width:93px;" name="passenger_countryCode"
                                                    id="passenger_countryCode" onchange="check_healthcare_data();" >
                                            <option data-countryCode="US" value="+1" Selected>US (+1)</option>
                                            <option data-countryCode="GB" value="44">UK (+44)</option>
                                            <option data-countryCode="CA" value="+1">CA (+1)</option>
                                            <option data-countryCode="IN" value="+91">IN (+91)</option>
                                        </select></span>

                                <input style="position: relative;right: -10px;top: -31px;width:59%;max-width: 85px;" type="text" name="passenger_phone"
                                       placeholder="" id="passenger_phone"
                                       value="{{ Session::get('passenger_phone') }}" onchange="check_healthcare_data();">
                            </div>
                        </div>
                        <div class="col-sm-6 col-xs-12" style="font-size:12px;">
                            Passenger Email
                            <br>
                            <input type="text" id="passenger_email" name="passenger_email"
                                   placeholder=""
                                   value="{{ Session::get('passenger_email') }}"></div>
                    </div>
                    <?php   } ?>
                    <div class="form-group row">
                        <div class="col-sm-6 col-xs-12" style="font-size:12px;">
                            Pickup address
                            <br>
                            <input type="text" name="passenger_pickupaddress" id="geocomplete"
                                   placeholder="" value="" onchange="check_roundtrip()" style="border-bottom: 1px solid rgba(10,120,177,0.4);">
                            <span class="start">
							<input type="hidden" name="start_lat" data-geo="lat">
							<input type="hidden" name="start_lng" data-geo="lng">
						</span>
                        </div>
                        <div class="col-sm-6 col-xs-12" style="font-size:12px;">
                            Dropoff address
                            <br>
                            <input type="text" name="passenger_dropoffaddress" id="dropoff" placeholder=""
                                   value="" <?php if($user_select==3){ ?> onchange="checkownerdata();check_roundtrip();" <?php }else{ ?>onchange="check_roundtrip();" <?php  } ?>  style="border-bottom: 1px solid rgba(10,120,177,0.4);">
                            <span class="end">
							<input type="hidden" name="end_lat" data-geo="lat">
							<input type="hidden" name="end_lng" data-geo="lng">
						</span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-6 col-xs-12" id ="date" style="font-size:12px;">
                            Date
                            <div id="datetimepicker4" class="input-append datetimepicker">
                                <input data-format="yyyy-MM-dd" type="text" id="pickup_date" name="pickup_date"
                                       value="<?php echo date('Y-m-d');?>"></input>
                                <span class="add-on" style="height: 32px;" id="icon">
						                <i data-time-icon="icon-time" data-date-icon="icon-calendar" class="fa fa-calendar icon-calendar">
							        </i>
						        </span>
                            </div>
                        </div>
                        <div class="col-sm-6 col-xs-12" id="time" style="font-size:12px;">
                            Time
                            <div id="datetimepicker3" class="input-append datetimepicker" style="padding:0;">
                                <input data-format="hh:mm:ss" type="text" id="pickup_time" name="pickup_time" value="<?php echo date('h:i A');?>" readonly></input>
                                <span class="add-on" id="icon1">
								<i data-time-icon="icon-time" data-date-icon="icon-calendar" class="fa fa-clock-o">
							  </i>
							</span>

                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-6 col-xs-12" style="font-size:12px;">
                            Special Request
                            <br>
                            <input type="text" name="special_request" id="special_request" placeholder=""
                                   value="{{ Session::get('special_request') }}">
                        </div>
                        <div class="col-sm-6 col-xs-12" style="font-size:12px;">
                            Billing Code
                            <br>
                            <input type="text" name="billing_code" id="billing_code" placeholder=""
                                   value="{{ Session::get('billing_code') }}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-6 col-xs-12" id="height_div" style="font-size:12px;">
                            Height
                            <br>
                            <input type="text" name="height" id="height" placeholder=""
                                   value="">
                        </div>
                        <div class="col-sm-6 col-xs-12" id="weight_div"  style="font-size:12px;">
                            Weight
                            <br>
                            <input type="text" name="weight" id="weight" placeholder=""
                                   value="">
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-6 col-xs-12" id="condition_div"  style="font-size:12px;">
                            Condition
                            <br>
                            <input type="text" name="condition" id="condition" placeholder=""
                                   value="">
                        </div>
                        <div class="col-sm-6 col-xs-12" id="any_attachments_div"  style="font-size:12px;">
                            Any Attachments
                            <br>
                            <input type="text" name="any_attachments" id="any_attachments" placeholder=""
                                   value="">
                        </div>
                    </div>
                    <div class="form-group row">
                        <div id="add_payment" style="margin-top:12px;display:none;" class="col-md-8 col-xs-12">
                            <a data-toggle="modal" href="#mypaymentModal">Add Customer's Payment Details</a>
                        </div>
                        <div id="another_payment" style="margin-top:12px;display:none;" class="col-md-8 col-xs-12">
                            <a data-toggle="modal" href="#mypaymentModal">Add Another Payment Details</a>
                        </div>
                        <div id="show_cards" style="margin-top:12px;display:none;" class="col-md-12 col-sm-12">

                        </div>
                    </div>
                    <div id="attendantdiv" style="display:none;" class="form-group row">
                        <div class="col-sm-6 col-xs-12" style="font-size:12px;">
                            <h2 style="text-align:center;border-bottom:1px solid#000;line-height:0.3em;margin: 10px 0 20px;"><span style="background:#fff;padding:0 10px;"><strong style="font-size:15px;">Attendant Travelling</strong></span></h2>
                            <div class="form-group row">
                                <div class="col-sm-6 col-xs-12" style="font-size:12px;">
                                    Attendant Name
                                    <br>
                                    <input type="text" name="attendant_name" id="attendant_name"
                                           placeholder="" value="" style="border-bottom: 1px solid rgba(10,120,177,0.4);">

                                </div>
                                <div class="col-sm-6 col-xs-12" style="font-size:12px;">
                                    Phone
                                    <br>
                                    <span style="vertical-align: -9px;font-size: 15px;">+1</span> <input type="text" name="attendant_phone" id="attendant_phone" style="border-bottom: 1px solid rgba(10,120,177,0.4);width: 84%;position: absolute;" placeholder=""
                                                                                                         value="" >
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="roundtripdiv" style="display:none;" class="form-group row">
                        <div class="col-sm-6 col-xs-12" style="font-size:12px;">
                            <h2 style="text-align:center;border-bottom:1px solid#000;line-height:0.3em;margin: 10px 0 20px;"><span style="background:#fff;padding:0 10px;"><strong style="font-size:15px;">Round Trip</strong></span></h2>
                            <div class="form-group row">
                                <div class="col-sm-6 col-xs-12">
                                    <div style="font-size:12px;">Pickup address</div>
                                    <div style="font-size: 15px;" id="round_pickupaddress"></div>

                                </div>
                                <div class="col-sm-6 col-xs-12">
                                    <div style="font-size:12px;">Dropoff address</div>
                                    <div style="font-size: 15px;" id="round_dropoffaddress"></div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-6 col-xs-12" id ="rounddate" style="font-size:12px;">
                                    Date<br>
                                    <div id="datetimepicker5" class="input-append datetimepicker">
                                        <input data-format="yyyy-MM-dd" type="text" id="round_pickup_date" name="round_pickup_date"
                                               value="<?php echo date('Y-m-d');?>"></input>
                                        <span class="add-on" style="height: 32px;" id="icon">
								                <i data-time-icon="icon-time" data-date-icon="icon-calendar" class="fa fa-calendar icon-calendar">
									        </i>
								        </span>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-xs-12" id="roundtime" style="font-size:12px;">
                                    Time
                                    <div id="datetimepicker6" class="input-append datetimepicker">
                                        <input data-format="hh:mm:ss" type="text" id="round_pickup_time" name="round_pickup_time" value="<?php echo date('h:i A');?>" readonly></input>
                                        <span class="add-on" style="height: 32px;" id="icon1">
                                            <i data-time-icon="icon-time" data-date-icon="icon-calendar" class="fa fa-clock-o icon-calendar">
                                          </i>
							            </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="estimate_cost" style="display:none;" class="form-group row">
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-6 col-xs-12">

                        </div>
                        <div class="col-sm-6 col-xs-12">
                            <div class="submit-button">
                                <input type="hidden" name="paymentflag" id="payment_flag" value="0">
                                <button id="request_submit_button" type="button" class="btn btn-primary pull-right" onclick="validate_fields()" style="font-size:13px; display: none;">
                                    Request Ride
                                </button>
                                <button id="request_estimate_button" type="button" class="btn btn-primary pull-right" onclick="get_estimate()" style="font-size:13px;">
                                    Get Estimate
                                </button>
                            </div>
                            <input type="hidden" id="dispatcher_assigned_id" name="dispatcher_assigned_id" value="">
                            <input type="hidden" id="owner_id" name="owner_id" value="<?php echo $user_id ?>">
                            <input type="hidden" id="distance_db" name="distance_db" value="">
                            <input type="hidden" id="time_db" name="time_db" value="">
                            <input type="hidden" id="total_db" name="total_db" value="">
                            <input type="hidden" id="total_roundtrip_amount" name="total_roundtrip_amount" value="">
                            <input type="hidden" id="user_select" name="user_select" value="<?php echo $user_select ?>">
                            <input type="hidden" id="user_timezone" name="user_timezone"></input>
                            <script>
                                var usertimezone = moment.tz.guess();
                                document.getElementById("user_timezone").setAttribute('value', usertimezone);
                            </script>
                        </div>
                    </div>
                </div>
            </form>

            <div class="modal fade modal1" id="mypaymentModal" role="dialog">
                <div class="modal-dialog" style="margin-top:180px;">
                    <div class="modal-content">
                        <div class="modal-header" style="background-color: rgb(106, 0, 188);">
                            <h4 class="modal-title" style="color:#ffffff;">Add Customer's Payment Details</h4>
                        </div>
                        <div class="modal-body" style="height:auto;">
                            <form action="/booking/save-customerpayments" method="post" id="payment-form" class="form-horizontal" name="reason_form" role="form">
                                <fieldset>
                                    <div class="modal-body margin-top-0">
                                        <div id="error"></div>
                                        <label for="name">
                                            <span style="text-align:left;">Name</span>
                                            <input id="cardholder-name" name="cardholder-name" class="field" placeholder="Please enter card-holder name" />
                                        </label>
                                        <label for="phone">
                                            <span style="text-align:left;margin-bottom: 20px;">Phone</span>
                                            <input id="cardholder-phone" name="cardholder-phone" class="field" placeholder="Enter your phone no" />
                                        </label>
                                        <div id="card-element">
                                            <!-- a Stripe Element will be inserted here. -->
                                        </div>
                                        <label style="margin-top: 10px;">
                                            <span style="width:5%;text-align:left;padding-top: 2px;"><input type="checkbox" name="rememberme" checked="checked" value="1"/></span><span style="width:50%;text-align:left;">Remember for future payments</span>
                                            <span id="loading" style="display:none;">
							<img style="width: 43px;" src="<?php echo asset_url(); ?>/web/img/loading1.gif" alt="logo"/>
						</span>

                                        </label>
                                        <!-- Used to display Element errors -->
                                        <div id="card-errors" role="alert"></div>
                                    </div>
                                    <div class="text-center margin-bottom-20">
                                        <button style="margin-left: 60px;" id="payment_submit" type="submit">Submit</button>
                                        <button data-dismiss="modal" type="button">Cancel</button>
                                        <input type="hidden" name="paymentflag" id="paymentflag" value="0">
                                        <input type="hidden" id="dispatcher_assigned_id" name="dispatcher_assigned_id" value="">
                                        <input type="hidden" id="owner_id" name="owner_id" value="">
                                    </div>
                                </fieldset>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
    <link href="<?php echo asset_url(); ?>/web/css/stripe.css" rel="stylesheet">
    <!--<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>-->
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBh5zs08CTuBh0rKXnW4Li3IW8scAVnLl4&libraries=places"></script>
    <script src="https://ubilabs.github.io/geocomplete/jquery.geocomplete.js"></script>
    <script src="https://maplacejs.com/dist/maplace.min.js"></script>
    <script src="https://js.stripe.com/v3/"></script>
    <script type="text/javascript">
        var startGeocodeResult;
        var endGeocodeResult;
        var drop_off_address;
        var start_lat = $('[name=start_lat]'),
            start_lng = $('[name=start_lng]'),
            end_lat = $('[name=end_lat]'),
            end_lng = $('[name=end_lng]');
        function schedule_ride(){
            document.getElementById('date').style.display = "";
            document.getElementById('time').style.display = "";
        }

        function demand_ride(){
            document.getElementById('date').style.display = "none";
            document.getElementById('time').style.display = "none";
        }

        function check_healthcare_data() {
            var payment_type = $('input[name="payment_type"]:checked', '#dispatcher_form').val();
            creditcard(payment_type);
        }

        function invoice(){
            $('#provider_name').show();
            $('#add_payment').hide();
            $('#another_payment').hide();
            $('#show_cards').hide();
        }

        function creditcard(payment_type) {
            if(payment_type==2){
                $('#provider_name').hide();
            }

            var passenger_contact_name = $('#passenger_contact_name').val();
            var passenger_phone = $('#passenger_phone').val();
            var passenger_countryCode = $('#passenger_countryCode').val();
            if ($('#passenger_phone').val()) {
                // Submit the ajax request
                $.ajax({
                    type: "POST",
                    url: '<?php echo URL::Route('healthcareassigned') ?>',
                    data: {phone: passenger_phone, country_code: passenger_countryCode},
                    success: function (data) {
                        console.log(data);
                        if (data != 1) {
                            if ($('#passenger_contact_name').val() == '') {
                                $('#passenger_contact_name').val(data.contact_name);
                            }
                            $('#passenger_email').val(data.email);
                            $('#dispatcher_assigned_id').val(data.id);
                            console.log("payment:"+payment_type);
                            if(data.id >0 && payment_type==2){
                                //alert("hi");
                                $.ajax({
                                    type: "POST",
                                    url:'<?php echo URL::Route('checkpaymentownerdata') ?>',
                                    data:{dispatch_assign_id:data.id},
                                    success: function(data) {
                                        console.log(data);
                                        if(data==1){
                                            $('#add_payment').show();
                                            $('#another_payment').hide();
                                            $('#show_cards').hide();
                                        } else{
                                            $('#another_payment').show();
                                            $('#add_payment').hide();
                                            $('#show_cards').show();
                                            $('#show_cards').html(data);
                                            $('#paymentflag').val(1);
                                        }
                                        var fullname = $('#passenger_contact_name').val();
                                        $('#cardholder-name').val(fullname);
                                        $('#cardholder-phone').val($('#passenger_phone').val());
                                    }
                                });
                            }
                        } else {
                            $('#dispatcher_assigned_id').val('');
                        }
                    }
                });

            } else {
                //alert("hi0");
                //$('#add_payment').show();
                $('#another_payment').hide();
                $('#show_cards').hide();
                $('#passenger_contact_name').val('');
                $('#passenger_email').val('');
                $('#dispatcher_assigned_id').val('');
                var fullname = $('#passenger_contact_name').val();
                $('#cardholder-name').val(fullname);
                $('#cardholder-phone').val($('#passenger_phone').val());
            }
        }

        function checkownerdata(){

            if ($('#dropff').val() !='' && $('#owner_id').val() >0){
                // Submit the ajax request
                $.ajax({
                    type: "POST",
                    url:'<?php echo URL::Route('checkpaymentownerdata') ?>',
                    data:{dispatch_assign_id:$('#owner_id').val()},
                    success: function(data) {
                        console.log(data);
                        if(data==1){
                            $('#add_payment').show();
                            $('#another_payment').hide();
                            $('#show_cards').hide();
                        } else{
                            $('#another_payment').show();
                            $('#add_payment').hide();
                            $('#show_cards').show();
                            $('#show_cards').html(data);
                            $('#paymentflag').val(1);
                        }
                    }
                });
            }
        }

        function validate_fields() {
            clearInterval(drop_off_address);
            var attendant = + $('#attendant').is(':checked');
            var roundtrip = + $('#roundtrip').is(':checked');
            var form = document.getElementById('dispatcher_form');

            if($('#user_select').val()==3){
                if($('#geocomplete').val()=='' || $('#dropoff').val()=='' || $('#paymentflag').val()==0){
                    alert("Please fill pickup,droff address and payment details");
                    return false;
                }
                else {
                    form.submit();
                    $('#request_submit_button').addClass("disabled");
                }
            }
            else {
                if ($('#passenger_contact_name').val() == '' || $('#passenger_phone').val() == '' || $('#geocomplete').val()=='' || $('#dropoff').val()=='') {
                    alert("Please fill name, phone-no, pickup and dropoff address");
                    return false;
                }
                else {
                    form.submit();
                    $('#request_submit_button').addClass("disabled");
                }
            }

        }

        function get_estimate() {
            var address = $('input[name=passenger_pickupaddress]').val();
            if(address.length == 0) {
                alert("Please enter the Pickup Address");
                return;
            }

            var address = $('input[name=passenger_dropoffaddress]').val();
            if(address.length == 0) {
                alert("Please enter the Dropoff Address");
                return;
            }

            $("#geocomplete").trigger("geocode");
            $("#dropoff").trigger("geocode");
        }


        function showPosition(position) {
            var lat = parseFloat(position.coords.latitude);
            var lng = parseFloat(position.coords.longitude);
            start_lat.val(lat);
            start_lng.val(lng);
            var latlng = new google.maps.LatLng(lat, lng);
            var geocoder = geocoder = new google.maps.Geocoder();
            geocoder.geocode({'latLng': latlng}, function (results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    if (results[1]) {
                        //alert("Location: " + results[1].formatted_address);
                        $("#geocomplete").val(results[1].formatted_address);
                        showRouteOnMap(lat, lng, null, null);
                    }
                }
            });
        }
        $(function () {
            $("#services").change(function() {
                var e = document.getElementById("services");
                var service_id = e.options[e.selectedIndex].value;
                if(service_id == 4){
                    document.getElementById('height_div').style.display = "";
                    document.getElementById('weight_div').style.display = "";
                    document.getElementById('condition_div').style.display = "";
                    document.getElementById('oxygen_mask_span').style.display = "";
                    document.getElementById('any_attachments_div').style.display = "";
                }
                else{
                    document.getElementById('height_div').style.display = "none";
                    document.getElementById('weight_div').style.display = "none";
                    document.getElementById('condition_div').style.display = "none";
                    document.getElementById('oxygen_mask_span').style.display = "none";
                    document.getElementById('any_attachments_div').style.display = "";
                    $('#oxygen_mask').attr('checked', false);
                    $('#respirator').attr('checked', false);
                    $('#any_tubing').attr('checked', false);
                    $('#colostomy_bag').attr('checked', false);
                }
            });

			$('#pickup_date').datepicker({
				dateFormat: 'yy-mm-dd'
			});
			$('#round_pickup_date').datepicker({
				dateFormat: 'yy-mm-dd'
			});
			$('#pickup_time').timepicker({
				showPeriod: true
			});
			$('#round_pickup_time').timepicker({
				showPeriod: true
			});

            document.getElementById('height_div').style.display = "none";
            document.getElementById('weight_div').style.display = "none";
            document.getElementById('condition_div').style.display = "none";
            document.getElementById('oxygen_mask_span').style.display = "none";
            document.getElementById('date').style.display = "none";
            document.getElementById('time').style.display = "none";
            var curr_location = document.getElementById("geocomplete");
            if (navigator.geolocation) {
                //alert("ii");
                navigator.geolocation.getCurrentPosition(showPosition);
            } else {
                curr_location.val('');
            }
        });
        function initialize(latitude, longtitude) {
            var myLatlng = new google.maps.LatLng(latitude, longtitude);
            var myOptions = {
                zoom: 15,
                center: myLatlng,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            }
            var map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
            addMarker(myLatlng, 'Default Marker', map);
            map.addListener('click', function (event) {
                addMarker(event.latLng, 'Click Generated Marker', map);
            });
        }

        function addMarker(latlng, title, map) {
            var marker = new google.maps.Marker({
                position: latlng,
                map: map,
                title: title,
                draggable: true
            });
            marker.addListener('drag', function (event) {
                $('#lat').val(event.latLng.lat());
                $('#lng').val(event.latLng.lng());
            });
            marker.addListener('dragend', function (event) {
                $('#lat').val(event.latLng.lat());
                $('#lng').val(event.latLng.lng());
            });
        }
        $(document).ready(function () {
            var drop_off_address='';
            initialize(34.04665157, -118.26312337);
            var start = $("#geocomplete"),
                end = $('#dropoff'),
                via = $("#via");
            var services = $("#services");
            services.change(function () {
                if (start_lat.val() && start_lng.val() && end_lat.val() && end_lng.val()) {
                    displayExpectedRidePrice(start_lat.val(), start_lng.val(), end_lat.val(), end_lng.val())
                }
            });
            start.geocomplete({
                details: ".start",
                detailsAttribute: "data-geo",
                types: ["geocode", "establishment"]
            }).bind("geocode:result", function (event, result) {
                var address = result.formatted_address;
                start_lat.val(result.geometry.location.lat());
                start_lng.val(result.geometry.location.lng());
                //alert(start_lat.val());
                //alert(start_lng.val());
                showRouteOnMap(start_lat.val(), start_lng.val(), end_lat.val(), end_lng.val());
            });
            end.geocomplete({
                details: ".end",
                detailsAttribute: "data-geo",
                types: ["geocode", "establishment"]
            }).bind("geocode:result", function (event, result) {
                end_lat.val(result.geometry.location.lat());
                end_lng.val(result.geometry.location.lng());
                showRouteOnMap(start_lat.val(), start_lng.val(), end_lat.val(), end_lng.val());
                //displayExpectedRidePrice();
            });

            schedule_ride();
        });
        function check_wheelchair(){
            if (start_lat.val() && start_lng.val() && end_lat.val() && end_lng.val()) {
                displayExpectedRidePrice(start_lat.val(), start_lng.val(), end_lat.val(), end_lng.val())
            }
        }
        function check_oxygen_mask(){
            if (start_lat.val() && start_lng.val() && end_lat.val() && end_lng.val()) {
                displayExpectedRidePrice(start_lat.val(), start_lng.val(), end_lat.val(), end_lng.val())
            }
        }
        function check_roundtrip(){
            //alert("hi");
            var roundtrip = + $('#roundtrip').is(':checked');
            var attendant = + $('#attendant').is(':checked');

            if(roundtrip==1){
                if($('#geocomplete').val()!=''){
                    $('#round_pickupaddress').html($('#dropoff').val());
                }
                if($('#dropoff').val()!=''){
                    $('#round_dropoffaddress').html($('#geocomplete').val());
                }
                var drop_off_address = setInterval(function(){ $('#round_pickupaddress').val($('#dropoff').val()); }, 30);
                $('#roundtripdiv').show();
            } else{
                $('#roundtripdiv').hide();
            }
            if (start_lat.val() && start_lng.val() && end_lat.val() && end_lng.val()) {
                displayExpectedRidePrice(start_lat.val(), start_lng.val(), end_lat.val(), end_lng.val())
            }
        }


        $('#add_payment').click(function() {
            call_stripe();
        });
        $('#another_payment').click(function() {
            call_stripe();
        });

        function call_stripe(){
            var stripe = Stripe('<?php echo Config::get('app.stripe_publishable_key'); ?>');
            var elements = stripe.elements();

            // Custom styling can be passed to options when creating an Element.
            var style = {
                base: {
                    // Add your base input styles here. For example:
                    fontSize: '16px',
                    lineHeight: '24px'
                }
            };

            // Create an instance of the card Element
            var card = elements.create('card', {style: style});

            // Add an instance of the card Element into the `card-element` <div>
            card.mount('#card-element');

            card.addEventListener('change', function(event) {
                var displayError = document.getElementById('card-errors');
                if (event.error) {
                    $('#loading').hide();
                    $("#payment_submit").removeAttr('disabled');
                    displayError.textContent = event.error.message;
                } else {
                    $("#payment_submit").removeAttr('disabled');
                    displayError.textContent = '';
                }
            });

            // Create a token or display an error when the form is submitted.
            var form = document.getElementById('payment-form');
            form.addEventListener('submit', function(event) {
                event.preventDefault();

                if($('#cardholder-name').val()=='' || $('#cardholder-phone').val()==''){
                    alert("Please Enter both field values.");
                }else{
                    $('#loading').show();
                    stripe.createToken(card).then(function(result) {
                        if (result.error) {
                            // Inform the user if there was an error
                            $('#loading').hide();
                            $("#payment_submit").removeAttr('disabled');
                            var errorElement = document.getElementById('card-errors');
                            errorElement.textContent = result.error.message;
                        } else {
                            //console.log(result.token.card.last4);
                            // Send the data to your server
                            $('#payment_submit').click(function() {
                                //alert("submit disabled");
                                //$('#payment_submit').attr('disabled', 'disabled');
                            });
                            stripeDataHandler(result.token);
                        }
                    });
                }
            });
        }

        function stripeDataHandler(data) {
            var cardholder_name  = document.getElementById('cardholder-name').value;
            var cardholder_phone = document.getElementById('cardholder-phone').value;
            var active = $('input[type="checkbox"]:checked', '#payment-form').val();

            /*Add token in request form for updating dispatcher_assigned_id in payment table*/
            var form = document.getElementById('dispatcher_form');
            var hiddenInput = document.createElement('input');
            hiddenInput.setAttribute('type', 'hidden');
            hiddenInput.setAttribute('name', 'Token');
            hiddenInput.setAttribute('value', data.id);
            form.appendChild(hiddenInput);
            var card_no = "XXXX-XXXX-XXXX-"+data.card.last4;
            if($('#owner_id').val() !== ''){
                var dispatcher_owner_id = $('#owner_id').val();
            }
            if($('#dispatcher_assigned_id').val() !== ''){
                var dispatcher_owner_id = $('#dispatcher_assigned_id').val();
            }
            if(active != 1){
                active=0;
            }
            console.log(dispatcher_owner_id);

            // Submit the ajax request
            $.ajax({
                type: "POST",
                url:'<?php echo URL::Route('save-ownerpayments') ?>',
                data:{stripeToken:data.id,cardholdername:cardholder_name,cardholderphone:cardholder_phone,cardtype:data.card.brand,last4:data.card.last4,rememberme:active,dispatcher_owner_id:dispatcher_owner_id,card_id:data.card.id},
                success: function(data) {
                    console.log(data);
                    if(data.error!='' && typeof data.error !== "undefined"){
                        $('#loading').hide();
                        $("#error").html('<span style="text-align: center;font-size:15px;color: #f56954;">'+data.error+'</span>');
                        $("#payment_submit").removeAttr('disabled');
                    } else {
                        $('#loading').hide();
                        $('#mypaymentModal').modal('hide');
                        $('#add_payment').hide();
                        $('#another_payment').show();
                        $('#show_cards').show();
                        $('#show_cards').html(data);
                        $('#paymentflag').val(1);
                        $("#error").html('');
                        $("#cardholder-name").html('');
                        $("#cardholder-phone").html('');
                    }
                }
            });
        }

        function changedefault(id){
            if(id > 0){
                $.ajax({
                    type: "POST",
                    url:'<?php echo URL::Route('updatedefaultOwnerpaymentcard') ?>',
                    data:{defaultcard_id:id,disp_assign_id:$('#owner_id').val()},
                    success: function(data) {
                        console.log(data);
                    }
                });
            }
        }

        function changeassigneddefault(id){
            if(id > 0){
                $.ajax({
                    type: "POST",
                    url:'<?php echo URL::Route('updatedefaultOwnerpaymentcard') ?>',
                    data:{defaultcard_id:id,disp_assign_id:$('#dispatcher_assigned_id').val()},
                    success: function(data) {
                        console.log(data);
                    }
                });
            }
        }

        function add_attendant_data(){
            var roundtrip = + $('#roundtrip').is(':checked');
            var attendant = + $('#attendant').is(':checked');
            if(attendant==1){
                $('#attendantdiv').show();
                if(roundtrip==1){
                    $('#roundtrip_attendant').show();
                }
            } else{
                $('#attendantdiv').hide();
                if(roundtrip==1){
                    $('#roundtrip_attendant').hide();
                }
            }
            if (start_lat.val() && start_lng.val() && end_lat.val() && end_lng.val()) {
                //displayExpectedRidePrice(start_lat.val(), start_lng.val(), end_lat.val(), end_lng.val())
            }
        }


        function displayExpectedRidePrice(start_lat, start_lng, end_lat, end_lng) {
            var origin1 = new google.maps.LatLng(start_lat, start_lng);
            var destination1 = new google.maps.LatLng(end_lat, end_lng);
            var oxygen_mask = + $('#oxygen_mask').is(':checked');
            var roundtrip = + $('#roundtrip').is(':checked');
            var serviceType = $('#services').val();
            var wheelchair = $('#wheelchair').is(':checked');
            $.ajax({
                type: "POST",
                url: '<?php echo URL::Route('EnterpriseClientCalculateAmount') ?>',
                data: {
                    origin_latitude:        start_lat,
                    origin_longitude:       start_lng,
                    destination_latitude:   end_lat,
                    destination_longitude:  end_lng,
                    type:                   serviceType,
                    is_wheelchair:          wheelchair,
                    is_oxygen_mask:         oxygen_mask,
                    is_roundtrip:           roundtrip
                },
                success: function (html) {
                    console.log(html);
                    if (html !== '') {
                        $("#estimate_cost").html(html);
                        $('#estimate_cost').show();
                    }
                }
            });
        }
        function showHideWheelChairElements(show, amount) {
            if (show === 1) {
                $('#wheelchair_cost').text(amount);
                $('#is_wheelchair').show();
                $('#wheelchair_cost').show();
            }
            else {
                $('#is_wheelchair').hide();
                $('#wheelchair_cost').hide();
            }
        }
        function showHideOxygenMaskElements(show, amount) {
            if (show === 1) {
                $('#oxygen_mask_price').text(amount);
                $('#is_oxygen_mask').show();
                $('#oxygen_mask_price').show();
            }
            else {
                $('#is_oxygen_mask').hide();
                $('#oxygen_mask_price').hide();
            }
        }
        function showRouteOnMap(start_lat, start_lng, end_lat, end_lng) {
            $("#request_estimate_button").css("display", "block");
            $("#request_submit_button").css("display", "none");
            if (start_lat && start_lng && end_lat && end_lng) {

                displayExpectedRidePrice(start_lat, start_lng, end_lat, end_lng);
                //alert("both");
                $('#map_canvas').html('');
                new Maplace({
                    locations: [{
                        lat: start_lat,
                        lon: start_lng
                    }, {
                        lat: end_lat,
                        lon: end_lng
                    }],
                    map_div: '#map_canvas',
                    type: 'directions',
                    afterRoute: function (distance) {
                        //alert(distance);
                    }
                }).Load();

                $("#request_estimate_button").css("display", "none");
                $("#request_submit_button").css("display", "block");
            } else if (start_lat && start_lng) {
                //alert("start");
                new Maplace({
                    locations: [{
                        lat: start_lat,
                        lon: start_lng
                    }],
                    map_div: '#map_canvas'
                }).Load({
                    map_options: {
                        zoom: 16
                    }
                });
            } else if (end_lat && end_lng) {
                //alert("end");
                new Maplace({
                    locations: [{
                        lat: end_lat,
                        lon: end_lng
                    }],
                    map_div: '#map_canvas'
                }).Load({
                    map_options: {
                        zoom: 16
                    }
                });
            }
        }
    </script>
@stop