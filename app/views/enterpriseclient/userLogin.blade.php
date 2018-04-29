<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="">
	<meta name="author" content="Dashboard">
	<meta name="keyword" content="Dashboard, Bootstrap, Admin, Template, Theme, Responsive, Fluid, Retina">


	<title>{{Config::get('app.website_title')}}</title>

    <?php
    $theme = Theme::all();

    $logo = '/image/logo.png';
    $favicon = '/image/favicon.ico';
    foreach ($theme as $themes) {
        $logo = '/uploads/' . $themes->logo;
        $favicon = '/uploads/' . $themes->favicon;
    }
    if ($logo == '/uploads/') {
        $logo = '/image/logo.png';
    }
    if ($favicon == '/uploads/') {
        $favicon = '/image/favicon.ico';
    }
    ?>

	<link rel="icon" type="image/ico" href="<?php echo asset_url(); ?><?php echo $favicon; ?>">

	<!-- Bootstrap core CSS -->
	<link href="<?php echo asset_url(); ?>/web/css/bootstrap.css" rel="stylesheet">
	<!--external css-->
	<link href="<?php echo asset_url(); ?>/web/font-awesome/css/font-awesome.css" rel="stylesheet" />

	<!-- Custom styles for this template -->
	<link href="<?php echo asset_url(); ?>/web/css/dispatcher.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Ladda/1.0.4/ladda-themeless.min.css">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/Ladda/1.0.4/spin.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/Ladda/1.0.4/ladda.min.js"></script>

	<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
	<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
	<![endif]-->
	<script src="<?php echo asset_url(); ?>/web/js/validation.js"></script>
	<style>
		.signin-button
		{
			background-color: #80037e;
			-webkit-border-radius: 40px;
			-moz-border-radius: 40px;
			-ms-border-radius: 40px;
			-o-border-radius: 40px;
			border-radius: 40px;
			height: 50px;
			text-align: center;
			font-size: 18px;
			font-weight: 600;
			text-transform: uppercase;
			color: #FFF;
			border: 0 none;
			width: 300px;
			padding-top:12px;
		}
		.signin-button:hover
		{
			background-color: #80037e;
			color:#fff;
		}
	</style>
</head>
<body>
<div class="wrapper form-wrapper sign_in_wrapper">
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 login-area">

				<form id="healthcare_form" class="form-login" name="healthcare_form">
					<img src="<?php echo asset_url(); ?>/web/img/form-logo.png" alt="form-logo" />
					<ul>
						<li>
							<span id="radio_buttons">
								<input style="width:20px;height:17px;vertical-align:-3px;" type="radio" name="user_select" id="user_select" value="1" onclick="healthcare_click();" checked="checked"><strong style="font-size:16px;">Corporate</strong>
								<input style="width:20px;height:17px;margin-left: 25px;vertical-align:-3px;" type="radio" name="user_select" id="user_select" value="3" onclick="click_user();"> <strong style="font-size:16px;margin-left:-4px;">Consumer</strong>
							</span>
						</li>

						<span id="no_email_error1" style="display: none"> </span>
						<p id="otp_msg" style="color:#000;">Please check your email or phone for One Time Password</p>
						<li><input type="text" id="otp" name="otp" placeholder="OTP" autocomplete="off"  class="form-control placeholder-no-fix" required="" ></li>
						<li><input type="email" name="email" value="" placeholder="Email" onblur="ValidateEmail(1)" id="email_check1" required="" autofocus /></li>
						<li><input type="password" id="password" name="password" value="" placeholder="Password" /></li>
						<li id="agent_checkbox"><input style="width:20px;height:17px;margin-left: 60px;vertical-align:-3px;float:left" type="checkbox" name="agent_select" id="agent_select" value="2"> <strong style="font-size:16px;margin-right:430px;vertical-align: -6px;">Agent</strong></li>
						<li id="otp_resend"><a href="javascript:void(0);" style="float:right;margin-right: 59px;color:#0a78b1;" onclick="resendOTP();" id="otp_resend">Resend OTP</a></li>




						<li class="half" id="forgot_password">
							<a data-toggle="modal" href="#myModal"> Forgot Password?</a>
						</li>

						<li class="half" id="create_account">
							<a href="{{route('/booking/signup')}}">Create an account</a>
						</li>
						<li>
							<div id="error_messages" style="display:none;" class="alert alert-danger"></div>
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
						</li>
						<span id="sign_in"><a href="javascript:void(0);" class="btn signin-button ladda-button" data-style="zoom-in" onclick="userVerify();"><span class="ladda-label" style="color:#ffffff;">Sign in</span></a></span>
						<span id="submit_otp"><a href="javascript:void(0);" class="btn signin-button ladda-button submit_otp" data-style="zoom-in" onclick="checkOTP();"><span class="ladda-label" style="color:#ffffff;">Submit</span></a></span>
					</ul>
				</form>
				<!-- Modal -->
				<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="myModal" class="modal fade">
					<div class="modal-dialog" style="width: 415px;">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
								<h4 class="modal-title">Forgot Password ?</h4>
							</div>
							<form method="POST" action="{{route('/booking/forgot-password')}}">
								<div class="modal-body">
									<p>Enter your e-mail address below to reset your password.</p>
									<span id="no_email_error2" style="display: none"> </span>
									<input type="text" name="email" placeholder="Email" autocomplete="off"  class="form-control placeholder-no-fix" required="" id="email_check2" onblur="ValidateEmail(2)">


								</div>
								<div class="modal-footer">
									<button data-dismiss="modal" class="btn btn-default" type="button">Cancel</button>
									<input type="hidden" id="user_click" name="user_click" value="1">
									<button class="btn btn-default" type="submit">Submit</button>
								</div>
							</form>
						</div>
					</div>
				</div>
				<!-- modal -->
			</div>
		</div>
	</div>
</div>
<script src="<?php echo asset_url(); ?>/web/js/jquery.js"></script>
<script src="<?php echo asset_url(); ?>/web/js/bootstrap.min.js"></script>
<script type="text/javascript">
	function click_user(){
        document.getElementById('agent_checkbox').style.display = "none";
        $('#user_click').val(3);
	}
	function healthcare_click(){
        document.getElementById('agent_checkbox').style.display = "";
	}
    function userVerify(){
        Ladda.create( document.querySelector( '.ladda-button' ) ).stop();
        if($('#email_check1').val()=='' || $('#password').val()==''){
            $("#no_email_error1").html('<span style="text-align: center;font-size:15px;color: #f56954;">Please fill both the fields.</span>');
            $("#no_email_error1").show();
        } else{
            var user_type = $('input[name="user_select"]:checked', '#healthcare_form').val();
            var agent_type = $('input[name="agent_select"]:checked', '#healthcare_form').val();
            //alert(agent_type);
            Ladda.create( document.querySelector( '.ladda-button' ) ).start();
            $('#loadingimage').show();
            $('#usersubmit').addClass("disabled");
            $.ajax({
                type: "POST",
                url:'<?php echo URL::Route('/booking/verify') ?>',
                data:{email:$('#email_check1').val(),password:$('#password').val(),user_select:user_type,agent_select:agent_type},
                success: function(data) {
                    console.log(data);
                    Ladda.create( document.querySelector( '.ladda-button' ) ).stop();
                    if(data==2){
                        $("#error_messages").html('<b>Your Account is pending approval</b>');
                        $("#error_messages").show();
                    } else if(data==3) {
                        $("#error_messages").html('<b>Invalid email and password</b>');
                        $("#error_messages").show();
                    } else {
                        $("#error_messages").hide();
//                        $('#user_email').val(data);
//                        $("#show_otp_form").trigger("click");
                        document.getElementById('radio_buttons').style.display = "none";
                        document.getElementById('agent_checkbox').style.display = "none";
                        document.getElementById('email_check1').style.display = "none";
                        document.getElementById('password').style.display = "none";
                        document.getElementById('create_account').style.display = "none";
                        document.getElementById('forgot_password').style.display = "none";
                        document.getElementById('otp').style.display = "";
                        document.getElementById('otp_resend').style.display = "";
                        document.getElementById('sign_in').style.display = "none";
                        document.getElementById('submit_otp').style.display = "";
                        document.getElementById('otp_msg').style.display = "";

                    }
                }
            });
        }

    }

    function checkOTP(){
        if($('#otp').val()==''){
            $("#no_email_error1").html('<span style="text-align: center;font-size:15px;color: #f56954;">Please enter OTP.</span>');
            $("#no_email_error1").show();
            Ladda.create( document.querySelector( '.submit_otp' ) ).stop();
        } else{
            var user_type = $('input[name="user_select"]:checked', '#healthcare_form').val();
            var agent_type = $('input[name="agent_select"]:checked', '#healthcare_form').val();
            Ladda.create( document.querySelector( '.submit_otp' ) ).start();
            //$('#otp_submit').addClass("disabled");
            $.ajax({
                type: "POST",
                url:'<?php echo URL::Route('CheckHealthcareOTP') ?>',
                data:{otp:$('#otp').val(),email:$('#email_check1').val(),user_select:user_type,agent_select:agent_type},
                success: function(data) {
                    console.log(data);
                    if(data==1){
                        Ladda.create( document.querySelector( '.submit_otp' ) ).stop();
                        $("#no_email_error1").html('<span style="text-align: center;font-size:15px;color: #f56954;">Invalid OTP</span>');
                        $("#no_email_error1").show();
                    } else{
                        window.location.href = data;
                    }
                }
            });
        }
    }

    function resendOTP(){
        $('#loadingimage1').show();
        $('#otp_resend').addClass("disabled");
        var user_type = $('input[name="user_select"]:checked', '#healthcare_form').val();
        var agent_type = $('input[name="agent_select"]:checked', '#healthcare_form').val();
        $.ajax({
            type: "POST",
            url:'<?php echo URL::Route('ResendHealthcareOTP') ?>',
            data:{email:$('#email_check1').val(),user_select:user_type,agent_select:agent_type},
            success: function(data) {
                console.log(data);
                $('#loadingimage1').hide();
                $('#user_email').val(data);
                $("#no_email_error1").html('<span style="text-align: center;font-size:15px;color: #f56954;">OTP has been sent. </span>');
                $("#no_email_error1").show();
            }
        });
    }
    $(function () {
        document.getElementById('otp').style.display = "none";
        document.getElementById('otp_resend').style.display = "none";
        document.getElementById('submit_otp').style.display = "none";
        document.getElementById('otp_msg').style.display = "none";

    });
</script>
<!-- js placed at the end of the document so the pages load faster -->
<script src="<?php echo asset_url(); ?>/web/js/jquery.js"></script>
<script src="<?php echo asset_url(); ?>/web/js/bootstrap.min.js"></script>
</body>
</html>
