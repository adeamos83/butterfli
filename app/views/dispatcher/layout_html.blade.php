<!DOCTYPE html>
<html lang="en" class="no-js">
    <head>
        <meta charset="utf-8"/>
        <?php $theme = Theme::all(); ?>
        <title><?= $title ?> | <?= Config::get('app.website_title') ?>Web Dashboard</title>
        <?php
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
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta content="width=device-width, initial-scale=1" name="viewport"/>
        <meta content="" name="description"/>
        <meta content="" name="author"/>
        <!-- BEGIN GLOBAL MANDATORY STYLES -->
        <link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css"/>
        <link href="<?php echo asset_url(); ?>/admins_main/assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo asset_url(); ?>/admins_main/assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo asset_url(); ?>/admins_main/assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo asset_url(); ?>/admins_main/assets/global/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo asset_url(); ?>/admins_main/assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" rel="stylesheet" type="text/css"/>
        <!-- END GLOBAL MANDATORY STYLES -->
        <link href="<?php echo asset_url(); ?>/assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
        <!-- BEGIN PAGE LEVEL PLUGIN STYLES -->
        <link href="<?php echo asset_url(); ?>/admins_main/assets/global/plugins/bootstrap-daterangepicker/daterangepicker-bs3.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo asset_url(); ?>/admins_main/assets/global/plugins/fullcalendar/fullcalendar.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo asset_url(); ?>/admins_main/assets/global/plugins/jqvmap/jqvmap/jqvmap.css" rel="stylesheet" type="text/css"/>
        <link rel="stylesheet" type="text/css" href="<?php echo asset_url(); ?>/admins/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css"/>
        <!-- END PAGE LEVEL PLUGIN STYLES -->
        <!-- BEGIN PAGE STYLES -->
        <link href="<?php echo asset_url(); ?>/admins_main/assets/admin/pages/css/tasks.css" rel="stylesheet" type="text/css"/>
        <!-- END PAGE STYLES -->
        <!-- BEGIN THEME STYLES -->
        <link href="<?php echo asset_url(); ?>/admins_main/assets/global/css/components.css" id="style_components" rel="stylesheet" type="text/css"/>
        <link href="<?php echo asset_url(); ?>/admins_main/assets/global/css/plugins.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo asset_url(); ?>/admins_main/assets/admin/layout/css/layout_html.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo asset_url(); ?>/admins_main/assets/admin/layout/css/themes/darkblue.css" rel="stylesheet" type="text/css" id="style_color"/>
        <link href="<?php echo asset_url(); ?>/admins_main/assets/admin/layout/css/custom.css" rel="stylesheet" type="text/css"/>
        <!-- END THEME STYLES -->
        <link rel="icon" type="image/ico" href="<?php echo asset_url(); ?><?php echo $favicon; ?>">




        <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>

    </head>
    <style>
        table tr td{text-align: center;}
        table tr th{text-align: center;}
        table tr th{background-color: #2866A1;color: #FFFFFF}
        table tr th a{color: #FFFFFF; text-decoration:none;}
        table tr th a:hover{color: #FFFFFF;text-decoration:none;}
        a:hover{text-decoration: none;}
        /*       .choose_file{
            position:relative;
            display:inline-block;    
            border-radius:8px;
            border:#ebebeb solid 1px;
            width:250px; 
            padding: 4px 6px 4px 8px;
            font: normal 14px Myriad Pro, Verdana, Geneva, sans-serif;
            color: #7f7f7f;
            margin-top: 2px;
            background:white
        }
        .choose_file input[type="file"]{
            -webkit-appearance:none; 
            position:absolute;
            top:0; left:0;
            opacity:0; 
        }*/
    </style>
    <!-- END HEAD -->
    <!-- BEGIN BODY -->
    <!-- DOC: Apply "page-header-fixed-mobile" and "page-footer-fixed-mobile" class to body element to force fixed header or footer in mobile devices -->
    <!-- DOC: Apply "page-sidebar-closed" class to the body and "page-sidebar-menu-closed" class to the sidebar menu element to hide the sidebar by default -->
    <!-- DOC: Apply "page-sidebar-hide" class to the body to make the sidebar completely hidden on toggle -->
    <!-- DOC: Apply "page-sidebar-closed-hide-logo" class to the body element to make the logo hidden on sidebar toggle -->
    <!-- DOC: Apply "page-sidebar-hide" class to body element to completely hide the sidebar on sidebar toggle -->
    <!-- DOC: Apply "page-sidebar-fixed" class to have fixed sidebar -->
    <!-- DOC: Apply "page-footer-fixed" class to the body element to have fixed footer -->
    <!-- DOC: Apply "page-sidebar-reversed" class to put the sidebar on the right side -->
    <!-- DOC: Apply "page-full-width" class to the body element to have full width page without the sidebar menu -->
    <body class="page" style="background-color: #ffffff;">
        <!-- BEGIN HEADER -->
        

        <div class="page-container">  

            <!-- BEGIN SIDEBAR -->

            



            <!-- END SIDEBAR -->
            <!-- BEGIN CONTENT -->
            <div class="page-content-wrapper">  
                @yield('content')      
            </div>
        </div>

        <div>

            <div class="scroll-to-top">
                <i class="icon-arrow-up"></i>
            </div>
        </div>
        <!-- END FOOTER -->
        <!-- BEGIN JAVASCRIPTS(Load javascripts at bottom, this will reduce page load time) -->
        <!-- BEGIN CORE PLUGINS -->
        <!--[if lt IE 9]>
        <script src="../../assets/global/plugins/respond.min.js"></script>
        <script src="../../assets/global/plugins/excanvas.min.js"></script> 
        <![endif]-->
        <script src="<?php echo asset_url(); ?>/admins_main/assets/global/plugins/jquery.min.js" type="text/javascript"></script>
        <script src="<?php echo asset_url(); ?>/admins_main/assets/global/plugins/jquery-migrate.min.js" type="text/javascript"></script>
        <!-- IMPORTANT! Load jquery-ui-1.10.3.custom.min.js before bootstrap.min.js to fix bootstrap tooltip conflict with jquery ui tooltip -->
        <script src="<?php echo asset_url(); ?>/admins_main/assets/global/plugins/jquery-ui/jquery-ui-1.10.3.custom.min.js" type="text/javascript"></script>
        <script src="<?php echo asset_url(); ?>/admins_main/assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
        <script src="<?php echo asset_url(); ?>/admins_main/assets/global/plugins/bootstrap-hover-dropdown/bootstrap-hover-dropdown.min.js" type="text/javascript"></script>
        <script src="<?php echo asset_url(); ?>/admins_main/assets/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js" type="text/javascript"></script>
        <script src="<?php echo asset_url(); ?>/admins_main/assets/global/plugins/jquery.blockui.min.js" type="text/javascript"></script>
        <script src="<?php echo asset_url(); ?>/admins_main/assets/global/plugins/jquery.cokie.min.js" type="text/javascript"></script>
        <script src="<?php echo asset_url(); ?>/admins_main/assets/global/plugins/uniform/jquery.uniform.min.js" type="text/javascript"></script>
        <script src="<?php echo asset_url(); ?>/admins_main/assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js" type="text/javascript"></script>
        <!-- END CORE PLUGINS -->
        <!-- BEGIN PAGE LEVEL PLUGINS -->
<!--        <script src="<?php echo asset_url(); ?>/admins/assets/global/plugins/jqvmap/jqvmap/jquery.vmap.js" type="text/javascript"></script>
        <script src="<?php echo asset_url(); ?>/admins/assets/global/plugins/jqvmap/jqvmap/maps/jquery.vmap.russia.js" type="text/javascript"></script>
        <script src="<?php echo asset_url(); ?>/admins/assets/global/plugins/jqvmap/jqvmap/maps/jquery.vmap.world.js" type="text/javascript"></script>
        <script src="<?php echo asset_url(); ?>/admins/assets/global/plugins/jqvmap/jqvmap/maps/jquery.vmap.europe.js" type="text/javascript"></script>
        <script src="<?php echo asset_url(); ?>/admins/assets/global/plugins/jqvmap/jqvmap/maps/jquery.vmap.germany.js" type="text/javascript"></script>
        <script src="<?php echo asset_url(); ?>/admins/assets/global/plugins/jqvmap/jqvmap/maps/jquery.vmap.usa.js" type="text/javascript"></script>
        <script src="<?php echo asset_url(); ?>/admins/assets/global/plugins/jqvmap/jqvmap/data/jquery.vmap.sampledata.js" type="text/javascript"></script>-->
        <script src="<?php echo asset_url(); ?>/admins_main/assets/global/plugins/flot/jquery.flot.min.js" type="text/javascript"></script>
        <script src="<?php echo asset_url(); ?>/admins_main/assets/global/plugins/flot/jquery.flot.resize.min.js" type="text/javascript"></script>
        <script src="<?php echo asset_url(); ?>/admins_main/assets/global/plugins/flot/jquery.flot.categories.min.js" type="text/javascript"></script>
        <script src="<?php echo asset_url(); ?>/admins_main/assets/global/plugins/jquery.pulsate.min.js" type="text/javascript"></script>
        <script src="<?php echo asset_url(); ?>/admins_main/assets/global/plugins/bootstrap-daterangepicker/moment.min.js" type="text/javascript"></script>
        <script src="<?php echo asset_url(); ?>/admins_main/assets/global/plugins/bootstrap-daterangepicker/daterangepicker.js" type="text/javascript"></script>
        <!-- IMPORTANT! fullcalendar depends on jquery-ui-1.10.3.custom.min.js for drag & drop support -->
        <script src="<?php echo asset_url(); ?>/admins_main/assets/global/plugins/fullcalendar/fullcalendar.min.js" type="text/javascript"></script>
        <script src="<?php echo asset_url(); ?>/admins_main/assets/global/plugins/jquery-easypiechart/jquery.easypiechart.min.js" type="text/javascript"></script>
        <script src="<?php echo asset_url(); ?>/admins_main/assets/global/plugins/jquery.sparkline.min.js" type="text/javascript"></script>
        <!-- END PAGE LEVEL PLUGINS -->
        <!-- BEGIN PAGE LEVEL SCRIPTS -->
        <script src="<?php echo asset_url(); ?>/admins_main/assets/global/scripts/metronic.js" type="text/javascript"></script>
        <script src="<?php echo asset_url(); ?>/admins_main/assets/admin/layout/scripts/layout.js" type="text/javascript"></script>
        <script src="<?php echo asset_url(); ?>/admins_main/assets/admin/layout/scripts/quick-sidebar.js" type="text/javascript"></script>
        <script src="<?php echo asset_url(); ?>/admins_main/assets/admin/layout/scripts/demo.js" type="text/javascript"></script>

        <script src="<?php echo asset_url(); ?>/admins_main/assets/admin/pages/scripts/components-ion-sliders.js"></script>
        <script src="<?php echo asset_url(); ?>/admins_main/assets/admin/pages/scripts/index.js" type="text/javascript"></script>
        <script src="<?php echo asset_url(); ?>/admins_main/assets/admin/pages/scripts/tasks.js" type="text/javascript"></script>


        <!--slider begin-->
        <script src="<?php echo asset_url(); ?>/admins_main/assets/admin/pages/scripts/components-ion-sliders.js"></script>
        <script src="<?php echo asset_url(); ?>/admins_main/assets/global/plugins/ion.rangeslider/js/ion-rangeSlider/ion.rangeSlider.min.js"></script>

        <link href="<?php echo asset_url(); ?>/admins_main/assets/global/plugins/ion.rangeslider/css/ion.rangeSlider.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo asset_url(); ?>/admins_main/assets/global/plugins/ion.rangeslider/css/ion.rangeSlider.Metronic.css" rel="stylesheet" type="text/css"/>   
        <!--date picekr js-->
        <script src="<?php echo asset_url(); ?>/admins_main/assets/admin/pages/scripts/components-pickers.js"></script>
        <script type="text/javascript" src="<?php echo asset_url(); ?>/admins/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>



        <!-- END PAGE LEVEL SCRIPTS -->
        <script>
jQuery(document).ready(function () {
    Metronic.init(); // init metronic core componets
    Layout.init(); // init layout
    QuickSidebar.init(); // init quick sidebar
    Demo.init(); // init demo features
    Index.init();
    Index.initDashboardDaterange();
    Index.initJQVMAP(); // init index page's custom scripts
    Index.initCalendar(); // init index page's custom scripts
    Index.initCharts(); // init index page's custom scripts
    Index.initChat();
    Index.initMiniCharts();
    Tasks.initDashboardWidget();
    ComponentsIonSliders.init();
    ComponentsPickers.init();
});
        </script>

        <script type="text/javascript">
            $("#<?= $page ?>").addClass("active");
            $('#option3').show();
            $('.fade').css('opacity', '1');
            $('.nav-pills > li.active > a, .nav-pills > li.active > a:hover, .nav-pills > li.active > a:focus').css('color', '#ffffff');
            $('.nav-pills > li.active > a, .nav-pills > li.active > a:hover, .nav-pills > li.active > a:focus').css('background-color', '<?php echo $active; ?>');
        </script>   



        <!-- END JAVASCRIPTS -->
    </body>
    <!-- END BODY -->

    <!-- END BODY -->
</html>	
