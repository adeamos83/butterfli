@extends('layout')

@section('content')

<script type="text/javascript" src="https://developer.jboss.org/servlet/JiveServlet/previewBody/52971-102-1-171969/jstz-1.0.4.min.js"></script>
<script src="https://momentjs.com/downloads/moment.min.js"></script>
<script src="https://momentjs.com/downloads/moment-timezone-with-data.min.js"></script> 

<div class="col-md-6 col-sm-12" style="display:none;">

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


<div style="display:none;" class="col-md-6 col-sm-12">

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

<div><a id="cancel_reason_pop_up" class="btn-sm" data-toggle="modal" href="#cancel_reason_pop_up_responsive" style="display:none;"></a></div>
<div><a id="complete_ride_pop_up" class="btn-sm" data-toggle="modal" href="#complete_ride_pop_up_responsive" style="display:none;"></a></div>
<style>
    .modal-backdrop {
        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        background-color: #000000;
    }

    .modal-backdrop.fade {
        opacity: 0;
    }

    .modal-backdrop,
    .modal-backdrop.fade.in {
        opacity: 0;
        filter: alpha(opacity=80);
    }
    .modal-title {
        color: #e8ecec;
    }
</style>

<div id="cancel_reason_pop_up_responsive" style="height:222px;top:30%;width:50%;align:center;margin-left:400px;/*padding-right:17px;*/" class="modal fade" tabindex="-1" aria-hidden="false">
    <div class="modal-content">
        <form role="form" class="form-horizontal" name="reason_form" id="reason_form">
            <div class="modal-header" style="background-color:#68dff0;">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title"><h4 class="modal-title">Please provide the cancel reason</h4></h4>
            </div>
            <div class="modal-body margin-top-0">
                <div class="scroller" style="" data-always-visible="1" data-rail-visible1="1">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="portlet-body form">
                                <div class="form-body">
                                    <div class="form-group form-md-line-input field-messageform-details"><label class="col-md-3 control-label" for="form_control_1">Cancel Reason</label>
                                        <span class="help-block"></span>
                                        <div class="col-md-7">
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
            <div class="text-center margin-bottom-20" style="margin-bottom:20px;">
                <a id="cancel_submit" href="javascript:void(0)" onclick="insert_cancel_reason();" class="btn default" >Submit</a>
                <input type="hidden" id="request_id" name="request_id" value="">
                <button type="button" data-dismiss="modal" class="btn default" >Cancel</button>
            </div>
        </form>
    </div>
</div>
<div id="complete_ride_pop_up_responsive" class="modal fade" style="height:337px;top:10%;width:50%;align:center;margin-left:400px;/*padding-right:17px;*/" tabindex="-1" aria-hidden="false">
    <style>
        .pac-container {
            z-index: 10000 !important;
        }
    </style>
    <div class="modal-content">
        <form role="form" class="form-horizontal" name="complete_form" style="margin:0 0 20px" id="complete_form">
            <div class="modal-header" style="background:#68dff0">
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
                                    <div class="form-group form-md-line-input field-complete_form-details"><label class="col-md-4 control-label" for="form_control_1">Dropoff Address</label>

                                        <div class="col-md-4">
                                            <input type="text" name="dropoffaddress" id="dropoff" value="" >
                                            <span class="end">
                                                <input type="hidden" name="end_lat" data-geo="lat">
                                                <input type="hidden" name="end_lng" data-geo="lng">
                                            </span>
                                            <div class="form-control-focus"></div>

                                        </div>
                                    </div>
                                    <div class="form-group form-md-line-input field-complete_form-distance"><label class="col-md-4 control-label" for="form_control_1">Distance (in miles)</label>

                                        <div class="col-md-4">
                                            <input type="text" name="distance" id="distance" value="" >
                                            <div class="form-control-focus"></div>

                                        </div>
                                    </div>
                                    <div class="form-group form-md-line-input field-complete_form-time"><label class="col-md-4 control-label" for="form_control_1">Time (in mins)</label>

                                        <div class="col-md-4">
                                            <input type="text" name="time" id="time" value="" >
                                            <div class="form-control-focus"></div>

                                        </div>
                                    </div>
                                    <div class="form-group form-md-line-input field-complete_form-comment"><label class="col-md-4 control-label" for="form_control_1">Comment</label>
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
                <a id="complete_submit" href="javascript:void(0)" onclick="walker_ride_complete();" class="btn default" >Submit</a>
                <input type="hidden" id="request_id" name="request_id" value="">
                <button type="button" data-dismiss="modal" class="btn default" >Cancel</button>
            </div>
        </form>
    </div>
</div>
<div align="center" id="confirmrequest"></div>
<div align="center" id="completerequest"></div>
<div class="box box-info tbl-box">
    <div align="left" id="paglink"><?php echo $walks->appends(array('type' => Session::get('type'), 'valu' => Session::get('valu')))->links(); ?></div>
    <table class="table table-bordered">
        <tbody>
            <tr>
                <th>Request ID</th>
                <th>Provider</th>
                <th>Pickup</th>
                <th>Dropoff</th>
                <th>{{ trans('customize.User');}} Name</th>
                <th>User Phone</th>
                <th>{{ trans('customize.Provider');}}</th>
                <th>Driver Phone</th>
                <th>Service</th>
                <th>Date/Time</th>
                <th>Status</th>
                <th>Amount</th>
                <th>Payment Mode</th>
                <th>Payment Status</th>
                <th>Action</th>
            </tr>
            <?php $i = 0; ?>

            <?php foreach ($walks as $walk) {?>
                <tr>
                    <td><?= $walk->id ?></td>
                    <td><?php
                        if ($walk->dispatcher_firstname!='') {
                            echo $walk->dispatcher_firstname . " " . $walk->dispatcher_lastname;
                        }else {
                            echo "NA";
                        }
                        ?>
                    </td>
                    <td><?php echo $walk->src_address; ?></td>
                    <td><?php echo $walk->dest_address; ?></td>
                    <td><?php 
						if ($walk->owner_contact_name!='') {
							echo $walk->owner_contact_name;
                        }
						?>
					</td>
                    <td><?php echo $walk->user_phone_no ?></td>
                    <td>
                        <?php
                        if ($walk->is_confirmed) {
                            echo $walk->driver_name;
                        } elseif($walk->is_cancelled==0) {
                            echo "<input type='text' name='driver_name' id='driver_name_$walk->id' value=''/>";
                        } else{
                            echo "NA";
                        }
                        ?>
                    </td>
                    <td>
                        <?php
                        if ($walk->is_confirmed) {
                            echo $walk->driver_phone;
                        } elseif($walk->is_cancelled==0) {
                            echo "<input style='width:24px;display:none;' type='text' name='code' id='code' value='+1' readonly/>"; echo "<input type='text' name='driver_phone' id='driver_phone_$walk->id' value=''/>";
                        } else{
                            echo "NA";
                        }
                        ?>
                    </td>
                    <td><?php echo $walk->name ?></td>
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
                    <td> <?php echo "$".sprintf2($walk->total, 2); ?></td>

                    <td>
                        <?php
                        if ($walk->payment_mode == 0) {
                            echo "<span class='badge bg-orange'>Stored Cards</span>";
                        } elseif ($walk->payment_mode == 1) {
                            echo "<span class='badge bg-blue'>Pay by Cash</span>";
                        } elseif ($walk->payment_mode == 2) {
                            echo "<span class='badge bg-purple'>Paypal</span>";
                        }
                        ?>
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
                        <?php   if($walk->is_confirmed==0 && $walk->is_cancelled == 0) { ?>
                                    <li role="presentation"><a role="menuitem" id="confirm" tabindex="-1" href="javascript:void(0)" onclick="confirm_ride('<?php echo $walk->id ?>');">Confirm Ride</a></li>
                        <?php   }
                                if ($walk->is_cancelled == 0 && $walk->is_completed == 0 && $walk->is_walker_started == 0) { ?>
                                    <li role="presentation"><a role="menuitem" id="cancel" tabindex="-1" href="javascript:void(0)" onclick="check_cancel_reason('<?php echo $walk->id ?>');">Cancel Ride</a></li>
                        <?php   }
                                if($walk->is_paid==0 && $walk->is_cancelled==0 && $walk->is_confirmed==1 && $walk->is_completed == 0) {?>
                            <li role="presentation"><a role="menuitem" id="completed" tabindex="-1" href="javascript:void(0)" onclick="complete_ride('<?php echo $walk->id ?>','<?php echo $walk->dest_address ?>','<?php echo $walk->distance ?>','<?php echo $walk->time ?>');">Ride Complete</a></li>
                        <?php   }
                        ?>
                            </ul>
                        </div>  

                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
    <div align="left" id="paglink"><?php echo $walks->appends(array('type' => Session::get('type'), 'valu' => Session::get('valu')))->links(); ?></div>




</div>
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
        if($('#details').val()==''){
            $(".help-block").html('<span style="text-align: center;font-size:15px;color: #f56954;">Please enter some reason for cancelling</span>');
        } else{
            $('#cancel_submit').addClass("disabled");
            $.ajax({
                type: "POST",
                url:'<?php echo URL::Route('DispatcherCancelRide') ?>',
                data:{request_id:document.getElementById('request_id').value,cancel_reason:document.getElementById('details').value},
                success: function(data) {
                    console.log(data);
                    $('#cancel_reason_pop_up_responsive').modal('hide');
                    location.reload();
                }
            });
        }
    }

    function confirm_ride(requestid){
        if($('#driver_name_'+requestid).val() == '' || $('#driver_phone_'+requestid).val() == ''){
            alert("Please enter driver name and phone both.");
            return false;
        } else{
            $('#confirmrequest').html('<tr><td style="padding: 8px 9px 6px;border-radius: 20px;"><span class="badge bg-light-blue" style="padding-top: 12px;text-align: center;font-size: 17px;   padding-bottom: 12px;">Confirming.....</span></td></tr>');
            $.ajax({
                type: "POST",
                url:'<?php echo URL::Route('DispatcherConfirmRide') ?>',
                data:{request_id:requestid,driver_name:document.getElementById('driver_name_'+requestid).value,driver_phone:document.getElementById('driver_phone_'+requestid).value,code:document.getElementById('code').value},
                success: function(data) {
                    console.log(data);
                    location.reload();
                }
            });
        }
    }

    function complete_ride(requestid,droplocation,distance,time){
        //$('#completerequest').html('<tr><td style="padding: 8px 9px 6px;border-radius: 20px;"><span class="badge bg-light-blue" style="padding-top: 12px;text-align: center;font-size: 17px;   padding-bottom: 12px;">Completing Ride.....</span></td></tr>');
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
                url:'<?php echo URL::Route('DispatcherAdminManualRideCompleted') ?>',
                data:{request_id:document.getElementById('request_id').value,dropoffaddress:document.getElementById('dropoff').value,dist:document.getElementById('distance').value,distancetime:document.getElementById('time').value,comment:document.getElementById('complete_comment').value},
                success: function(data) {
                    console.log(data);
                    $('#complete_ride_pop_up_responsive').modal('hide');
                    location.reload();
                }
            });
        }
    }
</script>
@stop