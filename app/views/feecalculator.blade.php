@extends('layout')

@section('content')

    <div class="col-md-12 mt">

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
        <div><a id="email_pop_up" class="btn-sm" data-toggle="modal" href="#email_pop_up_responsive" style="display:none;"></a></div>

        <div class="modal fade modal1" id="email_pop_up_responsive" role="dialog">
            <div class="modal-dialog" style="margin-top:180px;">
                <div class="modal-content">
                    <div class="modal-header new-modal">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body" style="height:auto;">
                        <form class="form-horizontal">
                            <fieldset>
                                <div class="modal-body margin-top-0">
                                    <div class="scroller" style="height:50px" data-always-visible="1" data-rail-visible1="1">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="portlet-body form">
                                                    <div class="form-body">

                                                        <div class="form-group field-messageform-details">
                                                            <label class="col-md-2 control-label" style="text-align: right;position:relative;" for="form_control_1">Email</label>

                                                            <div class="col-md-4">
                                                                <input type="text"  class="form-control" style="height:30px;width: 250px;position: relative;padding:0;" name="email" id="email" value="" >
                                                                <div class="form-control-focus"></div>
                                                            </div><br/><br/>
                                                            <span id="sms-error-message"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-center margin-bottom-20">
                                    <a id="email_submit" href="javascript:void(0)" onclick="send_pdf();" class="btn green" >Submit</a>
                                    <button type="button" data-dismiss="modal" class="btn default" >Cancel</button>
                                </div>
                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="content-panel">
            <form class="form-horizontal style-form" method="post" id="main-form">
                <div class="form-group">
                    <label style="text-align:center;" class="col-sm-2 col-sm-2 control-label">Pickup Fee ($)</label>
                    <div class="col-sm-7">
                        <input type="text" style="height:30px;"  class="form-control" name="pickup_fee"   id="pickup_fee" value="110">
                    </div>
                </div>
                <div class="form-group">
                    <label style="text-align:center;" class="col-sm-2 col-sm-2 control-label">Miles</label>
                    <div class="col-sm-3">
                        <input type="number" style="height:30px;" class="form-control" name="miles"  id="miles"  value="">
                    </div>
                    <label style="text-align:right;" class="col-sm-2 col-sm-2 control-label">Cost per mile</label>
                    <div class="col-sm-2">
                        <input type="number" style="height:30px;" class="form-control" name="cost"  id="cost"  value="3.55">
                    </div>
                </div>
                <div class="form-group">
                    <label style="text-align:center;" class="col-sm-2 col-sm-2 control-label">Waiting Time (in min)</label>
                    <div class="col-sm-7">
                        <input type="number" style="height:30px;" min="0" step="1" class="form-control" name="waiting_time"  id="waiting_time" value="">
                    </div>
                </div>
                <div class="form-group">
                    <label style="text-align:center;" class="col-sm-2 col-sm-2 control-label">Deadhead Miles</label>
                    <div class="col-sm-7">
                        <input type="number" style="height:30px;" class="form-control" name="deadhead_miles" id="deadhead_miles"  value="">
                        <span id="error1"> </span>
                    </div>

                </div>
                <br>

                <div class="form-group">
                    <label style="text-align:center;" class="col-sm-2 col-sm-2 control-label">Total</label>
                    <div class="col-sm-7">
                        <input type="text" style="height:30px;" readonly class="form-control" name="total_fee" id="total_fee" value="">
                    </div>
                </div>
                <span class="col-sm-2"></span>
                <a href="javascript:void(0)" class="btn btn-primary" onclick="calculatefee();">Calculate</a>
                <!--a id="document" tabindex="-1" class="btn btn-primary"  href="javascript:void(0)" onclick="download_receipt();">Download Receipts</a-->
                <a id="email" tabindex="-1" class="btn btn-primary"  href="javascript:void(0)" onclick="generate_send_pdf();">Send Pdf by email</a>
            </form>
        </div>
    </div>


<script type="text/javascript">
    function calculatefee(){

        if($('#miles').val()==''|| $('#waiting_time').val()=='' || $('#deadhead_miles').val() == '' || $('#pickup_fee').val()=='') {
            $("#error1").html('<span style="text-align: center;font-size:15px;color: #f56954;">Please fill all the details.</span>');
        }else{
            if($.isNumeric($('#miles').val()) && $.isNumeric($('#waiting_time').val()) && $.isNumeric($('#deadhead_miles').val())){
                $("#error1").html('');
                if($('#pickup_fee').val()=='' || $('#pickup_fee').val()==0){
                    var picup_fee = 110;
                }else{
                    var picup_fee = $('#pickup_fee').val();
                }

                if($('#cost').val()=='' || $('#cost').val()==0){
                    var cost = 3.55;
                }else{
                    var cost = $('#cost').val();
                }
                var miles = $('#miles').val() * cost;
                var waiting_time = $('#waiting_time').val() * 1;
                var deadhead_miles = $('#deadhead_miles').val() * 1;

                var total = parseFloat(picup_fee) + parseFloat(miles) + parseInt(waiting_time) + parseFloat(deadhead_miles);

                total = parseFloat(total);

                $('#total_fee').val(total);
            } else{
                $("#error1").html('<span style="text-align: center;font-size:15px;color: #f56954;">Please enter numeric values.</span>');
            }

        }
    }

    function generate_send_pdf(){
        if($.isNumeric($('#miles').val()) && $.isNumeric($('#waiting_time').val()) && $.isNumeric($('#deadhead_miles').val()) && $.isNumeric($('#total_fee').val())
            && $.isNumeric($('#pickup_fee').val())){
            $( "#email_pop_up" ).trigger( "click" );
        } else{
            $("#error1").html('<span style="text-align: center;font-size:15px;color: #f56954;">Please fill all the details.</span>');
        }
    }

    function send_pdf(){

        if($('#email').val() ==''){
            $("#sms-error-message").html('<span style="text-align: center;font-size:15px;color: #f56954;padding-left: 108px;">Please enter email.</span>');
        } else{
            $('#email_submit').addClass("disabled");
            $.ajax({
                type: "POST",
                url:'<?php echo URL::Route('GenerateFeeReceipt') ?>',
                data:{miles:$('#miles').val(),waiting_time:$('#waiting_time').val(),deadhead_miles:$('#deadhead_miles').val(),
                    total:$('#total_fee').val(),email:$('#email').val(),pickup_fee:$('#pickup_fee').val()},
                success: function(data) {
                    console.log(data);
                    $('#email_pop_up_responsive').modal('hide');
                    location.reload();
                }
            });
        }


    }
</script>


@stop