@extends('layout')

@section('content')

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

<!-- form start -->
<form method="post" id="main-form" autocomplete="off" action="{{ URL::Route('EnterpriseClientProfileSave') }}">
	<input type="hidden" name="id" value="<?= $client->id ?>">
	<input type="hidden" name="rate_profile_count" value="">
	<input type="hidden" name="funding_profile_count" value="">

	<section id="rate-profile">
		<!-- Quoted Rates -->
		<div class="box box-primary">
			<section class="content-header">
				<div class="relationstate0 display-none float-right">
					<button type="button" class="add-default-rate-profile-rules btn btn-default">Add Default Rules</button>
					<button type="button" class="add-rate-profile-rule btn btn-default">Add Rule</button>
				</div>
				<div class="relationstate1 display-none float-right">
					<button type="button" class="edit-rate-profile btn btn-default">Edit</button>
				</div>
				<div class="relationstate2 display-none float-right">
					<button type="button" class="add-rate-profile-rule btn btn-default">Add Rule</button>
				</div>
				<h2>Rate Profile</h2>
				<div class="clear"></div>
				<div class="text-center">
					<h4 class="relationstate0 display-none">Rate Profile is currently empty</h4>
				</div>
			</section>
			<div class="text-center" id="rate-profile-quote">
				<table class="table table-auto relationstate1">
					<thead>
						<tr>
							<th>Type</th>
							<th>Base Fare</th>
							<th>Per Mile</th>
							<th>Included Miles</th>
							<th>Deadhead Per Mile</th>
							<th>Deadhead Included Miles</th>
							<th>Wait Time Per Minute</th>
							<th>Included Wait Time</th>
						</tr>
					</thead>
					<tbody>
						<?php
							$i = 0;
							foreach ($client->RateProfile as $rateprofile) { ?>
								<tr>
									<td class="text-left">
										<span id="readonly_service_type{{ $i }}">
											<?php echo $rateprofile->service_type_text(); ?> / Client
										</span>
									</td>
									<td class="text-right even">
										<span id="readonly_quoted_base_fare{{ $i }}">
											<?php echo sprintf("%0.2f", $rateprofile->quoted_base_fare()); ?>
										</span>
									</td>
									<td class="text-right">
										<span id="readonly_quoted_per_mile{{ $i }}">
											<?php echo sprintf("%0.2f", $rateprofile->quoted_per_mile()); ?>
										</span>
									</td>
									<td class="text-right even">
										<span id="readonly_quoted_included_mileage{{ $i }}">
											<?php echo $rateprofile->quoted_included_mileage(); ?>
										</span>
									</td>
									<td class="text-right">
										<span id="readonly_quoted_deadhead_per_mile{{ $i }}">
											<?php echo $rateprofile->quoted_deadhead_per_mile(); ?>
										</span>
									</td>
									<td class="text-right even">
										<span id="readonly_quoted_deadhead_included_mileage{{ $i }}">
											<?php echo $rateprofile->quoted_deadhead_included_mileage(); ?>
										</span>
									</td>
									<td class="text-right">
										<span id="readonly_quoted_wait_time_per_minute{{ $i }}">
											<?php echo $rateprofile->quoted_wait_time_per_minute(); ?>
										</span>
									</td>
									<td class="text-right even">
										<span id="readonly_quoted_wait_time_included{{ $i }}">
											<?php echo $rateprofile->quoted_wait_time_included(); ?>
										</span>
									</td>
								</tr>
								<tr>
									<td class="text-left">
										<span id="readonly_service_type{{ $i }}">
											<?php echo $rateprofile->service_type_text(); ?> / TP
										</span>
									</td>
									<td class="text-right even">
										<span id="readonly_quoted_tpcost_base_fare{{ $i }}">
											<?php echo sprintf("%0.2f", $rateprofile->quoted_tpcost_base_fare()); ?>
										</span>
									</td>
									<td class="text-right">
										<span id="readonly_tpcost_quoted_per_mile{{ $i }}">
											<?php echo sprintf("%0.2f", $rateprofile->quoted_tpcost_per_mile()); ?>
										</span>
									</td>
									<td class="text-right even">
									</td>
									<td class="text-right">
										<span id="readonly_quoted_tpcost_deadhead_per_mile{{ $i }}">
											<?php echo $rateprofile->quoted_tpcost_deadhead_per_mile(); ?>
										</span>
									</td>
									<td class="text-right even">
									</td>
									<td class="text-right">
										<span id="readonly_quoted_tpcost_wait_time_per_minute{{ $i }}">
											<?php echo $rateprofile->quoted_tpcost_wait_time_per_minute(); ?>
										</span>
									</td>
									<td class="text-right even">
									</td>
								</tr>
						<?php
								$i += 1;
							} ?>
					</tbody>
				</table>
			</div>
			<div class="text-center" id="rate-profile-edit">
				<table class="table table-auto display-none relationstate2">
					<thead>
						<tr>
							<th>Type</th>
							<th class="text-center">Base Fare</th>
							<th class="text-center">Per Mile</th>
							<th class="text-center">Included Miles</th>
							<th class="text-center">Deadhead Per Mile</th>
							<th class="text-center">Deadhead Included Miles</th>
							<th class="text-center">Wait Time Per Minute</th>
							<th class="text-center">Included Wait Time</th>
							<th class="text-center">Delete</th>
						</tr>
					</thead>
					<tbody>
						<?php
							$i = 0;
							foreach ($client->RateProfile as $rateprofile) {
								$path = app_path() . '/views/admin/enterprise/rateprofile.blade.php';
								include $path;
								$i += 1;
							} 
						?>
					</tbody>
				</table>
			</div>
		</div>
	</section>

	<section id="funding-profile">
		<!-- Funding Profile -->
		<div class="box box-primary" id="funding-profile-display">
			<section class="content-header">
				<div class="relationstate0 display-none float-right">
					<button type="button" class="add-default-funding-profile-rules btn btn-default">Add Default Rule</button>
					<button type="button" class="add-funding-profile-rule btn btn-default">Add Rule</button>
				</div>
				<div class="relationstate1 display-none float-right">
					<button type="button" class="edit-funding-profile btn btn-default">Edit</button>
				</div>
				<div class="relationstate2 display-none float-right">
					<button type="button" class="add-funding-profile-rule btn btn-default">Add Rule</button>
				</div>
				<h2>Funding Profile</h2>
				<div class="clear"></div>
				<div class="text-center">
					<h4 class="relationstate0 display-none">Funding Profile is currently empty</h4>
				</div>
			</section>
			<div class="text-center" id="funding-profile-display">
				<table class="table table-auto display-none relationstate1">
					<thead>
						<tr>
							<th>Rule</th>
							<th>Payment Type</th>
							<th>Portion</th>
							<th>Paid By</th>
						</tr>
					</thead>
					<tbody>
						<?php
							$i = 0;
							foreach ($client->FundingProfile as $fundingprofile) { ?>
								<tr>
									<td class="text-left">
										<span>
											<?php echo $fundingprofile->funding_rule_text(); ?>
										</span>
									</td>
									<td class="text-left">
										<span>
											<?php echo $fundingprofile->payment_type_text(); ?>
										</span>
									</td>
									<td class="text-right">
										<span>
											<?php echo $fundingprofile->amount_text() ?>
										</span>
									</td>
									<td class="text-left">
										<span>
											<?php
												$billclient = $fundingprofile->BillEnterpriseClient;
												if(! is_null($billclient)) {
													$the_id = $fundingprofile->enterpriseclient_id;
													echo $billclient->PartialDetails($the_id);
												}
												else {
													echo "Passenger";
												}
											?>
										</span>
									</td>
								</tr>
						<?php
								$i += 1;
							} 
						?>
					</tbody>
				</table>
			</div>	
			<div class="text-center" id="funding-profile-edit">
				<table class="table table-auto display-none relationstate2">
					<thead>
						<tr>
							<th>Rule</th>
							<th>Payment Type</th>
							<th>Portion</th>
							<th>Paid By</th>
							<th>Delete</th>
						</tr>
					</thead>
					<tbody>
						<?php
							$i = 0;
							foreach ($client->FundingProfile as $fundingprofile) {
								$path = app_path() . '/views/admin/enterprise/fundingprofile.blade.php';
								include $path;
								$i += 1;
							} 
						?>
					</tbody>
				</table>
			</div>
		</div>
	</section>

	<div class="box box-primary">
		<section class="content-header">
			<h2>Contact Information</h2>
		</section>
		<div class="box-body">
			<div class="form-group">
				<label>Company Name</label>
				<input class="form-control" type="text" name="company" value="<?= $client->company ?>" placeholder="Company Name">
			</div>

			<div class="form-group">
				<label>Contact Name</label>
				<input class="form-control" type="text" name="contact_name" value="<?= $client->contact_name ?>" placeholder="Contact Name" >
			</div>

			<div class="form-group">
				<label>Email</label>
				<p><a href="mailto:<?= $client->email ?>"><?= $client->email ?></a></p>
			</div>

			<div class="form-group">
				<label>Phone</label>
				<input class="form-control" type="text" name="operator_phone" value="<?= $client->operator_phone ?>" placeholder="Phone">
			</div>

			<div class="form-group">
				<label>Address</label>
				<textarea class="form-control" name="address" placeholder="Address" style="min-height: 100px;"><?= $client->address ?></textarea>
			</div>
	        <div class="form-group">
	            <label>Approved</label>
	            <div class="clear"></div>
	            <input type="radio" name="is_active" value="0" id="is_active_no"<?php if($client->is_active == 0) { echo ' checked';} ?>>
	            <label for="is_active_no">No</label>
	            <input type="radio" name="is_active" value="1" id="is_active_yes"<?php if($client->is_active == 1) { echo ' checked';} ?>>
	            <label for="is_active_yes">Yes</label>
	        </div>
		</div>


		<div class="box-footer" style="text-align: right">
			<button type="submit" class="btn btn-primary btn-flat">Update Profile</button>
		</div>
	</div>
</form>

<script>

var rate_profile_count = {{ $client->RateProfile->count() }};
var funding_profile_count = {{ $client->FundingProfile->count() }};
var enterpriseclient_id = {{ $client->id }};

$(document).ready(function() {
	if(rate_profile_count == 0) {
		selectState("#rate-profile", 0);
	}
	else {
		selectState("#rate-profile", 1);
	}

	$(".add-default-rate-profile-rules").click(function() {
		var baseurl = "/admin/enterpriseclient/rateprofile/";
		var url1 = baseurl + (rate_profile_count++) + "/" + enterpriseclient_id + "/2"
		var url2 = baseurl + (rate_profile_count++) + "/" + enterpriseclient_id + "/3"
		var url3 = baseurl + (rate_profile_count++) + "/" + enterpriseclient_id + "/4"
		$.get(url1, function(data, status, xhr) {
			$("#rate-profile-edit tbody").append(data);
			$.get(url2, function(data, status, xhr) {
				$("#rate-profile-edit tbody").append(data);
				$.get(url3, function(data, status, xhr) {
					$("#rate-profile-edit tbody").append(data);
					selectState("#rate-profile", 2);
				});
			});
		});
	});

	$(".add-rate-profile-rule").click(function() {
		var baseurl = "/admin/enterpriseclient/rateprofile/";
		var url1 = baseurl + (rate_profile_count++) + "/" + enterpriseclient_id + "/0"
		$.get(url1, function(data, status, xhr) {
			$("#rate-profile tbody").append(data);
			selectState("#rate-profile", 2);
		});
	});

	$(".edit-rate-profile").click(function() {
		selectState("#rate-profile", 2);
	});

	if(funding_profile_count == 0) {
		selectState("#funding-profile", 0);
	}
	else {
		selectState("#funding-profile", 1);
	}

	$(".edit-funding-profile").click(function() {
		selectState("#funding-profile", 2);
	});

	var checkboxes = "#rate-profile input[type=checkbox], #funding-profile input[type=checkbox]";
	$(checkboxes).prop("checked", false);

	$(".add-default-funding-profile-rules").click(function() {
		var baseurl = "/admin/enterpriseclient/fundingprofile/";
		var url1 = baseurl + (funding_profile_count++) + "/" + enterpriseclient_id + "/2"
		$.get(url1, function(data, status, xhr) {
			$("#funding-profile-edit tbody").append(data);
			selectState("#funding-profile", 2);
		});
	});

	$(".add-funding-profile-rule").click(function() {
		var baseurl = "/admin/enterpriseclient/fundingprofile/";
		var url1 = baseurl + (funding_profile_count++) + "/" + enterpriseclient_id + "/0"
		$.get(url1, function(data, status, xhr) {
			$("#funding-profile tbody").append(data);
			selectState("#funding-profile", 2);
		});
	});

	updateProfileCounts();
});

function updateProfileCounts() {
	$("input[name=rate_profile_count]").val(rate_profile_count);
	$("input[name=funding_profile_count]").val(funding_profile_count);
	var checkboxes = "#rate-profile input[type=checkbox], #funding-profile input[type=checkbox]";
	$(checkboxes).unbind('ifChanged').bind('ifChecked', function() {
		var id = $(this).attr("data-displayid");
		if($(this).is(":checked")) {
			$(id).html("X");
		}
		else {
			$(id).html("");
		}
	});
}

function selectState(idstring, state) {
	var i;
	for(i = 0; i < 3; i++) {
		var el = $(idstring + " .relationstate" + i);
		if(i == state) {
			showdiv(el);
		}
		else {
			hidediv(el);
		}
	}

	updateProfileCounts();
}

function hidediv(el) {
	el.removeClass("inline-block").removeClass("display-none").addClass("display-none");
}

function showdiv(el) {
	el.removeClass("inline-block").removeClass("display-none").addClass("inline-block");
}

</script>

@stop