@extends('layout')

@section('content')

<div class="row white_bg no_bg">


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
                <th  style="text-align: center">Driver Name</th>
                <th>Driver Phone</th>
                <th>Invoice Sent</th>
                <th>Invoice Paid</th>
                <th>Action</th>
            </tr>

                <?php   if(count($drivers)>0){
                            foreach ($drivers as $driver) {

                                ?>
                                <tr>
                                    <td  style="text-align: center"><?= $driver->contact_name ?></td>
                                    <td><?php echo $driver->phone;?></td>
                                    <td>
                                        <?php
                                            if($driver->certificate_status!='' && $driver->certificate_status!=NULL){
                                                $certificatestatus = json_decode($driver->certificate_status);

                                                foreach($certificatestatus as $status){
                                                    foreach($status as $status1) {
                                                        //print_r($status);
                                                        if($status1->category_id == $module_id){
                                                            if(isset($status1->invoice_sent) && $status1->invoice_sent=="YES"){ ?>
                                                                <label class='badge bg-green'>Invoice Sent</label>&nbsp;
                                                                <a class='badge bg-red' role="menuitem" id="invoicesent" tabindex="-2" href="javascript:void(0);"
                                                                   onclick="invoice_sent('<?php echo $driver->id ?>','<?php echo $module_id ?>','0');">Mark Invoice Not Sent</a>
                                        <?php               } else{ ?>
                                                                <label class='badge bg-green'>Invoice Not Sent</label>&nbsp;
                                                                <a class='badge bg-red' role="menuitem" id="invoicesent" tabindex="-2" href="javascript:void(0);"
                                                                   onclick="invoice_sent('<?php echo $driver->id ?>','<?php echo $module_id ?>','1');">Mark Invoice Sent</a>
                                        <?php               }
                                                        }
                                                    }
                                                }
                                            }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                            if($driver->certificate_status!='' && $driver->certificate_status!=NULL){
                                                $certificatestatus = json_decode($driver->certificate_status);

                                                foreach($certificatestatus as $status){
                                                    foreach($status as $status1) {
                                                        if($status1->category_id == $module_id){
                                                            if(isset($status1->invoice_paid) && $status1->invoice_paid=="YES"){ ?>
                                                                <label class='badge bg-green'>Invoice Paid</label>&nbsp;
                                                                <a class='badge bg-red' role="menuitem" id="invoicepaid" tabindex="-2"  href="javascript:void(0);"
                                                                   onclick="invoice_paid('<?php echo $driver->id ?>','<?php echo $module_id ?>','0');">Mark Invoice UnPaid</a>
                                        <?php               }else{ ?>
                                                                <label class='badge bg-green'>Invoice Not Paid</label> &nbsp;
                                                                <a class='badge bg-red' role="menuitem" id="invoicepaid" tabindex="-2"  href="javascript:void(0);"
                                                                    onclick="invoice_paid('<?php echo $driver->id ?>','<?php echo $module_id ?>','1');">Mark Invoice Paid</a>
                                        <?php               }
                                                        }
                                                    }
                                                }
                                            }
                                        ?>
                                    </td>
                                    <td>
                                        <a role="menuitem" id="map" tabindex="-2" class="btn btn-primary" href="/admin/trainingmodule/deletedriver/<?php echo $module_id ?>/<?php echo $driver->id ?>">Delete</a>
                                    </td>
                                </tr>
            <?php           }
                        }
            ?>
        </tbody>
    </table>
 </div>
</div>
<script type="text/javascript">
    function invoice_sent(driver_id,module_id,invoice){
        if(driver_id>0){
            $.ajax({
                type: "POST",
                url:'<?php echo URL::Route('InvoiceSent') ?>',
                data:{driver_id:driver_id,module_id:module_id,invoice_status:invoice},
                success: function(data) {
                   location.reload();
                }
            });
        }
    }

    function invoice_paid(driver_id,module_id,invoice){
        if(driver_id>0){
            $.ajax({
                type: "POST",
                url:'<?php echo URL::Route('InvoicePaid') ?>',
                data:{driver_id:driver_id,module_id:module_id,invoice_status:invoice},
                success: function(data) {
                    location.reload();
                }
            });
        }
    }
</script>
@stop
