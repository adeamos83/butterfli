@extends('enterpriseclient.layout')

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

	<div align="center" id="receipts"></div>

	<form role="form" method="get" action="{{ URL::Route('DownloadReport') }}">
		<div class="box-footer">
			<button type="submit" name="submit" class="btn btn-primary" value="Download_Report">Download Report</button><br/><br/>
		</div>
	</form>
	<div class="box box-info tbl-box">
		<div align="left" id="paglink"><?php echo $docs->appends(array('type' => Session::get('type'), 'valu' => Session::get('valu')))->links(); ?></div>
		<div class="row white_bg">
			<table class="table table-bordered">
				<tbody>
				<tr>
					<th>Request ID</th>
					<th>{{ trans('customize.User');}} Name</th>
					<th>{{ trans('customize.Provider');}} Name</th>
					<th>Hospital Provider</th>
					<th>Date/Time</th>
					<th style="width:16%;">Action</th>
				</tr>
                <?php $i = 0; ?>

                <?php foreach ($docs as $doc) { ?>
				<tr>
					<td><?= $doc->requestid ?></td>
					<td><?php echo $doc->owner_contact_name; ?> </td>
					<td>
                        <?php
                        if ($doc->driver_name) {
                            echo $doc->driver_name;
                        } else {
                            echo "Un Assigned";
                        }
                        ?>
					</td>
					<td><?php echo $doc->provider_name;?></td>
					<td id= 'time<?php echo $i; ?>' >
						<script>
                            var tz = jstz.determine();
                            //alert(tz.name());
                            var timevar = moment.utc("<?php echo $doc->date; ?>");
                            var format = 'MMMM Do YYYY, h:mm:ss a';
                            var datetime = moment(timevar).tz(tz.name()).format(format);
                            document.getElementById("time<?php echo $i; ?>").innerHTML = datetime;
                            <?php $i++; ?>
						</script>
					</td>
					<td>
						<div class="dropdown">
							<?php  	if($doc->is_confirmed==1 && $doc->document_url!='') {?>
										<a download class='badge bg-green' role="menuitem" id="document" tabindex="-1" href="<?php echo $doc->document_url ?>">Download Receipts</a>
                            <?php   } ?>

						</div>

					</td>
				</tr>
                <?php } ?>
				</tbody>
			</table>
		</div>
		<div align="left" id="paglink"><?php echo $docs->appends(array('type' => Session::get('type'), 'valu' => Session::get('valu')))->links(); ?></div>


	</div>

	<script type="text/javascript">
	</script>
@stop