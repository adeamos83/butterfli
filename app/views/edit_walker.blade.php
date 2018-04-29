@extends('layout')

@section('content')

<?php $counter = 1;
$all_transportation_provider = TransportationProvider::all();
?>
<style>
    .new_driver_company{
        background: transparent;
        width: 100%;
        height: 35px;
        padding-left: 8px;
    }
</style>
<div class="box box-primary">
    <div class="box-header">
        <h3 class="box-title"><?= $title ?></h3>
    </div><!-- /.box-header -->
    <!-- form start -->
    <form method="post" id="main-form" action="{{ URL::Route('AdminProviderUpdate') }}"  enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $walker->id ?>">

        <div class="box-body">
            <div class="form-group">
                <label>Name</label>
                <input type="text" class="form-control" name="contact_name" value="<?= $walker->contact_name ?>" placeholder="Name" >
            </div>

            <div class="form-group">
                <label>Email</label>
                <input class="form-control" type="email" name="email" value="<?= $walker->email ?>" placeholder="Email" readonly="true" >
            </div>

            <div class="form-group">
                <label>Phone</label>
                <input class="form-control" type="text" name="phone" value="<?= $walker->phone ?>" placeholder="Phone">
            </div>

            <div class="form-group">
                <label>Bio</label>
                <input class="form-control" type="text" name="bio" value="<?= $walker->bio ?>" placeholder="Bio">
            </div>


            <div class="form-group">
                <label>Address</label>
                <input class="form-control" type="text" name="address" value="<?= $walker->address ?>" placeholder="Address">
            </div>


            <div class="form-group">
                <label>State</label>
                <input class="form-control" type="text" name="state" value="<?= $walker->state ?>" placeholder="State">
            </div>


            <div class="form-group">
                <label>Country</label>
                <input class="form-control" type="text" name="country" value="<?= $walker->country ?>" placeholder="Country">
            </div>

            <div class="form-group">
                <label>Zip Code</label>
                <input class="form-control" type="text" name="zipcode" value="<?= $walker->zipcode ?>" placeholder="Zip Code">
            </div>

            <div class="form-group">
                <label>Car Number</label>
                <input class="form-control" type="text" name="car_number" value="<?= $walker->car_number ?>" placeholder="Car Number">
            </div>

            <div class="form-group">
                <label>Car Model</label>
                <input class="form-control" type="text" name="car_model" value="<?= $walker->car_model ?>" placeholder="Car Model">
            </div>

            <div class="form-group">
                <label>Company</label>
                <select class="new_driver_company" name="company_name" id="company_name">
                    <option value="" style="width:90%;">Select a company</option>
                    <?php foreach ($all_transportation_provider as $transporters){?>
                            <option value="<?php echo $transporters->id ?>"
                            <?php if($transporters->id==$walker->transportation_provider_id){ ?> selected ="selected" <?php } ?>><?php echo $transporters->company ?></option>
                <?php } ?>
                </select>
            </div>

            <div class="form-group">
                <label>Picture</label>
                <input class="form-control" type="file" name="pic" >
                <br>
                <img src="<?= $walker->picture; ?>" height="50" width="50"><br>
                <p class="help-block">Please Upload image in jpg, png format.</p>
            </div>
            <div class="form-group">
                <label>Is Currently Providing : </label>
                <?php
                $walk = DB::table('walk')
                        ->select('id')
                        ->where('walk.is_started', 1)
                        ->where('walk.is_completed', 0)
                        ->where('walker_id', $walker->id);
                $count = $walk->count();
                if ($count > 0) {
                    echo "Yes";
                } else {
                    echo "No";
                }
                ?>
            </div>
            <div class="form-group">
                <label>Is Provider Active : </label>
                <?php
                $walk = DB::table('walker')
                        ->select('id')
                        ->where('walker.is_active', 1)
                        ->where('walker.id', $walker->id);
                $count = $walk->count();
                if ($count > 0) {
                    echo "Yes";
                } else {
                    echo "No";
                }
                ?>
            </div>
            <div class="form-group">
            <label>Service Type</label>
                <span class=" bg_arrow">
                    <select class="form-control" name="services" id="services">
                        <?php foreach ($type as $service){?>
                                    <option value="<?php echo $service->id ?>" <?php if($walker->type==$service->id) { ?> selected ="selected" <?php } ?>><?php echo $service->name ?></option>
                        <?php } ?>
                    </select>
                </span>
            </div>
        </div><!-- /.box-body -->

        <div class="box-footer">

            <button type="submit" class="btn btn-primary btn-flat btn-block">Update Changes</button>
        </div>
    </form>
</div>



<?php if ($success == 1) { ?>
    <script type="text/javascript">
            alert(''.Config::get('app.generic_keywords.Provider').' Profile Updated Successfully');</script>
<?php } ?>
<?php if ($success == 2) { ?>
    <script type="text/javascript">
                alert('Sorry Something went Wrong');
    </script>
<?php } ?>

<script type="text/javascript">
    $("#main-form").validate({
        rules: {
            contact_name: "required",
            company_name: "required",
            email: {
                required: true,
                email: true
            },
            phone: {
                required: true,
                digits: true
            }
        }
    });
</script>
@stop