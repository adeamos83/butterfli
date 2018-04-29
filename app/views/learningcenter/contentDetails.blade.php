@extends(Session::has('admin_id') ? 'layout' : 'learningcenter.providerLayout')
@section('content')
    <?php if (Session::has('admin_id')) {
        $admin = 1;
    } else {
        $admin = 0;
    }?>
    <style>
        a:focus,
        a:hover,
        a:active {
            outline: 0;
            text-decoration: none;
        }
        .panel {
            border-width: 0 0 1px 0;
            border-style: solid;
            border-color: #fff;
            background: none;
            box-shadow: none;
        }
        .panel:last-child {
            border-bottom: none;
        }
        .panel-group > .panel:first-child .panel-heading {
            border-radius: 4px 4px 0 0;
        }
        .panel-group .panel {
            border-radius: 0;
        }
        .panel-group .panel + .panel {
            margin-top: 0;
        }
        .panel-heading {
            background-color: #d8d6d6;
            border-radius: 0;
            border: none;
            color: #fff;
            padding: 0;
        }
        .panel-title a {
            display: block;
            color: #000;
            padding: 15px;
            position: relative;
            font-size: 16px;
            font-weight: 400;
        }
        .panel-body {
            background: #fff;
        }
        .panel:last-child .panel-body {
            border-radius: 0 0 4px 4px;
        }
        .panel:last-child .panel-heading {
            border-radius: 0 0 4px 4px;
            transition: border-radius 0.3s linear 0.2s;
        }
        .panel:last-child .panel-heading.active {
            border-radius: 0;
            transition: border-radius linear 0s;
        }
        /* #bs-collapse icon scale option */
        .panel-heading a:before {
            content: '+';
            position: absolute;
            font-family: 'Material Icons';
            right: 5px;
            top: 10px;
            font-size: 24px;
            transition: all 0.5s;
            transform: scale(1);
        }
        .panel-heading.active a:before {
            content: ' ';
            transition: all 0.5s;
            transform: scale(0);
        }
        #bs-collapse .panel-heading a:after {
            content: ' ';
            font-size: 24px;
            position: absolute;
            font-family: 'Material Icons';
            right: 5px;
            top: 10px;
            transform: scale(0);
            transition: all 0.5s;
        }
        #bs-collapse .panel-heading.active a:after {
            content: '-';
            transform: scale(1);
            transition: all 0.5s;
            padding-right: 6px;
        }
        #accordion .panel-heading.active a:before {
            transform: rotate(0deg);
            transition: all 0.5s;
        }
        /*---------------------------------------abhay------------------------------------------------*/
        /* Next & previous buttons */
        .prev,
        .next {
            cursor: pointer;
            position: absolute;
            top: 50%;
            width: auto;
            padding: 16px;
            color: white;
            font-weight: bold;
            font-size: 20px;
            transition: 0.6s ease;
            border-radius: 0 3px 3px 0;
            user-select: none;
            -webkit-user-select: none;
        }

        /* Position the "next button" to the right */
        .next {
            right: 0;
            border-radius: 3px 0 0 3px;
        }
        /* On hover, add a black background color with a little bit see-through */
        .prev:hover,
        .next:hover {
            background-color: rgba(0, 0, 0, 0.8);
        }
        .modal-dialog
        {
            margin-top:65px;
        }

        @media only screen
            and (min-width : 768px) {
                .modal-dialog {
                    width: 800px;
                }
            }

        @media only screen
        and (min-width : 768px) {
            .thumb{min-height:450px;}
        }
        @media only screen and (min-device-width : 320px)
        and (max-device-width : 568px)
        {
            .thumb{min-height:200px;}
            .collapse-padding{
                padding-top:3em;
            }
            .pdf-view
            {
                min-height:400px;
            }
        }

        .thumb
        {
            background-color: #fff;
            border: 0px solid transparent!important;
            margin-bottom: 20px;
            border-color: #ddd;
            width: 100%;
            padding: 0px 0px 0px  0px!important;
        }
        @media only screen
        and (min-width : 768px) {
            .collapse-padding{
                padding-top:7em;
            }
        }

        /*---------------------------------------abhay------------------------------------------------*/
    </style>

    <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <a href="{{ URL::Route('Sections', $category_id) }}">
                <div class="btn btn-primary"><span style="font-size:10px;text-align: center;">Back to Sections</span></div>
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12 text-center">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal">Slide Show</button>
        </div>
    </div>
    <div id="myModal" class="modal fade" role="dialog">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <?php   if (count($content_details_json) > 0){
                foreach ($content_details_json['sections'] as $index=>$jsondata){
                if($jsondata['type'] == 'text' || $jsondata['type'] == 'video' || $jsondata['type'] == 'document'){
                ?>
                <div class="mySlides">
                    <div class="thumb">
                        <h4 style="color:#fff; text-align: center;padding: 10px 0px 10px 0px;margin:0px auto;background-color: #00a5d2;">
                            <strong><?php echo $jsondata['heading'] ?></strong>
                        </h4>
                        <?php if($jsondata['type'] == 'text'){ ?>
                        <div id="<?php echo $index; ?>" class="panel-collapse collapse in collapse-padding">
                            <div class="panel-body"><?php echo $jsondata['description'] ?>
                            </div>
                        </div>
                        <?php }elseif($jsondata['type'] == "video"){?>
                        <div class="embed-responsive embed-responsive-16by9">
                            <iframe class="embed-responsive-item"  src="<?php echo $jsondata['url'] ?>" frameborder="0"
                                    allowfullscreen></iframe>
                        </div>
                        <?php }elseif($jsondata['type'] == "document"){?>
                        <div class="embed-responsive embed-responsive-16by9 pdf-view" style="min-height:400px;">
                            <iframe src="https://docs.google.com/gview?url=<?php echo $jsondata['url'] ?>&embedded=true" frameborder="0"></iframe>
                        </div>
                        <?php } ?>
                    </div>
                </div>
                <?php           }
                }
                }else{ ?>
                <div class="col-sm-12" style=" text-align: left; margin-bottom: 5px;">
                    <h5 class="no_data_found" style="padding:5px 0;color:black;">No Content Available</h5>
                </div>
                <?php   }  ?>
                <a class="prev" onclick="plusSlides(-1)">&#10094;</a>
                <a class="next" onclick="plusSlides(1)">&#10095;</a>
            </div>
        </div>
    </div>

    <?php   if($learningContent->quiz_id > 0 && $admin == 0){
    if(isset($learningQuizResult) && $learningQuizResult->result=="Pass"){

    ?>              <a href="#">
        <div class="nextButton"><strong style="font-size:15px;text-align: center">Test Passed</strong></div>
    </a>
    <?php       } else{ ?>
    <a href="/learningcenter/test/<?php echo $learningContent->quiz_id ?>/<?php echo $content_id ?>/<?php echo $category_id ?>">
        <div class="nextButton"><strong style="font-size:15px;text-align: center">Test</strong></div>
    </a>
    <?php      }
    }
    ?>

    <script src="<?php echo asset_url(); ?>/web/js/jquery-ui-1.9.2.custom.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $('.collapse.in').prev('.panel-heading').addClass('active');
            $('#accordion, #bs-collapse')
                .on('show.bs.collapse', function (a) {
                    $(a.target).prev('.panel-heading').addClass('active');
                })
                .on('hide.bs.collapse', function (a) {
                    $(a.target).prev('.panel-heading').removeClass('active');
                });

            $('#myModal').modal('show');
        });
        function add_edit_text_section(contentid) {
            $('#content_id').val(contentid);
            $("#content_title").trigger("click");
        }
        function addedit_title() {
            if ($('#title').val() == '' || $('#description').val() == '') {
                $(".help-block").html('<span style="text-align: center;font-size:15px;color: #f56954;">Please enter both the field values.</span>');
            } else {
                if ($('#content_id').val() > 0) {
                    var content_id = $('#content_id').val();
                } else {
                    var content_id = 0;
                }
                $.ajax({
                    type: "POST",
                    url: '<?php echo URL::Route('AddEditTextSection') ?>',
                    data: {
                        content_id: content_id,
                        previoustitle: $('#previoustitle').val(),
                        title: $('#title').val(),
                        description: $('#description').val()
                    },
                    success: function (data) {
                        console.log(data);
                        $('#content_id').val('');
                        $('#contentDetails_responsive').modal('hide');
                        location.reload();
                    }
                });
            }
        }
        function get_section_details(title, contentid) {
            $.ajax({
                type: "POST",
                url: '<?php echo URL::Route('GetTitleDetails') ?>',
                data: {title: title, content_id: contentid},
                success: function (data) {
                    console.log(data);
                    if (data != '') {
                        $('#content_id').val(data.content_id);
                        $('#title').val(data.title);
                        $('#previoustitle').val(data.title);
                        $('#description').val(data.description);
                        $("#content_title").trigger("click");
                    }
                }
            });
        }
        function delete_section_title(title, contentid) {
            var r = confirm("Are you sure you want to delete this Section?");
            if (r == true) {
                $.ajax({
                    type: "POST",
                    url: '<?php echo URL::Route('ContentSectionDelete') ?>',
                    data: {content_id: contentid, title: title},
                    success: function (data) {
                        console.log(data);
                        location.reload();
                    }
                });
            }
        }
        function add_edit_video_section(contentid, sectionid, categoryid) {
            $('#contentids').val(contentid);
            $('#sectionid').val(sectionid);
            $('#categoryid').val(categoryid);
            $("#content_videos").trigger("click");
        }
        function addedit_videos() {
            if ($('#videotitle').val() == '' || $('#url').val() == '') {
                $(".help-block").html('<span style="text-align: center;font-size:15px;color: #f56954;">Please enter both the field values.</span>');
            } else {
                if ($('#content_id').val() > 0) {
                    var content_id = $('#content_id').val();
                } else {
                    var content_id = 0;
                }
                $.ajax({
                    type: "POST",
                    url: '<?php echo URL::Route('AddEditVideoSection') ?>',
                    data: {
                        content_id: content_id,
                        previoustitle: $('#previoustitle').val(),
                        title: $('#videotitle').val(),
                        description: $('#videodescription').val(),
                        url: $('#url').val()
                    },
                    success: function (data) {
                        console.log(data);
                        $('#contentids').val('');
                        $('#sectionid').val('');
                        $('#categoryid').val('');
                        $('#content_videos_responsive').modal('hide');
                        location.reload();
                    }
                });
            }
        }
        function delete_video_title(title, contentid) {
            var r = confirm("Are you sure you want to delete this Video?");
            if (r == true) {
                $.ajax({
                    type: "POST",
                    url: '<?php echo URL::Route('ContentVideoDelete') ?>',
                    data: {content_id: contentid, title: title},
                    success: function (data) {
                        console.log(data);
                        location.reload();
                    }
                });
            }
        }
        function add_edit_image_section(contentid, sectionid, categoryid) {
            $('#contentid').val(contentid);
            $('#section_id').val(sectionid);
            $('#category_id').val(categoryid);
            $("#content_images").trigger("click");
        }
        function add_edit_document_section(contentid, sectionid, categoryid){
            $('#contentsid').val(contentid);
            $('#sectionsid').val(sectionid);
            $('#categorysid').val(categoryid);
            $("#content_documents").trigger("click");
        }
        function addedit_images() {
            if ($('#imagetitle').val() == '' || $('#imagedescription').val() == '') {
                $(".help-block").html('<span style="text-align: center;font-size:15px;color: #f56954;">Please enter both the field values.</span>');
            } else {
                if ($('#content_id').val() > 0) {
                    var content_id = $('#content_id').val();
                } else {
                    var content_id = 0;
                }
                $.ajax({
                    type: "POST",
                    url: '<?php echo URL::Route('AddEditImageSection') ?>',
                    //data:{content_id:content_id,previoustitle:$('#previoustitle').val(),title:$('#imagetitle').val(),description:$('#imagedescription').val()},
                    data: new FormData(this),
                    success: function (data) {
                        console.log(data);
                        //$('#content_images_responsive').modal('hide');
                        //location.reload();
                    }
                });
            }
        }
        function delete_image_section(title, contentid) {
            var r = confirm("Are you sure you want to delete this Image?");
            if (r == true) {
                $.ajax({
                    type: "POST",
                    url: '<?php echo URL::Route('ContentImageDelete') ?>',
                    data: {content_id: contentid, title: title},
                    success: function (data) {
                        console.log(data);
                        location.reload();
                    }
                });
            }
        }
        function delete_document_section(title, contentid) {
            var r = confirm("Are you sure you want to delete this Document?");
            if (r == true) {
                $.ajax({
                    type: "POST",
                    url: '<?php echo URL::Route('ContentDocumentDelete') ?>',
                    data: {content_id: contentid, title: title},
                    success: function (data) {
                        console.log(data);
                        location.reload();
                    }
                });
            }
        }

        /*---------------------------------------abhay------------------------------------------------*/
        var slideIndex = 1;
        showSlides(slideIndex,'onRefresh');

        function plusSlides(n) {
            showSlides(slideIndex += n,0);
        }
        function plusSlides1(n) {
            showSlides(slideIndex += n,1);
        }
        function showSlides(n,check) {
            var i;
            var slides = document.getElementsByClassName("mySlides");
            var slides1 = document.getElementsByClassName("mySlides1");
            var dots = document.getElementsByClassName("demo");
            var captionText = document.getElementById("caption");
            if(check === 0 || check === 'onRefresh') {
                if (n > slides.length) {
                    slideIndex = 1
                }
                if (n < 1) {
                    slideIndex = slides.length
                }
                for (i = 0; i < slides.length; i++) {
                    slides[i].style.display = "none";
                }
                for (i = 0; i < dots.length; i++) {
                    dots[i].className = dots[i].className.replace(" active", "");
                }
                slides[slideIndex - 1].style.display = "block";
            }
            if(check === 1 || check === 'onRefresh'){
                if (n > slides1.length) {
                    slideIndex = 1
                }
                if (n < 1) {
                    slideIndex = slides1.length
                }
                for (i = 0; i < slides1.length; i++) {
                    slides1[i].style.display = "none";
                }
                for (i = 0; i < dots.length; i++) {
                    dots[i].className = dots[i].className.replace(" active", "");
                }
                slides1[slideIndex - 1].style.display = "block";
            }
        }
        /*---------------------------------------abhay------------------------------------------------*/
    </script>
@stop
























