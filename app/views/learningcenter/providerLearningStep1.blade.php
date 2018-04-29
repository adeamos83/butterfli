@extends(Session::has('admin_id') ? 'layout' : 'learningcenter.providerLayout')
@section('content')

    <?php if(Session::has('admin_id')){
        $admin = 1;
    }else{
        $admin = 0;
    }?>
    <h3 style="text-align: center">Hello, {{ $walker_name }}</h3>
    <div class="row row-dashboard">
        <div class="col-sm-12 col-md-12 col-xs-12">
            <div class="panel panel-default panel-custom">
                <div class="panel-body">
                    <div class="panel-body-heading">
                        <h4 style="text-align:center;">Training Content for ButterFLi Drivers. Continue to start your session.</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row row-button">
        <div class="col-sm-12 col-md-12 col-xs-12">
            <div class="panel-body" style="text-align: center;">
                <a href="{{route('DriverLearningCenter')}}">
                    <div class="btn btn-primary"><strong style="font-size:15px;text-align: center">Continue</strong></div>
                </a>
            </div>
        </div>
    </div>
@stop