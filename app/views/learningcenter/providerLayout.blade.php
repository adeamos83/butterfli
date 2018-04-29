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
    <link rel="stylesheet" href="<?php echo asset_url(); ?>/web/css/bootstrap-datetimepicker.css">        <!--external css-->
    <link href="<?php echo asset_url(); ?>/web/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="<?php echo asset_url(); ?>/web/js/gritter/css/jquery.gritter.css" />
    <link href="<?php echo asset_url(); ?>/web/css/style.css" rel="stylesheet">
    <link href="<?php echo asset_url(); ?>/web/css/style-responsive.css" rel="stylesheet">
    <link href="<?php echo asset_url(); ?>/web/css/Roboto-Bold.woff" rel="stylesheet">
    <link href="<?php echo asset_url(); ?>/web/css/Roboto-Bold.woff2" rel="stylesheet">
    <link href="<?php echo asset_url(); ?>/web/css/Roboto-Light.woff" rel="stylesheet">
    <link href="<?php echo asset_url(); ?>/web/css/Roboto-Light.woff2" rel="stylesheet">
    <link href="<?php echo asset_url(); ?>/web/css/Roboto-Regular.woff" rel="stylesheet">
    <link href="<?php echo asset_url(); ?>/web/css/Roboto-Regular.woff2" rel="stylesheet">
    <link href="<?php echo asset_url(); ?>/web/css/mdb.min.css" rel="stylesheet" />
    <link href="<?php echo asset_url(); ?>/web/css/mdb.css" rel="stylesheet" />
    <!-- Custom styles for this template -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Ladda/1.0.4/ladda-themeless.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Ladda/1.0.4/spin.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Ladda/1.0.4/ladda.min.js"></script>

    <script src="<?php echo asset_url(); ?>/web/js/jquery.js"></script>
    <script src="<?php echo asset_url(); ?>/web/js/jquery-1.8.3.min.js"></script>
    <script src="https://momentjs.com/downloads/moment.min.js"></script>
    <script src="https://momentjs.com/downloads/moment-timezone-with-data.min.js"></script>
    <script src="<?php echo asset_url(); ?>/web/js/bootstrap.min.js"></script>
    <script src="<?php echo asset_url(); ?>/web/js/jquery.dcjqaccordion.2.7.js"></script>
    <script src="<?php echo asset_url(); ?>/web/js/jquery.scrollTo.min.js"></script>
    <script src="<?php echo asset_url(); ?>/web/js/jquery.nicescroll.js" type="text/javascript"></script>
    <script src="<?php echo asset_url(); ?>/web/js/jquery.sparkline.js"></script>
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
        <div class="sidebar-toggle-box">
            <div class="fa fa-bars tooltips" data-placement="right" data-original-title="Toggle Navigation"></div>
        </div>
        <!--logo start-->
        <a class="logo"><img class="butterfli-logo" src="<?= asset_url() . '/web/img/form-logo.png' ?>" alt="Logo" style="width:150px;"/></a>
        <!--logo end-->
        <div class="header-menu top-menu">
            <ul class="nav pull-right top-menu">
                <li><a class="logout" href="{{route('ProviderLogout')}}">{{trans('customize.log_out'); }}</a></li>
            </ul>
        </div>
    </header>
    <!--header end-->

    <!-- **********************************************************************************************************************************************************
    MAIN SIDEBAR MENU
    *********************************************************************************************************************************************************** -->
    <!--sidebar start-->
    <aside>
        <div id="sidebar"  class="nav-collapse ">
            <!-- sidebar menu start-->
            <ul class="sidebar-menu" id="nav-accordion">
                <?php
                $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
                $url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                ?>
                <p class="centered"><a href="profile.html"><img src="<?= Session::get('walker_pic') ?  Session::get('walker_pic') : asset_url() . '/web/img/default_icon.png' ?>" class="img-circle" width="60"></a></p>
                <!--h5 class="centered">Marcel Newman</h5-->
            <!--h5 class="centered">{{ Session::get('username') }}</h5-->
                <li class="mt">
                    <a <?php if($url==route('DriverLearningCenter')){ ?>class="active" <?php } ?> id="learning" href="{{route('DriverLearningCenter')}}">
                        <i class="fa fa-car"></i>
                        <span>Driver Learning Center</span>
                    </a>
                </li>
                <li class="sub-menu">
                    <a <?php if($url==route('providerProfile')){ ?>class="active" <?php } ?> id="profile" href="{{route('providerProfile')}}">
                        <i class="fa fa-briefcase"></i>
                        <span>My Profile</span>
                    </a>
                </li>
                <li class="sub-menu">
                    <a id="logout" href="{{route('ProviderLogout')}}">
                        <i class="fa fa-power-off"></i>
                        <span>{{trans('customize.log_out')}}</span>
                    </a>
                </li>
            </ul>
            <!-- sidebar menu end-->
        </div>
    </aside>
    <!--sidebar end-->
    <!--main content start-->
    <section id="main-content">
        <section class="wrapper site-min-height">
            <h3 style="text-align: center;padding-top:0.5em;"><!--<i class="fa fa-angle-right"></i>--> {{ $title }}</h3>
            <div class="row mt">
                <div class="col-lg-12">
                    @yield('content')
                </div>
            </div>

        </section>
    </section>
    <!--main content end-->
    <!-- Script Strart-->
    <script src="<?php echo asset_url(); ?>/web/js/common-scripts.js"></script>
    <!--script end -->
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

