@extends(Session::has('admin_id') ? 'layout' : 'learningcenter.providerLayout')
@section('content')

    <?php if(Session::has('admin_id')){
        $admin = 1;
    }else{
        $admin = 0;
    }?>
    <div class="row row-dashboard">
        <div class="col-sm-8 col-md-8 col-xs-12" style="margin-left: 100px;">
            <div class="panel panel-default panel-custom">
                <div class="panel-body">
                    <div class="panel-body-heading">
                        <h5 style="text-align:center;">Training Module Requires Payment to Access. <br>Contact
                            <a href="mailto:info@gobutterfli.com">info@gobutterfli.com</a> if you are interested in purchasing</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop