@extends('layout')

@section('content')
    <style>
        .modal-backdrop {
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background-color: #000000;
        }

        .modal-backdrop.fade {
            opacity: 0;
        }

        .modal-backdrop,
        .modal-backdrop.fade.in {
            opacity: 0;
            filter: alpha(opacity=80);
        }
        .modal-title {
            color: #e8ecec;
        }
        .cont {
            /*text-align: center!important;*/
            /*background: #111;*/
            color: #EEE;
            /*border-radius: 5px;
            border: thin solid #444;*/
            overflow: hidden;
            width: 100%;
        }

        div.stars {
            width:220px;
            display: inline-block;
        }
        input.star { display: none; }

        label.star {
            float: right;
            padding: 5px;
            font-size: 36px;
            color: #444;
            transition: all .2s;
        }

        input.star:checked ~ label.star:before {
            content: '\f005';
            color: #FD4;
            transition: all .25s;
        }

        input.star-5:checked ~ label.star:before {
            color: #FE7;
            /*text-shadow: 0 0 20px #952;*/
        }

        input.star-1:checked ~ label.star:before { color: #F62; }

        label.star:hover { transform: rotate(-15deg) scale(1.3); }

        label.star:before {
            content: '\f006';
            font-family: FontAwesome;
        }
    </style>
    <div class="modal fade" style="height:290px;align:center;top:30%;width:40%;margin-left:400px;"  id="tp_rating_pop_up_responsive" role="dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#68dff0;">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Rating</h4>
            </div>
            <div class="modal-body margin-top-0">
                <form class="form-horizontal">
                    <fieldset>
                        <div class="form-group">
                            <span class="help-block"></span>
                            <div class="col-md-1"></div>
                            <div class="col-md-4" style="padding-top:24px;">
                                <label for="inputtitle">Rating:</label>
                            </div>
                            <div class="col-md-6">
                                <div class="cont">
                                    <div class="stars" id="rating">

                                    </div>
                                </div>
                                <div class="form-control-focus"></div>
                            </div>
                            <div class="col-md-1"></div>
                        </div>
                        <div class="form-group">
                            <span class="help-block"></span>
                            <div class="col-md-1"></div>
                            <div class="col-md-4">
                                <label for="inputtitle">Feedback Comment:</label>
                            </div>
                            <div class="col-md-6">
                                <input value="" readonly id="comment" style="border:none;width: 295px;"></input>
                            </div>
                            <div class="col-md-3"></div>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
    </div>

<div align="center" id="loadingimage" style="display:none;"><img src="<?= asset_url() . '/web/img/preloader.gif' ?>" style="z-index: 9999;position: fixed;left: 565px;" /></div>
<div class="box box-info tbl-box">
    <div align="left" id="paglink"><?php echo $Dispatchers->appends(array('type' => Session::get('type'), 'valu' => Session::get('valu')))->links(); ?></div>
    <table class="table table-bordered">
        <tbody><tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Transportation Provider</th>
                <th>Actions</th>
            </tr>

            <?php foreach ($Dispatchers as $Disp) { ?>
                <tr>
                    <td><?= $Disp->transportation_provider_id ?></td>
                    <td><?php echo $Disp->contact_name; ?> </td>
                    <td><?= $Disp->email ?></td>
                    <td><?php
                            if($Disp->transportation_provider_id!=null){
                                echo $Disp->transportation_company;
                            } else{
                                echo "NA";
                            }
                            ?>
                    </td>
                    <td>

                        <div class="dropdown">
                            <button class="btn btn-flat btn-info dropdown-toggle" type="button" id="dropdownMenu1" name="action" data-toggle="dropdown">
                                Actions
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
                                <li role="presentation"><a role="menuitem" id="trans_rating" tabindex="-1" href="javascript:void(0)" onclick="show_feedback('<?php echo $Disp->rating ?>','<?php echo $Disp->comment ?>');">Show Rating</a></li>
                            </ul>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        </tbody></table>
    <div align="left" id="paglink"><?php echo $Dispatchers->appends(array('type' => Session::get('type'), 'valu' => Session::get('valu')))->links(); ?></div>
</div>

<script type="text/javascript">
    function show_feedback(rating,comment){
        var tp_rating = "NA",tp_comments = "NA";
        if( rating == 1 ){
            tp_rating = '<input class="star star-5"  id="star-5-2" type="radio"  disabled name="star"  />\n' +
                '<label class="star star-5" for="star-5-2"></label>\n' +
                '<input class="star star-4" id="star-4-2"  type="radio" disabled name="star" />\n' +
                '<label class="star star-4" for="star-4-2"></label>\n' +
                '<input class="star star-3" id="star-3-2" type="radio" disabled name="star"  />\n' +
                '<label class="star star-3"  for="star-3-2"></label>\n' +
                '<input class="star star-2" id="star-2-2"  type="radio" disabled name="star"  />\n' +
                '<label class="star star-2" for="star-2-2"></label>\n' +
                '<input class="star star-1" id="star-1-2" type="radio" disabled name="star" checked />\n' +
                '<label class="star star-1"  for="star-1-2"></label>';
        }else if(rating == 2 ){
            tp_rating = '<input class="star star-5"  id="star-5-2" type="radio"  disabled name="star"  />\n' +
                '<label class="star star-5" for="star-5-2"></label>\n' +
                '<input class="star star-4" id="star-4-2"  type="radio" disabled name="star" />\n' +
                '<label class="star star-4" for="star-4-2"></label>\n' +
                '<input class="star star-3" id="star-3-2" type="radio" disabled name="star"  />\n' +
                '<label class="star star-3"  for="star-3-2"></label>\n' +
                '<input class="star star-2" id="star-2-2"  type="radio" disabled name="star" checked />\n' +
                '<label class="star star-2" for="star-2-2"></label>\n' +
                '<input class="star star-1" id="star-1-2" type="radio" disabled name="star"  />\n' +
                '<label class="star star-1"  for="star-1-2"></label>';
        }else if(rating == 3){
            tp_rating = '<input class="star star-5"  id="star-5-2" type="radio"  disabled name="star"  />\n' +
                '<label class="star star-5" for="star-5-2"></label>\n' +
                '<input class="star star-4" id="star-4-2"  type="radio" disabled name="star" />\n' +
                '<label class="star star-4" for="star-4-2"></label>\n' +
                '<input class="star star-3" id="star-3-2" type="radio" disabled name="star" checked />\n' +
                '<label class="star star-3"  for="star-3-2"></label>\n' +
                '<input class="star star-2" id="star-2-2"  type="radio" disabled name="star" />\n' +
                '<label class="star star-2" for="star-2-2"></label>\n' +
                '<input class="star star-1" id="star-1-2" type="radio" disabled name="star"  />\n' +
                '<label class="star star-1"  for="star-1-2"></label>';
        }else if(rating == 4){
            tp_rating = '<input class="star star-5"  id="star-5-2" type="radio"  disabled name="star"  />\n' +
                '<label class="star star-5" for="star-5-2"></label>\n' +
                '<input class="star star-4" id="star-4-2"  type="radio" disabled name="star" checked />\n' +
                '<label class="star star-4" for="star-4-2"></label>\n' +
                '<input class="star star-3" id="star-3-2" type="radio" disabled name="star"  />\n' +
                '<label class="star star-3"  for="star-3-2"></label>\n' +
                '<input class="star star-2" id="star-2-2"  type="radio" disabled name="star" />\n' +
                '<label class="star star-2" for="star-2-2"></label>\n' +
                '<input class="star star-1" id="star-1-2" type="radio" disabled name="star"  />\n' +
                '<label class="star star-1"  for="star-1-2"></label>';
        }else if(rating == 5){
            tp_rating = '<input class="star star-5"  id="star-5-2" type="radio"  disabled name="star"  checked />\n' +
                '<label class="star star-5" for="star-5-2"></label>\n' +
                '<input class="star star-4" id="star-4-2"  type="radio" disabled name="star"  />\n' +
                '<label class="star star-4" for="star-4-2"></label>\n' +
                '<input class="star star-3" id="star-3-2" type="radio" disabled name="star"  />\n' +
                '<label class="star star-3"  for="star-3-2"></label>\n' +
                '<input class="star star-2" id="star-2-2"  type="radio" disabled name="star" />\n' +
                '<label class="star star-2" for="star-2-2"></label>\n' +
                '<input class="star star-1" id="star-1-2" type="radio" disabled name="star"  />\n' +
                '<label class="star star-1"  for="star-1-2"></label>';
        }else {
            tp_rating = '<input class="star star-5"  id="star-5-2" type="radio"  disabled name="star"  />\n' +
                '<label class="star star-5" for="star-5-2"></label>\n' +
                '<input class="star star-4" id="star-4-2"  type="radio" disabled name="star"  />\n' +
                '<label class="star star-4" for="star-4-2"></label>\n' +
                '<input class="star star-3" id="star-3-2" type="radio" disabled name="star"  />\n' +
                '<label class="star star-3"  for="star-3-2"></label>\n' +
                '<input class="star star-2" id="star-2-2"  type="radio" disabled name="star" />\n' +
                '<label class="star star-2" for="star-2-2"></label>\n' +
                '<input class="star star-1" id="star-1-2" type="radio" disabled name="star"  />\n' +
                '<label class="star star-1"  for="star-1-2"></label>';
        }

        if(comment!=''){
            tp_comments =  comment;
        }

        $('#rating').html(tp_rating);
        document.getElementById('comment').value = tp_comments;
        $('#tp_rating_pop_up_responsive').modal('toggle');

    }
</script>

@stop