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
                <th style="text-align: center">Module Name</th>
                <th style="text-align: center">Certificate Sent</th>
                <th style="text-align: center">Time</th>
                <th style="text-align: center">Invoice Sent</th>
                <th style="text-align: center">Invoice Paid</th>
            </tr>
            <tr>
                <?php   if($walker_certificate_status!=''){
                            foreach($walker_certificate_status as $status){
                                foreach($status as $status1) {
                                    $datetime = new DateTime($status1->certificate_generate_time);
                                    $datetime->format('Y-m-d H:i:s') . "\n";
                                    if($walker_timezone!='' && $walker_timezone!=NULL){
                                        $user_time = new DateTimeZone($walker_timezone);
                                    } else{
                                        $user_time = new DateTimeZone(Config::get('app.timezone'));
                                    }
                                    $datetime->setTimezone($user_time);
                                    $certificate_generate_time = $datetime->format('Y-m-d H:i:s');
                                    $learningcategory = LearningCategory::find($status1->category_id);
                ?>
                                    <tr>
                                        <td style="text-align: center"><?php echo $learningcategory->category ?></td>
                                        <td style="text-align: center"><?php echo $status1->certificate_sent ?></td>
                                        <td style="text-align: center"><?php echo $certificate_generate_time ?></td>
                                        <td>
                <?php                       if(isset($status1->invoice_sent) && $status1->invoice_sent=="YES"){ ?>
                                                <label class='badge bg-green'>Invoice Sent</label>&nbsp;
                                                <a class='badge bg-red' role="menuitem" id="invoicesent" tabindex="-2" href="javascript:void(0);"
                                               onclick="invoice_sent('<?php echo $walker_id ?>','<?php echo $status1->category_id ?>','0');">Mark Invoice Not Sent</a>
                <?php                       } else{ ?>
                                                <label class='badge bg-green'>Invoice Not Sent</label>&nbsp;
                                                <a class='badge bg-red' role="menuitem" id="invoicesent" tabindex="-2" href="javascript:void(0);"
                                               onclick="invoice_sent('<?php echo $walker_id ?>','<?php echo $status1->category_id ?>','1');">Mark Invoice Sent</a>
                <?php                       } ?>
                                        </td>
                                        <td>
                <?php                       if(isset($status1->invoice_paid) && $status1->invoice_paid=="YES"){ ?>
                                             <label class='badge bg-green'>Invoice Paid</label>&nbsp;
                                             <a class='badge bg-red' role="menuitem" id="invoicepaid" tabindex="-2" href="javascript:void(0);"
                                            onclick="invoice_paid('<?php echo $walker_id ?>','<?php echo $status1->category_id ?>','0');">Mark Invoice UnPaid</a>
                <?php                       } else{ ?>
                                                <label class='badge bg-green'>Invoice Not Paid</label>&nbsp;
                                                <a class='badge bg-red' role="menuitem" id="invoicepaid" tabindex="-2" href="javascript:void(0);"
                                            onclick="invoice_paid('<?php echo $walker_id ?>','<?php echo $status1->category_id ?>','1');">Mark Invoice Paid</a>
                <?php                       } ?>
                                        </td>
                                    </tr>

            <?php                }
                            }
                       }
            ?>
            </tr>
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
