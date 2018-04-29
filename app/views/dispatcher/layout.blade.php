<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/html">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="Dashboard">
    <meta name="keyword" content="Dashboard, Bootstrap, Admin, Template, Theme, Responsive, Fluid, Retina">

    <title>{{Config::get('app.website_title');}}</title>


    <?php
    $user_id = Session::get('user_id');

    if(Session::get('is_admin') == 1) {
        $request = RideRequest::where('request.is_confirmed', '=', '0')
            ->where('request.is_cancelled', '=', '0')
            ->where('request.is_completed', '=', '0')
            ->select('confirmed_walker')->get();
    } else{
        $request = DB::table('assigned_dispatcher_request')
            ->Join('request', 'request.id', '=', 'assigned_dispatcher_request.request_id')
            ->select('request.confirmed_walker')
            ->where('assigned_dispatcher_request.assigned_dispatcher_id', '=', $user_id)
            ->where('request.is_confirmed', '=', '0')
            ->where('request.is_cancelled', '=', '0')
            ->where('request.is_completed', '=', '0')
            ->where('assigned_dispatcher_request.is_cancelled', '=', '0')
            ->get();
    }
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
    <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet"/>

    <link rel="stylesheet" href="<?php echo asset_url(); ?>/web/css/bootstrap-datetimepicker.css"/>
    <!--external css-->
    <link href="<?php echo asset_url(); ?>/web/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="<?php echo asset_url(); ?>/web/js/gritter/css/jquery.gritter.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Ladda/1.0.4/ladda-themeless.min.css">
    <!-- Custom styles for this template -->
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
    <style>
        @media only screen and (min-device-width : 300px) and (max-device-width : 700px)
        {
            .mobile-hide
            {
                display: none!important;
            }
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
    <script src="<?php echo asset_url(); ?>/web/js/jquery.js"></script>
    <script src="<?php echo asset_url(); ?>/web/js/jquery-1.8.3.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="<?php echo asset_url(); ?>/web/js/bootstrap.min.js"></script>
    <script src="https://momentjs.com/downloads/moment.min.js"></script>
    <script src="https://momentjs.com/downloads/moment-timezone-with-data.min.js"></script>
    <script src="<?php echo asset_url(); ?>/web/js/mdb.js"></script>
    <script src="<?php echo asset_url(); ?>/web/js/mdb.min.js"></script>
    <script src="<?php echo asset_url(); ?>/web/js/bootstrap-datetimepicker.js"></script>
    <script src="<?php echo asset_url(); ?>/web/js/notify.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Ladda/1.0.4/spin.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Ladda/1.0.4/ladda.min.js"></script>
    <script class="include" type="text/javascript" src="<?php echo asset_url(); ?>/web/js/jquery.dcjqaccordion.2.7.js"></script>
    <script src="<?php echo asset_url(); ?>/web/js/jquery.scrollTo.min.js"></script>
    <script src="<?php echo asset_url(); ?>/web/js/jquery.nicescroll.js" type="text/javascript"></script>
    <script src="<?php echo asset_url(); ?>/web/js/jquery.sparkline.js"></script>
    <!--common script for all pages-->


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
        <a class="logo"><img class="butterfli-logo" src="<?= asset_url() . '/web/img/form-logo.png' ?>" alt="Logo" style="width:150px;"/></a>

               <span class="search-panel-area mobile-hide">
                <input type="text" id="search51" value="" placeholder="" onclick="search_hide();"/>
                <span class="search_menu" id="search_menu" onclick="search_hide();">Search....</span>
                <button class="search-btn-icon new_button"><i class="fa fa-search" style="color: white;" aria-hidden="true"></i></button>
                </span>

        <div class="top-menu">
            <ul class="nav pull-right top-menu">
                <li><a class="logout" href="{{route('/dispatcher/logout')}}">{{trans('customize.log_out')}}</a></li>
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
            <ul class="sidebar-menu" id="">
                <!--ul class="sidebar-menu" id="nav-accordion"-->
            <?php
            $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            ?>
                <p class="centered"><a href="#"><img src="<?= Session::get('user_pic') ? Session::get('user_pic') : asset_url() . '/web/img/default_icon.png' ?>" class="img-circle" width="50%"></a></p>
                <h5 class="centered">{{ Session::get('username') }}</h5>
            <!--li class="">
                <a <?php if($url==route('myservice')){ ?>class="active" <?php } ?> id="payment" href="{{route('myservice')}}">
                     <i class="fa fa-car"></i>
                    <span>Automatic Rides</span>
                </a>
            </li-->
                <li data-toggle="" data-target="#new" class="sub-menu">
                    <a href="<?php echo route('submittedrides')?>"><i class="fa fa-power-off"></i><span class="title">New Rides</span></a>
                    <ul class="sub-menu" id="new">
                        <li>
                            <a <?php if($url==route('submittedrides')){ ?>class="active" <?php } ?> id="submittedride" href="{{route('submittedrides')}}">
                                <i class="fa fa-car"></i>
                                <span>Unassigned (<?php echo $countarray['submitted'] ?>)</span>
                            </a>
                        </li>

                        <li>
                            <a <?php if($url==route('confirmedrides')){ ?>class="active" <?php } ?> id="confirmedride" href="{{route('confirmedrides')}}">
                                <i class="fa fa-car"></i>
                                <span>Confirmed (<?php echo $countarray['confirmed'] ?>)</span>
                            </a>
                        </li>

                        <li>
                            <a <?php if($url==route('cancelledrides')){ ?>class="active" <?php } ?> id="cancelledride" href="{{route('cancelledrides')}}">
                                <i class="fa fa-car"></i>
                                <span>Cancelled (<?php echo $countarray['cancelled'] ?>)</span>
                            </a>
                        </li>
                    </ul>
                </li>



                <li data-toggle="" data-target="#new1" class="sub-menu">
                    <a href="<?php echo route('completedrides')?>"><i class="fa fa-power-off"></i><span class="title">Past Rides</span></a>
                    <ul class="sub-menu" id="new1">
                        <li>
                            <a <?php if($url==route('completedrides')){ ?>class="active" <?php } ?> id="completedride" href="{{route('completedrides')}}">
                                <i class="fa fa-car"></i>
                                <span>Completed Rides (<?php echo $countarray['completed'] ?>)</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php
                if(Session::get('is_admin') == 1){
                ?>
                <li class="" id="flow1">
                    <a <?php if($url==route('requestservice')){ ?>class="active" <?php } ?> id="request" href="{{route('requestservice')}}">
                        <i class="fa fa-phone"></i>
                        <span>Request {{trans('customize.Trip')}}</span>
                    </a>
                </li>
                <?php
                }
                ?>
                <li class="">
                    <a <?php if($url==route('TrainingModules')){ ?>class="active" <?php } ?> id="module" href="{{route('TrainingModules')}}">
                        <i class="fa fa-book"></i>
                        <span>Training Modules</span>
                    </a>
                </li>

                <li class="">
                    <a <?php if($url==route('RegisterDrivers')){ ?>class="active" <?php } ?> id="profile" href="{{route('RegisterDrivers')}}">
                        <i class="fa fa-users"></i>
                        <span>Register Drivers</span>
                    </a>
                </li>

                <li class="">
                    <a <?php if($url==route('DispatcherProfile')){ ?>class="active" <?php } ?> id="profile" href="{{route('DispatcherProfile')}}">
                        <i class="fa fa-briefcase"></i>
                        <span>My Profile</span>
                    </a>
                </li>
                <li class="">
                    <a id="logout" href="{{route('/dispatcher/logout')}}">
                        <i class="fa fa-power-off"></i>
                        <span>{{trans('customize.log_out')}}</span>
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
            <h3><strong><!--<i class="fa fa-angle-right"></i>--> {{ $title }}</strong></h3>
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

<script>
    var check_request = <?php echo count($request)?>;
    var number_of_rides = check_request;



    //commented until server issues fixed
/*var tick = <?php echo Session::get('test'); ?>1;
    $(function(){
        setInterval(function(){
            $.ajax({
                url: '<?php echo URL::Route('Notification') ?>',
                type: 'post',
                success: function(response){
                    var request = response.length;
                    if(request !== check_request || tick === 11 && (request - check_request) >= 0) {
                        check(response.length);
                        check_request = request;
                        tick = <?php echo Session::forget('test'); ?>1;
                    }
                }
            });
        }, 15000)
    });*/



    function check(checked){
        if(checked > 0) {
            var current_request = checked - number_of_rides;
            if(current_request !== 0 && current_request > 0) {
                $('#notification-count').show();
                $('#notification-count').css("background-color","#6a00bc");
                $counter = $('.notification-counter');
                val = parseInt($counter.text());
                $counter.text(current_request);
            }else{
                $('#notification-count').hide();
            }



                if (!Notification) {
                    alert('Desktop notifications not available in your browser. Try Chromium.');
                }
                if (Notification.permission !== "granted")
                    Notification.requestPermission();


                var notification = new Notification('Butter-Fli', {
                    icon: 'https://s3-us-west-2.amazonaws.com/butterfli-bucket/email_icons/footer.png',
                    body: checked + ' ' + "Rides Pending!",
                });

                notification.onclick = function () {
                    window.focus();
                    setTimeout(function () {
                        notification.close();
                    }, 1000);
                };
            }
    }

    function notify(){
        document.getElementsByClassName('notification-counter').style.background = '#ffffff';
    }

    function search_hide() {
        document.getElementById('search_menu').style.display = "none";
        $('#search51').on({
            focus: function () {
                $(this).addClass('focused');
            },
            blur: function () {
                $(this).removeClass('focused');
                document.getElementById('search_menu').style.display = "";
            }
        });
    }
</script>
<script src="<?php echo asset_url(); ?>/web/js/common-scripts.js"></script>

    </body>
</html>


