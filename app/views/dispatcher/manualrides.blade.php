@extends('dispatcher.layout')

@section('content') 
<script type="text/javascript" src="https://developer.jboss.org/servlet/JiveServlet/previewBody/52971-102-1-171969/jstz-1.0.4.min.js"></script>

<div class="row white_bg no_bg">
<div class="col-md-5 col-sm-12 white_bg_panel">

    <div class="box box-danger">

        <form method="get" action="{{ URL::Route('/admin/sortreq') }}">
            <div class="box-header">
                <h3 class="box-title">Sort</h3>
            </div>
            <div class="box-body row">

                <div class="col-md-6 col-sm-12">

                    <select class="form-control" id="sortdrop" name="type">
                        <option value="reqid" <?php
                        if (isset($_GET['type']) && $_GET['type'] == 'reqid') {
                            echo 'selected="selected"';
                        }
                        ?>  id="reqid">Request ID</option>
                        <option value="owner" <?php
                        if (isset($_GET['type']) && $_GET['type'] == 'owner') {
                            echo 'selected="selected"';
                        }
                        ?>  id="owner">{{ trans('customize.User');}} Name</option>
                        <option value="walker" <?php
                        if (isset($_GET['type']) && $_GET['type'] == 'walker') {
                            echo 'selected="selected"';
                        }
                        ?>  id="walker">{{ trans('customize.Provider');}}</option>
                        <option value="payment" <?php
                        if (isset($_GET['type']) && $_GET['type'] == 'payment') {
                            echo 'selected="selected"';
                        }
                        ?>  id="payment">Payment Mode</option>
                    </select>

                    <br>
                </div>
                <div class="col-md-6 col-sm-12">
                    <select class="form-control" id="sortdroporder" name="valu">
                        <option value="asc" <?php
                        if (isset($_GET['type']) && $_GET['valu'] == 'asc') {
                            echo 'selected="selected"';
                        }
                        ?>  id="asc">Ascending</option>
                        <option value="desc" <?php
                        if (isset($_GET['type']) && $_GET['valu'] == 'desc') {
                            echo 'selected="selected"';
                        }
                        ?>  id="desc">Descending</option>
                    </select>

                    <br>
                </div>

            </div>

            <div class="box-footer">

                <button type="submit" id="btnsort" class="btn btn-flat btn-block btn-success">Sort</button>


            </div>
        </form>

    </div>
</div>


<div class="col-md-5 col-sm-12">

    <div class="box box-danger">

        <form method="get" action="{{ URL::Route('/admin/searchreq') }}">
            <div class="box-header">
                <h3 class="box-title">Filter</h3>
            </div>
            <div class="box-body row">

                <div class="col-md-6 col-sm-12">

                    <select class="form-control" id="searchdrop" name="type">
                        <option value="reqid" id="reqid">Request ID</option>
                        <option value="owner" id="owner">{{ trans('customize.User');}} Name</option>
                        <option value="walker" id="walker">{{ trans('customize.Provider');}}</option>
                        <option value="payment" id="payment">Payment Mode</option>
                    </select>

                    <br>
                </div>
                <div class="col-md-6 col-sm-12">

                    <input class="form-control" type="text" name="valu" value="<?php
                    if (Session::has('valu')) {
                        echo Session::get('valu');
                    }
                    ?>" id="insearch" placeholder="keyword"/>
                    <br>
                </div>

            </div>

            <div class="box-footer">

                <button type="submit" id="btnsearch" class="btn btn-flat btn-block btn-success">Search</button>


            </div>
        </form>

    </div>
</div>
</div>

<div align="center" id="cancelrequest"></div>
<div align="center" id="paymentrequest"></div>
<div><a id="cancel_reason_pop_up" class="btn-sm" data-toggle="modal" href="#cancel_reason_pop_up_responsive" style="display:none;"></a></div>
<div><a id="complete_ride_pop_up" class="btn-sm" data-toggle="modal" href="#complete_ride_pop_up_responsive" style="display:none;"></a></div>
<div><a id="charge_ride_pop_up" class="btn-sm" data-toggle="modal" href="#charge_ride_pop_up_responsive" style="display:none;"></a></div>

<div id="charge_ride_pop_up_responsive" class="modal fade" style="height:450px;" tabindex="-1" aria-hidden="false">
	<form role="form" class="form-horizontal" name="charge_form" id="charge_form">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
			<h4 class="modal-title"><h4 class="modal-title">Invoice</h4></h4>
		</div>
		<div class="modal-body margin-top-0">
			<div class="scroller" style="height:50px" data-always-visible="1" data-rail-visible1="1">
				<div class="row">
					<div class="col-md-12">
						<div class="portlet-body form">
							<div class="form-body">
								<span id="comment-error-message"></span>
								<div class="form-group form-md-line-input field-charge_form-details">
									<label class="col-md-2 control-label" for="form_control_1">Ride Amount</label>
									<div class="col-md-4">
										<input type="text" name="rideamount" id="rideamount" value="" readonly>
										<div class="form-control-focus"></div>

									</div>
								</div>
								<div class="form-group form-md-line-input field-charge_form-distance"><label class="col-md-2 control-label" for="form_control_1">Promotional Offer</label>

									<div class="col-md-4">
										<input type="number" name="promotional_offer" id="promotional_offer" value="0.00" onchange="change_final_total();">
										<div class="form-control-focus"></div>

									</div>
								</div>
								<div class="form-group form-md-line-input field-charge_form-time"><label class="col-md-2 control-label" for="form_control_1">Additional Fee</label>

									<div class="col-md-4">
										<input type="number" name="additional_fee" id="additional_fee" value="0.00" onchange="change_final_total();">
										<div class="form-control-focust"></div>

									</div>
								</div>
								<div class="form-group form-md-line-input field-charge_form-comment"><label class="col-md-2 control-label" for="form_control_1">Comment</label>
									<div class="col-md-4">
										<textarea style="margin: 0px;width: 205px;height: 68px;" class="form-control" data-provide="markdown" id="comment" name="comment"></textarea>
										<div class="form-control-focus"></div>

									</div>
								</div>
								<div class="form-group form-md-line-input field-charge_form-comment" id="show_approval_text" style="display:none;"><label class="col-md-2 control-label" for="form_control_1">Approval</label>
									<div class="col-md-4">
										<input type="text" name="approval" id="approval" value="" >
										<div class="form-control-focus"></div>

									</div>
								</div>
								<div class="form-group form-md-line-input field-charge_form-comment"><label class="col-md-2 control-label" for="form_control_1">Total</label>
									<div class="col-md-4">
										<input type="text" readonly name="final_amount" id="final_amount" value="" >
										<div class="form-control-focus"></div>

									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="text-center margin-bottom-20" style="margin-right: 70px;">
			<a id="charge_submit" href="javascript:void(0)" onclick="charge_user();" class="btn green" >Submit</a>
			<input type="hidden" id="request_id" name="request_id" value="">
			<button type="button" data-dismiss="modal" class="btn default" >Cancel</button>
		</div>
	</form>
</div>
<div id="complete_ride_pop_up_responsive" class="modal fade" style="height:337px;" tabindex="-1" aria-hidden="false">
	<style>
		.pac-container {
			z-index: 10000 !important;
		}
	</style>
	<form role="form" class="form-horizontal" name="complete_form" id="complete_form">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
			<h4 class="modal-title"><h4 class="modal-title">Complete the Ride</h4></h4>
		</div>
		<div class="modal-body margin-top-0">
			<div class="scroller" style="height:50px" data-always-visible="1" data-rail-visible1="1">
				<div class="row">
					<div class="col-md-12">
						<div class="portlet-body form">
							<div class="form-body">
								<span id="error-message"></span>
								<div class="form-group form-md-line-input field-complete_form-details"><label class="col-md-2 control-label" for="form_control_1">Dropoff Address</label>
									
									<div class="col-md-4">
										<input type="text" name="dropoffaddress" id="dropoff" value="" >
										<span class="end">
											<input type="hidden" name="end_lat" data-geo="lat">
											<input type="hidden" name="end_lng" data-geo="lng">
										</span>
										<div class="form-control-focus"></div>
										
									</div>
								</div>
								<div class="form-group form-md-line-input field-complete_form-distance"><label class="col-md-2 control-label" for="form_control_1">Distance (in miles)</label>
									
									<div class="col-md-4">
										<input type="text" name="distance" id="distance" value="" >
										<div class="form-control-focus"></div>
										
									</div>
								</div>
								<div class="form-group form-md-line-input field-complete_form-time"><label class="col-md-2 control-label" for="form_control_1">Time (in mins)</label>
									
									<div class="col-md-4">
										<input type="text" name="time" id="time" value="" >
										<div class="form-control-focus"></div>
										
									</div>
								</div>
								<div class="form-group form-md-line-input field-complete_form-comment"><label class="col-md-2 control-label" for="form_control_1">Comment</label>
									<div class="col-md-4">
										<textarea style="margin: 0px;width: 205px;height: 68px;" class="form-control" data-provide="markdown" id="complete_comment" name="complete_comment"></textarea>
										<div class="form-control-focus"></div>
										
									</div>
								</div>
							</div>									
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="text-center margin-bottom-20">
			<a id="complete_submit" href="javascript:void(0)" onclick="walker_ride_complete();" class="btn green" >Submit</a>
			<input type="hidden" id="request_id" name="request_id" value="">
			<button type="button" data-dismiss="modal" class="btn default" >Cancel</button>
		</div>
	</form>	
</div>

<div id="cancel_reason_pop_up_responsive" style="height:222px;" class="modal fade" tabindex="-1" aria-hidden="false">
	<div class="modal-content">
		<form role="form" class="form-horizontal" name="reason_form" id="reason_form">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title"><h4 class="modal-title">Please provide the cancel reason</h4></h4>
			</div>
			<div class="modal-body margin-top-0">
				<div class="scroller" style="height:50px" data-always-visible="1" data-rail-visible1="1">
					<div class="row">
						<div class="col-md-12">
							<div class="portlet-body form">
								<div class="form-body">
									<div class="form-group form-md-line-input field-messageform-details"><label class="col-md-2 control-label" for="form_control_1">Cancel Reason</label>
									<span class="help-block"></span>
										<div class="col-md-10">
											<textarea class="form-control" data-provide="markdown" id="details" name="details"></textarea>
											<div class="form-control-focus"></div>
											
										</div>
									</div>
								</div>									
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="text-center margin-bottom-20">
				<a id="cancel_submit" href="javascript:void(0)" onclick="insert_cancel_reason();" class="btn green" >Submit</a>
				<input type="hidden" id="request_id" name="request_id" value="">
				<button type="button" data-dismiss="modal" class="btn default" >Cancel</button>
			</div>
		</form>
	</div>
</div>
<div class="box box-info tbl-box">
    <div align="left" id="paglink"><?php echo $walks->appends(array('type' => Session::get('type'), 'valu' => Session::get('valu')))->links(); ?></div>
 <div class="row white_bg">   
	<table class="table table-bordered">
        <tbody>
            <tr>
                <th>Request ID</th>
                <th>{{ trans('customize.User')}} Name</th>
                <th>{{ trans('customize.Provider')}}</th>
                <th>Service</th>
                <th>Date/Time</th>
                <th>Status</th>
				<th>Amount</th>
				<th>Adjustment</th>
                <th>Payment Status</th>
                <th style="width:16%;">Action</th>
            </tr>
            <?php $i = 0; ?>

            <?php foreach ($walks as $walk) { 
					$adjustment = $walk->additional_fee - $walk->promo_payment;
			?>
                <tr>
                    <td><?= $walk->id ?></td>
                    <td><?php echo $walk->owner_contact_name; ?> </td>
                    <td>
                        <?php
                        if ($walk->driver_name) {
                            echo $walk->driver_name;
                        } else {
                            echo "Un Assigned";
                        }
                        ?>
                    </td>
                     <td><?php echo $walk->name;?></td>
                    <td id= 'time<?php echo $i; ?>' >
						<script>
							var tz = jstz.determine();
							//alert(tz.name());
							var timevar = moment.utc("<?php echo $walk->date; ?>");
							var format = 'MMMM Do YYYY, h:mm:ss a';
							var datetime = moment(timevar).tz(tz.name()).format(format);
							document.getElementById("time<?php echo $i; ?>").innerHTML = datetime;
							<?php $i++; ?>
                        </script>
                     </td>

                    <td>
                        <?php
                        if ($walk->is_cancelled == 1) {
                            echo "<span class='badge bg-red'>Cancelled</span>";
                        } elseif ($walk->is_completed == 1) {
                            echo "<span class='badge bg-green'>Completed</span>";
                        } elseif ($walk->is_started == 1) {
                            echo "<span class='badge bg-yellow'>Started</span>";
                        } elseif ($walk->is_walker_arrived == 1) {
                            echo "<span class='badge bg-yellow'>" . Config::get('app.generic_keywords.Provider') . " Arrived</span>";
                        } elseif ($walk->is_walker_started == 1) {
                            echo "<span class='badge bg-yellow'>" . Config::get('app.generic_keywords.Provider') . " Started</span>";
                        } else {
                            echo "<span class='badge bg-light-blue'>Yet To Start</span>";
                        }
                        ?>
                    </td>
                    <td>
                        <?php echo "$".sprintf2($walk->total, 2); ?>
                    </td>
					<td>
                        <?php echo "$".sprintf2($adjustment, 2); ?>
                    </td>
					<td>
                        <?php
                        if ($walk->is_paid == 1) {
                            echo "<span class='badge bg-green'>Completed</span>";
                        } elseif ($walk->is_paid == 0 && $walk->is_completed == 1) {
                            echo "<span class='badge bg-red'>Pending</span>";
                        } else {
                            echo "<span class='badge bg-yellow'>Request Not Completed</span>";
                        }
                        ?>
                    </td>
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-flat btn-info dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown">
                                Actions
                                <span class="caret"></span>
                            </button>

                            <?php /* echo Config::get('app.generic_keywords.Currency'); */ ?>
                            <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
                                <li role="presentation"><a role="menuitem" id="map" tabindex="-1" href="{{ URL::Route('dispatcherMap', $walk->id) }}">View Map</a></li>
								<?php
								
								if ($walk->is_cancelled == 0 && $walk->is_completed == 0 && $walk->is_walker_started == 0) { ?>
									<li role="presentation"><a role="menuitem" id="cancelride" tabindex="-1" href="javascript:void(0)" onclick="check_cancel_reason('<?php echo $walk->id ?>');">Cancel Ride</a></li>
								<?php 
								}
								
								if($walk->is_paid==0 && $walk->is_cancelled==0 && $walk->is_confirmed==1 && $walk->is_completed == 0) {?>
									<li role="presentation"><a role="menuitem" id="ride_completed" tabindex="-1" href="javascript:void(0)" onclick="complete_ride('<?php echo $walk->id ?>','<?php echo $walk->dest_address ?>','<?php echo $walk->distance ?>','<?php echo $walk->time ?>');">Ride Complete</a></li>
                                <?php } 

								if($walk->is_paid==0 && $walk->is_completed==1 && $walk->payment_mode!=1 && $walk->total!=0) {?>
									<li role="presentation"><a role="menuitem" id="user_payment" tabindex="-1" href="javascript:void(0)" onclick="create_invoice('<?php echo $walk->id ?>', '<?php echo sprintf2($walk->total_service_amount, 2); ?>');">Charge User</a></li>
                                <?php } ?>
                                
                                <!--
                                <li role="presentation" class="divider"></li>
                                <li role="presentation"><a role="menuitem" tabindex="-1" href="<?php echo web_url(); ?>/admin/walk/delete/<?= $walk->id; ?>">Delete Walk</a></li>
                                -->
                            </ul>
                        </div>  

                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
	</div>
    <div align="left" id="paglink"><?php echo $walks->appends(array('type' => Session::get('type'), 'valu' => Session::get('valu')))->links(); ?></div>





</div>

<!--
  <script>
  $(function() {
    $( "#start-date" ).datepicker({
      defaultDate: "+1w",
      changeMonth: true,
      numberOfMonths: 1,
      onClose: function( selectedDate ) {
        $( "#end-date" ).datepicker( "option", "minDate", selectedDate );
      }
    });
    $( "#end-date" ).datepicker({
      defaultDate: "+1w",
      changeMonth: true,
      numberOfMonths: 1,
      onClose: function( selectedDate ) {
        $( "#start-date" ).datepicker( "option", "maxDate", selectedDate );
      }
    });
  });
  </script>
-->
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB6NA_AMOEp8CiAxEZPWf_PTyy4v6xKvdA&libraries=places"></script>
<script src="https://ubilabs.github.io/geocomplete/jquery.geocomplete.js"></script>

<script type="text/javascript">

$(document).ready(function() {
	/* $( "#cancelride" ).click(function(e){
		$('#cancelrequest').html('<tr><td style="padding: 8px 9px 6px;border-radius: 20px;"><span class="badge bg-light-blue" style="padding-top: 12px;text-align: center;font-size: 17px;   padding-bottom: 12px;">Cancelling.....</span></td></tr>');
    });
	$("#user_payment").click(function(e){
		$('#paymentrequest').html('<tr><td style="padding: 8px 9px 6px;border-radius: 20px;"><span class="badge bg-light-blue" style="padding-top: 12px;text-align: center;font-size: 17px;   padding-bottom: 12px;">Processing.....</span></td></tr>');
	});	*/
	
	var end = $("#dropoff"),
        via = $("#via");
	var end_lat = $('[name=end_lat]'),
        end_lng = $('[name=end_lng]');
	
	end.geocomplete({   
		details: ".end",
		detailsAttribute: "data-geo",
		types: ["geocode", "establishment"]
	}).bind("geocode:result", function(event, result) {
		end_lat.val(result.geometry.location.lat());
		end_lng.val(result.geometry.location.lng());
	});
});

function check_cancel_reason(requestid){
	//document.getElementById('request_id').value = '';
	$('#cancelride').addClass("disabled");	
	/*$('#cancelrequest').html('<tr><td style="padding: 8px 9px 6px;border-radius: 20px;"><span class="badge bg-light-blue" style="padding-top: 12px;text-align: center;font-size: 17px;   padding-bottom: 12px;">Cancelling.....</span></td></tr>');*/
	document.getElementById('request_id').value = requestid;
	$( "#cancel_reason_pop_up" ).trigger( "click" );
}

function insert_cancel_reason(){
	//alert(document.getElementById('details').value);
	if(document.getElementById('details').value==''){
		$(".help-block").html('<span style="text-align: center;font-size:15px;color: #f56954;">Please enter some reason for cancelling</span>');
	} else{
		$('#cancel_submit').addClass("disabled");
		$.ajax({
			type: "POST",
			url:'<?php echo URL::Route('cancelmanulridedispatcher') ?>',
			data:{request_id:document.getElementById('request_id').value,cancel_reason:document.getElementById('details').value},
			success: function(data) {
				console.log(data);
				$('#cancel_reason_pop_up_responsive').modal('hide');
				location.reload();
			}
		});
	}
}

function complete_ride(requestid,droplocation,distance,time){
	$('#ride_completed').addClass("disabled");
	document.getElementById('request_id').value = requestid;
    document.getElementById('dropoff').value = droplocation;
    document.getElementById('distance').value = distance;
    document.getElementById('time').value = time;

	$( "#complete_ride_pop_up" ).trigger( "click" );
}

function walker_ride_complete(){
	if(document.getElementById('dropoff').value=='' || document.getElementById('distance').value=='' || document.getElementById('time').value=='' || document.getElementById('complete_comment').value==''){
		$("#error-message").html('<span style="text-align: center;font-size:15px;color: #f56954;">Please fill all the details.</span>');
	} else{
		$('#complete_submit').addClass("disabled");
		$.ajax({
			type: "POST",
			url:'<?php echo URL::Route('DispatcherManualRideCompleted') ?>',
			data:{request_id:document.getElementById('request_id').value,dropoffaddress:document.getElementById('dropoff').value,dist:document.getElementById('distance').value,distancetime:document.getElementById('time').value,comment:document.getElementById('complete_comment').value},
			success: function(data) {
				console.log(data);
				$('#complete_ride_pop_up_responsive').modal('hide');
				location.reload();
			}
		});
	}
}

function create_invoice(requestid,total_service_amount){
    //document.getElementById('request_id').value = '';
    $('#user_payment').addClass("disabled");
    $('#request_id').val(requestid);
	$('#rideamount').val(total_service_amount);
	$('#final_amount').val(total_service_amount);
    $( "#charge_ride_pop_up" ).trigger( "click" );
}

function change_final_total(){
	var rideamount        = $('#rideamount').val();
	var promotional_offer = $('#promotional_offer').val();	
	var additional_fee    = $('#additional_fee').val();
	
	if($('#additional_fee').val()==''){
		additional_fee = '0.00';
	}
	
	if($('#promotional_offer').val()==''){
		promotional_offer = '0.00';
	}
	
	var sum1 = parseFloat(rideamount) - parseFloat(promotional_offer);
	sum1 = parseFloat(sum1);
	var sum2 = parseFloat(sum1) + parseFloat(additional_fee);
	
	$('#final_amount').val(sum2.toFixed(2));	
	
	
	var referencetotal = rideamount - (0.13 * rideamount);
	
	var adjustment = parseFloat(additional_fee) - parseFloat(promotional_offer);
	//alert(adjustment);
	var finaltotal = parseFloat(rideamount) + parseFloat(adjustment);
	//alert(finaltotal);
	//alert(referencetotal);
	if(finaltotal < referencetotal){
		$('#show_approval_text').show();
		var form = document.getElementById('charge_form');
		var hiddenInput = document.createElement('input');
	    hiddenInput.setAttribute('type', 'hidden');
	    hiddenInput.setAttribute('id', 'approval_no_change');
	    hiddenInput.setAttribute('value', '0');
		form.appendChild(hiddenInput);
	}else{
		$('#show_approval_text').hide();
		$('#approval').val('');
		$('#approval_no_change').val('1');
	}
}

function charge_user(){
	//alert($('#approval').val());
	if($('#approval').val()!=''){
		if(($('#approval').val()=='AJD') || ($('#approval').val() =='KT')){
			var approval = $('#approval').val();
		}else{
			var approval = '';
			$("#comment-error-message").html('');
			$("#comment-error-message").html('<span style="text-align: center;font-size:15px;color: #f56954;">Please enter proper approval text.</span>');
			return false;
		}
		
	}else{
		var approval = '';
		if($('#approval_no_change').val() == '0'){
			$("#comment-error-message").html('');
			$("#comment-error-message").html('<span style="text-align: center;font-size:15px;color: #f56954;">Please enter approval text.</span>');
			return false;
		}		
		
	}
	
	if($('#final_amount').val() =='' || $('#comment').val()==''){
		$("#comment-error-message").html('<span style="text-align: center;font-size:15px;color: #f56954;">Please add some comment.</span>');
	} else{
		$('#charge_submit').addClass("disabled");
		$.ajax({
			type: "POST",
			url:'<?php echo URL::Route('DispatcherChargeUser') ?>',
			data:{request_id:$('#request_id').val(),final_amount:$('#final_amount').val(),add_fee:$('#additional_fee').val(),promo_offer:$('#promotional_offer').val(),comments:$('#comment').val(),approval_text:approval},
			success: function(data) {
				console.log(data);
				$('#charge_ride_pop_up_responsive').modal('hide');
				location.reload();
			}
		});
	}
}
</script>
@stop
