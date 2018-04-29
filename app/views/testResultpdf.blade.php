<!DOCTYPE html>
<html lang="en">
<head>
    <title>Certificate</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"/>

    <style>

        body {
            background: url("https://s3-us-west-2.amazonaws.com/butterfli-bucket/email_icons/Certificate+Template2+copy+copy.png");
            background-repeat: no-repeat;
            background-size: auto;
            min-width:50%;
        }
    </style>
</head>

<body>
<div class="container" style="font-size:30px;color:#83007f;">
    <p style="position:absolute;margin-top:210px;margin-left:145px;"><?php echo $driver_contact_name ?> succesfully completed</p>
    <p style="position:absolute;top:250px;left:190px;"><?php echo $category_name ?> on <?php echo $date ?></p>
</div>
</body>
</html>