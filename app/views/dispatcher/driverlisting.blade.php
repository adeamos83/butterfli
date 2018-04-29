@extends('dispatcher.layout')

@section('content')

<div class="row white_bg no_bg">
<div class="col-md-5 col-sm-12 white_bg_panel">

    <div class="box box-danger">

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

<div class="box box-info tbl-box">
 <div class="row white_bg" id="driverlisting">
	<table class="table table-bordered">
        <tbody>
            <tr>
                <th>Driver Name</th>
                <th>Driver Phone</th>
                <th>Action</th>
            </tr>

                <?php   if(count($drivers)>0){
                            foreach ($drivers as $driver) { ?>
                                <tr>
                                    <td><?= $driver->contact_name ?></td>
                                    <td><?php echo $driver->phone;?></td>
                                    <td>
                                        <a role="menuitem" id="map" tabindex="-2" class="btn btn-primary" href="/dispatcher/trainingmodule/deletedriver/<?php echo $module_id ?>/<?php echo $driver->id ?>">Delete</a>
                                    </td>
                                </tr>
            <?php           }
                        }
            ?>
        </tbody>
    </table>
 </div>
</div>
@stop
