@extends(Session::has('admin_id') ? 'layout' : 'learningcenter.providerLayout')
@section('content')
    <?php if(Session::has('admin_id')){
        $admin = 1;
    }else{
        $admin = 0;
    }?>
    <style>
        @import url(http://fonts.googleapis.com/css?family=Rokkitt);
        p, strong {
            font-family:serif;
            color:#000;
            padding-left:5px;
        }
        .hover:hover {
            box-shadow: 3px 3px #e8e9ea;
            border-top: 2px solid #9c27b0;
        }
        .hover{
            border-top:2px solid #e8e9ea;
            border-radius:5px;
        }
        .cont{
            text-align:center;
            font-size:14px;
            color:deepskyblue;
        }
        h3{
            text-align:center;
        }
        .mb-3 {
            margin-bottom: 0rem !important;
        }

    </style>
    <div class="row">
        <div class="content-width">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <a href="{{ URL::Route('Sections', $back_category_id) }}">
                    <div class="btn btn-primary"><span style="font-size:10px;text-align: center;color: #ffffff;">Back to Sections</span></div>
                </a>
            </div>
        </div>
        <br/>
        <?php  if(count($learningContent) > 0){
        foreach($learningContent as $index=>$content){
        if (($index)%3 == 3) { ?>
        <div class="col-sm-1"></div>
        <?php           } ?>
        <?php
        if($admin == 0){?>
        <a href="/learningcenter/content_details/<?php echo $content->category_id?>/<?php echo $content->section_id ?>/<?php echo $content->id ?>">
            <?php }?>
            <div class="content-width">
                <div class="col-sm-2 col-md-4 col-xs-12 con">
                    <?php if($admin == 1) {?>
                    <a href="/learningcenter/content_details/<?php echo $content->category_id?>/<?php echo $content->section_id ?>/<?php echo $content->id ?>">
                        <?php }?>
                        <div class="panel panel-default hover" style="border-radius:5px;padding:10px;">
                            <div style="border-radius:5px;">
                                <div class="row">
                                    <?php if($admin == 0) {?>
                                    <div class="col-sm-9"> <strong><span class="cont"><?php echo $content->content ?></span></strong><?php if($content->result=="Pass"){ ?>
                                        <i id="test-complete" class="fa fa-check fa-4x mb-3" style="background: #fbf8f8;color:#1fe21f;margin-left: 10px;font-size: 1.2em;"></i>
                                        <?php } ?></div>
                                    <?php }?>
                                    <?php if($admin == 1) {?>
                                    <a href="/learningcenter/content_details/<?php echo $content->category_id?>/<?php echo $content->section_id ?>/<?php echo $content->id ?>">
                                        <div class="col-sm-9"> <strong><span class="cont"><a href="/learningcenter/content_details/<?php echo $content->category_id?>/<?php echo $content->section_id ?>/<?php echo $content->id ?>"><?php echo $content->content ?></a></span></strong></div>
                                        <div class="col-sm-3">
                                            <a href="javascript:void(0);"  onclick="get_content_details('<?=$content->id ?>');"><img src="<?=asset_url(). '/web/img/edit_content.png'?>" style="width:30%;float:left;" alt=""/></a>
                                            <a href="javascript:void(0);"  onclick="content_delete('<?=$content->id ?>')"><img src="<?=asset_url(). '/web/img/delete_content.png'?>" style="width:30%;float:right;" alt=""/></a>
                                        </div>
                                    </a>
                                    <?php }?>
                                </div>
                                <?php if($admin == 1) {?>

                                <a href="/learningcenter/content_details/<?php echo $content->category_id?>/<?php echo $content->section_id ?>/<?php echo $content->id ?>">
                                    <?php }?>
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <p><?php echo $content->content_description ?></p>
                                        </div>
                                    </div>
                                    <?php if($admin == 1) {?>
                                </a>
                                <?php }?>
                            </div>
                        </div>
                        <?php if($admin == 1) {?>
                    </a>
                    <?php } ?>
                </div>
            </div>

            <?php  if($admin == 0){?>
        </a>
        <?php  }
        }
        } else{ ?>
        <div class="col-sm-12" style=" text-align: left; margin-bottom: 5px;">
            <h5 style="padding:5px 0;color:black;">No data found</h5>
        </div>
        <?php   }
        ?>
        <?php if($admin == 1) {?>
        <div><a id="content" class="btn-sm" data-toggle="modal" href="#content_responsive" style="display:none;"></a></div>
        <div class="col-sm-12" style=" text-align: left; margin-bottom: 5px;">
            <h5 style="padding:5px 0;color:black;"><a href="#" class="btn btn-success" data-toggle="modal" data-target="#content_responsive">Add Content</a></h5>
        </div>
        <?php }?>

        <div class="modal fade modal1" id="content_responsive" role="dialog">
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Add/Edit Content</h4>
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
                                    <label style="text-align:center;padding-top: 5px;" class="col-sm-2 control-label">Add Quiz</label>
                                    <div class="col-sm-6">
                                        <select class="form-control" id="quiz_id" name="quiz_id">
                                            <option value="">Select Quiz</option>
                                            <?php   if(count($learningQuiz)>0){
                                            foreach($learningQuiz as $value){?>
                                            <option value="<?php echo $value->id ?>"><?php echo $value->quiz_name ?></option>
                                            <?php       }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-md-10">
                                        <a id="content_submit" href="javascript:void(0)" onclick="addedit_content();" class="btn btn-primary">Submit</a>
                                        <input type="hidden" id="content_id" name="content_id" value="">
                                    </div>
                                </div>
                            </fieldset>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <script type="text/javascript">
        function get_content_details(contentid){
            $.ajax({
                type: "POST",
                url:'<?php echo URL::Route('GetContents') ?>',
                data:{content_id:contentid},
                success: function(data) {
                    console.log(data);
                    if (data != '') {
                        $( "#content" ).trigger( "click" );
                        $('#title').val(data.title);
                        $('#description').val(data.description);
                        $('#content_id').val(data.content_id);
                        $('#quiz_id').val(data.quiz_id);
                    }
                }
            });
        }

        function addedit_content(){

            if($('#title').val()=='' || $('#description').val()==''){
                $(".help-block").html('<span style="text-align: center;font-size:15px;color: #f56954;">Please enter both the field values.</span>');
            } else{
                if($('#content_id').val() >0){
                    var content_id = $('#content_id').val();
                } else{
                    var content_id = 0;
                }

                $.ajax({
                    type: "POST",
                    url:'<?php echo URL::Route('AddEditContent') ?>',
                    data:{content_id:content_id,title:$('#title').val(),description:$('#description').val(),quiz_id:$('#quiz_id').val()},
                    success: function(data) {
                        console.log(data);
                        $('#title').val('');
                        $('#description').val('');
                        $('#content_id').val('');
                        $('#quiz_id').val('');
                        location.reload();
                    }
                });
            }

        }
        function content_delete(contentid){
            var r = confirm("Are you sure you want to delete this Content?");
            if (r == true) {
                //$('#completerequest').html('<tr><td style="padding: 8px 9px 6px;border-radius: 20px;"><span class="badge bg-light-blue" style="padding-top: 12px;text-align: center;font-size: 17px;' +
                // 'padding-bottom: 12px;">Deleting Request.....</span></td></tr>');

                $.ajax({
                    type: "POST",
                    url:'<?php echo URL::Route('ContentDelete') ?>',
                    data:{content_id:contentid},
                    success: function(data) {
                        console.log(data);
                        location.reload();
                    }
                });
            }
        }
    </script>
@stop