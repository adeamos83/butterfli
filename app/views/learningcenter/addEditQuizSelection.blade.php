@extends(Session::has('admin_id') ? 'layout' : 'learningcenter.providerLayout')
@section('content')
    <?php if(Session::has('admin_id')){
        $admin = 1;
    }else{
        $admin = 0;
    }?>
    <style>
        .select
        {
            width:50%;
            text-align: center;
            margin: auto;
        }
        .btn-block2
         {
             width:49%;
             display: inline-block;
             margin-top: 0px;
         }
        .btn-block3
        {
            width:98%
            margin-left:10px;
        }
        .btn-block+.btn-block {
            margin-top: 0px!important;
        }

    </style>
    <div class="row row-dashboard">
        <div class="panel panel-default select">
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-12">
                        <a href="#quiz_selection" class="btn btn-primary btn-block btn-block3" data-toggle="modal" >Add New Quiz</a>
                    </div>
                    <div class="col-md-12">
                        <h4>OR</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 col-md-12 start-page">
                        <h1 class="text_underline">Select a Quiz to Edit: </h1>
                        <form class="form-signin well" method="post" id="signin" name="signin" action="questions.php" novalidate="novalidate">
                            <div class="form-group">
                                <select class="form-control" name="quiz" id="quiz">
                                    <option value="">Select Option</option>
                                    <?php   if(count($learningQuiz)>0){
                                    foreach($learningQuiz as $value){?>
                                    <option value="<?php echo $value->id ?>"><?php echo $value->quiz_name ?></option>
                                    <?php       }
                                    }
                                    ?>
                                </select>
                                <span class="help-block"></span>
                            </div>
                            <br>
                            <a href="javascript:void(0);" onclick="edit_quiz()" id="start_btn" class="btn btn-success btn-block btn-block2" type="submit">Select</a>
                            &nbsp;
                            <a href="javascript:void(0);" onclick="delete_quiz()" id="start_btn2" class="btn btn-success btn-block btn-block2" type="submit">Delete</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel-body-img"></div>
    </div>
    <div class="modal fade modal1" id="quiz_selection" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Quiz Name</h4>
                </div>
                <div class="modal-body" style="height:180px;">
                    <form class="form-horizontal">
                        <fieldset>
                            <div class="form-group">
                                <span class="help-block"></span>
                                <label for="inputtitle"  class="col-lg-2 control-label">Name</label>
                                <div class="col-md-10">
                                    <input type="text" class="form-control" name="quizTitle" id="quizTitle" placeholder="Title">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-12" style="text-align: center;">
                                    <a id="content_submit" href="javascript:void(0);" onclick="add_new_quiz();" class="btn btn-primary">Submit</a>
                                    <input type="hidden" id="content_id" name="content_id" value="">
                                </div>
                            </div>
                        </fieldset>
                    </form>
                </div>
            </div>

        </div>
    </div>
    <script type="text/javascript">

        function edit_quiz(){
                    var e = document.getElementById("quiz");
                    var quiz_id = e.options[e.selectedIndex].value;
                    if (quiz_id=='')
                    {
                        alert("Select Quiz or Add new Quiz");
                        exit();
                    }
            var s = $( "#quiz option:selected" ).text();
            $.ajax({
                type: "POST",
                url:'<?php echo URL::Route('DisplayQuiz') ?>',
                data:{quiz_id:quiz_id},
                success: function(data) {
                    console.log(data);
                    location.replace('<?php echo route('AddEditQuiz',1) ?>'+"/"+quiz_id);
                }
            });
        }
        function delete_quiz(){
            var e = document.getElementById("quiz");
            var quiz_id = e.options[e.selectedIndex].value;
            if (quiz_id=='')
            {
                alert("Select Quiz need to be deleted ");
                exit();
            }
//            var s = $( "#quiz option:selected" ).text();
            $.ajax({
                type: "POST",
                url:'<?php echo URL::Route('DeleteQuiz') ?>',
                data:{quiz_id:quiz_id},
                success: function(data) {
                    console.log(data);
                    location.replace('<?php echo route('AddEditQuizSelection')?>');
                }
            });
        }
        function add_new_quiz(){

            if($('#quizTitle').val()=='' ){
                $(".help-block").html('<span style="text-align: center;font-size:15px;color: #f56954;">Please enter this field.</span>');
            } else{
                if($('#content_id').val() >0){
                    var content_id = $('#content_id').val();
                } else{
                    var content_id = 0;
                }
                $.ajax({
                    type: "POST",
                    url:'<?php echo URL::Route('AddNewQuiz') ?>',
                    data:{content_id:content_id,quizTitle:$('#quizTitle').val()},
                    success: function(data) {
                        console.log(data);
                        location.replace('<?php echo route('AddEditQuiz',1) ?>'+"/"+data);
                    }
                });
            }

        }
    </script>
@stop