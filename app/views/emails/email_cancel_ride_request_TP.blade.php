<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Ride Request Cancelled</title>
    <style type="text/css">
        /* Client-specific Styles */
        div, p, a, li, td { -webkit-text-size-adjust:none; }
        #outlook a {padding:0;} /* Force Outlook to provide a "view in browser" menu link. */
        html{width: 100%; }
        body{width:100% !important; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; margin:0; padding:0;}
        /* Prevent Webkit and Windows Mobile platforms from changing default font sizes, while not breaking desktop design. */
        .ExternalClass {width:100%;} /* Force Hotmail to display emails at full width */
        .ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {line-height: 100%;} /* Force Hotmail to display normal line spacing. */
        #backgroundTable {margin:0; padding:0; width:100% !important; line-height: 100% !important;}
        img {outline:none; text-decoration:none;border:none; -ms-interpolation-mode: bicubic;}
        a img {border:none;}
        .image_fix {display:block;}
        body {font-family:'Helvetica Neue', helvetica, arial, sans-serif;}
        p {margin: 0px 0px !important;}
        ul{list-style: none;padding-left: 0px;}
        table td {border-collapse: collapse;}
        table { border-collapse:collapse; mso-table-lspace:0pt; mso-table-rspace:0pt; }
        a {color: #33b9ff;text-decoration: none;text-decoration:none!important;}
        /*STYLES*/
        table[class=full] { width: 100%; clear: both; }

        .btn-round {
            border-radius: 20px;
            -webkit-border-radius: 20px;
        }
        /*IPAD STYLES*/
        @media only screen and (max-width: 640px) {
            a[href^="tel"], a[href^="sms"] {
                text-decoration: none;
                color: #33b9ff; /* or whatever your want */
                pointer-events: none;
                cursor: default;
            }
            .mobile_link a[href^="tel"], .mobile_link a[href^="sms"] {
                text-decoration: default;
                color: #33b9ff !important;
                pointer-events: auto;
                cursor: default;
            }
            table[class=devicewidth] {width: 440px!important;text-align:center!important;}
            table[class=devicewidthinner] {width: 420px!important;text-align:center!important;}
            img[class=banner] {width: 440px!important;height:220px!important;}
            img[class=col2img] {width: 440px!important;height:220px!important;}


        }
        /*IPHONE STYLES*/
        @media only screen and (max-width: 480px) {
            a[href^="tel"], a[href^="sms"] {
                text-decoration: none;
                color: #33b9ff; /* or whatever your want */
                pointer-events: none;
                cursor: default;
            }
            .mobile_link a[href^="tel"], .mobile_link a[href^="sms"] {
                text-decoration: default;
                color: #33b9ff !important;
                pointer-events: auto;
                cursor: default;
            }
            table[class=devicewidth] {width: 280px!important;text-align:center!important;}
            table[class=devicewidthinner] {width: 260px!important;text-align:center!important;}
            img[class=banner] {width: 280px!important;height:140px!important;}
            img[class=col2img] {width: 280px!important;height:140px!important;}


        }
    </style>
</head>
<body style="background-color:#FFFFff">
<div align="center" style="background-color:#FFFFff;">

    <!-- Début en-tête -->



    <!-- Fin en-tête -->

    <table id="email-penrose-conteneur" width="660" align="center" style="border-right:1px solid #e2e8ea; border-left:1px solid #e2e8ea; background-color:#ffffff;" border="0" cellspacing="0" cellpadding="0">

        <!-- Début bloc "mise en avant" -->

        <tr>
            <td style="background-color:#FFFFff ">
                <table width="660" class="resp-full-table" align="center" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td class="resp-full-td" valign="top" style="padding:20px; text-align:center;">
                            <span style="font-size:25px; font-family:'Helvetica Neue', helvetica, arial, sans-serif; font-weight:100; color:#ffffff"><a href="{{ web_url() }}" style="color:#ffffff; outline:none; text-decoration:none;"><img src="https://s3-us-west-2.amazonaws.com/butterfli-bucket/email_icons/form-logo.png" width="150"></a></span>
                        </td>
                    </tr>
                    <tr>
                        <td class="resp-full-td" valign="top" style="text-align:center;">
                            <hr style="border:1px solid lightgrey; width:80%;"/>
                            <p style="font-size:20px; line-height:2;color:black;font-weight: bold;">Ride Request Cancelled by Transportation Partner!</p>
                            <br>
                        </td>
                    </tr>
                    <tr>
                        <td class="resp-full-td" valign="top" style="padding:0; text-align:center;">
                            <img src="https://s3-us-west-2.amazonaws.com/butterfli-bucket/email_icons/butterfli-van-icon.png" width="85">
                            <p style="line-height:0;padding:0;color:#000000;font-size:18px;">Ride Detail</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>


    <table id="email-penrose-conteneur" width="660" align="center" style="border-right:1px solid #e2e8ea; border-left:1px solid #e2e8ea; background-color:#ffffff;" border="0" cellspacing="0" cellpadding="0">

        <!-- Début bloc "mise en avant" -->

        <tr>
            <td style="background-color:#FFFFff ">
                <table width="660" class="resp-full-table" align="center" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td class="resp-full-td" valign="top" style="padding-left:20px;padding-top: 20px;">
                            <p>Transport Provider: <strong>{{ $mail_body['transport_partner_email'] }}</strong></p>
                            <br>
                        </td>
                    </tr>
                    <tr>
                        <td class="resp-full-td" valign="top" style="padding-left:20px;">

                            <img src="https://s3-us-west-2.amazonaws.com/butterfli-bucket/email_icons/circle.png" width="9"><span style="color:black;padding-left:3px;position:relative;">Pickup</span>
                            <strong style="padding-left:14px;display:block;color:black;">{{ $mail_body['pickup_location'] }}</strong>
                            <br>
                            <img src="https://s3-us-west-2.amazonaws.com/butterfli-bucket/email_icons/circle_pink.png" width="9"><span style="padding-left:3px;color:black;position:relative;">Dropoff</span>
                            <strong style="padding-left:14px;display:block;color:black;">{{ $mail_body['dropoff_location'] }}</strong>

                        </td>
                    </tr>

                    <tr>
                        <td class="resp-full-td" valign="top" style="padding-top: 20px;text-align:center;">
                            <a href="{{ $mail_body['follow_url'] }}" class="btn-round" style="color:#7f0f7e;">Assign Ride to Confirm</a>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
    <table id="email-penrose-conteneur" width="660" align="center" style="border-right:1px solid #e2e8ea; border-left:1px solid #e2e8ea; background-color:#ffffff;" border="0" cellspacing="0" cellpadding="0">

        <!-- Début bloc "mise en avant" -->

        <tr>
            <td style="background-color:#FFFFff ">
                <table width="660" class="resp-full-table" align="center" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <div style="text-align:center;padding:0 20px;">
                            <hr style="border:1px solid lightgrey; width:80%;"/>
                            <span style="font-size:25px; font-family:'Helvetica Neue', helvetica, arial, sans-serif; font-weight:100; color:#ffffff"><a href="{{ web_url() }}" style="color:#ffffff; outline:none; text-decoration:none;"><img src="https://s3-us-west-2.amazonaws.com/butterfli-bucket/email_icons/dispatcher-icon.png" width="50"></a></span>
                        </div>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <table id="email-penrose-conteneur" width="660" align="center" style="border-right:1px solid #e2e8ea; border-left:1px solid #e2e8ea; background-color:#ffffff;" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td style="background-color:#FFFFff ">
                <table width="660" class="resp-full-table" align="center" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td class="resp-full-td" valign="top" style="padding:0 20px;width:50%;color:black;">
                            ButterFli Dispatcher
                        </td>
                        <td class="resp-full-td" valign="top" style="padding:0 75px; text-align:right;width:50%;">
                            <img src="https://s3-us-west-2.amazonaws.com/butterfli-bucket/email_icons/phone-icon.png" width="18"><span style="float:right;padding-left:3px;color:black;"><strong>855.267.2354</strong></span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table id="email-penrose-conteneur" width="660" align="center" style="border-right:1px solid #e2e8ea; border-bottom:1px solid #e2e8ea; border-left:1px solid #e2e8ea; background-color:#ffffff;" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td style="background-color:#FFFFff; ">
                <table width="660" class="resp-full-table" align="center" border="0" cellspacing="0" cellpadding="0">
                    <tr style="width:100%;">
                        <div style="text-align:center;padding:10px 20px;">
                            <br><br>
                            <hr style="border:1px solid lightgrey; width:80%;"/>
                        </div>
                    </tr>
                    <tr style="width:100%;">
                        <td class="resp-full-td" valign="top" style=" text-align:center;width:100%;">
                            <p style="color:black">Questions? service@gobutterfli.com</p>
                        </td>
                    </tr>
                    <tr style="width:100%;">
                        <td class="resp-full-td" valign="top" style="width:100%;color:black;">
                            <strong>Request generated on {{$mail_body['server']}} Server.</strong>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>
</body>
</html>