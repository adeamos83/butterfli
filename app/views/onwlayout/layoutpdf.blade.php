<!DOCTYPE html>
<html>
    <!-- START Head -->
    <head>
        <?php $theme = Theme::all(); ?>
        <!-- START META SECTION -->
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="author" content="pampersdry.info">
        <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0">
        <title><?= $title ?> | <?= Config::get('app.website_title') ?> Web Dashboard</title>

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

        <link rel="icon" type="image/ico" href="<?php echo asset_url(); ?><?php echo $favicon; ?>">
        <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <!-- Ionicons -->
        <link href="//code.ionicframework.com/ionicons/1.5.2/css/ionicons.min.css" rel="stylesheet" type="text/css" />
        <!-- Theme style -->
        <link href="<?php echo asset_url(); ?>/admins/css/AdminLTE.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo asset_url(); ?>/admins/css/custom-admin.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo asset_url(); ?>/admins/css/style.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo asset_url(); ?>/admins/css/jquery.datetimepicker.css" rel="stylesheet" type="text/css" />

        <!--      Day tour js file for add extra input and image-->
        <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
        <!--        <link rel="stylesheet" href="//code.jquery.com/ui/1.11.3/themes/smoothness/jquery-ui.css">-->
        <!--        <script src="//code.jquery.com/jquery-1.10.2.js"></script>-->
        <script src="<?php echo asset_url(); ?>/admins/js/validator/jquery.validate.min.js"></script>
        <script src="<?php echo asset_url(); ?>/admins/js/validator/jquery.validate.min.js"></script>
        <script src="<?php echo asset_url(); ?>/admins/js/jquery.min"></script>
        <script src="<?php echo asset_url(); ?>/admins/js/AdminLTE/app.js" type="text/javascript"></script>

        <script src="<?php echo asset_url(); ?>/admins/js/jquery.datetimepicker.js"></script>
        <script src="//code.jquery.com/ui/1.11.3/jquery-ui.js"></script>
        <script src="<?php echo asset_url(); ?>/admins/js/jquery.js"></script>
        <script src="<?php echo asset_url(); ?>/admins/js/jquery1.js"></script>


        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
          <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->

        <style type="text/css">
            .error{
                color:red;
            }
        </style>

    </head>

    <body class="skin-blue" >
        <!-- header logo: style can be found in header.less -->
        <header class="header">

            <!-- Header Navbar: style can be found in header.less -->

        </header>
        <div class="wrapper row-offcanvas row-offcanvas-left">
            <!-- Left side column. contains the logo and sidebar -->


            <!-- Right side column. Contains the navbar and content of the page -->
            <div class="row col-md-12">
                

                <aside class="right-side">
                    <div class="row col-md-12">
<!--                    <a  class="logo" href="http://rider-sa.co.za" >
                         Add the class icon to your logo image or logo icon to add the margining 
                        <img src="http://rider-sa.co.za/website/new/images/logo.png"  width="80" height="40"> 
                    </a>-->
                </div>

                    <section class="content-header">
                       

                    </section>

                    <!-- Main content -->
                    <section class="content">

                        <div class="page-container">

                            <div class="page-content-wrapper">
                                @yield('content')
                            </div>
                        </div>


                    </section><!-- /.content -->
                </aside><!-- /.right-side -->
            </div><!-- ./wrapper -->
        </div>






        <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js" type="text/javascript"></script>
        <!-- AdminLTE App -->

        <!-- AdminLTE for demo purposes -->
        <script src="<?php echo asset_url(); ?>/admins/js/AdminLTE/demo.js" type="text/javascript"></script>

        <script type="text/javascript">



            $("#<?= $page ?>").addClass("active");
            $('#option3').show();
            $('.fade').css('opacity', '1');
            $('.nav-pills > li.active > a, .nav-pills > li.active > a:hover, .nav-pills > li.active > a:focus').css('color', '#ffffff');
            $('.nav-pills > li.active > a, .nav-pills > li.active > a:hover, .nav-pills > li.active > a:focus').css('background-color', '<?php echo $active; ?>');

        </script>





        <script>
            $(function () {

                $("#start-date").datepicker({
                    defaultDate: "+1w",
                    changeMonth: true,
                    numberOfMonths: 1,
                    onClose: function (selectedDate) {
                        $("#end-date").datepicker("option", "minDate", selectedDate);
                    }
                });
                $("#end-date").datepicker({
                    defaultDate: "+1w",
                    changeMonth: true,
                    numberOfMonths: 1,
                    onClose: function (selectedDate) {
                        $("#start-date").datepicker("option", "maxDate", selectedDate);
                    }
                });
            });
        </script>
        <script type="text/javascript">
            $(document).ready(function () {
                $("#myModal").modal('show');
            });
        </script>







    </body>
    <!--/ END Body -->
</html>
