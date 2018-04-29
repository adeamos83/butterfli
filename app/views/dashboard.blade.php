@extends('layout')

@section('content')
<!--   summary start -->


<div class="row">
    <div class="col-lg-3 col-xs-5">
        <!-- small box -->
        <div class="small-box bg-aqua">
            <div class="inner">
                <h3>
                    <?= $completed_rides + $cancelled_rides + $driver_assigned_rides ?>
                </h3>
                <p>
                    Total Rides
                </p>
            </div>
            <div class="icon">
                <?php /* $icon = Keywords::where('keyword', 'total_trip')->first(); */ ?>
                <i class="fa"><?php
                    /* $show = Icons::find($icon->alias); */
                    $show = Icons::find(Config::get('app.generic_keywords.total_trip'));
                    echo $show->icon_code;
                    ?></i>
            </div>

        </div>
    </div><!-- ./col -->
    <div class="col-lg-3 col-xs-5">
        <!-- small box -->
        <div class="small-box bg-green">
            <div class="inner">
                <h3>
                    <?= $completed_rides ?>
                </h3>
                <p>
                    Completed Rides
                </p>
            </div>
            <div class="icon">
                <?php /* $icon = Keywords::where('keyword', 'completed_trip')->first(); */ ?>
                <i class="fa"><?php
                    /* $show = Icons::find($icon->alias); */
                    $show = Icons::find(Config::get('app.generic_keywords.completed_trip'));
                    echo $show->icon_code;
                    ?></i>
            </div>

        </div>
    </div><!-- ./col -->
    <div class="col-lg-3 col-xs-5">
        <!-- small box -->
        <div class="small-box bg-red">
            <div class="inner">
                <h3>
                    <?= $cancelled_rides ?>
                </h3>
                <p>
                    Cancelled Rides
                </p>
            </div>
            <div class="icon">
                <?php /* $icon = Keywords::where('keyword', 'cancelled_trip')->first(); */ ?>
                <i class="fa"><?php
                    /* $show = Icons::find($icon->alias); */
                    $show = Icons::find(Config::get('app.generic_keywords.cancelled_trip'));
                    echo $show->icon_code;
                    ?></i>
            </div>

        </div>
    </div><!-- ./col -->
    <div class="col-lg-3 col-xs-5">
        <!-- small box -->
        <div class="small-box bg-maroon">
            <div class="inner">
                <h3>
                    <?= $driver_assigned_rides ?>
                </h3>
                <p>
                    Driver Assigned Rides
                </p>
            </div>
            <div class="icon">
                <?php /* $icon = Keywords::where('keyword', 'credit_payment')->first(); */ ?>
                <i class="fa"><?php
                    /* $show = Icons::find($icon->alias); */
                    $show = Icons::find(Config::get('app.generic_keywords.credit_payment'));
                    echo $show->icon_code;
                    ?></i>
            </div>

        </div>
    </div>
    <div class="col-lg-3 col-xs-5">
        <!-- small box -->
        <div class="small-box bg-purple">
            <div class="inner">
                <h3>
                    <?= $currency_sel ?> <?= sprintf2(($total_payment), 2) ?>
                </h3>
                <p>
                    Total Butterfli Made
                </p>
            </div>
            <div class="icon">
                <?php /* $icon = Keywords::where('keyword', 'total_payment')->first(); */ ?>
                <i class="fa"><?php
                    /* $show = Icons::find($icon->alias); */
                    $show = Icons::find(Config::get('app.generic_keywords.total_payment'));
                    echo $show->icon_code;
                    ?></i>
            </div>

        </div>
    </div><!-- ./col -->
    <div class="col-lg-3 col-xs-5">
        <!-- small box -->
        <div class="small-box bg-yellow">
            <div class="inner">
                <h3>
                    <?= $currency_sel ?>
                </h3>
                <p>
                    Total TP's Owed
                </p>
            </div>
            <div class="icon">
                <?php /* $icon = Keywords::where('keyword', 'card_payment')->first(); */ ?>
                <i class="fa"><?php
                    /* $show = Icons::find($icon->alias); */
                    $show = Icons::find(Config::get('app.generic_keywords.card_payment'));
                    echo $show->icon_code;
                    ?></i>
            </div>

        </div>
    </div><!-- ./col -->
    <!-- ./col -->
    <div class="col-lg-3 col-xs-5">
        <!-- small box -->
        <div class="small-box bg-aqua">
            <div class="inner">
                <h3>
                    <?= $wheelchair_rides ?>
                </h3>
                <p>
                    WheelChair Rides
                </p>
            </div>
            <div class="icon">
                <?php /* $icon = Keywords::where('keyword', 'total_trip')->first(); */ ?>
                <i class="fa"><?php
                    /* $show = Icons::find($icon->alias); */
                    $show = Icons::find(Config::get('app.generic_keywords.cash_payment'));
                    echo $show->icon_code;
                    ?></i>
            </div>

        </div>
    </div><!-- ./col -->
    <div class="col-lg-3 col-xs-5">
        <!-- small box -->
        <div class="small-box bg-green">
            <div class="inner">
                <h3>
                    <?= $ambulatory_rides ?>
                </h3>
                <p>
                    Ambulatory Rides
                </p>
            </div>
            <div class="icon">
                <?php /* $icon = Keywords::where('keyword', 'completed_trip')->first(); */ ?>
                <i class="fa"><?php
                    /* $show = Icons::find($icon->alias); */
                    $show = Icons::find(Config::get('app.generic_keywords.promotional_payment'));
                    echo $show->icon_code;
                    ?></i>
            </div>

        </div>
    </div><!-- ./col -->
    <div class="col-lg-3 col-xs-5">
        <!-- small box -->
        <div class="small-box bg-red">
            <div class="inner">
                <h3>
                    <?= $gurney_rides ?>
                </h3>
                <p>
                   Gurney Rides
                </p>
            </div>
            <div class="icon">
                <?php /* $icon = Keywords::where('keyword', 'completed_trip')->first(); */ ?>
                <i class="fa"><?php
                    /* $show = Icons::find($icon->alias); */
                    $show = Icons::find(Config::get('app.generic_keywords.schedules_icon'));
                    echo $show->icon_code;
                    ?></i>
            </div>
        </div>
    </div><!-- ./col -->
</div>



<!--  Summary end -->



<!-- filter start -->

<div class="box box-danger">
    <div class="box-header">
        <h3 class="box-title">Filter</h3>
    </div>
    <div class="box-body">
        <div class="row">

            <form role="form" method="get" action="{{ URL::Route('AdminReport') }}">

                <div class="col-md-6 col-sm-6 col-lg-6">
                    <input type="text" class="form-control" style="overflow:hidden;" id="start-date" name="start_date" value="{{ Input::get('start_date') }}" placeholder="Start Date">
                    <br>
                </div>

                <div class="col-md-6 col-sm-6 col-lg-6">
                    <input type="text" class="form-control" style="overflow:hidden;" id="end-date" name="end_date" placeholder="End Date"  value="{{ Input::get('end_date') }}">
                    <br>
                </div>

                <div class="col-md-4 col-sm-4 col-lg-4">

                    <select name="status"  class="form-control">
                        <option value="0">Status</option>
                        <option value="1" <?php echo Input::get('status') == 1 ? "selected" : "" ?> >Completed</option>
                        <option value="2" <?php echo Input::get('status') == 2 ? "selected" : "" ?>>Cancelled</option>
                    </select>
                    <br>
                </div>

                <div class="col-md-4 col-sm-4 col-lg-4">

                    <select name="walker_id" style="overflow:hidden;" class="form-control">
                        <option value="0">Driver</option>
                        <?php foreach ($walkers as $walker) { ?>
                            <option value="<?= $walker->id ?>" <?php echo Input::get('walker_id') == $walker->id ? "selected" : "" ?>><?= $walker->contact_name; ?></option>
                        <?php } ?>
                    </select>
                    <br>
                </div>

                <div class="col-md-4 col-sm-4 col-lg-4">

                    <select name="hospital_provider_id" style="overflow:hidden;" class="form-control">
                        <option value="0">Hospital Providers</option>
                        <?php foreach ($hospital_providers as $hospital_provider) { ?>
                            <option value="<?= $hospital_provider->id ?>" <?php echo Input::get('hospital_provider_id') == $hospital_provider->id ? "selected" : "" ?>><?= $hospital_provider->provider_name; ?></option>
                        <?php } ?>
                    </select>
                    <br>
                </div>


        </div>
    </div><!-- /.box-body -->
    <div class="box-footer">
        <button type="submit" name="submit" class="btn btn-primary" value="Filter_Data">Filter Data</button>
        <button type="submit" name="submit" class="btn btn-primary" value="Download_Report">Download Report</button>
    </div>

</form>

</div>

<!-- filter end-->




<div class="box box-info tbl-box">
    <div align="left" id="paglink"><?php echo $walks->appends(array('start_date' => Session::get('start_date'), 'end_date' => Session::get('end_date'), 'walker_id' => Session::get('walker_id'), 'hospital_provider_id' => Session::get('hospital_provider_id'), 'status' => Session::get('status'), 'submit' => Session::get('submit')))->links(); ?></div>
    <table class="table table-bordered">
        <tbody><tr>
                <th>Request ID</th>
                <th>Hospital Providers</th>
                <th>{{ trans('customize.User');}} Name</th>
                <th>{{ trans('customize.Provider');}}</th>
                <th>Type of Service</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
                <th>Amount</th>
                <th>Payment Status</th>
                <!--th>Referral Bonus</th>
                <th>Promotional Bonus</th>
                <th>Card Payment</th>
                <th>Cash Payment</th-->
            </tr>


            <?php foreach ($walks as $walk) { ?>

                <tr>
                    <td><?= $walk->id ?></td>
                    <td>
                        <?php
                        if ($walk->provider_name) {
                            echo $walk->provider_name;
                        } else {
                            echo "NA";
                        }
                        ?>
                    </td>
                    <td><?php 
						if ($walk->owner_contact_name!='') {
							echo $walk->owner_contact_name;
                        }else {
                            echo $walk->contact_name;
                        }
						?></td>
                    <td>
                        <?php
                        if ($walk->confirmed_walker) {
                            echo $walk->walker_contact_name;
                        } else {
                            echo "Un Assigned";
                        }
                        ?>
                    </td>
                    <td><?= $walk->type ?></td>
                    <td><?php echo date("d M Y", strtotime($walk->date)); ?></td>
                    <td><?php echo date("g:iA", strtotime($walk->date)); ?></td>

                    <td>
                        <?php
                        if ($walk->is_cancelled == 1) {

                            echo "<span class='badge bg-red'>Cancelled</span>";
                        } elseif ($walk->is_completed == 1) {
                            echo "<span class='badge bg-green'>Completed</span>";
                        } elseif ($walk->is_started == 1) {
                            echo "<span class='badge bg-yellow'>Started</span>";
                        } elseif ($walk->is_walker_arrived == 1) {
                            echo "<span class='badge bg-yellow'>" . Config::get('app.generic_keywords.Provider') . " Arrived</span>";
                        } elseif ($walk->is_walker_started == 1) {
                            echo "<span class='badge bg-yellow'>" . Config::get('app.generic_keywords.Provider') . " Started</span>";
                        } else {
                            
                        }
                        ?>
                    </td>
                    <td>
                        <?php echo $currency_sel. sprintf2($walk->total, 2); ?>
                    </td>
                    <td>
                        <?php
                        if ($walk->is_paid == 1) {

                            echo "<span class='badge bg-green'>Completed</span>";
                        } elseif ($walk->is_paid == 0 && $walk->is_completed == 1) {
                            echo "<span class='badge bg-red'>Pending</span>";
                        } else {
                            echo "<span class='badge bg-yellow'>Request Not Completed</span>";
                        }
                        ?>
                    </td>
                    <!--td>
                        <?= sprintf2($walk->ledger_payment, 2); ?>
                    </td>
                    <td>
                        <?= sprintf2($walk->promo_payment, 2); ?>
                    </td>
                    <?php if ($walk->payment_mode == 1) { ?>
                        <td>
                            <?= sprintf2(0, 2); ?>
                        </td>
                    <?php } else { ?>
                        <td>
                            <?= sprintf2($walk->card_payment, 2); ?>
                        </td>
                        <?php
                    }
                    if ($walk->payment_mode == 1) {
                        ?>
                        <td>
                            <?= sprintf2($walk->card_payment, 2); ?>
                        </td>
                    <?php } else { ?>
                        <td>
                            <?= sprintf2(0, 2); ?>
                        </td-->
                    <?php } ?>
                </tr>
            <?php } ?>

        </tbody>
    </table>
    <?php echo Session::get('start_date'); ?>
    <div align="left" id="paglink"><?php echo $walks->appends(array('start_date' => Session::get('start_date'), 'end_date' => Session::get('end_date'), 'walker_id' => Session::get('walker_id'), 'hospital_provider_id' => Session::get('hospital_provider_id'), 'status' => Session::get('status'), 'submit' => Session::get('submit')))->links(); ?></div>
</div>
<!--</form>-->
</div>
</div>
</div>

<script>
    $(function () {
        $("#start-date").datepicker({
            defaultDate: "+1w",
            changeMonth: true,
            numberOfMonths: 1,
            onClose: function (selectedDate) {
                $("#end-date").datepicker("option", "minDate", selectedDate);
            }
        });
        $("#end-date").datepicker({
            defaultDate: "+1w",
            changeMonth: true,
            numberOfMonths: 1,
            onClose: function (selectedDate) {
                $("#start-date").datepicker("option", "maxDate", selectedDate);
            }
        });
    });
</script>
<script type="text/javascript">
    $(document).ready(function () {
        $("#myModal").modal('show');
    });
</script>

@stop