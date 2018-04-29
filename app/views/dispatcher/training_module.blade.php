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

<div class="tbl-box new-box">
 <div class="row white_bg new-row">
	<table class="table table-bordered">
        <tbody>
            <tr>
                <th style="width:10%;">Training ID</th>
				<th style="width:20%;">Training Session</th>
                <th style="width:20%;">Action</th>
            </tr>
            <?php foreach ($learningmodules as $modules) { ?>
					<tr>
						<td><?= $modules->id ?></td>
						<td><?php echo $modules->category;?></td>
						<td>
							<div class="dropdown">
								<button class="btn btn-flat btn-info dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" style="width:100px;">
									Actions
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
									<li role="presentation"><a role="menuitem" id="map" tabindex="-1" target="_blank" href="/dispatcher/trainingmodule/enrolldrivers/<?php echo $modules->id ?>">Enroll Drivers</a></li>
                                    <li role="presentation"><a role="menuitem" id="map" tabindex="-2" target="_blank" href="/dispatcher/trainingmodule/driverlisting/<?php echo $modules->id ?>">Enrolled Drivers List</a></li>
								</ul>
							</div>

						</td>
					</tr>
            <?php } ?>
        </tbody>
    </table>
 </div>
</div>
@stop
