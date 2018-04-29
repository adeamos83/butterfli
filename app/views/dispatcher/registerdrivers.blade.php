@extends('dispatcher.layout')

@section('content')
<div class="row mt">
    <div class="col-md-12">
        @if(Session::has('error'))
            <div class="alert alert-danger">
                <b>{{ Session::get('error') }}</b>
            </div>
        @endif
        @if(Session::has('success'))
            <div class="alert alert-success">
                <b>{{ Session::get('success') }}</b>
            </div>
        @endif
        @if(isset($error))
            <div class="alert alert-danger">
                <b>{{ $error }}</b>
            </div>
        @endif
        @if(isset($success))
            <div class="alert alert-success">
                <b>{{ $success }}</b>
            </div>
        @endif
            <div class="form-panel" style="margin:0px;">
            <form id="form1" class="form-horizontal style-form" method="post" enctype="multipart/form-data" action="{{URL::Route('AddCSVDrivers')}}">
                <div class="form-group">
                    <label style="text-align:center;" class="col-sm-2 control-label">Upload CSV for register Drivers</label>

                    <div class="col-sm-3">
                        <input style="border:none;margin-top: 4px;" type="file" class="form-control" accept=".csv" id="csvdriver" name="csvdriver" >
                    </div>
                    <div class="col-sm-3">
                        <input style="border:none;" type="submit" class="btn btn-primary" name="submit" value="Upload CSV" >
                    </div>
                </div>
            </form>

            <form id="form2" class="form-horizontal style-form" method="post" action="{{URL::Route('AddNewDriver')}}">
                <div class="form-group">
                    <label style="text-align:center;" class="col-sm-2 col-sm-2 control-label">Name</label>
                    <div class="col-sm-10">
                        <input type="text" style="height:30px;" class="form-control" name="contact_name" value="">
                    </div>
                </div>
                <div class="form-group">
                    <span id="no_mobile_error1" style="display: none"> </span>
                    <label style="text-align:center;" class="col-sm-2 col-sm-2 control-label">Email</label>
                    <div class="col-sm-10">
                        <input type="email" style="height:30px;" class="form-control" name="email"  value="">
                    </div>
                </div>
                <div class="form-group">
                    <label style="text-align:center;" class="col-sm-2 col-sm-2 control-label">Password</label>
                    <div class="col-sm-10">
                        <input type="password" style="height:30px;" class="form-control" name="password" value="">
                    </div>
                </div>
    <?php   if(Session::get('is_admin') == 1) { ?>
                <div class="form-group">
                    <label style="text-align:center;" class="col-sm-2 col-sm-2 control-label">Company</label>
                    <div class="col-sm-10">
                        <select class="select_company_name" name="transportation_provider_id" id="transportation_provider_id">
                            <option selected="true" disabled="disabled" style="width:90%;">Select Company Name</option>
                            <?php   if(count($transportation_provider) >0){
                                        foreach($transportation_provider as $tps){ ?>
                                            <option value="<?php echo $tps->id ?>"><?php echo $tps->company ?></option>
                            <?php       }
                                    }
                            ?>
                        </select>
                    </div>
                </div>
    <?php   } ?>
                <div class="form-group">
                    <label style="text-align:center;" class="col-sm-2 col-sm-2 control-label">Phone</label>
                    <div class="col-sm-10">
                        <input type="text" style="height:30px;" class="form-control" name="phone"  value="">
                    </div>
                </div>
                <input type="hidden" name="type" id="type" value="2">
                <button id="register" type="submit1" class="btn btn-primary">Register</button>
            </form>
        </div>
    </div>
</div>
@stop 