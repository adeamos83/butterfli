@extends(Session::has('admin_id') ? 'layout' : 'learningcenter.providerLayout')
@section('content')
    <?php if (Session::has('admin_id')) {
        $admin = 1;
    } else {
        $admin = 0;
    }?>
    <div class="row row-dashboard">
        <div class="col-sm-10">
            <div class="panel panel-default">
                <div class="panel-body" style="margin-left: 20px; margin-right: 20px;">
                    <?php   if(count($learningSectionsArray) > 0){
                                foreach($learningSectionsArray as $SectionsArray){
                                    if(count($SectionsArray['section']) >0){ ?>
                                        <div class="row" style="background-color: #F5F5F5;border-left:2px solid #9410f2;margin-bottom:5px;">
                                            <?php if($admin == 0) {?>
                                            <a href="/learningcenter/content/<?php echo $SectionsArray['section']['data']->category_id?>/<?php echo $SectionsArray['section']['data']->id?>">
                                                <div class="col-sm-3" style=" text-align: center;"><h5
                                                            style="padding:5px 0;"><?=$SectionsArray['section']['data']->section_title?></h5></div>
                                                <div class="col-sm-9" style=" text-align: left;"><h5
                                                            style="padding:5px 0;color:black;"><?=$SectionsArray['section']['data']->section_description ?><?php if($SectionsArray['section']['completed']=='true'){ ?>
                                                                <i id="test-complete" class="fa fa-check fa-4x mb-3" style="background: #fbf8f8;color:#1fe21f;margin-left: 10px;font-size: 1.2em;"></i>
                                                            <?php } ?>
                                                        </h5>
                                                </div>
                                            </a>
                                            <?php }
                                            if($admin == 1) { ?>
                                            <a href="/learningcenter/content/<?php echo $SectionsArray['section']['data']->category_id?>/<?php echo $SectionsArray['section']['data']->id?>">
                                                <div class="col-sm-3" style=" text-align: center;"><h5
                                                            style="padding:5px 0;"><?=$SectionsArray['section']['data']->section_title ?></h5></div>
                                                <div class="col-sm-7" style=" text-align: left;"><h5
                                                            style="padding:5px 0;color:black;"><?=$SectionsArray['section']['data']->section_description ?></h5>
                                                </div>
                                            </a>
                                            <div class="col-sm-2" style=" text-align: left;"><h5 style="padding:0px 0;color:black;">
                                                    <a href="javascript:void(0);" onclick="get_section_details('<?=$SectionsArray['section']['data']->id ?>');"><img
                                                                src="<?=asset_url() . '/web/img/edit_content.png'?>" style="width:15%;"
                                                                alt=""/></a>
                                                    <a href="javascript:void(0);" onclick="section_delete('<?=$SectionsArray['section']['data']->id ?>');"><img
                                                                src="<?=asset_url() . '/web/img/delete_content.png'?>" style="width:15%;"
                                                                alt=""/></a></h5>
                                            </div>
                                            <?php }?>
                                        </div>
                    <?php           }else{ ?>
                                        <div class="col-sm-12" style=" text-align: left; margin-bottom: 5px;">
                                            <h5 style="padding:5px 0;color:black;">No data found</h5>
                                        </div>
                        <?php       }
                                }
                            }else{ ?>
                                <div class="col-sm-12" style=" text-align: left; margin-bottom: 5px;">
                                    <h5 style="padding:5px 0;color:black;">No data found</h5>
                                </div>
                    <?php   }
                    ?>
                </div>
                <?php if($admin == 1) {?>
                <div class="col-sm-12" style=" text-align: left; margin-bottom: 5px;"><h5
                            style="padding:5px 0;color:black;"><a href="#" class="btn btn-success" data-toggle="modal" data-target="#section_responsive">Add Section</a></h5>
                </div>
                <?php }?>
            </div>
        </div>
        <div><a id="section" class="btn-sm" data-toggle="modal" href="#section_responsive" style="display:none;"></a>
        </div>
        <div class="modal fade modal1" id="section_responsive" role="dialog">
            <div class="modal-dialog">

                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Add/Edit Section</h4>
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
                                        <a id="section_submit" href="javascript:void(0)" onclick="addedit_section();"
                                           class="btn btn-primary">Submit</a>
                                        <input type="hidden" id="section_id" name="section_id" value="">
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
        function get_section_details(sectionid) {
            $.ajax({
                type: "POST",
                url: '<?php echo URL::Route('GetSectionDetails') ?>',
                data: {section_id: sectionid},
                success: function (data) {
                    console.log(data);
                    if (data != '') {
                        $("#section").trigger("click");
                        $('#title').val(data.title);
                        $('#description').val(data.description);
                        $('#section_id').val(data.section_id);
                    }
                }
            });
        }

        function addedit_section() {
            if ($('#title').val() == '' || $('#description').val() == '') {
                $(".help-block").html('<span style="text-align: center;font-size:15px;color: #f56954;">Please enter both the field values.</span>');
            } else {
                if ($('#section_id').val() > 0) {
                    var section_id = $('#section_id').val();
                } else {
                    var section_id = 0;
                }

                $.ajax({
                    type: "POST",
                    url: '<?php echo URL::Route('AddEditSection') ?>',
                    data: {section_id: section_id, title: $('#title').val(), description: $('#description').val()},
                    success: function (data) {
                        console.log(data);
                        $('#title').val('');
                        $('#description').val('');
                        $('#section_id').val('');
                        location.reload();
                    }
                });
            }
        }

        function section_delete(sectionid) {
            var r = confirm("Are you sure you want to delete this Section?");
            if (r == true) {
                //$('#completerequest').html('<tr><td style="padding: 8px 9px 6px;border-radius: 20px;"><span class="badge bg-light-blue" style="padding-top: 12px;text-align: center;font-size: 17px;' +
                // 'padding-bottom: 12px;">Deleting Request.....</span></td></tr>');

                $.ajax({
                    type: "POST",
                    url: '<?php echo URL::Route('SectionDelete') ?>',
                    data: {section_id: sectionid},
                    success: function (data) {
                        console.log(data);
                        location.reload();
                    }
                });
            }
        }
    </script>
@stop