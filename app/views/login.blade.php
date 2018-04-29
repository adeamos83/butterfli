<!DOCTYPE html>
<html lang="en">
    <!-- START Head -->
    <head>
		<meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="">
        <meta name="author" content="Dashboard">
        <meta name="keyword" content="Dashboard, Bootstrap, Admin, Template, Theme, Responsive, Fluid, Retina">
		<title><?= $title ?> | <?= Config::get('app.website_title') ?> Web Dashboard</title>
		
		<?php
        $theme = Theme::all();
		$active = '#000066';
        $logo = '/image/logo.png';
        $favicon = '/image/favicon.ico';
        foreach ($theme as $themes) {
			$active = $themes->active_color;
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
		<link href="<?php echo asset_url(); ?>/web/css/bootstrap.css" rel="stylesheet">
        <link href="<?php echo asset_url(); ?>/adminlogins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo asset_url(); ?>/web/css/admin.css" rel="stylesheet">
        <script src="<?php echo asset_url(); ?>/web/js/validation.js"></script>

        <!--/ END JAVASCRIPT SECTION -->
    </head>
    <!--/ END Head -->
<body>
   <div class="wrapper form-wrapper sign_in_wrapper">
		<div class="container-fluid">
			<div class="row">
				<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 login-area">
					
					<form class="form-login" action="{{ URL::Route('AdminVerify') }}" method="post">
					<h2 style="color:#000;">Welcome to<br><?= Config::get('app.website_title') ?></h2>
					<img src="<?php echo asset_url(); ?>/web/img/form-logo.png" alt="form-logo" />
					
						<ul>
							 <span id="no_email_error1" style="display: none"> </span>
							<li><input type="email" name="username" value="" placeholder="Email" onblur="ValidateEmail(1)" id="email_check1" required="" autofocus /></li>
							<li><input type="password" name="password" value="" placeholder="Password" /></li>
							<!--li class="half">
								<a data-toggle="modal" href="#myModal"> Forgot Password?</a>
							</li-->
							<li>
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
							<li><input type="submit" value="Sign in" /></li>
						</ul>
					</form>
					<!-- Modal -->
					<!--div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="myModal" class="modal fade">
						<div class="modal-dialog">
							<div class="modal-content">
								<div class="modal-header">
									<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
									<h4 class="modal-title">Forgot Password ?</h4>
								</div>
								<form method="POST" action="{{route('/dispatcher/forgot-password')}}">
									<div class="modal-body">
										<p>Enter your e-mail address below to reset your password.</p>
										<span id="no_email_error2" style="display: none"> </span>
										<input type="text" name="email" placeholder="Email" autocomplete="off"  class="form-control placeholder-no-fix" required="" onblur="ValidateEmail(2)" id="email_check2" >


									</div>
									<div class="modal-footer">
										<button data-dismiss="modal" class="btn btn-default" type="button">Cancel</button>
										<button class="btn btn-theme" type="submit">Submit</button>
									</div>
								</form>
							</div>
						</div>
					</div-->
                <!-- modal -->
				</div>
			</div>
		</div>
	</div>
	<!-- js placed at the end of the document so the pages load faster -->
        <script src="<?php echo asset_url(); ?>/web/js/jquery.js"></script>
        <script src="<?php echo asset_url(); ?>/web/js/bootstrap.min.js"></script>
</body>
</html>
