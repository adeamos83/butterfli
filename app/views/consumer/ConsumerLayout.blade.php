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
        <link href="<?php echo asset_url(); ?>/web/font-awesome/css/font-awesome.css" rel="stylesheet" />
        <link rel="stylesheet" type="text/css" href="<?php echo asset_url(); ?>/web/js/gritter/css/jquery.gritter.css" />

		 <!-- Custom styles for this template -->
        <link href="<?php echo asset_url(); ?>/web/css/style.css" rel="stylesheet">
        <link href="<?php echo asset_url(); ?>/web/css/style-responsive.css" rel="stylesheet">
        <script src="<?php echo asset_url(); ?>/web/js/jquery.js"></script>
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
							<li><a class="logout" href="{{route('ConsumerLogout')}}">{{trans('customize.log_out')}}</a></li>
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
                                <img src="<?= Session::get('consumer_pic') ?  Session::get('consumer_pic') : asset_url() . '/web/img/default_icon.png' ?>" class="img-circle" width="50%" alt="Logo" /></a></p>
                        <!--h5 class="centered">{{ Session::get('consumer_name') }}</h5-->
                    <ul class="sidebar-menu" id="nav-accordion">

						<?php
							$protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
							$url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
						?>
                        <li class="">
                            <a <?php if($url==route('ConsumerProfile')){ ?>class="active" <?php } ?> id="profile" href="{{route('ConsumerProfile')}}">
                                <i class="fa fa-briefcase"></i>
                                <span>My Profile</span>
                            </a>
                        </li>
						<li class="">
                            <a id="logout" href="{{route('ConsumerLogout')}}">
                                <i class="fa fa-power-off"></i>
                                <span>{{trans('customize.log_out')}}</span>
                            </a>
                        </li>
					</ul>
                    <!-- sidebar menu end-->
                </div>
            </aside>
            <!--sidebar end-->

            <!--                        <div class="col-sm-12" style=" background-color: #F5F5F5; font-size: 10em !important; color:black; text-align: center; margin-bottom: 5px;"><h3>Safety</h3></div> **********************************************************************************************************************************************************
            MAIN CONTENT
            *********************************************************************************************************************************************************** -->
            <!--main content start-->
            <section id="main-content">
                <section class="wrapper site-min-height">
                    <h3 style="text-align: center"><!--<i class="fa fa-angle-right"></i>--> {{ $title }}</h3>
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


