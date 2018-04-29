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

		
       
    </head>

    <body>

        <section id="container" >
            <!-- **********************************************************************************************************************************************************
            TOP BAR CONTENT & NOTIFICATIONS
            *********************************************************************************************************************************************************** -->
			<!--header start-->
				<header class="header black-bg">
                    <a class="logo"><img class="butterfli-logo" src="<?= asset_url() . '/web/img/form-logo.png' ?>" alt="Logo"></a>
				</header>
				<!--header end-->

            <!-- **********************************************************************************************************************************************************
            MAIN CONTENT
            *********************************************************************************************************************************************************** -->
            <!--main content start-->
            <section id="main-content" style="margin-left:0px;">
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


