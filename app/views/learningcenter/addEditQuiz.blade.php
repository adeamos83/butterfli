@extends(Session::has('admin_id') ? 'layout' : 'learningcenter.providerLayout')
@section('content')
    <?php if(Session::has('admin_id')){
        $admin = 1;
    }else{
        $admin = 0;
    }?>

    <?php if(Session::has('quiz_id')) {
        $quiz_id = Session::get('quiz_id');
        }
    if(Session::has('question') || Session::has('answer') ){
        $question = Session::get('question');
        $answer = Session::get('answer');

    }?>
    <style>
        .help-block
        {
            padding-left: 18%;
        }
        .quiz-answer
        {
            padding-left:5%;
        }
        .add_new
        {
            background-color: #2baf2b!important;
            border-color: #2baf2b!important;
            color:#fff!important;
        }
    </style>
    <div class="row row-dashboard">
        <div class="panel panel-default select">
            <div class="panel-body">
                <form class="form-horizontal">
                    <fieldset>
                        <?php foreach ($question as $question_data)
                               {
                        if($quiz_id == $question_data->quiz_id) {
                            $question_id = $question_data->id;
                        ?>
                            <div class="form-group">
                                <label for="inputtitle" class="col-lg-2 control-label">Question</label>
                                <div class="col-md-9" style="font-size:15px;">
                                    <form id="test">
                                    <strong><input id="<?php echo $question_id;?>" type="text" class="form-control" disabled style="background-color: #ffffff;border:none;" value="<?php echo $question_data->title;?>"/>
                                    </strong>
                                    </form>
                                </div>
                                <div class="col-md-1">
                                    <a href="javascript:void(0);" onclick="edit_content('<?=$question_id ?>');"><img src="<?=asset_url(). '/web/img/edit_content.png'?>" style="width:30%;float:left;" alt=""/></a>
                                        <a href="javascript:void(0);" onclick="delete_quiz_question_answer('<?=$question_id ?>');" ><img src="<?=asset_url(). '/web/img/delete_content.png'?>" style="width:30%;float:right;" alt=""/></a>
                                </div>
                            </div>

                        <?php    foreach ($answer as $answer_data){
                        if($question_id == $answer_data->question_id){
                            if($answer_data->is_answer == 1){?>
                        <div class="form-group">
                            <label class="col-lg-2 control-label"><span id="rdio-<?php echo $question_id;?>"><input class="<?php echo $question_id;?>" value="1" type="radio" checked="checked" id="<?php echo $answer_data->question_id;?>" name="<?php echo $answer_data->question_id;?>" style="margin-bottom:20px;"/></span></label>
                            <?php }
                            else{?>
                            <div class="form-group" id="<?php echo $question_id;?>">
                                <label class="col-lg-2 control-label"><span id="rdio-<?php echo $question_id;?>"><input class="<?php echo $question_id;?>" value="0" type="radio" id="<?php echo $answer_data->question_id;?>" name="<?php echo $answer_data->question_id;?>" style="margin-bottom:20px;"/></span></label>
                                <?php }?>
                            <div class="col-md-10" style="padding-top:7px;">
                                <input id="<?php echo $question_id;?>" name="<?php echo $question_id;?>" type="text" class="form-control" disabled style="background-color: #ffffff;border:none;margin-top:-6px;" value="   <?php echo $answer_data->answer;?>"/>
                            </div>
                        </div>
                            <?php }} ?>
                            <div class="form-group">
                                <div class="col-md-6"></div>
                                <div class="col-md-6" style="margin-top:-50px;">
                                    <button type="button" id="sbmit-<?php echo $question_id;?>" onclick="save_new_data(<?= $question_id;?>);" style="display:none;" class="btn btn-primary">Submit</button>
                                </div>
                            </div>
                            <hr>
                            <?php }}?>

                            <div class="form-group">

                            <div class="col-md-12" style="text-align: center;">
                                <button type="button" id="NewQuestion" data-toggle="modal" data-target="#add_new_quiz" class="btn btn-primary add_new">Add New Question</button>
                            </div>

                            </div>
                            </div>
                    </fieldset>
                </form>
            </div>
        </div>
        <div class="panel-body-img"></div>
    </div>
        <div class="col-md-5"></div>
        <div class="panel-body-img"></div>
        <div class="col-md-6"></div>
        <div class="modal fade modal1" id="add_new_quiz" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Add Quiz Question</h4>
                </div>
                <div class="modal-body" style="height:auto;">
                    <form class="form-horizontal" id="add-quiz-question-form">
                        <fieldset>
                            <div class="form-group">
                                <span class="help-block"></span>
                                <label for="inputtitle" class="col-lg-2 control-label">Add Question</label>
                                <div class="col-md-10">
                                    <input type="text" class="form-control" id="quizQuestion" placeholder="Title">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="inputtitle" class="col-lg-2 control-label">Add Answer</label>
                                <div class="col-md-10">
                                    <input id="btnAdd" type="button" class="btn btn-primary" value="Add Answer" />
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-12 quiz-answer">
                                    <div id="TextBoxContainer">
                                        <!--Textboxes will be added here -->
                                    </div>

                                </div>
                                <div class="form-group">
                                    <div class="col-md-10">
                                        <a id="content_submit" href="javascript:void(0)" onclick="add_quiz_question_answer();display_quiz_question_answer();" class="btn btn-primary" style="float:right;position:absolute;right:-72px;">Submit</a>
                                        <input type="hidden" id="content_id" name="content_id" value="">
                                    </div>
                                </div>
                            </div>
    <script type="text/javascript">
        (function add_title(){
            document.getElementById("page_title").innerText = "<?php echo Session::get("quiz_title") ?>";
        })();

        $(function () {
            $("#btnAdd").bind("click", function () {
                var div = $("<div />");
                div.html(QuizAnswer(""));
                $("#TextBoxContainer").append(div);
            });
            $("body").on("click", ".remove", function () {
                $(this).closest("div").remove();
            });
            $("#display").bind("click", function () {
                var div = $("<div />");
                div.html(displayquiz(""));
                $("#textcontent").append(div);
            });
            $("#question_added").bind("click", function () {
                var div = $("<div />");
                div.html(display_quiz_question_answer(""));
                $("#TextBoxContain").append(div);
            });
            $("input[type=radio]").attr('disabled', true);




        });
        function QuizAnswer(value) {
            var answers = document.getElementsByClassName( 'quizAnswer' ),
                options  = [].map.call(answers, function( answer ) {
                    return answer.value;
                });

            return '<label style="padding:2px;"><input name = "quizAnswerSelected" class="quizAnswerSelected" type="radio" id="quizAnswerSelected" value = "' + options.length + '" /><input name = "quizAnswer" type="text" id="quizAnswer" value = "' + value + '" class="quizAnswer" />&nbsp;' +
                '<input type="button" value="&times;" class="remove" /></label>'
        }

        function edit_content(id){
            $("input[type=radio]").attr('disabled', false).css({"color":"#000"});
            $("input[type=text]").attr('disabled', false);
            document.getElementById('sbmit-'+id).style.display = "";
            $( "div.iradio_minimal" ).removeClass("disabled")
        }

        function add_quiz_question_answer(){
            document.getElementById("NewQuestion").enabled = true;
            var answers = document.getElementsByClassName( 'quizAnswer' ),
                options  = [].map.call(answers, function( answer ) {
                    return answer.value;
                });
            if($('#quizQuestion').val()=='' || $('#quizAnswer').val()=='' || $('#quizAnswerSelected').val()=='' ){
                $(".help-block").html('<span style="text-align: center;font-size:15px;color: #f56954;">Please enter this field.</span>');
            } else {
                if ($('#content_id').val() > 0) {
                    var content_id = $('#content_id').val();
                } else {
                    var content_id = 0;
                }
                console.log('<?php echo $quiz_id ?>');
                    $.ajax({
                        type: "POST",
                        url: '<?php echo URL::Route('AddQuizQuestionAnswer') ?>',
                        data: {
                            content_id: content_id,
                            quizQuestion: $('#quizQuestion').val(),
                            quizAnswer: options,
                            quizAnswerSelected:$("#add-quiz-question-form input[type='radio']:checked").val(),
                            quiz_id:('<?php echo $quiz_id ?>')
                        },
                        success: function (data) {
                            console.log(data);
                            location.reload();
                        }
                    });

            }
        }
        function save_new_data(questions_id) {
var Questions = <?php echo json_encode($question)?>;
var new_question_data = [],new_answer_data = [];
var Answers = <?php echo json_encode($answer)?>;
var Answer_Selected = [];
var i = 1;
            Questions.forEach(function(entry) {
                //console.log(entry);
                if( questions_id == entry.id ){
                    var key = entry.id;
                    new_question_data =  {key : key,question: document.getElementById(questions_id).value};
                }
            });

//            Answers[]
            var currentAnswer = Answers.filter(item => {
                return item.question_id == questions_id;
            });

            currentAnswer.forEach(function(entry, index) {
                //console.log(entry);
                if( questions_id == entry.question_id ){
                    var key = entry.id;
                    var e = document.getElementsByClassName(questions_id)[index];


                    Answer_Selected[index] = e.checked ? 1:0;

                    new_answer_data[index] =  {answer : document.getElementsByName(questions_id)[i].value,
                                                key : key, is_answer_selected:e.checked ? 1:0};
                    i = i+2;
                }
            });
            var url = window.location.pathname;
            var quiz_id = url.substring(url.lastIndexOf('/') + 1);
            $.ajax({
                type: "POST",
                url:'<?php echo URL::Route('SaveUpdatedQuiz') ?>',
                data:{quiz_id:quiz_id,new_question:new_question_data,new_answer:new_answer_data,answer_selected:Answer_Selected,question_id:questions_id},
                success: function(data) {
                    console.log(data);
                    location.reload();
                }
            });
        }
        function delete_quiz_question_answer(question_id){
            var url = window.location.pathname;
            var quiz_id = url.substring(url.lastIndexOf('/') + 1);
            $.ajax({
                type: "POST",
                url:'<?php echo URL::Route('DeleteQuestionAnswer') ?>',
                data:{question_id:question_id,quiz_id:quiz_id},
                success: function(data) {
                    console.log(data);
                    location.reload();
                }
            });

        }
    </script>
@stop