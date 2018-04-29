@extends('enterpriseclient.layout')

@section('content')

    <div class="col-md-12 mt">

        @if($provider->companylogo == "" && $is_agent==0 && $user_select!=3)
            <div class="alert alert-danger">
                <b>{{ "Please Select logo..." }}</b>
            </div>
        @endif
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

        <div class="content-panel">
            <h4>Update Profile</h4><br>
            <form class="form-horizontal style-form" method="post" action="{{URL::Route('updateprofile')}}" enctype="multipart/form-data">
                <div class="form-group">
                    <label style="text-align:center;" class="col-sm-2 col-sm-2 control-label">Name</label>
                    <div class="col-sm-7">
                        <input type="text" style="height:30px;" class="form-control" name="contact_name" value="{{ $provider->contact_name }}">
                    </div>
                </div>
                <div class="form-group">
                    <span id="no_mobile_error1" style="display: none"> </span>
                    <label style="text-align:center;" class="col-sm-2 col-sm-2 control-label">Email</label>
                    <div class="col-sm-7">
                        <input type="text" style="height:30px;" disabled="disabled" class="form-control" name="email"  value="{{ $provider->email }}">
                    </div>
                </div>
                <?php   if($is_agent==0 && $user_select!=3){ ?>
                <div class="form-group">
                    <label  style="text-align:center;" class="col-sm-2 col-sm-2 control-label">Photo</label>
                    <div class="col-md-2 col-sm-2">
                        <img src="<?php
                        if ($provider->companylogo == "") {
                            echo asset_url() . "/web/default_profile.png";
                        } else {
                            echo $provider->companylogo;
                        }
                        ?>" class="img-circle" width="60">
                    </div>
                    <div class="col-sm-2" style="position:relative;top:15px;">
                        <input style="height:35px;line-height:22px;border: none;"   type="file" class="form-control" name="picture" >
                    </div>
                </div>
                <div class="form-group">
                    <label style="text-align:center;" class="col-sm-2 col-sm-2 control-label">Company</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" style="height:30px;" name="company"  value="{{ $provider->company }}">
                    </div>
                </div>
                <div class="form-group">
                    <label style="text-align:center;" class="col-sm-2 col-sm-2 control-label">Operator Email</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" style="height:30px;" name="operator_email"  value="{{ $provider->operator_email }}">
                    </div>
                </div>
                <div class="form-group">
                    <label style="text-align:center;" class="col-sm-2 col-sm-2 control-label">Operator Phone</label>
                    <div class="col-sm-7">
                                <span class=" bg_arrow"><select class="form-control" style="max-width:104px;height:30px;" name="countrycode" id="countrycode">
                                    <option data-countryCode="US" value="+1" Selected>US (+1)</option>
                                    <option data-countryCode="GB" value="44">UK (+44)</option>
                                    <option data-countryCode="CA" value="+1">CA (+1)</option>
                                    <option data-countryCode="IN" value="+91">IN (+91)</option>
                                </select></span>
                        <input type="text" style="width:600px;float: right;position: relative;top: -55px;height:30px;" class="form-control" name="operator_phone"  value="{{ $provider->operator_phone }}">
                    </div>
                </div>
                <?php  } else{ ?>
                <div class="form-group">
                    <label style="text-align:center;" class="col-sm-2 col-sm-2 control-label">Phone</label>
                    <div class="col-sm-7">
                                            <span class=" bg_arrow"><select class="form-control" style="max-width:104px;height:30px;" name="countrycode" id="countrycode">
                                                <option data-countryCode="US" value="+1" Selected>US (+1)</option>
                                                <option data-countryCode="GB" value="44">UK (+44)</option>
                                                <option data-countryCode="CA" value="+1">CA (+1)</option>
                                                <option data-countryCode="IN" value="+91">IN (+91)</option>
                                            </select></span>
                        <input type="text" style="width:511px;height:30px;" class="form-control" name="operator_phone"  value="{{ $provider->phone }}">
                    </div>
                </div>

                <?php } ?>
                <span class="col-sm-2"></span>
                <input type="hidden" name="is_agent" value="{{$is_agent}}" />
                <input type="hidden" name="user_select" value="{{$user_select}}" />
                <button id="update" type="submit" class="btn btn-primary">Update Profile</button>

            </form>
        </div>

        <div class="content-panel">
            <h4>Change Password</h4><br>
            <form class="form-horizontal style-form" method="post" action="{{URL::Route('updatepassword')}}">
                <div class="form-group">
                    <label style="text-align:center;" class="col-sm-2 col-sm-2 control-label">Current Password</label>
                    <div class="col-sm-7">
                        <input type="password" style="height:30px;" class="form-control" name="current_password" value="">
                    </div>
                </div>
                <div class="form-group">
                    <label style="text-align:center;" class="col-sm-2 col-sm-2 control-label">New Password</label>
                    <div class="col-sm-7">
                        <input type="password" style="height:30px;"  class="form-control" name="new_password" value="">
                    </div>
                </div>
                <div class="form-group">
                    <label style="text-align:center;" class="col-sm-2 col-sm-2 control-label">Confirm Password</label>
                    <div class="col-sm-7">
                        <input type="password" style="height:30px;"  class="form-control" name="confirm_password" value="">
                    </div>
                </div>
                <span class="col-sm-2"></span>
                <input type="hidden" name="is_agent" value="{{$is_agent}}" />
                <input type="hidden" name="user_select" value="{{$user_select}}" />
                <button id="pass" type="submit" class="btn btn-primary">Change Password</button>
            </form>
        </div>
        <?php   if($is_agent==0 && $user_select!=3){ ?>
        <div class="content-panel">
            <h4>Hospital Providers <button id="addnew" onclick="show_new_provider_form();" type="submit" class="btn btn-primary">Add New Provider</button></h4><br>
            <div style="display:none;" id="new_provider_form">
                <form class="form-horizontal style-form" method="post" action="{{URL::Route('addhospitalprovider')}}">
                    <div class="form-group">
                        <label style="text-align:center;    padding-left: 55px;padding-top: 20px;" class="col-sm-2 col-sm-2 control-label">New Provider</label>
                        <div class="col-sm-4" style="padding-left:30px;">
                            <input style="width:190px;height:30px;"  type="text" class="form-control" name="provider_name" value="">
                            <button id="pass" type="submit" class="btn btn-primary" style="float: right;position: relative;top: -45px;left: -33px;margin-bottom: -20px;">Add New</button>
                        </div>
                    </div>
                </form>
            </div>

            <?php   if(count($hospital_provider)>0){
            $i=1;
            foreach($hospital_provider as $prov){ ?>

            <div class="form-group">
                <form style="margin:0px;" class="form-horizontal style-form" method="post" action="{{URL::Route('updatehospitalprovider')}}">
                    <label style="padding-top: 15px;padding-left: 90px;text-align:center;" class="col-sm-2 col-sm-2 control-label">{{$i}}</label>
                    <div class="col-sm-5" style="width:33%;height:20px;">
                        <input style="width:190px;" type="text" class="form-control" name="provider_name" value="{{$prov->provider_name }}">
                        <input type="hidden" name="provider_id" id="provider_id" value="{{$prov->id }}"/>
                        <button id="pass" type="submit" class="btn btn-primary" style="float: right;position: relative;top: -45px;">Edit Provider</button>
                    </div>
                </form>
                <?php   if(count($hospital_provider)>1){?>
                <form class="form-horizontal style-form" method="post" action="{{URL::Route('deletehospitalprovider')}}">
                    <button id="delete" type="submit" class="btn btn-primary">Delete Provider</button>
                    <input type="hidden" name="provider_id" id="provider_id" value="{{$prov->id }}"/>
                </form>
                <?php } ?>
            </div>
            <?php       $i++;
            }
            }
            ?>
            <br><br>
        </div>
        <?php   } ?>
    </div>

    <script>
        function show_new_provider_form(){
            $('#new_provider_form').show();
        }
    </script>
@stop 