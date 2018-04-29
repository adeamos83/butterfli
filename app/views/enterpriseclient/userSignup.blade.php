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
    $image = EnterpriseClient::all();
    $theme = Theme::all();
    $active = '#000066';
    $logo = '/image/logo.png';
    $favicon = '/image/favicon.ico';
    foreach ($theme as $themes) {
        $active = $themes->active_color;
        $favicon = '/uploads/' . $themes->favicon;
        $logo = '/uploads/' . $themes->logo;
    }
    if ($logo == '') {
        $logo = '/image/logo.png';
    }
    if ($favicon == '') {
        $favicon = '/image/favicon.ico';
    }
    ?>

    <link rel="icon" type="image/ico" href="<?php echo asset_url(); ?><?php echo $favicon; ?>">


    <?php
    $image = EnterpriseClient::all();
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
    <!--external css-->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" />
    <link href="<?php echo asset_url(); ?>/web/css/dispatcher.css" rel="stylesheet">
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
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <script src="https://momentjs.com/downloads/moment.min.js"></script>
    <script src="https://momentjs.com/downloads/moment-timezone-with-data.min.js"></script>
    <script type="text/javascript" src="https://developer.jboss.org/servlet/JiveServlet/previewBody/52971-102-1-171969/jstz-1.0.4.min.js"></script>
    <script src="<?php echo asset_url(); ?>/web/js/validation.js"></script>
    <script type="text/javascript" src="<?php echo asset_url(); ?>/website/scripts/timezone/jstz.js"></script>
    <script type="text/javascript" src="<?php echo asset_url(); ?>/website/scripts/timezone/prettify.js"></script>
    <script type="text/javascript" src="<?php echo asset_url(); ?>/website/scripts/timezone/jquery.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            var tz = jstz.determine();

            response_text = 'No timezone found';

            if (typeof (tz) === 'undefined') {
                response_text = 'No timezone found';
            }
            else {
                response_text = tz.name();
            }

            $('#tz_info').val(response_text);

            $('#code-example').html("> var timezone = jstz.determine();\n" +
                "> timezone.name(); \n" +
                "\"" + tz.name() + "\"\n\n");
            /*$('#tz_info').show();*/
            prettyPrint();
        });
    </script>
</head>

<body>
<div class="wrapper form-wrapper sign_up_wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 login-area">
                <form class="form-login" id="healthcare_signin" enctype="multipart/form-data" onsubmit="Checkfiles(this)" action="{{URL::Route('/booking/save')}}" method="post">
                    <img src="<?php echo asset_url(); ?>/web/img/form-logo.png" alt="form-logo" />
                    <div class="form-input-wrapper">
                        <div class="input-wrapper">
                            <span id="no_email_error1" style="display: none"></span>
                            <input style="width:20px;height:17px;vertical-align:-3px;" type="radio" name="user_select"  value="1" onclick="company()" checked="checked"><strong style="font-size:16px;">Corporate</strong>
                            <!--input style="width:20px;height:17px;margin-left: 25px;vertical-align:-3px;" type="radio" name="user_select" onclick="agent()" value="2"> <strong style="font-size:16px;margin-left:-4px;">Agent</strong-->
                            <input style="width:20px;height:17px;margin-left: 25px;vertical-align:-3px;" type="radio" name="user_select" onclick="user()" value="3"> <strong style="font-size:16px;margin-left:-4px;">Consumer</strong>
                        </div>
                        <div class="input-wrapper">
                            <span class="fa fa-user"></span>
                            <input type="text" name="contact_name" required  placeholder="Your Name" autofocus>
                        </div>
                        <div class="input-wrapper">
                            <span class="fa fa-building-o"></span>
                            <input type="text" id="company_name" name="company_name" class="form-control" placeholder="Company Name" ></li>
                        </div>
                        <div class="input-wrapper">
                            <span class="fa fa-envelope"></span>
                            <input type="text" name="email"  placeholder="Email Address" onblur="ValidateEmail(1)" id="email_check1" required="" >
                        </div>
                        <div class="input-wrapper">
                            <span class="fa fa-lock"></span>
                            <input type="password" name="password" required  placeholder="Password">
                            <input type="hidden" name="timezone" id="tz_info" value="">
                        </div>
                        <div class="input-wrapper">
                            <span class="fa fa-phone"></span>
                            <input type="text" name="operator_phone" class="form-control" placeholder="Phone"></li>
                        </div>
                        <div class="input-wrapper">
                            <span class="company_option_border">
                                <img src="/web/img/input-company-icon.png" id="companylogo_icon" style="margin-right:15px;padding-bottom:7px;">
                            </span>
                        </div>
<!--                        <div class="input-wrapper">
                            <input type="file" class="companylogo" id="companylogo" data-max-size="2097152" name="companylogo" accept="companylogo/*" style="display:none;color:black;"/>
                            <img src="<?php echo asset_url();?>/web/img/add.png" id="upfile1" style="cursor:pointer;height:40px;width:40px;float:left;margin-left:56px;margin-top:15px;"/><span id="company"><p style="float:left;margin-top:22px;color:black;margin-left:7px; ">Company Logo</p></span>
                            <?php if (isset($image->id)) { ?>
                                <img src="<?php echo asset_url();?> \uploads\<?= $image->companylogo;?>" />
                            <?php  } ?>
                        </div> -->
                        <div class="input-wrapper">
                            <div id="agent_checkbox">
                                <label for="agent_select">
                                    <strong style="font-size:16px;">Agent</strong>
                                    <input style="width:20px;height:17px;margin-left: 67px;vertical-align:-3px;float: left;" type="checkbox" id="agent_select" name="agent_select"
                                   onclick="agent()" value="2">
                               </label>
                            </div>
                        </div>
                        <script>
                            $(function(){
                                document.getElementById('companylogo_icon').style.display = "none";
                                document.getElementById('companyname').style.display = "none";
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
                            function agent()
                            {
                                var agent_select = + $('#agent_select').is(':checked');
                                if(agent_select==1){
                                    document.getElementById('company').style.display = "none";
                                    document.getElementById('upfile1').style.display = "none";
                                    document.getElementById('company_name').style.display = "none";
                                    document.getElementById('companyname').style.display = "";
                                    document.getElementById('companylogo_icon').style.display = "";
                                } else{
                                    document.getElementById('companylogo_icon').style.display = "none";
                                    document.getElementById('company_name').style.display = "";
                                    document.getElementById('companylogo_icon').style.display = "none";
                                    document.getElementById('company').style.display = "";
                                    document.getElementById('upfile1').style.display = "";
                                    document.getElementById('companyname').style.display = "none";
                                }

                            }
                            function user(){
                                document.getElementById('company').style.display = "none";
                                document.getElementById('company_name').style.display = "none";
                                document.getElementById('companyname').style.display = "none";
                                document.getElementById('upfile1').style.display = "none";
                                document.getElementById('agent_checkbox').style.display = "none";
                                document.getElementById('companylogo_icon').style.display = "none";
                            }
                            function company()
                            {
                                document.getElementById('companyname').style.display = "none";
                                document.getElementById('companylogo_icon').style.display = "none";
                                document.getElementById('company').style.display = "";
                                document.getElementById('upfile1').style.display = "";
                                document.getElementById('company_name').style.display = "";
                                document.getElementById('agent_checkbox').style.display = "";
                            }


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
                                $("#companylogo").trigger('click');
                            });
                            $("#upfile2").click(function () {
                                $("#file2").trigger('click');
                            });
                            $("#upfile3").click(function () {
                                $("#file3").trigger('click');
                            });
                        </script>
                        <div>
                            @if(Session::has('error'))
                                <div class="alert alert-danger">
                                    <b>{{ Session::get('error') }}</b>
                                </div>
                            @endif
                        </div>
                        <div>
                            <button id="register" class="btn btn-theme btn-block" type="submit">Sign up</button>
                        </div>
                        <div class="registration half">
                            Do you have an account already?<br/>
                            <a class="" href="{{route('/booking/signin')}}">
                                Sign in
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


</body>
</html>
