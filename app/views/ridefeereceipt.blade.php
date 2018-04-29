<!DOCTYPE html>
<html lang="en">
<head>
    <title>Ride Fee Receipts</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
</head>
<style>
    /* Created by Igor Penjivrag (www.colorlightstudio.com) - 12.11.2006 */
    body {
        margin: 0px;
        background-repeat: repeat-x;
        font-family: Verdana, Arial, sans-serif;
        font-size: 13px;
    }
    p {
        line-height: 15px;
        margin: 11px 0 10px 0;
        padding: 0px;
    }
    h2 {
        color: #73353A;
        margin:0px;
        text-align: center;
        padding:0px;
        font-size: 15px;
    }
    .divcolor
    {
        background-color:#7d287d;
        text-align: center;
        padding-bottom:10px;
    }
    .divsecond
    {
        padding:20px 0px;
    }
    .divthird
    {
        background-color:#efefef;
    }
    .divsecond ul
    {
        padding: 5px 0 10px 30px;
    }
    ul{
        font-size: 13px;
        margin:0;
        padding:0;
        /*list-style-image: url(img/pink.png);*/
        list-style: none;
    }
    #footer {
        text-align: center;
        padding: 20px 0 0px 0;
        width: 90%;
    }
    #footer p {
        margin: 5px;
        padding:0;
        line-height: 0px;
        color:#606060;
    }
    .line
    {
        padding:0px 20px;
    }
    #wrap {
        margin-left: auto;
        margin-right: auto;
        width: 730px;
    }
    #content {
        width: 90%;
        margin-top:30px;
        padding: 0px 5px 25px 5px;
        border:1px solid #000;
    }
    #content h2 {
        margin: 0;
        padding: 10px 0 10px 0;
    }
    #clear {
        display: block;
        clear: both;
        width: 100%;
        height:1px;
        overflow:hidden;
    }
</style>
<body>
<div id="wrap">
    <div id="content">
        <h2><img src="https://s3-us-west-2.amazonaws.com/butterfli-bucket/email_icons/form-logo.png" width="150"></h2>
        <br>
        <div class="divcolor">
            <img src="https://s3-us-west-2.amazonaws.com/butterfli-bucket/email_icons/document_icon.png" width="40">
            <br>
            <span style="color:#fff;">Thank You For Your Business.</span>
        </div>

        <div class="divthird">
            <table width="100%">
                <tr>
                    <td style="width:60%">PickUp fee</td>
                    <td>$<?php echo $pickupfee ?></td>
                </tr>
                <tr>
                    <td style="width:60%">Miles</td>
                    <td><?php echo $miles ?></td>
                </tr>
                <tr>
                    <td style="width:60%">Waiting Time</td>
                    <td><?php echo $waiting_time ?></td>
                </tr>
                <tr>
                    <td style="width:60%">Deadhead Miles</td>
                    <td><?php echo $deadhead_miles ?></td>
                </tr>
            </table>
            <div class="line"><hr/></div>
            <table width="100%">
                <tr>
                    <td style="width:60%">SubTotal</td>
                    <td>$<?php echo $total ?></td>
                </tr>
            </table>
        </div>
        <div id="clear"></div>
        <div id="footer">
            <img src="https://s3-us-west-2.amazonaws.com/butterfli-bucket/email_icons/footer.png"/>
            <p><?php echo $admin_email_address ?></p>
        </div>
    </div>
</div>
</body>
</html>