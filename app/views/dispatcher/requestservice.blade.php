@extends('dispatcher.layout')
@section('content')
<style>
.modal-body {
    max-height: calc(100vh - 200px);
    overflow-y: auto;
}
</style>
<div class="row mt">
	<div class="col-md-12 col-sm-12">
       <!--<div style="width: 100%"><iframe width="100%" height="400" src="https://www.maps.ie/create-google-map/map.php?width=100%&amp;height=600&amp;hl=en&amp;q=1%20Grafton%20Street%2C%20Dublin%2C%20Ireland+(My%20Business%20Name)&amp;ie=UTF8&amp;t=&amp;z=14&amp;iwloc=A&amp;output=embed" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"><a href="https://www.mapsdirections.info/fr/mesurer-distance-surface-google-maps.html">distance à vol d'oiseau google</a></iframe></div><br />-->
      <div id="map_canvas" style="width:100%;height:400px;"></div>
     </div>
     <div class="col-md-12 col-sm-12">
		@if(Session::has('error'))
            <div class="alert alert-danger">
                <b>{{ Session::get('error') }}</b> 
                 
             </div>
         @endif
          @if(Session::has('success'))
             <div class="alert alert-success">
                  <b>{{ Session::get('success') }}</b> 
              </div>
           @endif
			@if(Session::has('Error'))
				<div class="alert alert-danger">
					<b>{{ Session::get('Error') }}</b>
				</div>
			@endif
	 </div>
	<div class="col-md-12 col-sm-12">
		<form name="dispatcher" id="dispatcher_form" class="theme-form" method="POST" action="{{route('savedispatcher')}}">
				<div class="form-group">
					<div class="col-md-12 col-sm-12">
						<input style="vertical-align: -5px;margin-bottom: 5px;width:15px;height:16px;" type="radio" name="all_radio" id="ondemand" value="1" onclick="demand_ride()" checked="checked">On Demand
						<input style="vertical-align: -5px;margin-bottom: 5px;width:15px;height:16px;margin-left: 25px;" type="radio" name="all_radio" id="scheduled" onclick="schedule_ride()" value="2"> <span style="margin-left:-3px;">Scheduled</span>
						<span id="oxygen_mask_span">
                            <div style="width:100%;"><input style="vertical-align: -5px;margin-bottom: 5px;width:15px;height:16px;" type="checkbox" onclick="check_oxygen_mask();" name="oxygen_mask" id="oxygen_mask" value="1"> <span style="margin-left:-3px;vertical-align:1px;">Oxygen Mask</span>
								<input style="vertical-align: -5px;margin-bottom: 5px;width:15px;height:16px;margin-left: 15px;" type="checkbox" name="respirator" id="respirator" value="1"> <span style="margin-left:-3px;">Respirator</span></div>
                            <div style="width:100%;"><input style="vertical-align: -5px;margin-bottom: 5px;width:15px;height:16px;" type="checkbox" name="any_tubing" id="any_tubing" value="1"> <span style="margin-left:-3px;">Any Tubing</span>
								<input style="vertical-align: -5px; margin-bottom: 5px;width:15px;height:16px;margin-left: 15px;" type="checkbox" name="colostomy_bag" id="colostomy_bag" value="1"> <span style="margin-left:-3px;">Colostomy Bag</span></div>
                         </span>
					</div>
					<div class="col-md-7 col-sm-7" style="padding-bottom: 30px;">
						<input style="width:15px;height:15px;margin-left:0; border-radius:4px;" type="checkbox" name="checkbox" onclick="check_wheelchair();" id="wheelchair" value="1"> <span style="vertical-align: 2px;padding-left: 2px;"> Wheelchair Request ($10; for patients 200 lbs or less)</span><br>
						<input style="width:15px;height:15px;margin-left:0; border-radius:4px;" type="checkbox" name="attendant" id="attendant" value="1" onclick="add_attendant_data();"><span style="vertical-align: 2px;padding-left: 1px;"> Attendant Traveling</span><br>
						<input style="width:15px;height:15px;margin-left:0; border-radius:4px;" onclick="check_roundtrip();" type="checkbox" name="roundtrip" id="roundtrip" value="1"> Round Trip <br/>

						Payment From: <input style="vertical-align: -5px;margin-bottom: 5px;width:15px;height:16px;" type="radio" name="payment_type" id="payment_type" onclick="creditcard(2)" value="2" checked="checked"> Passenger
						<input style="vertical-align: -5px;margin-bottom: 5px;width:15px;height:16px;margin-left: 25px;" type="radio" name="payment_type" id="payment_type" value="1" onclick="invoice()" ><span style="margin-left:-3px;"> Client</span>
					</div>
				<div class="form-group row">
					<div id="dispatcher" class="col-sm-6 col-md-6 col-xs-12" style="font-size:12px;padding:0px 10px 0px 15px!important;">
					<div>Enterprise Client</div>
						<span class=" bg_arrow" >
							<select style="height:30px;background-color:#ffffff;width: 50%; border:1px solid rgba(10,120,177,0.4); " name="company_name" id="company_name" onchange="company_name_select(this);">
								<option selected="true" value="0" style="width:90%;">Select Client</option>
								<?php foreach ($healthcare_company as $health){?>
								<option  value="<?php echo $health->id ?>"><?php echo $health->company ?></option>
								<?php } ?>
							</select>
						</span>
					</div>
					<div id="hospitalprov" class="col-sm-6 col-md-6 col-xs-12" style="font-size:12px;padding:0px 10px 0px 15px!important;">
						<div>Hospital Providers</div>
							<select class="form-control" style="height:30px;background-color:#ffffff;border:1px solid rgba(10,120,177,0.4); " name="provider_name" id="provider_name" onchange="">
								<option selected="true" value="0">Select Provider Name</option>
							</select>
					 </div>
				</div>
				<div class="form-group row" id="ride_services" style="display:none;">
					<div class="col-sm-12 col-md-12 col-xs-12" style="font-size:12px;padding:0px 10px 0px 15px!important;">
						<div>Service Type</div>
                            <select class="form-control" style="height: 30px;background-color: #ffffff; border:1px solid rgba(10,120,177,0.4);" name="services" id="services">
                                <?php foreach ($services as $service){?>
								<option value="<?php echo $service->id ?>"><?php echo $service->name ?></option>
                                <?php } ?>
						    </select>
					</div>
				</div>
				
				<div class="form-group row">
						<div class="col-sm-6 col-md-6 col-xs-12" style="font-size:12px;" id="agent_name">
							<div>Agent Name</div>
							<input type="text" name="agent_name1" id="agent_name1" placeholder="" value="{{ Session::get('agent_name') }}">
						</div>
						<div class="col-sm-6 col-md-6 col-xs-12" style="font-size:12px;" id="agent_phone">
							<div>Agent Phone</div>
							<span class="agent_phone" style="vertical-align: -9px;font-size: 15px;">+1</span><input type="text" name="agent_phone1" id="agent_phone1" class="agent_phone_input" style="position: absolute;" placeholder="" value="{{ Session::get('agent_phone') }}">
						</div>
				   </div>
					<div class="form-group row">
						<div class="col-sm-6 col-md-6 col-xs-12" style="font-size:12px;">
							<div>Passenger Name</div>
							<input type="text" name="passenger_contact_name" id="passenger_contact_name" placeholder="" value="{{ Session::get('passengerfirstname') }}" onchange="insertfullname();">
						</div>
				 </div>
				<div class="form-group row">
					<div class="col-sm-6 col-md-6 col-xs-12" style="margin-bottom:-30px;text-align:center;">
						<div class="input-append datetimepicker" style="padding:0;">
							<p style="margin-bottom:0;text-align:left;font-size:12px;">Passenger Phone Number</p>
							<span class=" bg_arrow">
                              <select style="background-color:#ffffff;height:31px;max-width:93px;" name="passenger_countryCode" id="passenger_countryCode" onchange="checkdata();" >
                                <option data-countryCode="US" value="+1" Selected>US (+1)</option>
                                <option data-countryCode="GB" value="44">UK (+44)</option>
                                <option data-countryCode="CA" value="+1">CA (+1)</option>
                                <option data-countryCode="IN" value="+91">IN (+91)</option>
                                </select>
							</span>

							<input style="position: relative;right: -10px;top: -31px;width:59%;max-width: 85px;" type="text" name="passenger_phone"
								   placeholder="" id="passenger_phone"
								   value="{{ Session::get('passenger_phone') }}" onchange="checkdata();">
						</div>
					</div>
						<div class="col-sm-6 col-md-6 col-xs-12" style="font-size:12px;">
						<div>Passenger Email</div>
							<input type="text" id="passenger_email" name="passenger_email" placeholder="" value="{{ Session::get('passenger_email') }}">
						</div>
					</div>
				<div class="form-group row">
					<div class="col-sm-6 col-md-6 col-xs-12" style="font-size:12px;">
						<div>Pickup address</div>
						<input type="text" name="passenger_pickupaddress" id="geocomplete" placeholder="" value="" onchange="check_roundtrip();">
						<button type="button" class="btn btn-success pull-right" onClick="geocodeLocation('pickup');" style="font-size: 10pt;">Find Location</button>
						<span class="start">
							<input type="hidden" id="start_lat" name="start_lat" data-geo="lat">
							<input type="hidden" id="start_lng" name="start_lng" data-geo="lng">
						</span>
					</div>
					<div class="col-sm-6 col-md-6 col-xs-12">
						<div>Dropoff address</div>
						<input type="text" name="passenger_dropoffaddress" id="dropoff" placeholder="" value="{{ Session::get('dropoffaddress') }}" onchange="check_roundtrip();">
						<button type="button" class="btn btn-success pull-right" onClick="geocodeLocation('dropoff');" style="font-size: 10pt;">Find Location</button>
						<span class="end">
							<input type="hidden" id="end_lat" name="end_lat" data-geo="lat">
							<input type="hidden" id="end_lng" name="end_lng" data-geo="lng">
						</span>
					</div>				
				</div>
				<div class="form-group row">
					<div class="col-sm-6 col-xs-12" id ="date" style="font-size:12px;">
						<div>Date</div>
						<div id="datetimepicker4" class="input-append datetimepicker" style="padding:0;">
							<input data-format="yyyy-MM-dd" type="text" id="pickup_date" name="pickup_date" value="<?php echo date('M jS Y');?>" readonly></input>
							<span class="add-on" id="icon">
								 <i data-time-icon="icon-time" data-date-icon="icon-calendar" class="fa fa-calendar"></i>
							</span>
						</div>
					</div>
					<div class="col-sm-6 col-md-6 col-xs-12" id="time" style="font-size:12px;">
						<div>Time</div>
						<div id="datetimepicker3" class="input-append datetimepicker" style="padding:0;">
							<input data-format="hh:mm:ss" type="text" id="pickup_time" name="pickup_time" readonly></input>
							<script>
                                var months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
                                //var days = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
                                var currentTime = new Date();
                                var month = months[currentTime.getMonth()];
                                //alert(month);
                                var day = currentTime.getDate();
                                //alert(day)
                                var year = currentTime.getFullYear();
                                //alert(year);
                                var hours = currentTime.getHours();
                                var minutes = currentTime.getMinutes();
                                var seconds = currentTime.getSeconds();
                                var suffix = "AM";
                                if (hours >= 12) {
                                    suffix = "PM";
                                    hours = hours - 12;
                                }
                                if (hours == 0) {
                                    hours = 12;
                                }
                                if (minutes < 10) {
                                    minutes = "0" + minutes;
                                }
                                if (seconds < 10) {
                                    seconds = "0" + seconds;
                                }
                                var newtime = hours + ":" + minutes + ":" + seconds + " " + suffix;
                                document.getElementById("pickup_time").setAttribute('value', newtime);
                                var newdate = month + " " + day + " " + year;
                                document.getElementById("pickup_date").setAttribute('value', newdate);
							</script>
							<span class="add-on" id="icon1">
								<i data-time-icon="icon-time" data-date-icon="icon-calendar" class="fa fa-clock-o">
							  </i>
							</span>

						</div>
					</div>
				</div>
					<div class="form-group row">
						<div class="col-sm-6 col-md-6 col-xs-12" style="font-size:12px;">
							<div>Special Request</div>
							<input type="text" name="special_request" id="special_request" placeholder=""
								   value="{{ Session::get('special_request') }}">
						</div>
						<div class="col-sm-6 col-md-6 col-xs-12" style="font-size:12px;">
							<div>Billing Code</div>
							<input type="text" name="billing_code" id="billing_code" placeholder=""
								   value="{{ Session::get('billing_code') }}">
						</div>
					</div>
					<div class="form-group row">
						<div class="col-sm-6 col-md-6 col-xs-12" id="height_div" style="font-size:12px;">
							<div>Height</div>
							<input type="text" name="height" id="height" placeholder="" value="">
						</div>
						<div class="col-sm-6 col-md-6 col-xs-12" id="weight_div"  style="font-size:12px;">
							<div>Weight</div>
							<input type="text" name="weight" id="weight" placeholder=""
								   value="">
						</div>
					</div>
					<div class="form-group row">
						<div class="col-sm-6 col-md-6 col-xs-12" id="condition_div"  style="font-size:12px;">
							<div>Condition</div>
							<input type="text" name="condition" id="condition" placeholder="" value="">
						</div>
						<div class="col-sm-6 col-md-6 col-xs-12" id="any_attachments_div"  style="font-size:12px;">
							<div>Any Attachments</div>
							<input type="text" name="any_attachments" id="any_attachments" placeholder="" value="">
						</div>
					</div>
					<div id="add_payment" style="margin-top:12px;display:none;">
						<a data-toggle="modal" href="#mypaymentModal">Add Customer's Payment Details</a>
					</div>
					<div id="another_payment" style="margin-top:12px;display:none;" >
						<a data-toggle="modal" href="#mypaymentModal">Add Another Payment Details</a>
					</div>
					<div id="show_cards" style="margin-top:12px;display:none;">

					</div>

			</div>
				<div id="attendantdiv" style="display:none;" class="form-group row">
					<div class="col-sm-6 col-xs-12" style="font-size:12px;">
						<h2 style="width:85%;text-align:center;border-bottom:1px solid#000;line-height:0.3em;margin: 10px 0 20px;"><span style="background:#fff;padding:0 10px;"><strong style="font-size:15px;">Attendant Travelling</strong></span></h2>
						<div class="form-group row">
							<div class="col-sm-6 col-xs-12" style="font-size:12px;">
								<div>Attendant Name</div>
								<input type="text" name="attendant_name" id="attendant_name"
									   placeholder="" value="">

							</div>
							<div class="col-sm-6 col-xs-12" style="font-size:12px;">
								<div>Phone</div>
								<span style="vertical-align: -9px;font-size: 15px;width:16%;">+1</span><input type="text" name="attendant_phone" id="attendant_phone" style="width:84%;position: absolute;" placeholder=""
										  value="" >
							</div>
						</div>
						<div class="form-group row">
							<div class="col-sm-12 col-xs-12"  style="font-size:12px;">
								<div>Pickup address</div>
								<input type="text" name="attendant_pickupaddress" id="attendant_pickupaddress" placeholder=""
									   value="" >
								<span class="attendantstart">
                                        <input type="hidden" name="attendantstart_lat" data-geo="lat">
                                        <input type="hidden" name="attendantstart_lng" data-geo="lng">
                                    </span>
							</div>
						</div>
					</div>
				</div>

				<div id="roundtripdiv" style="display:none;" class="form-group row">
					<div class="col-sm-6 col-xs-12" style="font-size:12px;">
						<h2 style="width:85%;text-align:center;border-bottom:1px solid#000;line-height:0.3em;margin: 10px 0 20px;"><span style="background:#fff;padding:0 10px;"><strong style="font-size:15px;">Round Trip</strong></span></h2>
						<div class="form-group row">
							<div class="col-sm-6 col-xs-12" style="font-size:12px;">
								<div>Pickup address</div>
								<input type="text" name="round_pickupaddress" id="round_pickupaddress"
									   placeholder="" value="" readonly>

							</div>
							<div class="col-sm-6 col-xs-12" style="font-size:12px;">
								<div>Dropoff address</div>
								<input type="text" name="round_dropoffaddress" id="round_dropoffaddress" placeholder=""
									   value="" readonly>
							</div>
						</div>
						<div class="form-group row">
							<div class="col-sm-6 col-md-6 col-xs-12" id ="rounddate" style="font-size:12px;">
								<div>Date</div>
								<div id="datetimepicker5" class="input-append datetimepicker" style="padding:0;">
									<input data-format="yyyy-MM-dd" type="text" id="round_pickup_date" name="round_pickup_date"
										   value="<?php echo date('M jS Y');?>"></input>
									<span class="add-on" style="height: 32px;" id="icon">
								                <i data-time-icon="icon-time" data-date-icon="icon-calendar" class="fa fa-calendar icon-calendar">
									        </i>
								        </span>
								</div>
							</div>
							<div class="col-sm-6 col-md-6 col-xs-12" id="roundtime" style="font-size:12px;">
								<div>Time</div>
								<div id="datetimepicker6" class="input-append datetimepicker" style="padding:0;">
									<input data-format="hh:mm:ss" type="text" id="round_pickup_time" name="round_pickup_time"></input>
									<script>
                                        var months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
                                        //var days = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
                                        var currentTime = new Date();
                                        var month = months[currentTime.getMonth()];
                                        //alert(month);
                                        var day = currentTime.getDate();
                                        //alert(day)
                                        var year = currentTime.getFullYear();
                                        //alert(year);
                                        var hours = currentTime.getHours();
                                        var minutes = currentTime.getMinutes();
                                        var seconds = currentTime.getSeconds();
                                        var suffix = "AM";
                                        if (hours >= 12) {
                                            suffix = "PM";
                                            hours = hours - 12;
                                        }
                                        if (hours == 0) {
                                            hours = 12;
                                        }
                                        if (minutes < 10) {
                                            minutes = "0" + minutes;
                                        }
                                        if (seconds < 10) {
                                            seconds = "0" + seconds;
                                        }
                                        var newtime = hours + ":" + minutes + ":" + seconds + " " + suffix;
                                        document.getElementById("round_pickup_time").setAttribute('value', newtime);
                                        var newdate = month + " " + day + " " + year;
                                        document.getElementById("round_pickup_date").setAttribute('value', newdate);
									</script>
									<span class="add-on" style="height: 32px;" id="icon1">
                                            <i data-time-icon="icon-time" data-date-icon="icon-calendar" class="fa fa-clock-o icon-calendar">
                                          </i>
							            </span>
								</div>
							</div>
						</div>
					</div>
				</div>
			<div class="col-md-6 col-sm-6"> 
				{{--<div class="col-md-5 col-sm-5"><input type="text" id="get_driver" autocomplete="off" name="search" placeholder="search"></div>
				<div class="col-md-7 col-sm-7">
					<ul>
						<li><input type="radio" name="all_radio" id="all_drivers" value="alldrivers" onclick="findalldrivers();"> All Drivers</li>
						<li><input type="radio" name="all_radio" id="logged_drivers" onclick="findloggedindrivers();" value="loggedon"> Only logged on </li>
					</ul>
				</div>--}}
				<div class="col-md-12 col-sm-12">
					{{--<table class="table table-bordered" >
					  <tr>
						<th>Driver Name</th>
						<th>Company</th>
						<th>Service Type</th>
						<th>Assign Ride</th>
					  </tr>
					  <tbody id="driverdetails">
					  <tr>
						<td colspan="4">No Driver available</td>
					  </tr>
					  
					  </tbody>
					</table> --}}
					<div id="estimate_cost" style="display:none;" class="form-group row">
					</div>
				</div>
			</div>
				<div class="col-md-7 col-sm-12"></div>
					<div class="col-md-5 col-sm-12">
					<div class="submit-button">
						<input type="hidden" name="paymentflag" id="payment_flag" value="0">
						<button id="request_submit_button" type="button" class="btn btn-primary pull-right" style="width:auto;" onclick="validate_fields();">Submit</button>
					</div>
					<input type="hidden" id="dispatcher_assigned_id" name="dispatcher_assigned_id" value="">
					<input type="hidden" id="user_timezone" name="user_timezone">
					<input type="hidden" id="distance_db" name="distance_db" value="">
					<input type="hidden" id="time_db" name="time_db" value="">
					<input type="hidden" id="total_db" name="total_db" value="">
					<input type="hidden" id="total_roundtrip_amount" name="total_roundtrip_amount" value="">
					<input type="hidden" name="ride" id="ride" value="2">
					<script>
					var usertimezone = moment.tz.guess();
					document.getElementById("user_timezone").setAttribute('value',usertimezone);
					</script>
				</div>

		</form>
	 </div>
	<!-- Modal for Stop Selection -->
	<?php if($dispatcher->is_admin == 1) { ?>
		<div class="modal fade madal1" id="stopModal" role="dialog">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title">Select Bus Stop</h4>
					</div>
					<div class="modal-body">
					</div>
				</div>
			</div>
		</div>
	<?php } ?>

	<!-- Modal for Payment-->
	<div class="modal fade modal1" id="mypaymentModal" role="dialog">
		<div class="modal-dialog" style="margin-top:180px;">
			<div class="modal-content">
				<div class="modal-header" style="background-color: rgb(106, 0, 188);">
					<h4 class="modal-title" style="color:#ffffff;">Add Customer's Payment Details</h4>
				</div>
				<div class="modal-body" style="height:auto;">
					<form action="/healthcare/save-customerpayments" method="post" id="payment-form" class="form-horizontal" name="reason_form" role="form" style="width:auto;">
						<fieldset>
							<div class="modal-body">
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
									<span style="width:10%;text-align:none;padding-top: 2px;"><input type="checkbox" name="rememberme" checked="checked" value="1"/></span><span style="width:100%;text-align:left;float:none;">Remember for future payments</span></label>
									<span id="loading" style="display:none;">
							<img style="width: 43px;" src="<?php echo asset_url(); ?>/web/img/loading1.gif" alt="logo"/>
						</span>

								</label>
								<!-- Used to display Element errors -->
								<div id="card-errors" role="alert"></div>
							</div>
							<div class="text-center margin-bottom-20">
								<button id="payment_submit" class="btn btn-primary" style="width:auto;" type="submit">Submit</button>
								<button data-dismiss="modal" class="btn btn-primary" style="width:auto;" type="button">Cancel</button>
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
        <!-- modal -->

	 </div>
     

<link href="<?php echo asset_url(); ?>/web/css/stripe.css" rel="stylesheet">
<script src="<?php echo asset_url(); ?>/web/js/jquery-ui-1.9.2.custom.min.js"></script>
<!--<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>-->
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDaKg4PG8MB0N-XfDnVU8mOXDKSmrSo-iI&libraries=places"></script>
<script src="https://ubilabs.github.io/geocomplete/jquery.geocomplete.js"></script>
<script src="<?php echo asset_url(); ?>/web/js/bootstrap-datetimepicker.min.js"></script>

<script src="https://maplacejs.com/dist/maplace.min.js"></script>  
<script src="https://js.stripe.com/v3/"></script>
<script>
	var drop_off_address;

	function lookupStop(elText, elLat, elLng) {

	}
    function company_name_select(selected){
        var id = selected.value;
        console.log(id);
        if(id == 0){
            document.getElementById('agent_name').style.display = "none";
            document.getElementById('agent_phone').style.display = "none";
            $('#provider_name').find('option').remove().end();
            var select = document.querySelector("#provider_name");
            var select_options = document.createElement("option");
            select_options.innerText = 'Select Provider Name';
            select.appendChild(select_options);
		}else{
            document.getElementById('agent_name').style.display = "";
            document.getElementById('agent_phone').style.display = "";
            $('#provider_name').find('option').remove().end();
		}
		var providers = <?php echo json_encode($hospital_provider)?>;
        var select = document.querySelector("#provider_name");
        var select_options = document.createElement("option");
        providers.forEach(function(entry) {
            //console.log(entry);
            if( id == entry.healthcare_id ){
                var select = document.querySelector("#provider_name");
                var select_options = document.createElement("option");
                select_options.innerText = entry.provider_name;
                select_options.value = entry.id;
                select.appendChild(select_options);
            }
        });
	}
    var start_lat = $('[name=start_lat]'),
        start_lng = $('[name=start_lng]'),
        end_lat = $('[name=end_lat]'),
        end_lng = $('[name=end_lng]');
    function schedule_ride(){
        $('#datetimepicker4').removeAttr("readonly");
        $('#datetimepicker3').removeAttr("readonly");
        $( "#datetimepicker4" ).datetimepicker('enable');
        $( "#datetimepicker3" ).datetimepicker('enable');
        var months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        //var days = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
        var currentTime = new Date();
        var month = months[currentTime.getMonth()];
        //alert(month);
        var day = currentTime.getDate();
        //alert(day)
        var year = currentTime.getFullYear();
        //alert(year);
        var hours = currentTime.getHours();
        var minutes = currentTime.getMinutes();
        var seconds = currentTime.getSeconds();
        var suffix = "AM";
        if (hours >= 12) {
            suffix = "PM";
            hours = hours - 12;
        }
        if (hours == 0) {
            hours = 12;
        }
        if (minutes < 10) {
            minutes = "0" + minutes;
        }
        if (seconds < 10) {
            seconds = "0" + seconds;
        }
        var newtime = hours + ":" + minutes + ":" + seconds + " " + suffix;
        document.getElementById("pickup_time").setAttribute('value', newtime);
        var newdate = month + " " + day + " " + year;
        document.getElementById("pickup_date").setAttribute('value', newdate);
        // document.getElementById('pickup_time').style.display = "";
        document.getElementById('date').style.display = "";
        document.getElementById('time').style.display = "";
        document.getElementById('pickup_time').style.border = "";
        document.getElementById('pickup_date').style.border = "";
    }
    function demand_ride(){
        document.getElementById('date').style.display = "none";
        document.getElementById('time').style.display = "none";
        // window.onload = demand_ride;
    }
	$(function(){
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

			$('#datetimepicker4').datetimepicker({
				pickTime: false,
				startDate: new Date(), // controll start date like startDate: '-2m' m: means Month
				endDate: '+30d'
			});
			$('#datetimepicker3').datetimepicker({
				pickDate: false
			});
			$('#datetimepicker5').datetimepicker({
				pickTime: false,
				startDate: new Date(), // controll start date like startDate: '-2m' m: means Month
				endDate: '+30d'
			});
			$('#datetimepicker6').datetimepicker({
				pickDate: false
			});
			document.getElementById('agent_name').style.display = "none";
			document.getElementById('agent_phone').style.display = "none";
			document.getElementById('height_div').style.display = "none";
			document.getElementById('weight_div').style.display = "none";
			document.getElementById('condition_div').style.display = "none";
			document.getElementById('oxygen_mask_span').style.display = "none";
			document.getElementById('pickup_time').style.border = "none";
			document.getElementById('pickup_date').style.border = "none";
			document.getElementById('date').style.display = "none";
			document.getElementById('time').style.display = "none";
			$( "#datetimepicker4" ).datetimepicker('disable');
			$( "#datetimepicker3" ).datetimepicker('disable');

	});
	function initialize(latitude,longitude) {
		var myLatlng = new google.maps.LatLng(latitude,longitude);
		var myOptions = {
		  zoom: 15, 
		  center: myLatlng,
		  mapTypeId: google.maps.MapTypeId.ROADMAP
		}
	   var map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
		addMarker(myLatlng, 'Default Marker', map);
		map.addListener('click',function(event) {
			addMarker(event.latLng, 'Click Generated Marker', map);
		});
	}
	function addMarker(latlng,title,map) {
		var marker = new google.maps.Marker({
				position: latlng,
				map: map,
				title: title,
				draggable:true
		});
		marker.addListener('drag',function(event) {
			 $('#lat').val(event.latLng.lat())  ;
			$('#lng').val(event.latLng.lng())  ;
		});
		marker.addListener('dragend',function(event) {
			$('#lat').val(event.latLng.lat())  ;
			$('#lng').val(event.latLng.lng())  ;
		});
	}


    function add_attendant_data(){
        var roundtrip = + $('#roundtrip').is(':checked');
        var attendant = + $('#attendant').is(':checked');
        if(attendant==1){
            var attendantstart_lat = $('[name=attendantstart_lat]'),
                attendantstart_lng = $('[name=attendantstart_lng]');

            var pickattend = $("#attendant_pickupaddress");
            pickattend.geocomplete({
                details: ".attendantstart",
                detailsAttribute: "data-geo",
                types: ["geocode", "establishment"]
            }).bind("geocode:result", function (event, result) {
                var address1 = result.formatted_address;
                attendantstart_lat.val(result.geometry.location.lat());
                attendantstart_lng.val(result.geometry.location.lng());
            });

            var roundattendantstart_lat = $('[name=round_attendantstart_lat]'),
                roundattendantstart_lng = $('[name=round_attendantstart_lng]');

            var roundpickattend = $("#round_attendant_pickupaddress");
            roundpickattend.geocomplete({
                details: ".round_attendantstart",
                detailsAttribute: "data-geo",
                types: ["geocode", "establishment"]
            }).bind("geocode:result", function (event, result) {
                var address2 = result.formatted_address;
                roundattendantstart_lat.val(result.geometry.location.lat());
                roundattendantstart_lng.val(result.geometry.location.lng());
            });

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

	function geocodeLocation(whence) {
	    var address = '';
	    var latvar = '';
	    var lngvar = '';
	    if (whence == 'pickup') {
	    	address = document.getElementById("geocomplete").value;
	    	latvar = '#start_lat';
	    	lngvar = '#start_lng';
	    }
	    if(whence == 'dropoff') {
	    	address = document.getElementById("dropoff").value;
	    	latvar = '#end_lat';
	    	lngvar = '#end_lng';
	    }

	    var array = parseLatLng(address);
	    if(typeof(array) == "object") {
	    	$(latvar).val(array[0]);
	    	$(lngvar).val(array[1]);
	        showRouteOnMap(start_lat.val(), start_lng.val(), end_lat.val(), end_lng.val());
	        return;
	    }

	    var stop = parseStopNumber(address);
	    if(typeof(stop) == "object") {
	    	$(latvar).val(stop.latitude);
	    	$(lngvar).val(stop.longitude);
	    	var stopString = "Stop " + stop.stopnumber + " / " + stop.title;
		    if (whence == 'pickup') {
		    	$("#geocomplete").val(stopString);
		    }
		    if (whence == 'dropoff') {
		    	$("#dropoff").val(stopString);
		    }

	        showRouteOnMap($("#start_lat").val(), $("#start_lng").val(),
				           $("#end_lat").val(), $("#end_lng").val());
	        return;
	    }

	    var geocoder = new google.maps.Geocoder();
        geocoder.geocode({'address': address}, function (results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                $(latvar).val(results[0].geometry.location.lat());
                $(lngvar).val(results[0].geometry.location.lng());
		        showRouteOnMap(start_lat.val(), start_lng.val(), end_lat.val(), end_lng.val());
            }

            if(status == google.maps.GeocoderStatus.ZERO_RESULTS) {
                $(latvar).val(0);
                $(lngvar).val(0);
				initialize(34.04665157,-118.26312337);
				alert("Unable to find an address or location");
            }
        });
	}

	function parseStopNumber(s) {
		var stopnumber = parseInt(s);
		if(isNaN(stopnumber)) {
			return -1;
		}

		return stops[stopnumber];
	}

	function parseLatLng(s) {
		var regex = /\s+/;
		var parts = s.split(regex);
		if(parts.length == 1) {
			parts = s.split(',');
			if(parts.length == 1) {
				return -1;
			}
		}

		var lat = parseFloat(parts[0]);
		if(isNaN(lat)) {
			return -1;
		}

		var lng = parseFloat(parts[1]);
		if(isNaN(lng)) {
			return -1;
		}

		var array = new Array();
		array[0] = lat;
		array[1] = lng;
		return array;
	}

$(document).ready(function() {
    var drop_off_address='';
    $('#ride_services').show();
    $('#estimate_cost').show();

	initialize(34.04665157,-118.26312337);
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
	}).bind("geocode:result", function(event, result) {
		var address=result.formatted_address;
		/*$.ajax({
			type:'POST',
			url:'<?php echo URL::Route('drivers') ?>',
			//url:'<?php echo asset_url(); ?>/dispatcher/getdrivers',
			data:'_token = <?php echo csrf_token() ?>&address='+address,
			success:function(data){
				if(data!=''){
					console.log(data);
					$("#driverdetails").html(data);					 
				}else {
					$('#driverdetails').html('<tr><td colspan="4">No Driver available</td></tr>');
				}
			 
			}
		});*/
		start_lat.val(result.geometry.location.lat());
		start_lng.val(result.geometry.location.lng());
        showRouteOnMap(start_lat.val(), start_lng.val(), end_lat.val(), end_lng.val());
	});
	end.geocomplete({
   
    details: ".end",
    detailsAttribute: "data-geo",
    types: ["geocode", "establishment"]
	}).bind("geocode:result", function(event, result) {
		end_lat.val(result.geometry.location.lat());
		end_lng.val(result.geometry.location.lng());
        showRouteOnMap(start_lat.val(), start_lng.val(), end_lat.val(), end_lng.val());
	});


	/*$("#get_driver").autocomplete({
		source: function( request, response ) {
			var driver_result=document.getElementById('get_driver').value
			$.ajax({
				type:'POST',
				url:'<?php echo URL::Route('drivername') ?>',
				//url:'<?php echo asset_url(); ?>/dispatcher/getdrivername',
				data:'name='+driver_result,
				success:function(data){
					if(data!=''){
						console.log(data);
						$("#driverdetails").html(data);
					} 
				}
			});
		},
		minLength: 0,
	});*/
	
	$('#add_payment').click(function() {		
		call_stripe();		
	});
	$('#another_payment').click(function() {		
		call_stripe();		
	});
	
});
    function displayExpectedRidePrice(start_lat, start_lng, end_lat, end_lng) {
        var origin1 = new google.maps.LatLng(start_lat, start_lng);
        var destination1 = new google.maps.LatLng(end_lat, end_lng);

		var wheelchair = + $('#wheelchair').is(':checked');
    	var oxygen_mask = + $('#oxygen_mask').is(':checked');
    	var roundtrip = + $('#roundtrip').is(':checked');
    	var enterpriseclient_id = $("#company_name").val();
        var serviceType = $('#services').val();
        var wheelchair = $('#wheelchair').is(':checked');
        $.ajax({
            type: "POST",
            url: '<?php echo URL::Route('dispatchercalculatecost') ?>',
            data: {
                origin_latitude:        start_lat,
                origin_longitude:       start_lng,
                destination_latitude:   end_lat,
                destination_longitude:  end_lng,
                type:                   serviceType,
                is_wheelchair:          wheelchair,
                is_oxygen_mask:         oxygen_mask,
                is_roundtrip:           roundtrip,
                enterpriseclient_id: 	enterpriseclient_id 
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

        if (start_lat && start_lng && end_lat && end_lng) {

            displayExpectedRidePrice(start_lat, start_lng, end_lat, end_lng);

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
			
			if(document.getElementById('cardholder-name').value=='' || document.getElementById('cardholder-phone').value==''){
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
		if(document.getElementById('dispatcher_assigned_id').value!=''){
			var disp_assign_id = document.getElementById('dispatcher_assigned_id').value;
		} else{
			var disp_assign_id = '';
		}
		if(active != 1){
			active=0;
		}
		
		// Submit the ajax request
		$.ajax({
			type: "POST",
			url:'<?php echo URL::Route('save-customerpayments') ?>',
			data:{stripeToken:data.id,cardholdername:cardholder_name,cardholderphone:cardholder_phone,cardtype:data.card.brand,last4:data.card.last4,rememberme:active,dispatcher_assigned_id:disp_assign_id,card_id:data.card.id},
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
	


	function insertfullname(){
		document.getElementById('cardholder-name').value = $('#passenger_contact_name').val();
	}

	function checkdata(){

		var passenger_contact_name = $('#passenger_contact_name').val();
		var passenger_phone     = $('#passenger_phone').val();
		var passenger_countryCode     = $('#passenger_countryCode').val();

		if ($('#passenger_phone').val()){
			// Submit the ajax request
			$.ajax({
				type: "POST",
				url:'<?php echo URL::Route('dispatcherassigned') ?>',
				data:{phone:passenger_phone,country_code:passenger_countryCode},
				success: function(data) {
					console.log(data);
					if(data!=1){
						$('#passenger_contactname').val(data.contact_name);
						$('#passenger_email').val(data.email);
						$('#dispatcher_assigned_id').val(data.id);
						
						if(data.id >0){
							$.ajax({
								type: "POST",
								url:'<?php echo URL::Route('checkpaymentdata') ?>',
								data:{dispatch_assign_id:data.id},
								success: function(data) {
									console.log(data);
                                    if ($('#payment_type').val() == 2) {
                                        if (data == 1) {
                                            $('#add_payment').show();
                                            $('#another_payment').hide();
                                            $('#show_cards').hide();
                                        } else {
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
								}
							});
						} 
					} else {
						$('#add_payment').show();
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
			});
		}
	}

	function changedefault(id){
		if(id > 0){
			$.ajax({
				type: "POST",
				url:'<?php echo URL::Route('updatedefaultcard') ?>',
				data:{defaultcard_id:id,disp_assign_id:document.getElementById('dispatcher_assigned_id').value},
				success: function(data) {
					console.log(data);
				}
			});
		}
	}

	function findalldrivers(){
		var active = $('input[name="all_radio"]:checked', '#dispatcher_form').val(); 
		if(active=="alldrivers"){
			$.ajax({
				type: "POST",
				url:'<?php echo URL::Route('alldrivers') ?>',
				success: function(data) {
					console.log(data);
					$("#driverdetails").html(data);
				}
			});
		} else{
			$('#driverdetails').html('<tr><td colspan="4">No Driver available</td></tr>');
		}
	}
	function findloggedindrivers(){
		var active = $('input[name="all_radio"]:checked', '#dispatcher_form').val(); 
		if(active=="loggedon"){
			$.ajax({
				type: "POST",
				url:'<?php echo URL::Route('loggedindrivers') ?>',
				//url: '<?php echo asset_url(); ?>/dispatcher/getloggedindrivers',
				success: function(data) {
					console.log(data);
					$("#driverdetails").html(data);
				}
			});
		} else{
			$('#driverdetails').html('<tr><td colspan="4">No Driver available</td></tr>');
		}
	}

	function validate_fields(){

        clearInterval(drop_off_address);

		var driver_assign = $('input[name="assignride"]:checked', '#dispatcher_form').val();
        var ride_type = $('input[name="ride"]:checked', '#dispatcher_form').val();
        var payment_type = $('input[name="payment_type"]:checked', '#dispatcher_form').val();
        //alert(ride_type);
        if(ride_type==1){
            var form = document.getElementById('dispatcher_form');
            if($('#passenger_contact_name').val()=='' || $('#passenger_phone').val()=='' || $('#geocomplete').val()=='' || $('#dropoff').val()=='' || typeof driver_assign === "undefined" ){
                alert("Please fill all the required details");
                return false;
            } else{
                form.submit();
                $('#request_submit_button').addClass("disabled");
                /*if($('#paymentflag').val()=== '0'){
                    //alert("Please enter payment details");
                    //return false;
                }else{
                    //alert($('#paymentflag').val());
                    form.submit();
                    $('#request_submit_button').addClass("disabled");
                }*/
            }
		} else{
            var form = document.getElementById('dispatcher_form');
            if($('#passenger_contact_name').val()=='' || $('#passenger_phone').val()=='' || $('#geocomplete').val()=='' || $('#dropoff').val()=='' ){
                alert("Please fill all the required details");
                return false;
            } else{
                var attendant = + $('#attendant').is(':checked');
                var roundtrip = + $('#roundtrip').is(':checked');
                if(attendant==1){
                    if($('#attendant_name').val()=='' || $('#attendant_phone').val()==''){
                        alert("Please fill all attendant details");
                        return false;
                    }
                }
				if(payment_type== 1){
					if($('#company_name').val()==0 || $('#provider_name').val()==0){
                        alert("Please select a provider");
                        return false;
					}

					if($('#company_name').val() >=1){
                        if($('#agent_name1').val()=='' || $('#agent_phone1').val()==''){
                            alert("Please fill agent information");
                            return false;
						}
					}
				}
                form.submit();
                $('#request_submit_button').addClass("disabled");
                /*if($('#paymentflag').val()== '0'){
                    alert("Please enter payment details");
                    return false;
                }else{
                    //alert($('#paymentflag').val());
                    form.submit();
                    $('#request_submit_button').addClass("disabled");
                }*/
            }
		}
	}
    function check_roundtrip(){
        var roundtrip = + $('#roundtrip').is(':checked');
        var attendant = + $('#attendant').is(':checked');
        if(roundtrip==1){
            if($('#geocomplete').val()!=''){
                $('#round_pickupaddress').val($('#dropoff').val());
            }
            if($('#dropoff').val()!=''){
                $('#round_dropoffaddress').val($('#geocomplete').val());
            }
            if(attendant==1){
                $('#roundtrip_attendant').show();
            } else{
                $('#roundtrip_attendant').hide();
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
	function automatic_ride(){
        $('#ride_services').hide();
        $('#estimate_cost').hide();
	}

	function manual_ride(){
	    $('#ride_services').show();
	    $('#estimate_cost').show();
	}

    function invoice(){
        $('#hospitalprov').show();
        $('#add_payment').hide();
        $('#another_payment').hide();
        $('#show_cards').hide();
    }

    function creditcard(payment_type) {
        if (payment_type == 2) {
            $('#hospitalprov').hide();
            checkdata();
        }
    }

var stops = new Array();

<?php foreach ($stops as $stop) { ?>
	stops[{{ $stop['stopnumber'] }}] = {
		"stopnumber": {{ $stop['stopnumber'] }},
		"title": '{{ $stop['title'] }}',
		"latitude": '{{ sprintf("%.15f", $stop['latitude']) }}',
		"longitude": '{{ sprintf("%.15f", $stop['longitude']) }}'
	};
<?php } ?>

</script>
@stop 
