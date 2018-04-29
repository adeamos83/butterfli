<!doctype html>
<!--[if lt IE 7]><html lang="en" class="no-js ie6"><![endif]-->
<!--[if IE 7]><html lang="en" class="no-js ie7"><![endif]-->
<!--[if IE 8]><html lang="en" class="no-js ie8"><![endif]-->
<!--[if gt IE 8]><!-->
<html lang="en" class="no-js" style="height:100%;">
<!--<![endif]-->

<head>
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
    <meta charset="UTF-8">
    <title><?= Config::get('app.website_title') ?></title>
        <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="HandheldFriendly" content="true">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <!--<link rel="shortcut icon" href="favicon.ico">-->
    <link rel="icon" type="image/ico" href="<?php echo asset_url() . $favicon; ?>">
    <link rel="stylesheet" href="<?php echo asset_url(); ?>/website/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo asset_url(); ?>/website/css/font-awesome.min.css">
    <link rel="stylesheet" href="<?php echo asset_url(); ?>/website/css/animate.css">
    <link rel="stylesheet" href="<?php echo asset_url(); ?>/website/css/owl.carousel.css">
    <link rel="stylesheet" href="<?php echo asset_url(); ?>/website/css/owl.theme.css">
    <link rel="stylesheet" href="<?php echo asset_url(); ?>/website/css/styles.css">
    <script src="<?php echo asset_url(); ?>/website/js/modernizr.custom.32033.js"></script>
    <script type="text/javascript" src="js/excanvas.compiled.js"></script>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <style>

            .signup-home
            {
                padding:100px 50px 0px 50px;
            }
            @media only screen
            and (min-device-width : 300px)
            and (max-device-width : 800px) {
                .signup-home
                {
                    padding:30px 0px 0px 0px;
                }
                .logo_welcome_image
                {
                    width:225px;
                    height:auto;
                }
                .section-content
                {
                    text-align: center;
                }
                .section-content a{margin-bottom: 15px;}
            }
            @media only screen
            and (min-width : 1224px) {
                .section-content
                {
                    padding-right:9em;
                }
            }
        </style>
</head>

<body class="background_welcome">

<div class="pre-loader">
    <div class="load-con">
    <!--<img src="<?php echo asset_url() ?><?php echo $logo; ?>" class="animated fadeInDown" alt="" width="200">-->
        <div class="spinner">
            <div class="bounce1"></div>
            <div class="bounce2"></div>
            <div class="bounce3"></div>
        </div>
    </div>
</div>


<div class="welcome_back">
    <section id="signup">
        <div class="container-fluid signup-home">
            <div class="row">
                <div class="col-md-8 col-sm-12 col-xs-12 col-md-offset-4 clearfix">
            <div class="section-logo" style="text-align: center;">
                <img class="logo_welcome_image" src="<?php echo asset_url() . '/web/img/form-logo.png'?>"/>
            </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-8 col-sm-12 col-xs-12 col-md-offset-4 clearfix">
                    <div class="section-heading scrollpoint sp-effect3">
                        <h3><?= Config::get('app.welcome_title') ?></h3>
                        <span class="divider"></span>
                    </div>
                </div>
            </div>
            <div class="section-content" style="float: right;">
                <a href="<?php echo web_url(); ?>/booking/signin" class="btn btn-sign-up">I want to Book a Ride</a>
                <a href="<?php echo web_url(); ?>/dispatcher/signin" class="btn btn-sign-up">I am a Transportation Provider</a>
                <a href="<?php echo web_url(); ?>/provider/signin" class="btn btn-sign-up">I am a ButterFLi Driver</a>
            </div>
        </div>
    </section>


   <!--<footer id="contact" style="height:65%;max-height:65%;">
       <div>
<img class="welcome_butterfli" src="<?php echo asset_url() . '/web/img/form-logo.png'?>" />
       </div>
   </footer>-->
</div>



<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="<?php echo asset_url(); ?>/website/js/bootstrap.min.js"></script>
<script src="<?php echo asset_url(); ?>/website/js/owl.carousel.min.js"></script>
<script src="<?php echo asset_url(); ?>/website/js/waypoints.min.js"></script>

<!-- <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyASm3CwaK9qtcZEWYa-iQwHaGi3gcosAJc&sensor=false"></script>

  jQuery REVOLUTION Slider  -->
<script type="text/javascript" src="<?php echo asset_url(); ?>/website/rs-plugin/js/jquery.themepunch.plugins.min.js"></script>
<script type="text/javascript" src="<?php echo asset_url(); ?>/website/rs-plugin/js/jquery.themepunch.revolution.min.js"></script>

<script src="<?php echo asset_url(); ?>/website/js/script.js"></script>
<script>
    $(document).ready(function () {
        appMaster.preLoader();
    });
</script>

</body>

</html>