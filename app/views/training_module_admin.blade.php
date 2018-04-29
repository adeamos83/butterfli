@extends('layout')

@section('content')
	<script src="<?php echo asset_url(); ?>/web/js/jstz.min.js"></script>


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

<div class="box box-info tbl-box">
 <div class="row white_bg">   
	<table class="table table-bordered">
        <tbody>
            <tr>
                <th style="text-align: center">Training ID</th>
				<th>Training Session</th>
                <th>Action</th>
            </tr>
            <?php foreach ($learningmodules as $modules) { ?>
					<tr>
						<td style="text-align: center"><?= $modules->id ?></td>
						<td><?php echo $modules->category;?></td>
						<td>
							<div class="dropdown">
								<button class="btn btn-flat btn-info dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" style="width:100px;">
									Actions
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1" style="left: 0px;">
                                    <li role="presentation"><a role="menuitem" id="map" tabindex="-2" target="_blank" href="/admin/trainingmodule/driverlisting/<?php echo $modules->id ?>">Drivers List</a></li>
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
