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
<div><a id="confirm_pop_up" class="btn-sm" data-toggle="modal" href="#confirm_pop_up_responsive" style="display:none;"></a></div>
<div><a id="cancel_reason_pop_up" class="btn-sm" data-toggle="modal" href="#cancel_reason_pop_up_responsive" style="display:none;"></a></div>
<div><a id="complete_ride_pop_up" class="btn-sm" data-toggle="modal" href="#complete_ride_pop_up_responsive" style="display:none;"></a></div>
<div><a id="ride_info_pop_up" class="btn-sm" data-toggle="modal" href="#ride_info_pop_up_responsive" style="display:none;"></a></div>
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
<div class="modal fade modal1" id="other_ride_details_modal" role="dialog">
    <div class="modal-dialog" style="margin-top:180px;">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header" style="background-color:#68dff0;border-top-left-radius:5px;border-top-right-radius:5px; ">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" style="color:#ffffff;">Other Ride Details</h4>
            </div>
            <div class="modal-body" style="height:auto;">
                <form class="form-horizontal">
                    <fieldset>
                        <div class="form-group">
                            <span class="help-block"></span>
                            <div class="col-md-3"></div>
                            <div class="col-md-4">
                                <label for="inputtitle">Attendant Travelling:</label>
                            </div>
                            <div class="col-md-1">
                                <input value="" readonly id="attendant_travelling" style="border:none;"></input>
                            </div>
                            <div class="col-md-3"></div>
                        </div>
                        <div class="form-group">
                            <span class="help-block"></span>
                            <div class="col-md-3"></div>
                            <div class="col-md-4">
                                <label for="inputtitle">Oxygen Mask:</label>
                            </div>
                            <div class="col-md-1">
                                <input value="" readonly id="oxygen_mask" style="border:none;"></input>
                            </div>
                            <div class="col-md-3"></div>
                        </div>
                        <div class="form-group">
                            <span class="help-block"></span>
                            <div class="col-md-3"></div>
                            <div class="col-md-4">
                                <label for="inputtitle">Respirator:</label>
                            </div>
                            <div class="col-md-1">
                                <input value="" readonly id="respirator" style="border:none;"></input>
                            </div>
                            <div class="col-md-3"></div>
                        </div>
                        <div class="form-group">
                            <span class="help-block"></span>
                            <div class="col-md-3"></div>
                            <div class="col-md-4">
                                <label for="inputtitle">Any Tubing:</label>
                            </div>
                            <div class="col-md-1">
                                <input value="" readonly id="any_tubing" style="border:none;"></input>
                            </div>
                            <div class="col-md-3"></div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-3"></div>
                            <div class="col-md-4">
                                <label for="inputtitle">Height:</label>
                            </div>
                            <div class="col-md-1">
                                <input value="" readonly id="height" style="border:none;"></input>
                            </div>
                            <div class="col-md-3"></div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-3"></div>
                            <div class="col-md-4">
                                <label for="inputtitle">Weight:</label>
                            </div>
                            <div class="col-md-1">
                                <input value="" readonly id="weight" style="border:none;"></input>
                            </div>
                            <div class="col-md-3"></div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-3"></div>
                            <div class="col-md-4">
                                <label for="inputtitle">Condition</label>
                            </div>
                            <div class="col-md-1">
                                <input value="" readonly id="condition" style="border:none;"></input>
                            </div>
                            <div class="col-md-3"></div>
                        </div>
                        <div class="form-group">
                            <span class="help-block"></span>
                            <div class="col-md-3"></div>
                            <div class="col-md-4">
                                <label for="inputtitle">Colostomy Bag:</label>
                            </div>
                            <div class="col-md-1">
                                <input value="" readonly id="colostomy_bag" style="border:none;"></input>
                            </div>
                            <div class="col-md-3"></div>
                        </div>
                        <div class="form-group">
                            <span class="help-block"></span>
                            <div class="col-md-3"></div>
                            <div class="col-md-4">
                                <label for="inputtitle">Any Attachments:</label>
                            </div>
                            <div class="col-md-1">
                               <textarea readonly rows="4" cols="30"  id="any_attachments" style="resize: none;overflow:hidden;border:none;"></textarea>
                            </div>
                            <div class="col-md-3"></div>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>

    </div>
</div>
<div id="ride_info_pop_up_responsive" style="height:260px;top:30%;width:30%;align:center;margin-left:500px;" class="modal fade"
     tabindex="-1" aria-hidden="false">
    <style>
        .pac-container {
            z-index: 10000 !important;
        }
    </style>
    <div class="modal-content">
        <form role="form" class="form-horizontal" name="ride_info" id="ride_info">
            <div class="modal-header" style="background-color:#68dff0;">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title">Send Ride Information</h4>
            </div>
            <div class="modal-body margin-top-0">
                <div class="scroller" style="height:50px" data-always-visible="1" data-rail-visible1="1">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="portlet-body form">
                                <div class="form-body">
                                    <span id="sms-error-message"></span>
                                    <div class="form-group field-messageform-details">
                                        <label class="col-md-2 control-label" style="text-align: center" for="form_control_1">SMS</label>
                                        <span class="help-block"></span>
                                        <div class="col-md-8">
												<span class=" bg_arrow"><select style="max-width:73px;height:30px;" name="code" id="code">
													<option data-countryCode="US" value="+1" Selected>US (+1)</option>
													<option data-countryCode="GB" value="44">UK (+44)</option>
													<option data-countryCode="CA" value="+1">CA (+1)</option>
													<option data-countryCode="IN" value="+91">IN (+91)</option>
												</select></span>
                                            <input type="text" style="height:30px;max-width: 130px;" name="sms" id="sms" value="" >
                                            <div class="form-control-focus"></div>

                                        </div>
                                    </div>
                                    <div class="form-group field-messageform-details">
                                        <label class="col-md-2 control-label" style="text-align: center" for="form_control_1">Email</label>
                                        <span class="help-block"></span>
                                        <div class="col-md-4">
                                            <input type="text"  class="form-control" style="height:30px;width: 260px;" name="email" id="email" value="" >
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
                <a id="ride_info_submit" href="javascript:void(0)" onclick="send_ride_info();" class="btn green" >Submit</a>
                <input type="hidden" id="request_id" name="request_id" value="">
                <button type="button" data-dismiss="modal" class="btn default" >Cancel</button>
            </div>
        </form>
    </div>
</div>
<div id="confirm_pop_up_responsive" style="height:270px;top:30%;width:40%;align:center;margin-left:400px;/*padding-right:17px;*/" class="modal fade" tabindex="-1" aria-hidden="false">
    <div class="modal-content">
        <form role="form" class="form-horizontal" name="reason_form" id="reason_form">
            <div class="modal-header" style="background-color:#68dff0;">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title">Confirm Ride</h4>
            </div>
            <div class="modal-body margin-top-0">
                <div class="scroller" style="" data-always-visible="1" data-rail-visible1="1">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="portlet-body form">
                                <div class="form-body">
                                    <span id="error-message"></span>
                                    <div class="form-group form-md-line-input field-confirm_form-total_cost">
                                        <label class="col-md-4 control-label" for="form_control_1">Total Cost</label>

                                        <div class="col-md-4">
                                            <input type="text" name="total_cost" id="total_cost" value="" >
                                            <div class="form-control-focus"></div>

                                        </div>
                                    </div>
                                    <div class="form-group form-md-line-input field-confirm_form-estimated_time">
                                        <label class="col-md-4 control-label" for="form_control_1">Estimated Time (in mins)</label>
                                        <div class="col-md-4">
                                            <input type="text" name="est_time" id="est_time" value="" >
                                            <div class="form-control-focus"></div>

                                        </div>
                                    </div>
                                </div>
                                <div align="center" id="loadingimage" style="display:none;"><img src="<?= asset_url() . '/web/img/clock-loading.gif' ?>" style="z-index: 9999;" /></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center margin-bottom-20" style="margin-bottom:20px;">
                <a id="confirm_submit" href="javascript:void(0)" onclick="confirm_ride();" class="btn default" >Submit</a>
                <input type="hidden" id="request_id" name="request_id" value="">
                <input type="hidden" id="driver_n" name="driver_n" value="">
                <input type="hidden" id="driver_p" name="driver_p" value="">
                <input type="hidden" id="countrycode" name="countrycode" value="">

                <button type="button" data-dismiss="modal" class="btn default" >Cancel</button>
            </div>
        </form>
    </div>
</div>

<div id="cancel_reason_pop_up_responsive" style="height:222px;top:30%;width:50%;align:center;margin-left:400px;/*padding-right:17px;*/" class="modal fade" tabindex="-1" aria-hidden="false">
    <div class="modal-content">
        <form role="form" class="form-horizontal" name="reason_form" id="reason_form">
            <div class="modal-header" style="background-color:#68dff0;">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title">Please provide the cancel reason</h4>
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
                <h4 class="modal-title">Complete the Ride</h4>
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
    <div align="left" id="paglink"><?php echo $walks->appends(array('type' => Session::get('type'), 'valu' => Session::get('valu')))->links();?></div>
    <table class="table table-bordered">
        <tbody>
            <tr>
                <th>Request ID</th>
                <th>Provider</th>
                <th>Pickup</th>
                <th>Dropoff</th>
                <th>{{ trans('customize.User');}} Name</th>
                <th>User Phone</th>
                <th>Agent Name</th>
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
                        if ($walk->provider_company!='') {
                            echo $walk->provider_company . "-" . $walk->provider_name;
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
                    <td><?php
                        if ($walk->agent_contact_name!='') {
                            echo $walk->agent_contact_name;
                        }else {
                            echo "NA";
                        }
                        ?>
                    </td>
                    <td>
                        <?php
                        if ($walk->is_confirmed) {
                            echo $walk->driver_name;
                        } else{
                            echo "NA";
                        }
                        ?>
                    </td>
                    <td>
                        <?php
                        if ($walk->is_confirmed) {
                            echo $walk->driver_phone;
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
                        } elseif ($walk->payment_mode == 3) {
                            echo "<span class='badge bg-green'>" .$walk->provider_name ."</span>";
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
                                    <li role="presentation"><a role="menuitem" id="confirm" tabindex="-1" href="javascript:void(0)" onclick="show_confirm_box('<?php echo $walk->id ?>','<?php echo $walk->total ?>');">Confirm Ride</a></li>
                        <?php   }
                                if ($walk->is_cancelled == 0 && $walk->is_completed == 0 && $walk->is_walker_started == 0) { ?>
                                    <li role="presentation"><a role="menuitem" id="cancel" tabindex="-1" href="javascript:void(0)" onclick="check_cancel_reason('<?php echo $walk->id ?>');">Cancel Ride</a></li>
                        <?php   }
                                if($walk->is_paid==0 && $walk->is_cancelled==0 && $walk->is_confirmed==1 && $walk->is_completed == 0) {?>
                                    <li role="presentation"><a role="menuitem" id="complete" tabindex="-1" href="javascript:void(0)" onclick="complete_ride('<?php echo $walk->id ?>');">Complete Ride</a></li>
                        <?php   }
                                if($walk->is_confirmed==1 && $walk->document_url!='') {?>
                                   <!-- <li role="presentation"><a  role="menuitem" id="document" tabindex="-1" download href="<?php echo $walk->document_url ?>">Download Receipts</a></li>-->
                        <?php   }
                                if($walk->is_confirmed==1) {?>
                                    <li role="presentation"><a role="menuitem" id="send_email" tabindex="-1" href="javascript:void(0)" onclick="show_ride_info_box('<?php echo $walk->id ?>');">Send Ride Info</a></li>
                        <?php   }?>

                                <li role="presentation"><a role="menuitem" id="other_ride_details" tabindex="-1" href="javascript:void(0)" onclick="show_other_ride_details('<?php echo $walk->oxygen_mask ?>','<?php echo $walk->user_height ?>','<?php echo $walk->user_weight ?>','<?php echo $walk->user_condition ?>','<?php echo $walk->respirator ?>','<?php echo $walk->any_tubing ?>','<?php echo $walk->colostomy_bag ?>','<?php echo $walk->any_attachments ?>','<?php echo $walk->attendant_travelling ?>');">Other Ride Details </a></li>


                                <li role="presentation" class="divider"></li>
                                <li role="presentation"><a role="menuitem" tabindex="-1" href="javascript:void(0)" onclick="delete_ride('<?php echo $walk->id ?>');">Delete Request</a></li>
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
                url:'<?php echo URL::Route('AdminCancelRide') ?>',
                data:{request_id:document.getElementById('request_id').value,cancel_reason:document.getElementById('details').value},
                success: function(data) {
                    console.log(data);
                    $('#cancel_reason_pop_up_responsive').modal('hide');
                    location.reload();
                }
            });
        }
    }

    function show_confirm_box(requestid,total_cost){
        if($('#driver_name_'+requestid).val() == '' || $('#driver_phone_'+requestid).val() == ''){
            alert("Please enter driver name and phone both.");
            return false;
        } else{
            $('#confirm').addClass("disabled");

            document.getElementById('request_id').value = requestid;
            document.getElementById('total_cost').value = total_cost;
            document.getElementById('driver_n').value = document.getElementById('driver_name_'+requestid).value;
            document.getElementById('driver_p').value = document.getElementById('driver_phone_'+requestid).value;
            document.getElementById('countrycode').value = document.getElementById('ccode').value;
            //alert(document.getElementById('countrycode').value);

            $( "#confirm_pop_up" ).trigger( "click" );
        }
    }

    function complete_ride(requestid){
        $('#completerequest').html('<tr><td style="padding: 8px 9px 6px;border-radius: 20px;"><span class="badge bg-light-blue" style="padding-top: 12px;text-align: center;font-size: 17px;   padding-bottom: 12px;">Completing Ride.....</span></td></tr>');
        document.getElementById('request_id').value = requestid;

        $.ajax({
            type: "POST",
            url:'<?php echo URL::Route('AdminCompleteRide') ?>',
            //data:{request_id:document.getElementById('request_id').value,dropoffaddress:document.getElementById('dropoff').value,dist:document.getElementById('distance').value,distancetime:document.getElementById('time').value,comment:document.getElementById('complete_comment').value},
            data:{request_id:document.getElementById('request_id').value},
            success: function(data) {
                console.log(data);
                //$('#complete_ride_pop_up_responsive').modal('hide');
                location.reload();
            }
        });
    }

    function walker_ride_complete(){
        if(document.getElementById('dropoff').value=='' || document.getElementById('distance').value=='' || document.getElementById('time').value=='' || document.getElementById('complete_comment').value==''){
            $("#error-message").html('<span style="text-align: center;font-size:15px;color: #f56954;">Please fill all the details.</span>');
        } else{
            $('#complete_submit').addClass("disabled");
            $.ajax({
                type: "POST",
                url:'<?php echo URL::Route('AdminCompleteRide') ?>',
                data:{request_id:document.getElementById('request_id').value,dropoffaddress:document.getElementById('dropoff').value,dist:document.getElementById('distance').value,distancetime:document.getElementById('time').value,comment:document.getElementById('complete_comment').value},
                success: function(data) {
                    console.log(data);
                    $('#complete_ride_pop_up_responsive').modal('hide');
                    location.reload();
                }
            });
        }
    }

    function delete_ride(requestid){

        var r = confirm("Are you sure you want to delete this request ?");
        if (r == true) {
            $('#completerequest').html('<tr><td style="padding: 8px 9px 6px;border-radius: 20px;"><span class="badge bg-light-blue" style="padding-top: 12px;text-align: center;font-size: 17px;' +
                'padding-bottom: 12px;">Deleting Request.....</span></td></tr>');

            $.ajax({
                type: "POST",
                url:'<?php echo URL::Route('AdminDeleteRide') ?>',
                data:{request_id:requestid},
                success: function(data) {
                    console.log(data);
                    location.reload();
                }
            });
        } else {
            
        }
    }

    function confirm_ride(){
        if(document.getElementById('est_time').value==''){
            $("#error-message").html('<span style="text-align: center;font-size:15px;color: #f56954;">Please enter estimated time.</span>');
        }else{
            $('#confirm_submit').addClass("disabled");
            //$('#confirmrequest').html('<tr><td style="padding: 8px 9px 6px;border-radius: 20px;"><span class="badge bg-light-blue" style="padding-top: 12px;text-align: center;font-size: 17px;   padding-bottom: 12px;">Confirming.....</span></td></tr>');
            $('#loadingimage').show();
            $.ajax({

                type: "POST",
                url:'<?php echo URL::Route('AdminConfirmRide') ?>',
                data:{request_id:document.getElementById('request_id').value,driver_name:document.getElementById('driver_n').value,
                    driver_phone:document.getElementById('driver_p').value,code:document.getElementById('countrycode').value,
                    total_cost:document.getElementById('total_cost').value,est_time:document.getElementById('est_time').value},
                success: function(data) {
                    console.log(data);
                    $('#confirm_pop_up_responsive').modal('hide');
                    $('#loadingimage').hide();
                    location.reload();
                }
            });
        }
    }

    function show_ride_info_box(requestid){
        $('#send_email').addClass("disabled");
        document.getElementById('request_id').value = requestid;
        $( "#ride_info_pop_up" ).trigger( "click" );
    }
function show_other_ride_details(oxygen_mask,height,weight,condition,respirator,any_tubing,colostomy_bag,any_attachments,attendant_travelling){
    var mask_yes_no = "No",respirator_yes_no = "No",any_tubing_yes_no = "No",colostomy_bag_yes_no = "No",attendant_travelling_yes_no = "No";
        if( oxygen_mask == 1 ){
             mask_yes_no = "Yes";
        }
    if( respirator == 1 ){
        respirator_yes_no = "Yes";
    }
    if( any_tubing == 1 ){
        any_tubing_yes_no = "Yes";
    }
    if( colostomy_bag == 1 ){
        colostomy_bag_yes_no = "Yes";
    }
    if( attendant_travelling == 1 ){
        attendant_travelling_yes_no = "Yes";
    }
    document.getElementById('oxygen_mask').value = mask_yes_no;
    document.getElementById('respirator').value = respirator_yes_no;
    document.getElementById('any_tubing').value = any_tubing_yes_no;
    document.getElementById('colostomy_bag').value = colostomy_bag_yes_no;
    document.getElementById('attendant_travelling').value = attendant_travelling_yes_no;
    document.getElementById('any_attachments').value = any_attachments;
    document.getElementById('height').value = height;
    document.getElementById('weight').value = weight;
    document.getElementById('condition').value = condition;
    $('#other_ride_details_modal').modal('toggle');

}
    function send_ride_info(){
        if($('#sms').val() == '' && $('#email').val() ==''){
            $("#sms-error-message").html('<span style="text-align: left;font-size:15px;color: #f56954;">Please enter atleast one.</span>');
        } else{
            $('#ride_info_submit').addClass("disabled");
            $.ajax({
                type: "POST",
                url:'<?php echo URL::Route('AdminSendRideInfo') ?>',
                data:{request_id:$('#request_id').val(),code:$('#code').val(),sms:$('#sms').val(),email:$('#email').val()},
                success: function(data) {
                    console.log(data);
                    $('#ride_info_pop_up_responsive').modal('hide');
                    location.reload();
                }
            });
        }
    }
</script>
@stop