@extends(Session::has('admin_id') ? 'layout' : 'learningcenter.providerLayout')
@section('content')

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
        margin-top: -.2em;
    }
    p {
        font-family:serif;
    }
</style>

<div class="row">
    <div class="col-sm-1"></div>
    <div class="col-sm-10 col-xs-12">
        <div class="panel panel-default panel-custom">
            <div class="panel-body">
                <body>
                    <div class="quizContainer">
                        <div class="question" style="top: 0 !important;"></div>
                        <ul class="choiceList"></ul>
                        <div class="quizMessage"></div>
                        <div class="result"></div>
                        <a href="/learningcenter/learning"><div class="nextButton">Back to Learning Center</div></a>
                        <br>
                    </div>
                </body>
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
<!--footer end-->
</section>

<script type="text/javascript">

    var questions = '<?= $result ?>';

    $(document).ready(function () {

       displayScore();
    });

    function displayScore() {
        var pass_fail = '<?php echo $pass_fail ?>';
        console.log("In display score");

        var questionClass = $(document).find(".quizContainer > .question");
        // Set the questionClass text to the current question
        //$(questionClass).text("You have scored "+questions);
        if( pass_fail == 1) {
            $(questionClass).text("You have Passed");
        }
        else{
            $(questionClass).text("You Fail");
        }
    }
</script>
@stop