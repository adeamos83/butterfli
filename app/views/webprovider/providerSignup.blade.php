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

        $logo = '/image/logo.png';
        $favicon = '/image/favicon.ico';
        foreach ($theme as $themes) {
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

        <!-- Bootstrap core CSS -->
        <link href="<?php echo asset_url(); ?>/web/css/bootstrap.css" rel="stylesheet">
        <!--external css-->
        <link href="<?php echo asset_url(); ?>/web/font-awesome/css/font-awesome.css" rel="stylesheet" />

        <!-- Custom styles for this template -->
        <link href="<?php echo asset_url(); ?>/web/css/dispatcher.css" rel="stylesheet">
        <link href="<?php echo asset_url(); ?>/web/css/style-responsive.css" rel="stylesheet">

        <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
          <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->
        <?php
        $transportation_provider = TransportationProvider::all();
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

        <script src="https://momentjs.com/downloads/moment.min.js"></script>
        <script src="https://momentjs.com/downloads/moment-timezone-with-data.min.js"></script>
        <script type="text/javascript" src="https://developer.jboss.org/servlet/JiveServlet/previewBody/52971-102-1-171969/jstz-1.0.4.min.js"></script>
        <script src="<?php echo asset_url(); ?>/web/js/validation.js"></script>
        <script type="text/javascript" src="<?php echo asset_url(); ?>/website/scripts/timezone/jstz.js"></script>
        <script type="text/javascript" src="<?php echo asset_url(); ?>/website/scripts/timezone/prettify.js"></script>
        <script type="text/javascript" src="<?php echo asset_url(); ?>/website/scripts/timezone/jquery.js"></script>
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
        </style>
    </head>

    <body>
    <div class="wrapper form-wrapper sign_up_wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 login-area">
                    <form class="form-login" enctype="multipart/form-data" onsubmit="Checkfiles(this)" action="{{URL::Route('ProviderSave')}}" method="post">
                        <img src="<?php echo asset_url(); ?>/web/img/form-logo.png" alt="form-logo" />
                        <ul>
                            <span id="no_email_error1" style="display: none"> </span>

                            <li><input type="text" name="contact_name" required  placeholder="Name" autofocus></li>
                            <li>
                                <input type="text" name="email"  placeholder="Email Address" onblur="ValidateEmail(1)" id="email_check1" required="" >
                            </li>
                            <li><input type="text" name="company_name" required  placeholder="Company" autofocus></li>
                            <li>
                                <input type="password" name="password" required  placeholder="Password">
                                <input type="hidden" name="timezone" id="tz_info" value="">
                                <input type="hidden" name="type" id="type" value="2">
                            </li>

                            <li><input type="text" name="phone" class="form-control" placeholder="Phone" ></li>
                            <br>

                            <li>
                                <span class="company_option_border"> <img src="/web/img/input-company-icon.png" id="companylogo_icon" style="margin-right:15px;padding-bottom:7px;">
                                    </span>
                            </li>
                            <li>
                                <input type="file" class="companylogo" id="picture" data-max-size="2097152" name="picture" accept="companylogo/*" style="display:none;color:black;"/>
                                <img src="<?php echo asset_url();?>/web/img/add.png" id="upfile1" style="cursor:pointer;height:40px;width:40px;float:left;margin-left:56px;margin-top:15px;"/><span id="company">
                                    <p style="float:left;margin-top:22px;color:black;margin-left:7px; ">Picture</p></span>
                            </li>
                            <script>
                                $(function(){
                                    var fileInput = $('.companylogo');
                                    var maxSize = fileInput.data('max-size');
                                    $('.form-login').submit(function(e){
                                        if(fileInput.get(0).files.length){
                                            var fileSize = fileInput.get(0).files[0].size; // in bytes
                                            if(fileSize>maxSize){
                                                alert('file size is more than 2Mb');
                                                return false;
                                            }
                                        }

                                    });
                                });
                                $(document).ready(function(e) {
                                    $(".showonhover").click(function(){
                                        $("#selectfile").trigger('click');
                                    });
                                });


                                var input = document.querySelector('input[type=file]'); // see Example 4

                                input.onchange = function () {
                                    var file = input.files[0];

                                    drawOnCanvas(file);   // see Example 6
                                    displayAsImage(file); // see Example 7
                                };


                                function drawOnCanvas(file) {
                                    var reader = new FileReader();

                                    reader.onload = function (e) {
                                        var dataURL = e.target.result,
                                            c = document.querySelector('canvas'), // see Example 4
                                            ctx = c.getContext('2d'),
                                            img = new Image();

                                        img.onload = function() {
                                            c.width = img.width;
                                            c.height = img.height;
                                            ctx.drawImage(img, 0, 0);
                                        };

                                        img.src = dataURL;
                                    };

                                    reader.readAsDataURL(file);
                                }

                                function displayAsImage(file) {
                                    var imgURL = URL.createObjectURL(file),
                                        img = document.createElement('img');

                                    img.onload = function() {
                                        URL.revokeObjectURL(imgURL);
                                    };

                                    img.src = imgURL;
                                    document.body.appendChild(img);
                                }

                                $("#upfile1").click(function () {
                                    $("#picture").trigger('click');
                                });
                                $("#upfile2").click(function () {
                                    $("#file2").trigger('click');
                                });
                                $("#upfile3").click(function () {
                                    $("#file3").trigger('click');
                                });
                            </script>
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
                                <a class="" href="{{route('ProviderSignin')}}">
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