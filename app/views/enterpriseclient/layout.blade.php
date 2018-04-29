<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="">
        <meta name="author" content="Dashboard">
        <meta name="keyword" content="Dashboard, Bootstrap, Admin, Template, Theme, Responsive, Fluid, Retina">

        <title>{{Config::get('app.website_title');}}</title>


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
        <link rel="stylesheet" href="<?php echo asset_url(); ?>/web/css/bootstrap-datetimepicker.css">
        <!--external css-->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" />
        <link rel="stylesheet" type="text/css" href="<?php echo asset_url(); ?>/web/js/gritter/css/jquery.gritter.css" />
        <link href="<?php echo asset_url(); ?>/web/css/mdb.min.css" rel="stylesheet" />
        <link href="<?php echo asset_url(); ?>/web/css/mdb.css" rel="stylesheet" />
		 <!-- Custom styles for this template -->
        <link href="<?php echo asset_url(); ?>/web/css/style.css" rel="stylesheet">
        <link href="<?php echo asset_url(); ?>/web/css/style-responsive.css" rel="stylesheet">
        <link href="<?php echo asset_url(); ?>/web/css/jquery.ui.timepicker.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" rel="stylesheet">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
        <script src="<?php echo asset_url(); ?>/web/js/jquery.ui.timepicker.js"></script>
        <script src="<?php echo asset_url(); ?>/web/js/bootstrap.min.js"></script>
        <script src="<?php echo asset_url(); ?>/web/js/bootstrap-tour.js"></script>
		<script src="https://momentjs.com/downloads/moment.min.js"></script>
		<script src="https://momentjs.com/downloads/moment-timezone-with-data.min.js"></script>
        <style>
            *{
                font-family: 'Ruda', sans-serif !important;
            }
            .fa {
                font-family: FontAwesome!important;
                font-size:15px!important;
            }

            .cont {
                text-align: center!important;
                /*background: #111;*/
                color: #EEE;
                /*border-radius: 5px;
                border: thin solid #444;*/
                overflow: hidden;
                width: 100%;
            }

            div.stars {
                width:220px;
                display: inline-block;
            }
            input.star { display: none; }

            label.star {
                float: right;
                padding: 5px;
                font-size: 36px;
                color: #444;
                transition: all .2s;
            }

            input.star:checked ~ label.star:before {
                content: '\f005';
                color: #FD4;
                transition: all .25s;
            }

            input.star-5:checked ~ label.star:before {
                color: #FE7;
                /*text-shadow: 0 0 20px #952;*/
            }

            input.star-1:checked ~ label.star:before { color: #F62; }

            label.star:hover { transform: rotate(-15deg) scale(1.3); }

            label.star:before {
                content: '\f006';
                font-family: FontAwesome;
            }

        </style>

    </head>

    <body>

        <section id="container" >
            <!-- **********************************************************************************************************************************************************
            TOP BAR CONTENT & NOTIFICATIONS
            *********************************************************************************************************************************************************** -->
			<!--header start-->
				<header class="header black-bg">
					<!--<div class="sidebar-toggle-box">
						<div class="fa fa-bars tooltips" data-placement="right" data-original-title="Toggle Navigation"></div>
					</div>-->
					<!--logo start-->

					<a class="logo"><img class="butterfli-logo" src="<?= asset_url() . '/web/img/form-logo.png' ?>" alt="Logo" /><b><?php
							//$siteTitle = Config::get('app.website_title');
							//echo $siteTitle;
							?></b></a>
					<span class="search-panel-area"></span>
					<!--logo end-->
					<div class="nav notify-row" id="top_menu">
						<!--  notification start -->

						<!--  notification end -->
					</div>
					<div class="top-menu">
						<ul class="nav pull-right top-menu">
							<li><a class="logout" href="{{route('/booking/logout')}}">{{trans('customize.log_out'); }}</a></li>
						</ul>
					</div>
				</header>
				<!--header end-->
            <!-- **********************************************************************************************************************************************************
            MAIN SIDEBAR MENU
            *********************************************************************************************************************************************************** -->
            <!--sidebar start-->
            <aside>
                <div id="sidebar"  class="nav-collapse">
                    <!-- sidebar menu start-->
					<!--<a href="/" class="logo-place"><img src="<? // = Session::get('user_pic') ? Session::get('user_pic') : asset_url() . '/web/images/logo_white.png' ?>" alt="Logo" /></a>-->
						<p class="centered"><a href="#">
                                <img src="<?= Session::get('user_pic') ?  Session::get('user_pic') : asset_url() . '/web/img/default_icon.png' ?>" class="img-circle" width="50%" alt="Logo" /></a></p>
                        <!--h5 class="centered">{{ Session::get('username') }}</h5-->
                    <ul class="sidebar-menu" id="nav-accordion">

						<?php
							$protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
							$url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
						?>
						<li class="">
                            <a <?php if($url==route('myservices')){ ?>class="active" <?php } ?> id="payment" href="{{route('myservices')}}">
                                 <i class="fa fa-car"></i>
                                <span>My Rides</span>
                            </a>
                        </li>
						<li class="" id="flow1">
                            <a <?php if($url==route('request-service')){ ?>class="active" <?php } ?> id="request" href="{{route('request-service')}}">
                                <i class="fa fa-phone"></i>
                                <span>Request Ride</span>
                            </a>
                        </li>
                        <!--<li class="" id="flow1" hide()>
                            <a <?php if($url==route('healthcarereceipts')){ ?>class="active" <?php } ?> id="receipts" href="{{route('healthcarereceipts')}}">
                                <i class="fa fa-inbox"></i>
                                <span>View Receipts</span>
                            </a>
                        </li>-->
                        <li class="">
                            <a <?php if($url==route('myprofile')){ ?>class="active" <?php } ?> id="profile" href="{{route('myprofile')}}">
                                <i class="fa fa-briefcase"></i>
                                <span>My Profile</span>
                            </a>
                        </li>
						<li class="">
                            <a id="logout" href="{{route('/booking/logout')}}">
                                <i class="fa fa-power-off"></i>
                                <span>{{trans('customize.log_out'); }}</span>
                            </a>
                        </li>
					</ul>
                    <!-- sidebar menu end-->
                </div>
            </aside>
            <!--sidebar end-->

            <!-- **********************************************************************************************************************************************************
            MAIN CONTENT
            *********************************************************************************************************************************************************** -->
            <!--main content start-->
            <section id="main-content">
                <section class="wrapper site-min-height">
                    <h3><!--<i class="fa fa-angle-right"></i>--> {{ $title }}</h3>
                    <div class="row mt">
                        <div class="col-lg-12">
                            @yield('content')
                        </div>
                    </div>

                </section>
            </section><!-- /MAIN CONTENT -->

            <!--main content end-->
            <!--footer start-->
            <footer class="site-footer">
                <div class="text-center">
<?php date("Y"); ?> - <?php echo Config::get('app.website_title'); ?>
                    <a href="#" class="go-top">
                        <i class="fa fa-angle-up"></i>
                    </a>
                </div>
            </footer>
            <!--footer end-->
        </section>

        <!-- js placed at the end of the document so the pages load faster -->






        <!--common script for all pages-->
        <!--<script src="<?php echo asset_url(); ?>/web/js/common-scripts.js"></script>

    </body>
</html>


