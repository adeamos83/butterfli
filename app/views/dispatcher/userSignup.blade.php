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
        $active = '#000066';
        $logo = '/image/logo.png';
        $favicon = '/image/favicon.ico';
        foreach ($theme as $themes) {
            $active = $themes->active_color;
            $favicon = '/uploads/' . $themes->favicon;
            $logo = '/uploads/' . $themes->logo;
        }
        if ($logo == '') {
            $logo = '/image/logo.png';
        }
        if ($favicon == '') {
            $favicon = '/image/favicon.ico';
        }
        ?>

        <link rel="icon" type="image/ico" href="<?php echo asset_url(); ?><?php echo $favicon; ?>">


        <?php
        $theme = Theme::all();
        $active = '#000066';
        $logo = '/image/logo.png';
        $favicon = '/image/favicon.ico';
        foreach ($theme as $themes) {
            $active = $themes->active_color;
            $favicon = '/uploads/' . $themes->favicon;
            $logo = '/uploads/' . $themes->logo;
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
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" />
         <link href="<?php echo asset_url(); ?>/web/css/dispatcher.css" rel="stylesheet">

        <style>
            .select_company_name{
                width: 72%;
                height: 30px;
                margin-top: 20px;
                border:none;
                background: transparent;
                color:#000;
            }
            .company_option_border{
                border-bottom: 1px solid;
                padding-bottom: 5px;
            }
            .select_number{
                background: transparent;
                border: 0;
                color: #000;
            }

        </style>
		<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
          <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->
        <script src="https://momentjs.com/downloads/moment.min.js"></script>
        <script src="https://momentjs.com/downloads/moment-timezone-with-data.min.js"></script>
        <script type="text/javascript" src="https://developer.jboss.org/servlet/JiveServlet/previewBody/52971-102-1-171969/jstz-1.0.4.min.js"></script>
        <script src="<?php echo asset_url(); ?>/web/js/validation.js"></script>
        <script type="text/javascript" src="<?php echo asset_url(); ?>/website/scripts/timezone/jstz.js"></script>
        <script type="text/javascript" src="<?php echo asset_url(); ?>/website/scripts/timezone/prettify.js"></script>
        <script type="text/javascript" src="<?php echo asset_url(); ?>/website/scripts/timezone/jquery.js"></script>
        <script type="text/javascript">
$(document).ready(function () {
    var tz = jstz.determine();

    response_text = 'No timezone found';

    if (typeof (tz) === 'undefined') {
        response_text = 'No timezone found';
    }
    else {
        response_text = tz.name();
    }

    $('#tz_info').val(response_text);

    $('#code-example').html("> var timezone = jstz.determine();\n" +
            "> timezone.name(); \n" +
            "\"" + tz.name() + "\"\n\n");
    /*$('#tz_info').show();*/
    prettyPrint();
});
        </script>
    </head>

 <body>
	<div class="wrapper form-wrapper sign_up_wrapper">
		<div class="container-fluid">
			<div class="row">
				<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 login-area">
					 <form class="form-login" action="{{route('/dispatcher/save')}}" method="post">
						 <img src="<?php echo asset_url(); ?>/web/img/form-logo.png" alt="form-logo" />
						<ul>
                        <li><input type="text" name="contact_name" required  placeholder="Name" autofocus></li>
						<li>
                        <span id="no_email_error1" style="display: none"> </span>
                        <input type="text" name="email"  placeholder="Email Address" onblur="ValidateEmail(1)" id="email_check1" required="" >
						</li>
						<li>
						 <input type="password" name="password" required  placeholder="Password">
						  <input type="hidden" name="timezone" id="tz_info" value="">
						</li>
                        <li>
                            <span class="company_option_border">
                            <select class="select_number" name="code" id="code" >
                                <option value="+1">+1</option>
                            </select>
                            <input type="phone" name="phone" placeholder="Phone" style="border:0;max-width: 410px;">
                            </span>
                        </li>
                        <li>

                              <span class="company_option_border">
                            <img src="/web/img/input-company-icon.png" id="companylogo_icon" style="margin-right:15px;padding-bottom:7px;">
                            </span>
                        </li>
                       <li>
						   @if(Session::has('error'))
							<div class="alert alert-danger">
								<b>{{ Session::get('error') }}</b> 
							</div>
  						   @endif
                        </li>

                        <li><button id="register" class="btn btn-theme btn-block" type="submit">Sign up</button></li>
						<li class="registration half">
                            Do you have an account already?<br/>
                            <a class="" href="{{route('/dispatcher/signin')}}">
                                Sign in
                            </a>
                        </li>

                   </ul>
                </form>
				</div>
			</div>
		</div>
	</div>


</body>
</html>
