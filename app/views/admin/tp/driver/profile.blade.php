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
<form method="post" id="main-form" action="{{ URL::Route('DriverProfileSave') }}"  enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= $driver->id ?>">
    <div class="box-body">
        <div class="form-group">
            <label>Name</label>
            <input class="form-control" type="text" name="contact_name" value="<?= $driver->contact_name ?>" placeholder="Name">
        </div>
        <div class="form-group">
            <label>Email</label>
            <input class="form-control" type="text" name="email" value="<?= $driver->email ?>" placeholder="Email">
        </div>
        <div class="form-group">
            <label>Company</label>
            <!-- transportation_provider_id: <?= $driver->transportation_provider_id ?> -->
            <select style="display: block;" autocomplete="off" name="transportation_provider_id">
                <option value="0"<?php if($driver->transportation_provider_id == 0 || $driver->transportation_provider_id == NULL) { echo ' selected="selected"'; } ?>>Select Transportation Provider</option>
                <?php foreach ($driver->transportation_providers() as $key => $value) { ?>
                    <?php if($value == null) { continue; } ?>
                    <option value="<?php echo $value->id; ?>"<?php if($driver->transportation_provider_id == $value->id) { echo ' selected="selected"'; } ?>><?php echo $value->company; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="form-group">
            <label>Phone</label>
            <input class="form-control" type="text" name="phone" value="<?= $driver->phone ?>" placeholder="Phone">
        </div>
        <div class="form-group">
            <label>Approved for Training</label>
            <div class="clear"></div>
            <input type="radio" name="is_approved" value="0" id="is_approved_no"<?php if($driver->is_approved == 0) { echo ' checked';} ?>>
            <label for="is_approved_no">No</label>
            <input type="radio" name="is_approved" value="1" id="is_approved_yes"<?php if($driver->is_approved == 1) { echo ' checked';} ?>>
            <label for="is_approved_yes">Yes</label>
        </div>
        <div class="form-group">
            <label>Approved for Driving</label>
            <div class="clear"></div>
            <input type="radio" name="is_authorize" value="0" id="is_authorize_no"<?php if($driver->is_authorize == 0) { echo ' checked';} ?>>
            <label for="is_authorize_no">No</label>
            <input type="radio" name="is_authorize" value="1" id="is_authorize_yes"<?php if($driver->is_authorize == 1) { echo ' checked';} ?>>
            <label for="is_authorize_yes">Yes</label>
        </div>
    </div><!-- /.box-body -->

    <div class="box-footer">
        <button type="submit" class="btn btn-primary btn-flat btn-block">Update Profile</button>
    </div>
</form>


<script type="text/javascript">
    $("#main-form").validate({
        rules: {
            phone: "required",
            phone: {
                required: true,
            }
        }
    });
</script>


@stop