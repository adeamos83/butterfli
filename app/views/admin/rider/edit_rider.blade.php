
@extends('layout')

@section('content')

<div class="box box-primary">
              
                                <!-- form start -->
                               <form method="post" id="main-form" action="{{ URL::Route('AdminUserUpdate') }}"  enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $owner->id ?>">

                                    <div class="box-body">
                                        <div class="form-group">
                                            <label>Name</label>
                                            <input type="text" class="form-control" name="contact_name" value="<?= $owner->contact_name ?>" placeholder="Name" >

                                        </div>

                                         <div class="form-group">
                                            <label>Phone</label>
                                            <input class="form-control" type="text" name="phone" value="<?= $owner->phone ?>" placeholder="Phone">

                                
                                        </div>


                                         <div class="form-group">
                                            <label>Address</label>
                                            <input class="form-control" type="text" name="address" value="<?= $owner->address ?>" placeholder="Address">


                                        </div>


                                         <div class="form-group">
                                            <label>State</label>
                                            <input class="form-control" type="text" name="state" value="<?= $owner->state ?>" placeholder="State">

                                        </div>



                                        <div class="form-group">
                                            <label>Zip Code</label>
                                            <input class="form-control" type="text" name="zipcode" value="<?= $owner->zipcode ?>" placeholder="Zip Code">

                                        </div>

                                        <div class="form-group">
                                            <label>User is an app tester (1 = Yes)</label>
                                            <input class="form-control" type="text" name="is_tester" value="<?= $owner->is_tester ?>" placeholder="App Tester">

                                        </div>


                                   
                                    </div><!-- /.box-body -->

                                    <div class="box-footer">

                                      <button type="submit" id="edit" class="btn btn-primary btn-flat btn-block">Update Changes</button>
                                    </div>
                                </form>
                            </div>



<?php
if($success == 1) { ?>
<script type="text/javascript">
    alert('Owner Profile Updated Successfully');
</script>
<?php } ?>
<?php
if($success == 2) { ?>
<script type="text/javascript">
    alert('Sorry Something went Wrong');
</script>
<?php } ?>

<script type="text/javascript">
$("#main-form").validate({
  rules: {
    contact_name: "required",

    email: {
      required: true,
      email: true
    },

   phone: {
    required: true,
    digits: true,
  }


  }
});
</script>

@stop