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
	</div>
	<!-- Modal -->


<!--<div class="col-md-5 col-sm-12">

    <div class="box box-danger">

        <form method="get" action="{{ URL::Route('/admin/searchreq') }}">
            <div class="box-header">
                <h3 class="box-title">Filter</h3>
            </div>
            <div class="box-body row">

                <div class="col-md-6 col-sm-12">

                    <select class="form-control" id="searchdrop" name="type">
                        <option value="reqid" id="reqid">Request ID</option>
                        <option value="owner" id="owner">{{ trans('customize.User')}} Name</option>
                        <option value="walker" id="walker">{{ trans('customize.Provider')}}</option>
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
</div>-->

<div align="center" id="cancelrequest"></div>
<div align="center" id="paymentrequest"></div>
<div><a id="assign_driver_pop_up" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#assign_driver_pop_up_responsive" style="display:none;"></a></div>
<div><a id="assign_tp_pop_up" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#assign_tp_pop_up_responsive" style="display:none;"></a></div>
<div><a id="cancel_reason_pop_up" class="btn-sm" data-toggle="modal" href="#cancel_reason_pop_up_responsive" style="display:none;"></a></div>
<div><a id="complete_ride_pop_up" class="btn-sm" data-toggle="modal" href="#complete_ride_pop_up_responsive" style="display:none;"></a></div>
<div><a id="card_alert_pop_up" class="btn-sm" data-toggle="modal" href="#card_alert_pop_up_responsive" style="display:none;"></a></div>



	<div class="modal fade" id="assign_driver_pop_up_responsive" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document" style="width:800px;">
		<!--Content-->
		<div class="modal-content">
			<!--Header-->
			<div class="modal-c-tabs">
				<div class="modal-header driver_header">
					<!-- Nav tabs -->
					<ul class="nav nav-tabs tabs-2 new_nav_bar" role="tablist">
						<li class="nav-item active" style="width:50%;text-align:center;" id="select_color_1">
							<a id="available_driver" class="nav-link active new_nav_bar"  onclick="show_tab_1();" data-toggle="tab" href="#panel7" role="tab" style="border:0;padding-top:20px;"><i style="color:#ffffff;" id="text_color_1" class="fa fa-user mr-1"></i><span style="color:#ffffff;" id="text_color_3"> Available Drivers</span></a>
						</li>
						<li class="nav-item" style="width:50%;text-align:center;" id="select_color_2">
							<a id="all_driver" class="nav-link new_nav_bar"  onclick="show_tab_2();" data-toggle="tab" href="#panel8" role="tab" style="border:0;margin-right:0;padding-top:20px;"><i id="text_color_2" style="color:#ffffff;" class="fa fa-users mr-1"></i><span style="color:#ffffff;" id="text_color_4">All Drivers</span></a>
						</li>
					</ul>
				</div>
			</div>
		</div>
		<!--Body-->
		<div class="modal-body back_modal_body" style="height:500px;">
			<input class="driver_search_icon" type="text" id="search" onkeyup="search();searchs();" placeholder="Search" style="padding-left:20px;margin-bottom:40px;width:300px;margin-left:230px;">
			<div><span style="position:relative;left: 11.3em;padding:4px;background-color:#6a00bc;"><i class="fa fa-clock-o" style="font-size: 1.5em;vertical-align:-3px;color:#ffffff;"></i></span><input type="text" name="est_time" id="est_time" value="" placeholder="Estimated Time (in minutes)" style="padding-left:35px;margin-bottom:10px;width:200px;margin-left:120px;" >
				<span style="position:relative;left: 2.3em;padding:4px;background-color:#6a00bc;"><i class="fa fa-money" style="font-size: 1.5em;vertical-align:-3px;color:#ffffff;"></i></span><input type="text" onclick="total_cost_content();" onchange="total_cost_show_content();" name="total_cost" id="total_cost" value="" placeholder="" style="padding-left:35px;margin-bottom:10px;width:200px;text-align:center;" ><span id="amount_click" onclick="total_cost_content();" style="position:relative;left:-14.5em;">Est. Cost</span></div>
			<form class="form-horizontal" role="form" name="reason_form" id="reason_form">
				<fieldset>
					<div class="modal-body margin-top-0">
						<div class="scroller" style="height:50px" data-always-visible="1" data-rail-visible1="1">
							<div class="row">
								<div class="col-md-12">
									<div class="portlet-body form">
										<div class="form-body">
											<div class="form-group form-md-line-input field-messageform-details"><label class="col-md-2 control-label" for="form_control_1"></label>
												<div id="drivers_list" class="col-md-8">
													<div class="tab-pane fade in active" id="panel7" role="tabpanel">
														<table class="table table-hover" id="search_drivers">
															<thead>
															<tr>
																<th><strong>#</strong></th>
																<th><strong>Name</strong></th>
																<th><strong>Contact</strong></th>
															</tr>
															</thead>
															<tbody>
                                                            <?php $i = 1; 	if(count($available_drivers)>0){
                                                            foreach($available_drivers as $driver){?>
															<tr onclick="show_ticked_driver(<?php echo $driver->id?>);">
																<th scope="row"><?php echo $i++?></th>
																<td><?php echo $driver->contact_name?></td>
																<td><?php echo $driver->phone?><i id="available-<?php echo $driver->id?>" class="fa fa-check fa-4x mb-3 select_driver" style="position:absolute;right:20px;"></i></td>
															</tr>
                                                            <?php       }
                                                            }
                                                            ?>
															</tbody>
														</table>
													</div>
													<div class="tab-pane fade" id="panel8" role="tabpanel">
														<table class="table table-hover"  id="search_drivers_1">
															<thead>
															<tr>
																<th><strong>#</strong></th>
																<th><strong>Name</strong></th>
																<th><strong>Contact</strong></th>
															</tr>
															</thead>
															<tbody>
                                                            <?php $i = 1; 	if(count($allDrivers)>0){
                                                            foreach($allDrivers as $driver){?>
															<tr onclick="show_ticked_driver_1(<?php echo $driver->id?>);">
																<th scope="row"><?php echo $i++?></th>
																<td><?php echo $driver->contact_name?></td>
																<td><?php echo $driver->phone?><i id="all-<?php echo $driver->id?>" class="fa fa-check fa-4x mb-3 select_driver" style="position:absolute;right:20px;"></i></td>
															</tr>
                                                            <?php     }
                                                            }
                                                            ?>
															</tbody>
														</table>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</fieldset>
			</form>
		</div>
		<!--Footer-->
		<div class="modal-footer back_modal_body" style="text-align:center;">
			<span class="help-block"></span>
			<span id="assign_driver_ladda"><a id="assign_submit" href="javascript:void(0)" onclick="assign_driver();"  class="btn btn-primary ladda-button" data-ui-ladda="loading"  data-spinner-size="35"  data-style="zoom-in"><span class="ladda-label">Submit</span></a></span>
			<span id="loading" style="display:none;">
							<img style="width: 43px;" src="<?php echo asset_url(); ?>/web/img/loading1.gif" alt="logo"/>
						</span>
			<input type="hidden" id="requestid" name="requestid" value="">
			<input type="hidden" id="service_type" name="service_type" value="">
			<input type="hidden" id="request_id" name="request_id" value="">
			<input type="hidden" id="driver_id" name="driver_id" value="">
			<button type="button" data-dismiss="modal" class="btn btn-secondary" >Cancel</button>
		</div>
	</div>
	<!--/.Content-->
</div>

<div class="modal fade" id="assign_tp_pop_up_responsive" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document" style="width:800px;">
		<!--Content-->
		<div class="modal-content">
			<!--Header-->
			<div class="modal-c-tabs">
				<div class="modal-header driver_header">
					<!-- Nav tabs -->
					<ul class="nav nav-tabs tabs-2 new_nav_bar" role="tablist">
						<li class="nav-item active" style="width:100%;text-align:center;">
							<a id="select_color" class="nav-link active new_nav_bar" data-toggle="tab" href="#panel9" role="tab" style="margin-right:0;border:0;padding-top:20px;"><i class="fa fa-user mr-1" style="color:#ffffff;"></i><span style="color:#ffffff;">TP Available</span></a>
						</li>
					</ul>
				</div>
			</div>
		</div>
		<!--Body-->
		<div class="modal-body back_modal_body" style="height:500px;">
			<input class="driver_search_icon" type="text" id="searching" onkeyup="searching();" placeholder="Search" style="padding-left:20px;margin-bottom:10px;width:300px;margin-left:230px;">
			<form class="form-horizontal">
				<fieldset>
					<div class="modal-body margin-top-0">
						<div class="scroller" style="height:50px" data-always-visible="1" data-rail-visible1="1">
							<div class="row">
								<div class="col-md-12">
									<div class="portlet-body form">
										<div class="form-body">
											<span class="help-block"></span>
											<div class="form-group form-md-line-input field-messageform-details"><label class="col-md-2 control-label" for="form_control_1"></label>
												<div id="tp_list" class="col-md-8">
													<div class="tab-pane fade in active" id="panel9" role="tabpanel">
														<table class="table table-hover" id="search_tp">
															<thead>
															<tr>
																<th><strong>#</strong></th>
																<th><strong>Company</strong></th>
																<th><strong>Name</strong></th>
																<th><strong>Contact</strong></th>
															</tr>
															</thead>
															<tbody>
                                                            <?php $i = 1;
                                                            if(count($dispatchers)>0){
	                                                            foreach($dispatchers as $dispatcher) {
		                                                            if($dispatcher->is_active == 1) {
                                                        	?>
																	<tr onclick="show_ticked_tp(<?php echo $dispatcher->id?>);">
																		<th scope="row"><?php echo $i++?></th>
																		<td><?php
																				$tp = $dispatcher->TransportationProvider;
																				if($tp != NULL) {
																					echo $tp->company;
																				}
																				else {
																					echo "N/A";
																				}
																			?>
																		</td>
																		<td><?php echo $dispatcher->contact_name ?></td>
																		<td><?php echo $dispatcher->phone?><i id="tp-<?php echo $dispatcher->id?>" class="fa fa-check fa-4x mb-3 select_driver" style="position:absolute;right:20px;"></i></td>
																	</tr>
		                                                	<?php	}
		                                                		}
		                                                    } ?>
															</tbody>
														</table>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</fieldset>
			</form>
		</div>
		<!--Footer-->
		<div class="modal-footer back_modal_body" style="text-align:center;">
			<a id="assign_submit" href="javascript:void(0)" onclick="assign_tp();" class="btn btn-primary" >Submit</a>
			<input type="hidden" id="requestsid" name="requestsid" value="">
			<input type="hidden" id="tp_id" name="tp_id" value="">
			<button type="button" data-dismiss="modal" class="btn btn-secondary" >Cancel</button>
		</div>
	</div>
	<!--/.Content-->
</div>

	<div class="modal fade modal1" id="complete_ride_pop_up_responsive" role="dialog">
		<div class="modal-dialog" style="margin-top:180px;">
			<div class="modal-content">
				<div class="modal-header new-modal">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title" style="color:#ffffff;">Complete the Ride</h4>
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
													<span id="error-message"></span>
													<div class="form-group form-md-line-input field-complete_form-details">
														<label class="col-md-4 control-label" for="form_control_1">Driver Name</label>

														<div class="col-md-4">
															<input class="new_input_text_field" type="text" name="driver_name" id="driver_name" value="" placeholder="" >
															<span class="end"></span>
															<input type="hidden" name="end_lat" data-geo="lat">
															<input type="hidden" name="end_lng" data-geo="lng">
															</span>
															<div class="form-control-focus"></div>

														</div>
													</div>
													<div class="form-group form-md-line-input field-complete_form-distance"><label class="col-md-4 control-label" for="form_control_1">Driver Phone</label>

														<div class="col-md-4">
															<input class="new_input_text_field" type="text" name="driver_phone" id="driver_phone" value="" >
															<div class="form-control-focus"></div>

														</div>
													</div>
													<div class="form-group form-md-line-input field-complete_form-comment"><label class="col-md-4 control-label" for="form_control_1">Comment</label>
														<div class="col-md-4">
															<textarea style="margin: 0px;width: 205px;height: 68px;" class="form-control" data-provide="markdown" id="complete_comment" name="complete_comment"></textarea>
															<div class="form-control-focus"></div>

															<input class="new_input_text_field" type="hidden" name="time" id="time" value="" >
															<input class="new_input_text_field" type="hidden" name="distance" id="distance" value="" >
															<input class="new_input_text_field" type="hidden" name="dropoffaddress" id="dropoff" value="" >


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
								<input type="hidden" id="comrequest_id" name="comrequest_id" value="">
								<button type="button" data-dismiss="modal" class="btn default" >Cancel</button>
							</div>
						</fieldset>
					</form>
				</div>
			</div>
		</div>
	</div>

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

<div class="modal fade modal1" id="cancel_reason_pop_up_responsive" role="dialog">
	<div class="modal-dialog" style="margin-top:180px;">
		<div class="modal-content">
			<div class="modal-header new-modal">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title" style="color:#ffffff;">Please provide the cancel reason</h4>
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
									<div class="form-group form-md-line-input field-messageform-details"><label class="col-md-3 control-label" for="form_control_1"><span style="color:#000;">Cancel Reason</span></label>
										<span class="help-block"></span>
										<div class="col-md-12">
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
					</fieldset>
		</form>
			</div>
		</div>
	</div>
</div>

	<div class="modal fade modal1" id="card_alert_pop_up_responsive" role="dialog">
		<div class="modal-dialog" style="margin-top:180px;">
			<div class="modal-content">
				<div class="modal-header new-modal">
					<h4 class="modal-title" style="color:#ffffff;">Please Provide card details.</h4>
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
															<span style="color:#000;font-size: 18px;text-align: center;text-align: center;">Please add credit card details first.</span></label>
														<span class="help-block"></span>
														<div id="cardlink" class="col-md-12">

														</div>
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
<div class="box box-info tbl-box" style="padding-bottom: 300px;">
    <div align="left" id="paglink"><?php echo $walks->appends(array('type' => Session::get('type'), 'valu' => Session::get('valu')))->links(); ?></div>
 <div class="row white_bg">   
	<table class="table table-bordered">
        <tbody>
            <tr>
                <th>Request ID</th>
                <th style="width:125px;">Action</th>
				<th>Client</th>
				<th>Agent Name</th>
                <th>Passenger</th>
                <th>Passenger Phone</th>
				<th>Estimated Cost</th>
				<th>Pick up</th>
				<th>Drop off</th>
                <th>Service</th>
				<?php 	if($admin == 1) { ?>
							<th>Status</th>
							<th>Transportation Provider (TP)</th>
				<?php   } ?>
                <th>Date/Time</th>
            </tr>
            <?php $i = 0; ?>

            <?php foreach ($walks as $walk) {

					//$adjustment = $walk->additional_fee - $walk->promo_payment;
			?>
                <tr>
                    <td><?= $walk->id ?></td>
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-flat btn-info dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown">
                                Actions
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">

								<li role="presentation"><a role="menuitem" id="map" tabindex="-1" target="_blank" href="/driverlocation/map/<?php echo $walk->id?>">View Map</a></li>


                                <?php 	if($admin == 1) { ?>
											<li role="presentation"><a role="menuitem" id="assigntp1" tabindex="-1" href="javascript:void(0)" onclick="mark_manual('<?php echo $walk->id ?>');">Mark Manual</a></li>
											<li role="presentation"><a role="menuitem" id="addcreditcard" tabindex="-1" href="/dispatcher/addeditcarddetails/<?php echo $walk->id ?>">Add/Edit Card Details</a></li>
								<?php 		} ?>

								<?php 	if($admin == 1) {
											if (($walk->assigned_dispatcher_id=='' || $walk->assigned_cancel_status==1) && $walk->current_walker==0 && $walk->is_cancelled == 0 && $walk->is_confirmed==0 && $walk->is_completed == 0 && $walk->is_walker_started == 0) {
											    if($walk->payment_mode>0 || ($walk->paymentid)){	?>
													<li role="presentation"><a role="menuitem" id="assigntp1" tabindex="-1" href="javascript:void(0)" onclick="show_assign_tp('<?php echo $walk->id ?>');">Assign to a TP</a></li>
								<?php 			}else{ ?>
													<li role="presentation"><a role="menuitem" id="cardalert" tabindex="-1" href="javascript:void(0)" onclick="show_card_alert('<?php echo $walk->id ?>');">Assign to a TP</a></li>
								<?php			}
											}
											if(($walk->assigned_dispatcher_id=='' || $walk->assigned_cancel_status==1) && $walk->current_walker==0 && $walk->is_cancelled==0 && $walk->is_confirmed==0 && $walk->is_completed == 0) {
												//if($walk->payment_mode>0 || ($walk->paymentid)){ ?>
													<li role="presentation"><a role="menuitem" id="assigndriver1" tabindex="-1" href="javascript:void(0)" onclick="show_assign_driver('<?php echo $walk->id ?>','<?php echo $walk->service_type ?>');show_confirm_box('<?php echo $walk->id ?>','<?php echo $walk->total ?>');">Assign to a Driver</a></li>
                                <?php 			/*}else{ */ ?>
<!--													<li role="presentation"><a role="menuitem" id="cardalert" tabindex="-1" href="javascript:void(0)" onclick="show_card_alert('<?php echo $walk->id ?>');">Assign to a Driver</a></li>
-->								<?php			//}
											}
                               	 			if ($walk->is_cancelled == 0 && $walk->is_completed == 0 && $walk->is_walker_started == 0) { ?>
												<li role="presentation"><a role="menuitem" id="cancelride1" tabindex="-1" href="javascript:void(0)" onclick="check_cancel_reason('<?php echo $walk->id ?>');">Cancel Ride</a></li>
           						<?php		}
           						 		} else{
                                    		if($walk->is_cancelled==0 && $walk->current_walker==0 && $walk->is_confirmed==0 && $walk->is_completed == 0) {?>
												<li role="presentation"><a role="menuitem" id="assigndriver1" tabindex="-1" href="javascript:void(0)" onclick="show_assign_driver('<?php echo $walk->id ?>','<?php echo $walk->service_type ?>');">Assign to a Driver</a></li>
                                <?php 		}
                                    		if ($walk->is_cancelled == 0 && $walk->is_completed == 0 && $walk->is_walker_started == 0) { ?>
												<li role="presentation"><a role="menuitem" id="cancelride1" tabindex="-1" href="javascript:void(0)" onclick="check_cancel_reason('<?php echo $walk->id ?>');">Cancel Ride</a></li>
                                <?php		}
										}
								?>
									<li role="presentation"><a role="menuitem" id="other_ride_details" tabindex="-1" href="javascript:void(0)" onclick="show_other_ride_details('<?php echo $walk->oxygen_mask ?>','<?php echo $walk->user_height ?>','<?php echo $walk->user_weight ?>','<?php echo $walk->user_condition ?>','<?php echo $walk->respirator ?>','<?php echo $walk->any_tubing ?>','<?php echo $walk->colostomy_bag ?>','<?php echo $walk->any_attachments ?>','<?php echo $walk->attendant_travelling ?>');">Other Ride Details </a></li>

                            </ul>
                        </div>  

                    </td>
					<td><?php
                        if ($walk->provider_company!='') {
                            $string = $walk->provider_company;
                            if(strlen($walk->provider_name) > 0) {
                            	$string = $string . "-" . $walk->provider_name;
                            }
                            echo $string;
                        }
                        else {
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
                    <td><?php 	if($walk->passenger_contact_name!=''){ echo $walk->passenger_contact_name;
                    			}
						?>
					</td>
                    <td><?php 	if($walk->passenger_phone!=''){
                        			echo $walk->passenger_phone;
                        		} else{
                            		echo $walk->passenger_phone;
                        		}
                        ?>
					</td>
					<td><?php echo "$".sprintf2($walk->total, 2); ?></td>
					<td><?php echo $walk->src_address ?></td>
					<td><?php echo $walk->dest_address ?></td>
					<td><?php echo $walk->name ?></td>
                    <?php 	if($admin == 1) { ?>
								<td><?php
									if ($walk->tp_status==1) {
										echo "Assigned to TP";
									}elseif($walk->current_walker!=0){
                                        echo "Assigned to Driver";
									} else {
										echo "Yet not Assigned";
									}
									?>
								</td>
								<td><?php
									if ($walk->assigned_contact_name && $walk->assigned_cancel_status==0) {
										echo $walk->assigned_contact_name;
									} else {
										echo "NA";
									}
									?>
								</td>
                    <?php   } ?>

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
                </tr>
            <?php } ?>
        </tbody>
    </table>
	</div>
    <div align="left" id="paglink"><?php echo $walks->appends(array('type' => Session::get('type'), 'valu' => Session::get('valu')))->links(); ?></div>

</div>

<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB6NA_AMOEp8CiAxEZPWf_PTyy4v6xKvdA&libraries=places"></script>
<script src="https://ubilabs.github.io/geocomplete/jquery.geocomplete.js"></script>

<script type="text/javascript">
	function show_card_alert(requestid){
        $.ajax({
            type: "POST",
            url:'<?php echo URL::Route('getcardlink') ?>',
            data:{request_id:requestid},
            success: function(data) {
                console.log(data);
                $( "#cardlink" ).html(data);
                $( "#card_alert_pop_up" ).trigger( "click" );
            }
        });

	}
    function show_confirm_box(requestid,total_cost){
            document.getElementById('request_id').value = requestid;
            document.getElementById('total_cost').value = total_cost;

    }
    function total_cost_content() {
        document.getElementById('amount_click').style.display = "none";
        document.getElementById("total_cost").style.textAlign = "left";
    }

    function total_cost_show_content() {
        document.getElementById('amount_click').style.display = "";
        document.getElementById("total_cost").style.textAlign = "center";
    }


    $(function () {
        $( "div.iradio_minimal" ).removeClass("disabled");
        //$('#notification-count').hide();
        var elements = document.getElementsByClassName('select_driver');
        for(var i=0; i<elements.length; i++) {
            elements[i].style.display='none';
        }
        document.getElementById('available_driver').style.background = "#6a00bc";
        document.getElementById('text_color_1').style.color = "#ffffff";
        document.getElementById('text_color_2').style.color = "#000";
        document.getElementById('text_color_3').style.color = "#ffffff";
        document.getElementById('text_color_4').style.color = "#000";
        document.getElementById('select_color').style.background = "#6a00bc";
        document.getElementById('panel8').style.display = "none";
        document.getElementById('panel7').style.display = "";
	});

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

function show_assign_driver(requestid,service_type){
    var js_var = service_type;
    var pl = "success";
    $('#assigndriver').addClass("disabled");
    document.getElementById('requestid').value = requestid;
    document.getElementById('service_type').value = service_type;

    $.ajax({
        type: "POST",
        url:'<?php echo URL::Route('getservicespecificdrivers') ?>',
        data:{service_type:service_type},
        success: function(data) {
            console.log(data);
            var dl = $('#driver_list');
            console.log(dl);
            $('#driver_list').html(data);
            $( "#assign_driver_pop_up" ).trigger( "click" );
        }
    });
    //
}

function show_assign_tp(requestid){
    $('#assigntp').addClass("disabled");
    document.getElementById('requestsid').value = requestid;
    $( "#assign_tp_pop_up" ).trigger( "click" );
}
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

function assign_driver(){
    //alert($("#driver_id").val();
	var k = $("#est_time").val();
	console.log(k);
    if($("#driver_id").val()<=0 || typeof $("#driver_id").val() === "undefined" || $("#est_time").val() ===''){
        $(".help-block").html('<span style="text-align: center;font-size:15px;color: #f56954;">Please select a Driver and Enter estimated time</span>');
    } else{
        $('#assign_submit').addClass("disabled");
//        Ladda.create( document.querySelector( '.assign_driver_ladda' ) ).start();
        $('#loading').show();
        $.ajax({
            type: "POST",
            url:'<?php echo URL::Route('assigndriver') ?>',
            data:{request_id:$("#requestid").val(),driver_id:$("#driver_id").val(),total_cost:document.getElementById('total_cost').value,est_time:document.getElementById('est_time').value},
            success: function(data) {
                console.log(data);
                $('#assign_driver_pop_up_responsive').modal('hide');
                location.reload();
            }
        });
    }
}

function assign_tp(){
    //alert($("#driver_id").val());
    if($("#tp_id").val()<=0 || typeof $("#tp_id").val() === "undefined"){
        $(".help-block").html('<span style="text-align: center;font-size:15px;color: #f56954;">Please select a tp</span>');
    } else{
        $('#assign_tp_submit').addClass("disabled");
        $.ajax({
            type: "POST",
            url:'<?php echo URL::Route('assigntp') ?>',
            data:{request_id:$("#requestsid").val(),tp_id:$("#tp_id").val()},
            success: function(data) {
                console.log(data);
                $('#assign_tp_pop_up_responsive').modal('hide');
                location.reload();
            }
        });
    }
}

function mark_manual(requestid){
	$('#ride_completed').addClass("disabled");
	document.getElementById('comrequest_id').value = requestid;

	$( "#complete_ride_pop_up" ).trigger( "click" );
}

    function walker_ride_complete(){
        if(document.getElementById('driver_name').value=='' || document.getElementById('driver_phone').value=='' || document.getElementById('complete_comment').value==''){
            $("#error-message").html('<span style="text-align: center;font-size:15px;color: #f56954;">Please fill all the details.</span>');
        } else{
            $('#complete_submit').addClass("disabled");
            $.ajax({
                type: "POST",
                url:'<?php echo URL::Route('DispatcherManualRideConfirmed') ?>',
                data:{request_id:document.getElementById('comrequest_id').value,comment:document.getElementById('complete_comment').value,driver_name:document.getElementById('driver_name').value,driver_phone:document.getElementById('driver_phone').value},
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

function show_ticked_driver(selected) {
    var select = <?php echo json_encode($available_drivers)?>;
    var k;
    console.log(selected);
    select.forEach(function(entry) {
        //console.log(entry);
        if(parseInt(entry.is_active) === 1) {
            if (selected === parseInt(entry.id)) {
                k = entry.id;
                $('#driver_id').val(k);
            }
            else {
                document.getElementById('available-' + entry.id).style.display = "none";
            }
        }
    });

    if(typeof(k) !== "undefined") {
	    document.getElementById('available-' + k).style.display = "";
	}
}

function show_ticked_driver_1(selected) {
    var select = <?php echo json_encode($allDrivers)?>;
    var k;
    console.log(selected);
    select.forEach(function(entry) {
        //console.log(entry);

            if (selected === parseInt(entry.id)) {
                k = entry.id;
                $('#driver_id').val(k);
            }
            else {
                document.getElementById('all-' + entry.id).style.display = "none";
            }

    });
    if(typeof(k) !== "undefined") {
	    document.getElementById('available-' + k).style.display = "";
	}
}

function show_tab_1() {
    document.getElementById('panel8').style.display = "none";
    document.getElementById('panel7').style.display = "";
    document.getElementById('text_color_1').style.color = "#ffffff";
    document.getElementById('text_color_2').style.color = "#000";
    document.getElementById('text_color_3').style.color = "#ffffff";
    document.getElementById('text_color_4').style.color = "#000";
    document.getElementById('available_driver').style.background = "#6a00bc";
    document.getElementById('all_driver').style.background = "#fbf8f8";
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

    function show_tab_2() {
        document.getElementById('panel7').style.display = "none";
        document.getElementById('panel8').style.display = "";
        document.getElementById('text_color_2').style.color = "#ffffff";
        document.getElementById('text_color_1').style.color = "#000";
        document.getElementById('text_color_4').style.color = "#ffffff";
        document.getElementById('text_color_3').style.color = "#000";
        document.getElementById('all_driver').style.background = "#6a00bc";
        document.getElementById('available_driver').style.background = "#fbf8f8";
    }
    function show_ticked_tp(selected) {
        var select = <?php echo json_encode($dispatchers)?>;
        var k;
        console.log(selected);
        select.forEach(function(entry) {
            //console.log(entry);
			if(parseInt(entry.is_active) === 1) {
	            if (selected === parseInt(entry.id)) {
                    k = entry.id;
                    $('#tp_id').val(k);
                }
                else {
                    document.getElementById('tp-' + entry.id).style.display = "none";
                }
            }
        });

        document.getElementById('tp-'+k).style.display = "";

    }
    function search() {
        // Declare variables
        var input, filter, table, tr, td, i;
        input = document.getElementById("search");
        filter = input.value.toUpperCase();
        table = document.getElementById("search_drivers");
        tr = table.getElementsByTagName("tr");

        // Loop through all table rows, and hide those who don't match the search query
        for (i = 0; i < tr.length; i++) {
            td = tr[i].getElementsByTagName("td")[0];
            if (td) {
                if (td.innerHTML.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    }
    function searchs() {
        // Declare variables
        var input, filter, table, tr, td, i;
        input = document.getElementById("search");
        filter = input.value.toUpperCase();
        table = document.getElementById("search_drivers_1");
        tr = table.getElementsByTagName("tr");

        // Loop through all table rows, and hide those who don't match the search query
        for (i = 0; i < tr.length; i++) {
            td = tr[i].getElementsByTagName("td")[0];
            if (td) {
                if (td.innerHTML.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    }
    function searching() {
        // Declare variables
        var input, filter, table, tr, td, i;
        input = document.getElementById("searching");
        filter = input.value.toUpperCase();
        table = document.getElementById("search_tp");
        tr = table.getElementsByTagName("tr");

        // Loop through all table rows, and hide those who don't match the search query
        for (i = 0; i < tr.length; i++) {
            td = tr[i].getElementsByTagName("td")[0];
            if (td) {
                if (td.innerHTML.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    }
</script>
@stop
