@extends('layout')

@section('content')

    <div><a id="add_balance_pop_up" class="btn-sm" data-toggle="modal" href="#add_balance_pop_up_responsive" style="display:none;"></a></div>
    <style>
        .modal-backdrop {
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background-color: #000000;
        }

        .modal-backdrop.fade {
            opacity: 0;
        }

        .modal-backdrop,
        .modal-backdrop.fade.in {
            opacity: 0;
            filter: alpha(opacity=80);
        }
        .modal-title {
            color: #e8ecec;
        }
    </style>
    <div id="add_balance_pop_up_responsive" style="height:290px;align:center;top:30%;width:40%;margin-left:400px;" class="modal fade" tabindex="-1" aria-hidden="false">
        <div class="modal-content">
            <form role="form" class="form-horizontal" name="reason_form" id="reason_form">
                <div class="modal-header" style="background-color:#68dff0;">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title"><h4 class="modal-title">Add Balance</h4></h4>
                </div>
                <div class="modal-body margin-top-0">
                    <div class="scroller" style="" data-always-visible="1" data-rail-visible1="1">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="portlet-body form">
                                    <div class="form-body">
                                        <span id="error-message"></span>
                                        <div class="form-group form-md-line-input field-confirm_form-total_cost">
                                            <label class="col-md-4 control-label" for="form_control_1">Total Amount</label>

                                            <div class="col-md-4">
                                                <input type="text" name="total_cost" id="total_cost" value="" readonly>
                                                <div class="form-control-focus"></div>

                                            </div>
                                        </div>
                                        <div class="form-group form-md-line-input field-confirm_form-current_balance">
                                            <label class="col-md-4 control-label" for="form_control_1">Current Balance </label>

                                            <div class="col-md-4">
                                                <input type="text" name="current_balance" id="current_balance" value="" readonly>
                                                <div class="form-control-focus"></div>
                                            </div>
                                        </div>
                                        <div class="form-group form-md-line-input field-confirm_form-current_balance">
                                            <label class="col-md-4 control-label" for="form_control_1">Add Amount </label>

                                            <div class="col-md-4">
                                                <input type="text" name="new_amount" id="new_amount" value="" >
                                                <div class="form-control-focus"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-center margin-bottom-20" style="margin-bottom:20px;">
                    <a id="confirm_submit" href="javascript:void(0)" onclick="add_balance();" class="btn default" >Submit</a>
                    <input type="hidden" id="healthcare_id" name="healthcare_id" value="">
                    <button type="button" data-dismiss="modal" class="btn default" >Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <div align="center" id="loadingimage" style="display:none;"><img src="<?= asset_url() . '/web/img/preloader.gif' ?>" style="z-index: 9999;position: fixed;left: 565px;" /></div>
<div class="box box-info tbl-box">
    <div align="left" id="paglink"><?php echo $healthproviders->appends(array('type' => Session::get('type'), 'valu' => Session::get('valu')))->links(); ?></div>
    <table class="table table-bordered">
        <tbody><tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Company</th>
                <th>CompanyLogo</th>
                <th>Operator Email</th>
                <th>Operator Phone</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>

            <?php foreach ($healthproviders as $healthprovider) { ?>
                <tr>
                    <td><a role="menuitem" href="{{ URL::Route('EnterpriseClientProfile', $healthprovider->id) }}"><?= $healthprovider->id ?></a></td>
                    <td><?php echo $healthprovider->contact_name; ?> </td>
                    <td><?= $healthprovider->email ?></td>
                    <td><?= $healthprovider->company ?></td>
                    <td style="width:10%"><p class="centered"><a href="#">
                                <img src="<?= $healthprovider->companylogo ?>" class="img-circle" width="50%" alt="Logo" /></a></p></td>
                    <td> <a href="mailto:<?=$healthprovider->operator_email ?>"><?=$healthprovider->operator_email ?></a></td>
                    <td><?= $healthprovider->operator_phone ?></td>
                    <td>
                    <?php
                        if ($healthprovider->is_active == 1) {
                            echo "<span class='badge bg-green'>Approved</span>";
                        } else {
                            echo "<span class='badge bg-red'>Pending</span>";
                        }
                        ?>
                    </td>
                    <td>

                        <div class="dropdown">
                            <button class="btn btn-flat btn-info dropdown-toggle" type="button" id="dropdownMenu1" name="action" data-toggle="dropdown">
                                Actions
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
                                <li role="presentation"><a role="menuitem" href="{{ URL::Route('EnterpriseClientProfile', $healthprovider->id) }}">Profile</a></li>
                                <li role="presentation" class="divider"></li>
                                <?php if ($healthprovider->is_active == 0) { ?>
                                    <li role="presentation"><a role="menuitem" id="approve" tabindex="-1" href="{{ URL::Route('AdminHealthcareProviderApprove', $healthprovider->id) }}">Approve</a></li>
                                <?php } else { ?>
                                    <li role="presentation"><a role="menuitem" id="decline" tabindex="-1" href="{{ URL::Route('AdminHealthcareProviderDecline', $healthprovider->id) }}">Decline</a></li>
                                <?php } ?>
                                <li role="presentation" class="divider"></li>
                                <li role="presentation"><a role="menuitem" id="balance" tabindex="-1" href="javascript:void(0)" onclick="show_balance('<?php echo $healthprovider->id ?>','<?php echo $healthprovider->total_amount ?>','<?php echo $healthprovider->account_balance ?>');">Add Balance</a></li>

                            </ul>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        </tbody></table>
    <div align="left" id="paglink"><?php echo $healthproviders->appends(array('type' => Session::get('type'), 'valu' => Session::get('valu')))->links(); ?></div>
</div>

<script type="text/javascript">
    function show_balance(healthcareid,total_cost,current_balance){
        document.getElementById('healthcare_id').value = healthcareid;
        document.getElementById('total_cost').value = total_cost;
        document.getElementById('current_balance').value = current_balance;
        $( "#add_balance_pop_up" ).trigger( "click" );
    }

    function add_balance(){
        if(document.getElementById('new_amount').value<=0 || document.getElementById('new_amount').value==''){
            $("#error-message").html('<span style="text-align: center;font-size:15px;color: #f56954;">Please enter some amount.</span>');
        }else{
            $('#confirm_submit').addClass("disabled");
            //$('#loadingimage').show();

            $.ajax({

                type: "POST",
                url:'<?php echo URL::Route('AddBalance') ?>',
                data:{healthcare_id:document.getElementById('healthcare_id').value,new_amount:document.getElementById('new_amount').value},
                success: function(data) {
                    console.log(data);
                    $('#add_balance_pop_up_responsive').modal('hide');
                    //$('#loadingimage').hide();
                    location.reload();
                }
            });
        }

    }
</script>

@stop