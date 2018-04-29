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

        <div class="form-panel">
            <h4>Update Profile</h4><br>
            <form class="form-horizontal style-form" method="post" action="{{URL::Route('UpdateDispatcherProfile')}}">
                <div class="form-group">
                    <label style="text-align:center;" class="col-sm-2 col-sm-2 control-label">Name</label>
                    <div class="col-sm-10">
                        <input type="text" style="height:30px;" class="form-control" name="contact_name" value="{{ $provider->contact_name }}">
                    </div>
                </div>
                <div class="form-group">
                    <span id="no_mobile_error1" style="display: none"> </span>
                    <label style="text-align:center;" class="col-sm-2 col-sm-2 control-label">Email</label>
                    <div class="col-sm-10">
                        <input type="text" style="height:30px;" disabled="disabled" class="form-control" name="email"  value="{{ $provider->email }}">
                    </div>
                </div>
                <!--div class="form-group">
                    <label style="text-align:center;" class="col-sm-2 col-sm-2 control-label">Company</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" style="height:30px;" name="company"  value="">
                    </div>
                </div-->
                <div class="form-group">
                    <label style="text-align:center;" class="col-sm-2 col-sm-2 control-label">Phone</label>
                    <div class="col-sm-10">

                        <input type="text" style="height:30px;" class="form-control" name="phone"  value="{{ $provider->phone }}">
                    </div>
                </div>
                <span class="col-sm-2"></span>
                <button id="update" type="submit" class="btn btn-primary">Update Profile</button>

            </form>
        </div>
    </div>
</div>
            <div class="row mt">
                <div class="col-md-12">
        <div class="form-panel">
            <h4>Change Password</h4><br>
            <form class="form-horizontal style-form" method="post" action="{{URL::Route('UpdateDispatcherPassword')}}">
                <div class="form-group">
                    <label style="text-align:center;" class="col-sm-2 col-sm-2 control-label">Current Password</label>
                    <div class="col-sm-10">
                        <input type="password" style="height:30px;" class="form-control" name="current_password" value="">
                    </div>
                </div>
                <div class="form-group">
                    <label style="text-align:center;" class="col-sm-2 col-sm-2 control-label">New Password</label>
                    <div class="col-sm-10">
                        <input type="password" style="height:30px;"  class="form-control" name="new_password" value="">
                    </div>
                </div>
                <div class="form-group">
                    <label style="text-align:center;" class="col-sm-2 col-sm-2 control-label">Confirm Password</label>
                    <div class="col-sm-10">
                        <input type="password" style="height:30px;"  class="form-control" name="confirm_password" value="">
                    </div>
                </div>
                <span class="col-sm-2"></span>
                <button id="pass" type="submit" class="btn btn-primary">Change Password</button>
            </form>
        </div>
    </div>
@stop 