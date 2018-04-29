@extends('dispatcher.layout')

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
        @if(isset($error))
            <div class="alert alert-danger">
                <b>{{ $error }}</b>
            </div>
        @endif
        @if(isset($success))
            <div class="alert alert-success">
                <b>{{ $success }}</b>
            </div>
        @endif
            <div style="padding-left:10px;">
                <a href="{{ URL::Route('submittedrides') }}">
                    <i class="fa fa-arrow-left" style="font-size: 20px;" aria-hidden="true"></i><span style="vertical-align: 1px;padding-left: 5px;">Unassigned Rides</span>
                </a>
            </div>
            <div class="col-md-3 col-sm-12">
            </div>
            <div class="col-xs-12 col-md-6 col-sm-12">
        <div class="content-panel">
            <form class="form-horizontal style-form" style="padding:0 20px 0 20px; width:auto;" method="post" id="payment-form" name="payment-form" role="form" action="/healthcare/save-customerpayments">
                <div id="error"></div>
                <div class="form-group">
                    <label style="padding-top:1px;" class="col-sm-2 col-sm-2 control-label">Name</label>
                        <input id="cardholder-name" name="cardholder-name" class="field" style="font-size: inherit;" placeholder="Please enter card-holder name" />
                </div>
                <div class="form-group">
                    <label style="padding-top:1px;" class="col-sm-2 col-sm-2 control-label">Phone</label>
                        <input id="cardholder-phone" name="cardholder-phone" class="field" style="font-size: inherit;" placeholder="Enter your phone no" />
                </div>
                <div id="card-element">
                    <!-- a Stripe Element will be inserted here. -->
                </div>
                <label style="margin-top: 10px;">
                    <span style="width:5%;text-align:left;padding-top: 2px;"><input type="checkbox" name="rememberme" checked="checked" value="1"/></span><span style="width:50%;text-align:left;">Remember for future payments</span>
                    <span id="loading" style="display:none;">
							<img style="width: 43px;" src="<?php echo asset_url(); ?>/web/img/loading1.gif" alt="logo"/>
                    </span>
                </label>
                <span class="col-sm-2"></span><br/>
                <div class="form-group credit_card_fields">
                    <label style="text-align:center;" class="col-sm-2 col-sm-2 control-label"></label>
                        <button id="payment_submit" type="submit">Submit</button>
                        <input type="hidden" id="request_id" name="request_id" value="<?php echo $request_id ?>">
                        <input type="hidden" id="passenger_id" name="passenger_id" value="<?php echo $passenger_id ?>">
                </div>
            </form>
        </div>
            </div>
            <div class="col-md-3 col-sm-12">
            </div>
    </div>
    <div class="col-md-12 mt">
        <div class="col-md-4 col-sm-12">
        </div>
        <div class="col-md-4 col-sm-12" style="text-align: center;">
            <span id="loading_card" style="text-align: center; display:none;">
							<img style="width: 70px;" src="<?php echo asset_url(); ?>/web/img/loading1.gif" alt="logo"/>
                    </span>
        </div>
        <div class="col-md-4 col-sm-12">
        </div>
    </div>
    <div class="col-md-12 mt">
        <div class='row row-dashboard'>
            <label class="col-sm-12" style="color:#9c27b0;padding-left:20px;">Added Cards</label>
                <div id="show_cards" class="form-group">
                    <?php    foreach($results as $result) {
                                echo "<div class='col-sm-4 con'>";
                                if($result->is_default==1){
                                    echo "<div class='panel panel-default credit_card_hover credit_card_panel default_credit_card' id='deafult_card' onclick=changedefault('$result->id');>";
                                }else{
                                    echo "<div class='panel panel-default credit_card_hover credit_card_panel' id='deafult_card' onclick=changedefault('$result->id');>";
                                }
                                echo "<div style='border-radius:5px;'>";
                                echo "<div class='credit_card_data'><input class='credit_card_data_fields' type=text name=last_four readonly value='XXXX-XXXX-XXXX-$result->last_four'>";
                                if($result->is_default==1){
                                    echo "<input class='credit_card_data_input'  type=radio id='is_default' name=is_default checked='checked' value='$result->id' onclick=changedefault('$result->id');>Active</div>";
                                }else{
                                    echo "<input class='credit_card_data_input' type=radio id='is_default' name=is_default value='$result->id' onclick=changedefault('$result->id');>Inactive</div>";
                                }
                                echo "</div>";
                                echo "</div>";
                                echo "</div>";

                             }
                             echo "<input type='hidden' name='passengerid' id='passengerid' value='$passenger_id'>";
                    ?>
                </div>
        </div>

        </div>
    <link href="<?php echo asset_url(); ?>/web/css/stripe.css" rel="stylesheet">
    <script src="https://js.stripe.com/v3/"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            call_stripe();
        });

        function call_stripe(){
            var stripe = Stripe('<?php echo Config::get('app.stripe_publishable_key'); ?>');
            var elements = stripe.elements();

            // Custom styling can be passed to options when creating an Element.
            var style = {
                base: {
                    // Add your base input styles here. For example:
                    fontSize: '16px',
                    lineHeight: '24px'
                }
            };

            // Create an instance of the card Element
            var card = elements.create('card', {style: style});

            // Add an instance of the card Element into the `card-element` <div>
            card.mount('#card-element');

            card.addEventListener('change', function(event) {
                var displayError = document.getElementById('card-errors');
                if (event.error) {
                    $('#loading').hide();
                    $("#payment_submit").removeAttr('disabled');
                    displayError.textContent = event.error.message;
                } else {
                    $("#payment_submit").removeAttr('disabled');
                    displayError.textContent = '';
                }
            });

            // Create a token or display an error when the form is submitted.
            var form = document.getElementById('payment-form');
            form.addEventListener('submit', function(event) {
                event.preventDefault();

                if(document.getElementById('cardholder-name').value=='' || document.getElementById('cardholder-phone').value==''){
                    alert("Please Enter both field values.");
                }else{
                    $('#loading').show();
                    stripe.createToken(card).then(function(result) {
                        if (result.error) {
                            // Inform the user if there was an error
                            $('#loading').hide();
                            $("#payment_submit").removeAttr('disabled');
                            var errorElement = document.getElementById('card-errors');
                            errorElement.textContent = result.error.message;
                        } else {
                            //console.log(result.token.card.last4);
                            // Send the data to your server
                            $('#payment_submit').click(function() {
                                //alert("submit disabled");
                                //$('#payment_submit').attr('disabled', 'disabled');
                            });
                            stripeDataHandler(result.token);
                        }
                    });
                }
            });
        }

        function stripeDataHandler(data) {
            var cardholder_name  = $('#cardholder-name').val();
            var cardholder_phone = $('#cardholder-phone').val();
            var active = $('input[type="checkbox"]:checked', '#payment-form').val();

            /*Add token in request form for updating dispatcher_assigned_id in payment table*/
            var form = document.getElementById('payment-form');
            var hiddenInput = document.createElement('input');
            hiddenInput.setAttribute('type', 'hidden');
            hiddenInput.setAttribute('name', 'Token');
            hiddenInput.setAttribute('value', data.id);
            form.appendChild(hiddenInput);
            var card_no = "XXXX-XXXX-XXXX-"+data.card.last4;
            if(document.getElementById('passenger_id').value!=''){
                var disp_assign_id = document.getElementById('passenger_id').value;
            } else{
                var disp_assign_id = '';
            }
            if(active != 1){
                active=0;
            }

            // Submit the ajax request
            $.ajax({
                type: "POST",
                url:'<?php echo URL::Route('save-customer-payments') ?>',
                data:{stripeToken:data.id,cardholdername:cardholder_name,cardholderphone:cardholder_phone,cardtype:data.card.brand,last4:data.card.last4,rememberme:active,passenger_id:disp_assign_id,card_id:data.card.id},
                success: function(data) {
                    console.log(data);
                    if(data.error!='' && typeof data.error !== "undefined"){
                        $('#loading').hide();
                        $("#error").html('<span style="text-align: center;font-size:15px;color: #f56954;">'+data.error+'</span>');
                        $("#payment_submit").removeAttr('disabled');
                    } else {
                        $('#loading').hide();
                        $('#show_cards').show();
                        $('#show_cards').html(data);
                        $("#error").html('');
                        $("#cardholder-name").val('');
                        $("#cardholder-phone").val('');
                        $("#card-element").html('');
                        location.reload();
                    }
                }
            });
        }

        function changedefault(id){
            if(id > 0){
                $('#loading_card').show();
                $.ajax({
                    type: "POST",
                    url:'<?php echo URL::Route('updatedefaultPassengercard') ?>',
                    data:{defaultcard_id:id,disp_assign_id:document.getElementById('passengerid').value},
                    success: function(data) {
                        console.log(data);
                        location.reload();
                    }
                });
            }
        }
    </script>
@stop 