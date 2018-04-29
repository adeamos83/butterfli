@extends('dispatcher.layout')

@section('content')
	<script src="<?php echo asset_url(); ?>/web/js/jstz.min.js"></script>

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
<div><a id="ride_info_pop_up" class="btn-sm" data-toggle="modal" href="#ride_info_pop_up_responsive" style="display:none;"></a></div>
<div><a id="charge_ride_pop_up" class="btn-sm" data-toggle="modal" href="#charge_ride_pop_up_responsive" style="display:none;"></a></div>
<div><a id="tp_rating_pop_up" class="btn-sm" data-toggle="modal" href="#tp_rating_pop_up_responsive" style="display:none;"></a></div>
<div><a id="card_alert_pop_up" class="btn-sm" data-toggle="modal" href="#card_alert_pop_up_responsive" style="display:none;"></a></div>

	<div class="modal fade modal1" id="walker_rating_pop_up_responsive" role="dialog">
		<div class="modal-dialog" style="margin-top:180px;">
			<div class="modal-content">
				<div class="modal-header new-modal">
					<h4 class="modal-title" style="color:#ffffff;"><b>Feedback</b></h4>
				</div>
				<div class="modal-body" style="height:auto;">
					<form class="form-horizontal">
						<fieldset>
							<div class="form-group">
								<span class="help-block"></span>
								<div class="col-md-1"></div>
								<div class="col-md-4" style="padding-top:24px;">
									<label for="inputtitle">Rating:</label>
								</div>
								<div class="col-md-6">
									<div class="cont">
										<div class="stars" id="rating">

										</div>
									</div>
									<div class="form-control-focus"></div>
								</div>
								<div class="col-md-1"></div>
							</div>
							<div class="form-group">
								<span class="help-block"></span>
								<div class="col-md-1"></div>
								<div class="col-md-4">
									<label for="inputtitle">Feedback Comment:</label>
								</div>
								<div class="col-md-6">
									<input value="" readonly id="walker_comment" style="border:none;width: 321px;"></input>
								</div>
								<div class="col-md-3"></div>
							</div>
						</fieldset>
					</form>
				</div>
			</div>
		</div>
	</div>

<div class="modal fade modal1" id="charge_ride_pop_up_responsive" role="dialog">
	<div class="modal-dialog" style="margin-top:180px;">
		<div class="modal-content">
			<div class="modal-header new-modal">
				<h4 class="modal-title" style="color:#ffffff;"><b>Invoice</b></h4>
			</div>
			<div class="modal-body" style="height:auto;">
				<form class="form-horizontal">
					<fieldset>
		<div class="modal-body margin-top-0">
			<div class="scroller" style="height:50px" data-always-visible="1" data-rail-visible1="1">
				<div class="row">
					<div class="col-md-12">
						<div class="portlet-body form">
							<div class="form-body">
								<span id="comment-error-message"></span>
								<div class="form-group form-md-line-input field-charge_form-details">
									<label class="col-md-3 control-label" for="form_control_1">Ride Amount</label>
									<div class="col-md-5">
										<input type="text" name="rideamount" id="rideamount" value="" readonly>
										<div class="form-control-focus"></div>

									</div>
								</div>
								<div class="form-group form-md-line-input field-charge_form-distance"><label class="col-md-3 control-label" for="form_control_1">Promotional Offer</label>

									<div class="col-md-5">
										<input type="number" name="promotional_offer" id="promotional_offer" value="0.00" onchange="change_final_total();">
										<div class="form-control-focus"></div>

									</div>
								</div>
								<div class="form-group form-md-line-input field-charge_form-time"><label class="col-md-3 control-label" for="form_control_1">Additional Fee</label>

									<div class="col-md-5">
										<input type="number" name="additional_fee" id="additional_fee" value="0.00" onchange="change_final_total();">
										<div class="form-control-focust"></div>

									</div>
								</div>
								<div class="form-group form-md-line-input field-charge_form-comment"><label class="col-md-3 control-label" for="form_control_1">Comment</label>
									<div class="col-md-5">
										<textarea style="margin: 0px;width: 205px;height: 68px;" class="form-control" data-provide="markdown" id="comment" name="comment"></textarea>
										<div class="form-control-focus"></div>

									</div>
								</div>
								<div class="form-group form-md-line-input field-charge_form-comment" id="show_approval_text" style="display:none;"><label class="col-md-3 control-label" for="form_control_1">Approval</label>
									<div class="col-md-5">
										<input type="text" name="approval" id="approval" value="" >
										<div class="form-control-focus"></div>

									</div>
								</div>
								<div class="form-group form-md-line-input field-charge_form-comment"><label class="col-md-3 control-label" for="form_control_1">Total</label>
									<div class="col-md-5">
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
					</fieldset>
	</form>
			</div>
		</div>
	</div>
</div>
	<div class="modal fade modal1" id="ride_info_pop_up_responsive" role="dialog">
		<div class="modal-dialog" style="margin-top:180px;">
			<div class="modal-content">
				<div class="modal-header new-modal">
					<h4 class="modal-title" style="color:#ffffff;"><b>Send Ride Information</b></h4>
				</div>
				<div class="modal-body" style="height:auto;">
					<form class="form-horizontal">
						<fieldset>
							<div class="modal-body margin-top-0">
								<div class="scroller" style="height:50px" data-always-visible="1" data-rail-visible1="1">
									<div class="row">
										<div class="col-md-12">
											<div class="portlet-body form">
												<div class="form-body">
													<span id="sms-error-message"></span>
													<div class="form-group field-messageform-details">
														<label class="col-md-2 control-label" style="text-align: right;position: relative;bottom:-6px;" for="form_control_1">SMS</label>
														<span class="help-block"></span>
														<div class="col-md-7">
												<span class=" bg_arrow"><select style="border: none;background: #ffffff;max-width:80px;" name="code" id="code">
													<option data-countryCode="US" value="+1" Selected>US (+1)</option>
													<option data-countryCode="GB" value="44">UK (+44)</option>
													<option data-countryCode="CA" value="+1">CA (+1)</option>
													<option data-countryCode="IN" value="+91">IN (+91)</option>
												</select></span>
															<input type="text"  class="form-control"  style="padding:0;float: right;position:relative;top:-15px;left: -45px;height:30px;max-width: 163px;margin-left: 15px;" name="sms" id="sms" value="" >
															<div class="form-control-focus"></div>

														</div>
													</div>
													<div class="form-group field-messageform-details">
														<label class="col-md-2 control-label" style="text-align: right;position:relative;top:-12px;" for="form_control_1">Email</label>
														<span class="help-block"></span>
														<div class="col-md-4">
															<input type="text"  class="form-control" style="height:30px;width: 250px;position: relative;top:-30px;padding:0;" name="email" id="email" value="" >
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
								<input type="hidden" id="requests_id" name="requests_id" value="">
								<button type="button" data-dismiss="modal" class="btn default" >Cancel</button>
							</div>
						</fieldset>
					</form>
				</div>
			</div>
		</div>
	</div>

<div id="cancel_reason_pop_up_responsive" style="height:222px;" class="modal fade" tabindex="-1" aria-hidden="false">
	<div class="modal-content">
		<form role="form" class="form-horizontal" name="reason_form" id="reason_form">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title"><h4 class="modal-title"><b>Please provide the cancel reason</b></h4></h4>
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
<div class="modal fade modal1" id="other_ride_details_modal" role="dialog">
	<div class="modal-dialog" style="margin-top:180px;">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header" style="background-color:#68dff0;border-top-left-radius:5px;border-top-right-radius:5px; ">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title" style="color:#ffffff;"><b>Other Ride Details</b></h4>
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
	<div class="modal fade modal1" id="tp_rating_pop_up_responsive" role="dialog">
		<div class="modal-dialog" style="margin-top:180px;">
			<div class="modal-content">
				<div class="modal-header new-modal">
					<h4 class="modal-title" style="color:#ffffff;"><b>Rate Transportation Provider</b></h4>
				</div>
				<div class="modal-body" style="height:auto;">
					<form class="form-horizontal" id="mainForm" name="mainForm">
						<fieldset>
							<div class="modal-body margin-top-0">
								<div class="scroller" style="height:50px" data-always-visible="1" data-rail-visible1="1">
									<div class="row">
										<div class="col-md-12">
											<div class="portlet-body form">
												<div class="form-body">
													<span id="error-message"></span>
													<div class="form-group form-md-line-input field-complete_form-comment">
														<label class="col-md-4 control-label" for="form_control_1" style="padding-top: 24px">Rating</label>
														<div class="col-md-6">
															<div class="cont">
																<div class="stars">
																	<input class="star star-5" id="star-5-2" type="radio" name="star" value="5" checked/>
																	<label class="star star-5" for="star-5-2"></label>
																	<input class="star star-4" id="star-4-2" type="radio" name="star" value="4"/>
																	<label class="star star-4" for="star-4-2"></label>
																	<input class="star star-3" id="star-3-2" type="radio" name="star" value="3" />
																	<label class="star star-3" for="star-3-2"></label>
																	<input class="star star-2" id="star-2-2" type="radio" name="star" value="2"/>
																	<label class="star star-2" for="star-2-2"></label>
																	<input class="star star-1" id="star-1-2" type="radio" name="star" value="1"/>
																	<label class="star star-1" for="star-1-2"></label>
																</div>
															</div>
															<div class="form-control-focus"></div>
														</div>
													</div>
													<div class="form-group form-md-line-input field-complete_form-feedback"><label class="col-md-4 control-label" for="form_control_1">Feedback</label>
														<div class="col-md-6">
															<div id="result"></div>
															<textarea style="margin: 0px;width:250px;height: 68px;" class="form-control" data-provide="markdown" id="feedback" name="feedback"></textarea>
															<div class="form-control-focus"></div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="modal-footer back_modal_body" style="text-align:center;padding-left: 50px;padding-bottom:0;padding-top: 0;padding-top: 0;">
								<a id="tp_rating_submit" href="javascript:void(0)" onclick="tp_rating();" class="btn green" >Submit</a>
								<span id="loading" style="display:none;">
							<img style="width: 43px;" src="<?php echo asset_url(); ?>/web/img/loading1.gif" alt="logo"/>
							</span>
								<input type="hidden" id="ratingrequest_id" name="ratingrequest_id" value="">
								<input type="hidden" id="tp_id" name="tp_id" value="">
								<button type="button" data-dismiss="modal" class="btn default" >Cancel</button>
							</div>
						</fieldset>
					</form>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade modal1" id="card_alert_pop_up_responsive" role="dialog">
		<div class="modal-dialog" style="margin-top:180px;width:500px;">
			<div class="modal-content">
				<div class="modal-header new-modal" style="background-color:#7935ab;padding:14px;">
					<h4 class="modal-title" style="font-size:20px;color:#ffffff;"><b>Charge User</b></h4>
				</div>
				<div class="modal-body" style="height:auto;">
					<form class="form-horizontal" name="reason_form" id="reason_form" role="form">
						<fieldset>
							<div class="modal-body margin-top-0">
								<div class="scroller" style="height:50px" data-always-visible="1" data-rail-visible1="1">
									<div class="row">
										<div class="col-md-12">
											<div class="portlet-body form">
												<div class="form-body">
													<div class="form-group form-md-line-input field-messageform-details">
														<label class="col-md-12 control-label" style="text-align: center;" for="form_control_1">
															<span style="color:#000;font-size: 18px;text-align: center;text-align: center;">This ride is not authorized for credit card payment.</span></label>
														<span class="help-block"></span>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							<div class="text-center margin-bottom-20">
								<input type="hidden" id="reqs_id" name="reqs_id" value="">
								<button type="button" data-dismiss="modal" class="btn btn-secondary" >Cancel</button>
							</div>
						</fieldset>
					</form>
				</div>
			</div>
		</div>
	</div>

<div class="box box-info tbl-box" style="padding-bottom: 200px;">
    <div align="left" id="paglink"><?php echo $walks->appends(array('type' => Session::get('type'), 'valu' => Session::get('valu')))->links(); ?></div>
 <div class="row white_bg">   
	<table class="table table-bordered">
        <tbody>
            <tr>
                <th>Request ID</th>
                <th>Action</th>
				<th>Provider</th>
				<th>Agent Name</th>
                <th>Passenger</th>
                <th>Phone</th>
				<th>Driver</th>
				<th>Phone</th>
                <th>Service</th>
                <th>Date/Time</th>
                <th>Pick up</th>
				<th>Drop off</th>
				<th>Amount</th>
				<th>Adjustment</th>
				<th>Payment Mode</th>
                <th>Payment Status</th>
            </tr>
            <?php $i = 0; ?>

            <?php foreach ($walks as $walk) { 
					$adjustment = $walk->additional_fee - $walk->promo_payment;
			?>
                <tr>
                    <td><?= $walk->id ?></td>
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-flat btn-info dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" style="width:100px;">
                                Actions
                                <span class="caret"></span>
                            </button>

                            <?php /* echo Config::get('app.generic_keywords.Currency'); */ ?>
                            <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
                                <li role="presentation"><a role="menuitem" id="map" tabindex="-1" target="_blank" href="/driverlocation/map/<?php echo $walk->id?>">View Map</a></li>
								<?php

                                if($walk->is_confirmed==1 && $walk->confirmed_walker != 0) {?>
								<li role="presentation"><a role="menuitem" id="send_email" tabindex="-1" href="javascript:void(0)" onclick="show_ride_info_box('<?php echo $walk->id ?>');">Send Ride Info</a></li>
                                <?php 		}else if($walk->is_confirmed==1 && $walk->is_manual == 1) { ?>
								<li role="presentation"><a role="menuitem" id="send_email" tabindex="-1" href="javascript:void(0)" onclick="show_ride_info_box('<?php echo $walk->id ?>');">Send Ride Info</a></li>

                                <?php 			}

								if($walk->is_paid==0 && $walk->is_completed==1 && $walk->payment_mode!=3 && $walk->total!=0) {?>
									<li role="presentation"><a role="menuitem" id="user_payment" tabindex="-1" href="javascript:void(0)" onclick="create_invoice('<?php echo $walk->id ?>', '<?php echo sprintf2($walk->total_service_amount, 2); ?>');">Charge User</a></li>
                                <?php } else {?>
                                    <li role="presentation"><a role="menuitem" id="user_payment" tabindex="-1" href="javascript:void(0)" onclick="show_card_alert('<?php echo $walk->id ?>');">Charge User</a></li>
                                <?php } if($admin == 1 && $walk->assigned_dispatcher_id>0 && $walk->tp_id==NULL) { ?>
									<li role="presentation"><a role="menuitem" id="tp_rating" tabindex="-1" href="javascript:void(0)" onclick="rate_tp('<?php echo $walk->id ?>','<?php echo $walk->assigned_dispatcher_id ?>');">Rate Transportation Provider</a></li>
					<?php       } ?>

								<li role="presentation"><a role="menuitem" id="other_ride_details" tabindex="-1" href="javascript:void(0)" onclick="show_other_ride_details('<?php echo $walk->oxygen_mask ?>','<?php echo $walk->user_height ?>','<?php echo $walk->user_weight ?>','<?php echo $walk->user_condition ?>','<?php echo $walk->respirator ?>','<?php echo $walk->any_tubing ?>','<?php echo $walk->colostomy_bag ?>','<?php echo $walk->any_attachments ?>','<?php echo $walk->attendant_travelling ?>');">Other Ride Details </a></li>
								<li role="presentation"><a role="menuitem" id="walker_rating" tabindex="-1" href="javascript:void(0)" onclick="show_feedback('<?php echo $walk->rating ?>','<?php echo $walk->comment ?>');">Feedback</a></li>
							</ul>
                        </div>  

                    </td>
					<td><?php
                        if ($walk->provider_company!='') {
                            echo $walk->provider_company . "-" . $walk->provider_name;
                        }else {
                            echo "NA";
                        }
                        ?>
					</td>
					<td><?php
                        if ($walk->agent_contact_name!='') {
                            echo $walk->agent_contact_name;
                        }else {
                            echo "NA";
                        }
                        ?>
					</td>
					<td><?php 	if($walk->owner_contact_name!=''){ echo $walk->owner_contact_name;
                        } else{
                            echo $walk->ownercontact_name;
                        }
                        ?>
					</td>
					<td><?php 	if($walk->owner_phone!=''){
                            echo $walk->owner_phone;
                        } else{
                            echo $walk->ownerphone;
                        }
                        ?>
					</td>
                    <td>
                        <?php
                        if ($walk->confirmed_walker) {
                            echo $walk->walker_contact_name;
                        } else if($walk->driver_name && $walk->driver_phone){
                            echo $walk->driver_name;
                        }else{
                            echo "Un Assigned";
                        }
                        ?>
                    </td>
					<td>
						<?php
						if (($walk->is_confirmed  == 1) && ($walk->is_manual == 0)) {
							echo $walk->walker_phone;
						} else if($walk->driver_phone){
							echo $walk->driver_phone;
						} else{
							echo "NA";
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
					<td><?php echo $walk->src_address ?></td>
					<td><?php echo $walk->dest_address ?></td>
                    <td>
                        <?php echo "$".sprintf2($walk->total, 2); ?>
                    </td>
					<td>
                        <?php echo "$".sprintf2($adjustment, 2); ?>
                    </td>
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
    function show_card_alert(requestid){
        $( "#card_alert_pop_up" ).trigger( "click" );

    }

    function show_ride_info_box(requestid){
        $('#send_email').addClass("disabled");
        document.getElementById('requests_id').value = requestid;
        $( "#ride_info_pop_up" ).trigger( "click" );
    }

    function send_ride_info(){
        if($('#sms').val() == '' && $('#email').val() ==''){
            $("#sms-error-message").html('<span style="text-align: left;font-size:15px;color: #f56954;">Please enter atleast one.</span>');
        } else{
            $('#ride_info_submit').addClass("disabled");
            $.ajax({
                type: "POST",
                url:'<?php echo URL::Route('DispatcherSendRideInfo') ?>',
                data:{request_id:$('#requests_id').val(),code:$('#code').val(),sms:$('#sms').val(),email:$('#email').val()},
                success: function(data) {
                    console.log(data);
                    $('#ride_info_pop_up_responsive').modal('hide');
                    location.reload();
                }
            });
        }
    }

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
			url:'<?php echo URL::Route('cancelridedispatcher') ?>',
			data:{request_id:document.getElementById('request_id').value,cancel_reason:document.getElementById('details').value},
			success: function(data) {
				console.log(data);
				$('#cancel_reason_pop_up_responsive').modal('hide');
				location.reload();
			}
		});
	}
}

function complete_ride(requestid){
	$('#ride_completed').addClass("disabled");
	document.getElementById('request_id').value = requestid;
	$( "#complete_ride_pop_up" ).trigger( "click" );
}

function walker_ride_complete(){
	if(document.getElementById('dropoff').value=='' || document.getElementById('distance').value=='' || document.getElementById('time').value=='' || document.getElementById('complete_comment').value==''){
		$("#error-message").html('<span style="text-align: center;font-size:15px;color: #f56954;">Please fill all the details.</span>');
	} else{
		$('#complete_submit').addClass("disabled");
		$.ajax({
			type: "POST",
			url:'<?php echo URL::Route('DispatcherRideCompleted') ?>',
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

    function show_feedback(rating,comment){
        var walker_rating = "NA",walker_comments = "NA";
        if( rating == 1 ){
            walker_rating = '<input class="star star-5"  id="star-5-2" type="radio"  disabled name="star"  />\n' +
                '<label class="star star-5" for="star-5-2"></label>\n' +
                '<input class="star star-4" id="star-4-2"  type="radio" disabled name="star" />\n' +
                '<label class="star star-4" for="star-4-2"></label>\n' +
                '<input class="star star-3" id="star-3-2" type="radio" disabled name="star"  />\n' +
                '<label class="star star-3"  for="star-3-2"></label>\n' +
                '<input class="star star-2" id="star-2-2"  type="radio" disabled name="star"  />\n' +
                '<label class="star star-2" for="star-2-2"></label>\n' +
                '<input class="star star-1" id="star-1-2" type="radio" disabled name="star" checked />\n' +
                '<label class="star star-1"  for="star-1-2"></label>';
        }else if(rating == 2 ){
            walker_rating = '<input class="star star-5"  id="star-5-2" type="radio"  disabled name="star"  />\n' +
                '<label class="star star-5" for="star-5-2"></label>\n' +
                '<input class="star star-4" id="star-4-2"  type="radio" disabled name="star" />\n' +
                '<label class="star star-4" for="star-4-2"></label>\n' +
                '<input class="star star-3" id="star-3-2" type="radio" disabled name="star"  />\n' +
                '<label class="star star-3"  for="star-3-2"></label>\n' +
                '<input class="star star-2" id="star-2-2"  type="radio" disabled name="star" checked />\n' +
                '<label class="star star-2" for="star-2-2"></label>\n' +
                '<input class="star star-1" id="star-1-2" type="radio" disabled name="star"  />\n' +
                '<label class="star star-1"  for="star-1-2"></label>';
        }else if(rating == 3){
            walker_rating = '<input class="star star-5"  id="star-5-2" type="radio"  disabled name="star"  />\n' +
                '<label class="star star-5" for="star-5-2"></label>\n' +
                '<input class="star star-4" id="star-4-2"  type="radio" disabled name="star" />\n' +
                '<label class="star star-4" for="star-4-2"></label>\n' +
                '<input class="star star-3" id="star-3-2" type="radio" disabled name="star" checked />\n' +
                '<label class="star star-3"  for="star-3-2"></label>\n' +
                '<input class="star star-2" id="star-2-2"  type="radio" disabled name="star" />\n' +
                '<label class="star star-2" for="star-2-2"></label>\n' +
                '<input class="star star-1" id="star-1-2" type="radio" disabled name="star"  />\n' +
                '<label class="star star-1"  for="star-1-2"></label>';
		}else if(rating == 4){
            walker_rating = '<input class="star star-5"  id="star-5-2" type="radio"  disabled name="star"  />\n' +
                '<label class="star star-5" for="star-5-2"></label>\n' +
                '<input class="star star-4" id="star-4-2"  type="radio" disabled name="star" checked />\n' +
                '<label class="star star-4" for="star-4-2"></label>\n' +
                '<input class="star star-3" id="star-3-2" type="radio" disabled name="star"  />\n' +
                '<label class="star star-3"  for="star-3-2"></label>\n' +
                '<input class="star star-2" id="star-2-2"  type="radio" disabled name="star" />\n' +
                '<label class="star star-2" for="star-2-2"></label>\n' +
                '<input class="star star-1" id="star-1-2" type="radio" disabled name="star"  />\n' +
                '<label class="star star-1"  for="star-1-2"></label>';
        }else if(rating == 5){
            walker_rating = '<input class="star star-5"  id="star-5-2" type="radio"  disabled name="star"  checked />\n' +
                '<label class="star star-5" for="star-5-2"></label>\n' +
                '<input class="star star-4" id="star-4-2"  type="radio" disabled name="star"  />\n' +
                '<label class="star star-4" for="star-4-2"></label>\n' +
                '<input class="star star-3" id="star-3-2" type="radio" disabled name="star"  />\n' +
                '<label class="star star-3"  for="star-3-2"></label>\n' +
                '<input class="star star-2" id="star-2-2"  type="radio" disabled name="star" />\n' +
                '<label class="star star-2" for="star-2-2"></label>\n' +
                '<input class="star star-1" id="star-1-2" type="radio" disabled name="star"  />\n' +
                '<label class="star star-1"  for="star-1-2"></label>';
        }else {
            walker_rating = '<input class="star star-5"  id="star-5-2" type="radio"  disabled name="star"  />\n' +
                '<label class="star star-5" for="star-5-2"></label>\n' +
                '<input class="star star-4" id="star-4-2"  type="radio" disabled name="star"  />\n' +
                '<label class="star star-4" for="star-4-2"></label>\n' +
                '<input class="star star-3" id="star-3-2" type="radio" disabled name="star"  />\n' +
                '<label class="star star-3"  for="star-3-2"></label>\n' +
                '<input class="star star-2" id="star-2-2"  type="radio" disabled name="star" />\n' +
                '<label class="star star-2" for="star-2-2"></label>\n' +
                '<input class="star star-1" id="star-1-2" type="radio" disabled name="star"  />\n' +
                '<label class="star star-1"  for="star-1-2"></label>';
        }

        if(comment!=''){
            walker_comments =  comment;
		}

        $('#rating').html(walker_rating);
        document.getElementById('walker_comment').value = walker_comments;
        $('#walker_rating_pop_up_responsive').modal('toggle');

    }

    function rate_tp(requestid,tp_id){
        document.getElementById('ratingrequest_id').value = requestid;
        document.getElementById('tp_id').value = tp_id;

        $( "#tp_rating_pop_up" ).trigger( "click" );
    }

    function tp_rating(){
        var rating = 0;
        var newrating=0;

		document.mainForm.onclick = function() {
			rating = document.mainForm.star.value;

			if(rating==0){
				newrating = 5;
			} else{
				newrating = rating;
			}

			$('#loading').show();
			$('#rating_submit').addClass("disabled");

			$.ajax({
				type: "POST",
				url:'<?php echo URL::Route('RateTransportationProvider') ?>',
				data:{request_id:document.getElementById('ratingrequest_id').value,tp_id:document.getElementById('tp_id').value,
					tp_rating:newrating,feedback_comment:$('#feedback').val()},
				success: function(data) {
					console.log(data);
					$('#tp_rating_pop_up_responsive').modal('hide');
					location.reload();
				}
			});
		}
	}
</script>
@stop
