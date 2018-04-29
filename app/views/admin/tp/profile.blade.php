@extends('layout')

@section('content')

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
<!-- form start -->
<form method="post" id="main-form" action="{{ URL::Route('TransportationProviderInsertUpdate') }}"  enctype="multipart/form-data">

    <div class="box-body">
        <div class="form-group">
            <label>Email</label>
            <input class="form-control" type="text" name="email" value="<?= $tp->email ?>" placeholder="Email">
        </div>
        <div class="form-group">
            <label>Company</label>
            <input type="text" class="form-control" name="company" value="<?= $tp->company ?>" placeholder="Company" >
        </div>
        <div class="form-group">
            <label>Phone</label>
            <input class="form-control" type="text" name="phone" value="<?= $tp->phone ?>" placeholder="Phone">
        </div>
        <div class="form-group">
            <label>Rate</label>
            <input class="form-control" type="text" name="rate" value="<?= $tp->rate ?>" placeholder="Rate">
        </div>
        <div class="form-group">
            <label>Contact</label>
            <input class="form-control" type="text" name="contact" value="<?= $tp->contact ?>" placeholder="Contact">
        </div>
        <div class="form-group">
            <label>Status</label>
            <input class="form-control" type="text" name="status" value="<?= $tp->status ?>" placeholder="Status">
        </div>
        <div class="form-group">
            <label>Service Area</label>
            <input class="form-control" type="text" name="service_area" value="<?= $tp->service_area ?>" placeholder="Service Area">
        </div>
        <div class="form-group">
            <label>Available for after hours</label>
            <input class="form-control" type="text" name="available_after_hours" value="<?= $tp->available_after_hours ?>" placeholder="Available for after hours">
        </div>

        <div class="form-group">
            <label>Service Hours</label>
            <input class="form-control" type="text" name="service_hours" value="<?= $tp->service_hours ?>" placeholder="Service Hours">
        </div>

        <div class="form-group">
            <label>WheelChair Vehicles</label>
            <input class="form-control" type="text" name="wheelchair_vehicles" value="<?= $tp->wheelchair_vehicles ?>" placeholder="Wheelchair Vehicles">
        </div>

        <div class="form-group">
            <label>Comment</label>
            <input class="form-control" type="text" name="comment" value="<?= $tp->comment ?>" placeholder="Comment">
        </div>
        <div class="form-group">
            <label>TP Address</label>
            <input class="form-control" type="text" name="tp_address" value="<?= $tp->tp_address ?>" placeholder="TP Address">
        </div>
        <div class="form-group">
            <label>TPS Vehicles</label>
            <input class="form-control" type="text" name="tps_vehicles" value="<?= $tp->tps_vehicles ?>" placeholder="TPS Vehicles">
        </div>
        <div class="form-group">
            <label>Device</label>
            <input class="form-control" type="text" name="device" value=""<?= $tp->device ?> placeholder="Device">
        </div>
    </div><!-- /.box-body -->

    <div class="box-footer">
        <input class="form-control" type="hidden" name="id" value="0">
        <button type="submit" class="btn btn-primary btn-flat btn-block">Add New Provider</button>
    </div>
</form>


<script type="text/javascript">
    $("#main-form").validate({
        rules: {
            phone: "required",
            company: "required",
            phone: {
                required: true,
            }
        }
    });
</script>


@stop