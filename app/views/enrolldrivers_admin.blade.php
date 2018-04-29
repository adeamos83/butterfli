@extends('layout')

@section('content')

<div class="row white_bg no_bg">
<div class="col-md-5 col-sm-12 white_bg_panel">

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
                <th style="text-align: center">Driver Name</th>
                <th>Driver Phone</th>
                <th>Enroll</th>
            </tr>

                <?php   foreach ($drivers as $driver) { ?>
                            <tr>
                                <td style="text-align: center"><?= $driver->contact_name ?></td>
                                <td><?php echo $driver->phone;?></td>
                                <td>
                                    <input  type="checkbox" onclick="enroll_driver('<?php echo $driver->id ?>','<?php echo $module_id ?>');" id="driver_id" value="<?php echo $driver->id ?>">
                                </td>
                            </tr>
                <?php   } ?>
        </tbody>
    </table>
 </div>
</div>
    <script type="text/javascript">
        function enroll_driver(driver_id,module_id){
            alert("hi");
            if(driver_id>0){
                $.ajax({
                    type: "POST",
                    url:'<?php echo URL::Route('AddDriversAdmin') ?>',
                    data:{driver_id:driver_id,module_id:module_id},
                    success: function(data) {
                        console.log(data);
                        if (data != '') {
                            $('#driverlisting').html(data);
                        }
                    }
                });
            }
        }
    </script>
@stop
