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
<form method="post" id="main-form" action="{{ URL::Route('DispatcherProfileSave') }}"  enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= $dispatcher->id ?>">
    <div class="box-body">
        <div class="form-group">
            <label>Name</label>
            <input class="form-control" type="text" name="contact_name" value="<?= $dispatcher->contact_name ?>" placeholder="Name">
        </div>
        <div class="form-group">
            <label>Email</label>
            <input class="form-control" type="text" name="email" value="<?= $dispatcher->email ?>" placeholder="Email">
        </div>
        <div class="form-group">
            <label>Company</label>
            <!-- transportation_provider_id: <?= $dispatcher->transportation_provider_id ?> -->
            <select style="display: block;" autocomplete="off" name="transportation_provider_id">
                <option value="0"<?php if($dispatcher->transportation_provider_id == 0 || $dispatcher->transportation_provider_id == NULL) { echo ' selected="selected"'; } ?>>Select Transportation Provider</option>
                <?php foreach ($dispatcher->transportation_providers() as $key => $value) { ?>
                    <?php if($value == null) { continue; } ?>
                    <option value="<?php echo $value->id; ?>"<?php if($dispatcher->transportation_provider_id == $value->id) { echo ' selected="selected"'; } ?>><?php echo $value->company; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="form-group">
            <label>Phone</label>
            <input class="form-control" type="text" name="phone" value="<?= $dispatcher->phone ?>" placeholder="Phone">
        </div>
        <div class="form-group">
            <label>Approved</label>
            <div class="clear"></div>
            <input type="radio" name="is_active" value="0" id="is_active_no"<?php if($dispatcher->is_active == 0) { echo ' checked';} ?>>
            <label for="is_active_no">No</label>
            <input type="radio" name="is_active" value="1" id="is_active_yes"<?php if($dispatcher->is_active == 1) { echo ' checked';} ?>>
            <label for="is_active_yes">Yes</label>
        </div>
        <div class="form-group">
            <label>Admin Privileges (ButterFLi Personnel Only)</label>
            <div class="clear"></div>
            <input type="radio" name="is_admin" value="0" id="is_admin_no"<?php if($dispatcher->is_admin == 0) { echo ' checked';} ?>>
            <label for="is_admin_no">No</label>
            <input type="radio" name="is_admin" value="1" id="is_admin_yes"<?php if($dispatcher->is_admin == 1) { echo ' checked';} ?>>
            <label for="is_admin_yes">Yes</label>
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