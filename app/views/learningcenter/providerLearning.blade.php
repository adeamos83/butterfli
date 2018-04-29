@extends(Session::has('admin_id') ? 'layout' : 'learningcenter.providerLayout')
@section('content')

<?php if(Session::has('admin_id')){
    $admin = 1;
}else{
    $admin = 0;
}?>

<div class="row row-dashboard">
    <?php   if(count($learningCategory)>0){
    foreach($learningCategory as $category){
    if($category->id >= 2){ ?>
    <div class="col-sm-1"></div>
    <?php }?>
    <a href="{{ URL::Route('Sections', $category->id) }}">
        <div class="col-sm-5 col-md-5 col-xs-12">
            <div class="panel panel-default panel-custom butterfli-learning-custom-logo box_shadow_panel">
                <div class="panel-body">
                    <div class="panel-body-heading">
                        <h4 style="text-align:center;"><?= $category->category ?></h4>
                    </div>
                    <div class="panel-body-img" style="padding-top:5px;">
                        <img class="butterfli-learning-logo box_shadow_image" src="<?=asset_url().$category->image_url ?>" alt="">
                    </div>
                </div>
            </div>
        </div>
    </a>
    <?php
    }
    }
    ?>

</div>
<br>
<br>
<?php   if($admin == 1){?>
            <a href="{{route('AddEditQuizSelection',1)}}">
                <div class="btn btn-primary" style="text-align: center;margin-left:390px;"><strong style="font-size:15px;text-align: center">Add/Edit Quiz</strong></div>
            </a>
<?php   }?>
@stop