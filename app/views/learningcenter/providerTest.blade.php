@extends(Session::has('admin_id') ? 'layout' : 'learningcenter.providerLayout')
@section('content')
    <?php if(Session::has('admin_id')){
        $admin = 1;
    }else{
        $admin = 0;
    }?>

    <style>
        @import url(http://fonts.googleapis.com/css?family=Rokkitt);
        h1 {
            font-family:serif;
            text-align: center;
        }
        ul {
            list-style: none;
        }
        li {
            font-family:serif;
            font-size: 1.3em;
        }
        input[type=radio] {
            border: 0px;
            width: 1em;
            height: 3em;
            margin-right: 1.5em;
            vertical-align:-18px;
        }
        p {
            font-family: serif;
        }
        input[type="radio"] {
            padding-right:10px!important;
        }
        .remove
        {
            background-color: #ff4d4d;
            color:white;
            border: 1px solid #ff4d4d;
        }
        .add_ans
        {
            background-color: #9C27B0;
            color: #fff;
            border: 1px solid #9C27B0;
            box-shadow: 2px 2px #f5f5f5;
            border-radius:3px;
        }
    </style>
    <body>
    <div class="row">
        <div class="col-sm-1"></div>
        <div class="col-sm-10">
            <div class="panel panel-default panel-custom">
                <div class="panel-body">
                    <div class="quizContainer">
                        <div class="col-xs-12"><div class="text-center" style="float:right;position:relative;top:2px;">Time left:<span id="timer"></span></div></div>
                        <div class="col-xs-12"><span class="question_id">Question</span><div class="question_number"></div></div>
                        <div class="col-xs-12"><div class="question"></div></div>
                        <ul class="choiceList"></ul>
                        <div class="quizMessage"></div>
                        <div class="result"></div>
                        <div class="testing_button" id="testing_button">
                            <div class="test_button btn btn-primary ladda-button" data-style="zoom-in"><span class="ladda-label">Next Question</span></div>
                        </div>
                        <br>
                    </div>

                </div>
            </div>
        </div>
        <div class="col-sm-1"></div>
    </div>
    <section>
        <footer class="site-footer">
            <div class="text-center">
                <?php date("Y"); ?> - <?php echo Config::get('app.website_title'); ?>
                <a href="#" class="go-top">
                    <i class="fa fa-angle-up"></i>
                </a>
            </div>
        </footer>
    </section>
    </body>

    <div class="modal fade" id="quiz_model" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Add/Edit Quiz</h4>
                </div>
                <div class="modal-body">
                    <form class="form-horizontal">
                        <fieldset>
                            <div class="form-group">
                                <label for="inputtitle" class="col-lg-2 control-label">Add Question</label>
                                <div class="col-md-10">
                                    <input type="text" class="form-control" id="inputtitle" placeholder="Title">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="inputtitle" class="col-lg-2 control-label">Add Quiz</label>
                                <div class="col-md-10">
                                    <input id="btnAdd" type="button" class="add_ans" value="Add Quiz" />
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-10">
                                    <div id="TextBoxContainer">
                                        <!--Textboxes will be added here -->
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-10">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </div>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        var questions = <?= $answerJson ?>;
        var currentQuestion = 0;
        var correctAnswers = 0;
        var quizOver = false;
        var completed_time;
        var contents_id = <?=$content_id ?>;
        var categorys_id = <?=$category_id ?>;
        //alert(contents_id);
        $(document).ready(function () {
            var c = 300;
            var test_duration_sec = c;
            var t;
            var question_number = 1;
            timedCount();
            function timedCount() {
                //var hours = parseInt( c / 3600 ) % 24;
                var minutes = parseInt( c / 60 ) % 60;
                var seconds = c % 60;
                var result = (minutes < 10 ? "0" + minutes : minutes) + ":" + (seconds  < 10 ? "0" + seconds : seconds);
                $('#timer').html(result);
                //console.log('timer ' + c);
                //console.log('Time Left: ' + result);
                console.log('Time Completed in: ' + (test_duration_sec-c));
                completed_time = test_duration_sec-c;
                var completed_quiz = document.getElementById('testing_button').value;
                if(c === 0 && completed_quiz !== 'Submit' ){
                    $.ajax({
                        type: "POST",
                        url:'<?php echo URL::Route('SendResults') ?>',
                        data:{result_array:questions,completed_time:completed_time,content_id:contents_id,category_id:categorys_id},
                        success: function(data) {
                            console.log(data);
                            window.location = "/learningcenter/participant_result";
                        }
                    });
                    quizOver = true;
                    displayScore();
                }else {
                    c = c - 1;
                    t = setTimeout(function () {
                        timedCount()
                    }, 1000);
                }
            }
            // Display the first question
            displayCurrentQuestion(question_number++);
            $(this).find(".quizMessage").hide();
            // On clicking next, display the next question
            $(this).find(".testing_button").on("click", function () {
                if (!quizOver) {
                    value = $("input[type='radio']:checked").val();
                    if (value == undefined) {
                        $(document).find(".quizMessage").text("Please select an answer");
                        $(document).find(".quizMessage").show();
                    } else {
                        console.log(question_number);
                        $(document).find(".test_button").text("Next Question");
                        var l = Ladda.create( document.querySelector( '.ladda-button' ) ).start();
                        var question_id= questions[currentQuestion].question_id;
                        // TODO: Remove any message -> not sure if this is efficient to call this each time....
                        $(document).find(".quizMessage").hide();
                        $.ajax({
                            type: "POST",
                            url: '<?php echo URL::Route('CheckQuestion') ?>',
                            data: {option: value, question_id: question_id},
                            success: function (data) {
                                console.log(data);
                                if(data==2){
                                    $(document).find(".quizMessage").text("Wrong answer");
                                    $(document).find(".quizMessage").show();
                                    var l = Ladda.create( document.querySelector( '.ladda-button' ) ).stop();
                                }else{
                                    questions[currentQuestion].selected_answer = value;
                                    var l = Ladda.create( document.querySelector( '.ladda-button' ) ).stop();
                                    currentQuestion++; // Since we have already displayed the first question on DOM ready
                                    if (currentQuestion < questions.length) {
                                        displayCurrentQuestion(question_number++);
                                        l.stop();
                                    } else {
                                        displayScore();
                                       var k = $('.test_button').data('style','contract');
                                       $(document).find(".test_button").text("Submit");
                                       quizOver = true;
                                    }
                                }
                            }
                        });
                    }
                } else {
                    quizOver = false;
                    Ladda.create( document.querySelector( '.ladda-button' ) ).start();
                }
            });
        });
        // This displays the current question AND the choices
        function displayCurrentQuestion(number) {
            var question_number = number ;
            console.log("In display current Question");
            var question = questions[currentQuestion].question;
            var questionClass = $(document).find(".quizContainer > .col-xs-12 > .question");
            var questionnumber = $(document).find(".quizContainer > .col-xs-12 > .question_number");
            var choiceList = $(document).find(".quizContainer > .choiceList");
            var numChoices = questions[currentQuestion].choices.length;
            // Set the questionClass text to the current question
            $(questionClass).text(question);
            $(questionnumber).text(question_number);
            // Remove all current <li> elements (if any)
            $(choiceList).find("li").remove();
            var choice;
            for (i = 0; i < numChoices; i++) {
                choice = questions[currentQuestion].choices[i];
                choice_id = questions[currentQuestion].choicesIdArray[i]['option_id'];
                $('<li><input type="radio" value=' + choice_id + ' name="dynradio" />' + choice + '</li>').appendTo(choiceList);
            }
        }
        function resetQuiz() {
            currentQuestion = 0;
            correctAnswers = 0;
            hideScore();
        }
        function displayScore() {
            //$(document).find(".quizContainer > .result").text("You scored: " + correctAnswers + " out of: " + questions.length);
            //$(document).find(".quizContainer > .result").show();
            //alert($('#timer').val());
            console.log('Time Completed in: ' + completed_time);
            $.ajax({
                type: "POST",
                url:'<?php echo URL::Route('SendResults') ?>',
                data:{result_array:questions,completed_time:completed_time,content_id:contents_id,category_id:categorys_id},
                success: function(data) {
                    console.log(data);
                    window.location = "/learningcenter/participant_result";
                }
            });
        }
        function hideScore() {
            $(document).find(".result").hide();
        }
        $(function () {
            $("#btnAdd").bind("click", function () {
                var div = $("<div />");
                div.html(GetDynamicTextBox(""));
                $("#TextBoxContainer").append(div);
            });
            $("#btnGet").bind("click", function () {
                var values = "";
                $("input[name=DynamicTextBox]").each(function () {
                    values += $(this).val() + "\n";
                });
                alert(values);
            });
            $("body").on("click", ".remove", function () {
                $(this).closest("div").remove();
            });
        });
        function GetDynamicTextBox(value) {
            return '<label><input name = "DynamicTextBox" type="radio" value = "\' + value + \'" /><input name = "DynamicTextBox" type="text" value = "' + value + '" />&nbsp;' +
                '<input type="button" value="x" class="remove" /></label>'
        }
    </script>
@stop