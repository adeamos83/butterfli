@extends('dispatcher.layout')

@section('content')
    <div class="col-md-13 mt">
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


        <!--div class="content-panel">
            <form id="form1" class="form-horizontal style-form" method="post" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label style="text-align:center;" class="col-sm-6 col-sm-6 control-label">Upload CSV for Enroll Drivers</label>

                    <div class="col-sm-2">
                        <input style="border:none;margin-top: 4px;" type="file" class="form-control" accept=".csv" id="csvdriver" name="csvdriver" >
                    </div>
                    <div class="col-sm-2">
                        <input type="hidden" id="module_id" name="module_id" value="">
                        <input style="border:none;" type="submit" class="btn btn-primary" name="submit" value="Upload CSV" >
                    </div>
                </div>
            </form>
        </div-->
    </div>
    <br/>
<div class="box box-info tbl-box">
 <div class="row white_bg" id="driverlisting">
	<table class="table table-bordered">
        <tbody>
            <tr>
                <th>Driver Name</th>
                <th>Driver Phone</th>
                <th>Enroll</th>
            </tr>

                <?php   foreach ($drivers as $driver) { ?>
                            <tr>
                                <td><?= $driver->contact_name ?></td>
                                <td><?php echo $driver->phone;?></td>
                                <td>
                                    <input style="width:15px;height:15px;margin-left:0; border-radius:4px;" type="checkbox" name="checkbox" onclick="enroll_driver('<?php echo $driver->id ?>','<?php echo $module_id ?>');" id="driver_id" value="<?php echo $driver->id ?>">
                                </td>
                            </tr>
                <?php   } ?>
        </tbody>
    </table>
 </div>
</div>
    <script type="text/javascript">
        function enroll_driver(driver_id,module_id){
            if(driver_id>0){
                $.ajax({
                    type: "POST",
                    url:'<?php echo URL::Route('AddDrivers') ?>',
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
