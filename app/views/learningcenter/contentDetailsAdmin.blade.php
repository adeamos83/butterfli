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
    </style>
    <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <a href="{{ URL::Route('Sections', $category_id) }}">
                <div class="btn btn-primary"><span style="font-size:10px;text-align: center;">Back to Sections</span></div>
            </a>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 col-sm-12">
            @if(Session::has('error'))
                <div class="alert alert-danger">
                    <b>{{ Session::get('error') }}</b>

                </div>
            @endif
        </div>
        <div class="col-md-12 col-sm-12">
            <div class="panel-group wrap" id="bs-collapse">
                <?php if (count($content_details_json) > 0){
                foreach ($content_details_json['sections'] as $index=>$jsondata){
                if($jsondata['type'] == 'text'){
                ?>
                <div class="panel">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <a data-toggle="collapse" data-parent="#" href="#<?php echo $index ?>">
                                <?php echo $jsondata['heading'] ?>
                            </a>
                        </h4>
                    </div>
                    <div id="<?php echo $index; ?>" class="panel-collapse collapse in">
                        <div class="panel-body"><?php echo $jsondata['description'] ?>
                            <?php if($admin == 1){?>
                            <div style="float:right;">
                                <a href="javascript:void(0);"
                                   onclick="get_section_details('<?=$jsondata['heading'] ?>','<?=$content_id ?>');"><img
                                            src="<?=asset_url() . '/web/img/edit_content.png'?>" style="width:8%;"
                                            alt=""/></a>
                                <a href="javascript:void(0);"
                                   onclick="delete_section_title('<?=$jsondata['heading'] ?>','<?=$content_id ?>');"><img
                                            src="<?=asset_url() . '/web/img/delete_content.png'?>" style="width:8%;"
                                            alt=""/></a>

                            </div>
                            <?php }?>
                        </div>
                    </div>
                </div>
                <?php               }
                }
                }else{ ?>
                <div class="col-sm-12" style=" text-align: left; margin-bottom: 5px;">
                    <h5 class="no_data_found" style="padding:5px 0;color:black;">No Content Available</h5>
                </div>
                <?php   }

                ?>
            </div>
        </div>
    </div>
    <?php if($admin == 1){?>
    <div class="row">
        <div class="col-sm-12" style=" text-align: left; margin-bottom: 5px;"><h5 style="padding:5px 0;color:black;"><a
                        href="javascript:void(0);" class="btn btn-success"
                        onclick="add_edit_text_section('<?=$content_id ?>');">Add Content</a></h5></div>
    </div>
    <?php }?>
    <div class="row">
        <div class="dri-heading"><h3>Videos</h3></div>
        <?php   if (count($content_details_json) > 0){
        foreach ($content_details_json['sections'] as $index=>$jsondata){
        if($jsondata['type'] == 'video'){?>
        <div class="col-sm-4">
            <div class="thumb">
                <h4>
                    <?php echo $jsondata['heading'] ?>

                    <?php if($admin == 1){?>
                    <div style="float:right;">
                        <a href="javascript:void(0);"
                           onclick="delete_video_title('<?=$jsondata['heading'] ?>','<?=$content_id ?>');"><img
                                    src="<?=asset_url() . '/web/img/delete_content.png'?>" style="width:20px;" alt=""/></a>

                    </div>
                    <?php }?>
                </h4>
                <div class="embed-responsive embed-responsive-16by9">
                    <iframe class="embed-responsive-item" src="<?php echo $jsondata['url'] ?>" frameborder="0"
                            allowfullscreen></iframe>
                </div>
            </div>
        </div>
        <?php           }
        }
        }else{ ?>
        <div class="col-sm-12" style=" text-align: left; margin-bottom: 5px;">
            <h5 class="no_data_found" style="padding:5px 0;color:black;">No Content Available</h5>
        </div>
        <?php   }  ?>
    </div>
    <?php if($admin == 1){?>
    <div class="row">
        <div class="col-sm-12" style=" text-align: left; margin-bottom: 5px;"><h5 style="padding:5px 0;color:black;"><a
                        href="#" class="btn btn-success"
                        onclick="add_edit_video_section('<?=$content_id ?>','<?=$section_id ?>','<?=$category_id ?>');">Add
                    Videos</a></h5></div>
    </div>
    <?php }?>
    <div class="row">
        <div class="dri-heading"><h3>Documents</h3></div>
        <?php   if (count($content_details_json) > 0){
        foreach ($content_details_json['sections'] as $index=>$jsondata){
        if($jsondata['type'] == 'document'){  ?>
        <div class="col-sm-4">
            <div class="thumb">
                <h4><?php echo $jsondata['heading'] ?>
                    <?php if($admin == 1){?>
                    <div style="float:right;">
                        <a href="javascript:void(0);"
                           onclick="delete_document_section('<?=$jsondata['heading'] ?>','<?=$content_id ?>');"><img
                                    src="<?=asset_url() . '/web/img/delete_content.png'?>" style="width:20px;" alt=""/></a>

                    </div>
                    <?php }?>
                </h4>
                <div class="embed-responsive embed-responsive-16by9 pdf">
                    <a href="<?php echo $jsondata['url'] ?>" target="_blank">
                        <img src="<?=asset_url(). '/web/img/pdf.png' ?>" style="width:170px;height:170px;" />
                    </a>
                </div>
            </div>
        </div>
        <?php           }
        }
        }else{ ?>
        <div class="col-sm-12" style=" text-align: left; margin-bottom: 5px;">
            <h5 class="no_data_found" style="padding:5px 0;color:black;">No Content Available</h5>
        </div>
        <?php   }
        ?>
    </div>
    <?php   if($admin == 1){?>
    <div class="row">
        <div class="col-sm-12" style=" text-align: left; margin-bottom: 5px;"><h5 style="padding:5px 0;color:black;"><a
                        href="#" class="btn btn-success" onclick="add_edit_document_section('<?=$content_id ?>','<?=$section_id ?>','<?=$category_id ?>');">Add
                    Documents</a></h5></div>
    </div>
    <?php   }?>
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
    <div><a id="content_title" class="btn-sm" data-toggle="modal" href="#contentDetails_responsive"
            style="display:none;"></a></div>
    <div class="modal fade modal1" id="contentDetails_responsive" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Add/Edit Title</h4>
                </div>
                <div class="modal-body" style="height:270px;">
                    <form class="form-horizontal">
                        <fieldset>
                            <div class="form-group">
                                <span class="help-block"></span>
                                <label for="inputtitle" class="col-lg-2 control-label">Title</label>
                                <div class="col-md-10">
                                    <input type="text" class="form-control" id="title" placeholder="Title">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="textArea" class="col-lg-2 control-label">Description</label>
                                <div class="col-md-10">
                                    <textarea class="form-control" rows="3" id="description"></textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-10">
                                    <a id="content_title_submit" href="javascript:void(0)" onclick="addedit_title();"
                                       class="btn btn-primary">Submit</a>
                                    <input type="hidden" id="content_id" name="content_id" value="">
                                    <input type="hidden" id="previoustitle" name="previoustitle" value="">
                                </div>
                            </div>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div><a id="content_videos" class="btn-sm" data-toggle="modal" href="#content_videos_responsive"
            style="display:none;"></a></div>
    <div class="modal fade modal1" id="content_videos_responsive" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Add/Edit Videos</h4>
                </div>
                <div class="modal-body" style="height:300px;">
                    <form method="post" class="form-horizontal" action="{{URL::Route('AddEditVideoSection')}}"
                          enctype="multipart/form-data">
                        <div class="form-group">
                            <span class="help-block"></span>
                            <label for="inputtitle" class="col-lg-2 control-label">Video Title</label>
                            <div class="col-md-10">
                                <input type="text" class="form-control" id="videotitle" name="videotitle"
                                       placeholder="Title">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputtitle" class="col-lg-2 control-label">Description</label>
                            <div class="col-md-10">
                                <input type="text" class="form-control" id="videodescription" name="videodescription"
                                       placeholder="Description">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputtitle" class="col-lg-2 control-label">Enter URL</label>
                            <div class="col-md-10">
                                <input type="text" class="form-control" id="url" name="url" placeholder="URL">
                            </div>
                        </div>
                        <!--<div class="form-group">
                            <label for="inputtitle" style="text-align: center;" class="col-lg-2 control-label">OR</label>
                            <div class="col-md-10">
                                <input type="file" name="videourl" id="videourl" accept="video/*">
                            </div>
                        </div>-->
                        <div class="form-group">
                            <div class="col-md-10">
                                <input type="hidden" name="MAX_FILE_SIZE" value="612000"/>
                                <input type="hidden" id="contentids" name="contentids" value="">
                                <input type="hidden" id="sectionid" name="sectionid" value="">
                                <input type="hidden" id="categoryid" name="categoryid" value="">
                                <button id="content_video_submit" type="submit" class="btn btn-primary">Submit</button>
                                {{--<a id="content_video_submit" href="javascript:void(0)" onclick="addedit_videos();" class="btn btn-primary">Submit</a>--}}
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div><a id="content_documents" class="btn-sm" data-toggle="modal" href="#content_documents_responsive"
            style="display:none;"></a></div>
    <div class="modal fade modal1" id="content_documents_responsive" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Add/Edit Documents</h4>
                </div>
                <div class="modal-body" style="height:300px;">
                    <form id="uploadForm" method="post" class="form-horizontal"
                          action="{{URL::Route('AddEditDocumentSection')}}" enctype="multipart/form-data">
                        <div class="form-group">
                            <span class="help-block"></span>
                            <label for="inputtitle" class="col-lg-2 control-label">Title</label>
                            <div class="col-md-10">
                                <input type="text" class="form-control" id="doctitle" name="doctitle"
                                       placeholder="Title">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputtitle" class="col-lg-2 control-label">Description</label>
                            <div class="col-md-10">
                                <input type="text" class="form-control" id="docdescription" name="docdescription"
                                       placeholder="Description">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputtitle" style="text-align: center;" class="col-lg-2 control-label"></label>
                            <div class="col-md-10">
                                <input type="file" name="doc" id="doc" accept="application/pdf">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-10">
                                <input type="hidden" id="contentsid" name="contentsid" value="">
                                <input type="hidden" id="sectionsid" name="sectionsid" value="">
                                <input type="hidden" id="categorysid" name="categorysid" value="">
                                <button id="content_doc_submit" type="submit" class="btn btn-primary">Submit</button>
                                {{--<a id="content_image_submit" href="javascript:void(0)" onclick="addedit_images();" class="btn btn-primary">Submit</a>--}}
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div><a id="content_images" class="btn-sm" data-toggle="modal" href="#content_images_responsive"
            style="display:none;"></a></div>
    <div class="modal fade modal1" id="content_images_responsive" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Add/Edit Images</h4>
                </div>
                <div class="modal-body" style="height:300px;">
                    <form id="uploadForm" method="post" class="form-horizontal"
                          action="{{URL::Route('AddEditImageSection')}}" enctype="multipart/form-data">
                        <div class="form-group">
                            <span class="help-block"></span>
                            <label for="inputtitle" class="col-lg-2 control-label">Title</label>
                            <div class="col-md-10">
                                <input type="text" class="form-control" id="imagetitle" name="imagetitle"
                                       placeholder="Title">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputtitle" class="col-lg-2 control-label">Description</label>
                            <div class="col-md-10">
                                <input type="text" class="form-control" id="imagedescription" name="imagedescription"
                                       placeholder="Description">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputtitle" style="text-align: center;" class="col-lg-2 control-label"></label>
                            <div class="col-md-10">
                                <input type="file" name="image" id="image" accept="image/*">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-10">
                                <input type="hidden" id="contentid" name="contentid" value="">
                                <input type="hidden" id="section_id" name="section_id" value="">
                                <input type="hidden" id="category_id" name="category_id" value="">
                                <button id="content_image_submit" type="submit" class="btn btn-primary">Submit</button>
                                {{--<a id="content_image_submit" href="javascript:void(0)" onclick="addedit_images();" class="btn btn-primary">Submit</a>--}}
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
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
    </script>
@stop