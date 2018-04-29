@extends('onwlayout.layout_html')
@section('content')

<style>
    .total{
        font-size: 45px;
        font: bold;

    }
    .heading{
        background-color: #2866A1;
        color: #ffffff;
    }
    .even{
        background-color: #f2f2f4;
    }
    .hr{
        margin: 7px 0;
        border: 0;
        border-top: 2px solid rgb(181, 181, 181);
        border-bottom: 0;
    }
    .headother{
        font-size: 15px;
        font: bold;
        width: 200px;
    }

    table{border: 0px}

    .table>tbody>tr>td, .table>tfoot>tr>td {
        padding: 8px;
        line-height: 1.42857143;
        vertical-align: top;
        border-top: 0px solid #ddd;
    }

    tr:nth-child(even) {background: #f2f2f4;}
    tr:nth-child(odd) {background: #ffffff;}

</style>
<div class="page-content">

    <div class="row col-md-12">
        <div class="col-md-6">
            <?php
            /* $settings = Settings::where('key', 'rider_fee')->first();
              $unit = $settings->value;
              $curentdate = Config::get('app.datenow');

              $owner = Owneradmin::where('id', Session::get('partner_id'))->first();

              echo "<b><h2> " . $owner->Firstname . ": Payment Statement</h2></b>"; */
            $unit = 1;
            ?> 

        </div>
        <div class="col-md-6 align-right right_side">
            <h2>{{Config::get('app.website_title')}} </h2>
        </div>
    </div>

    <br>
    <?php
    $totalweek = weektotal($walks);
    $payment_remaining_total = payment_remaining_total($walks);
    $refund_remaining_total = refund_remaining_total($walks);


    $ridercut = $payment_remaining_total - $refund_remaining_total;
    $pay = str_replace('.', '.', $totalpayout = number_format((float) $totalweek, 2));
    if ($pay < 0) {
        $color = "#FF0000";
    } else {
        $color = "#2866A1";
    }
    ?>
    <div class="row col-md-12">
        <div class="col-md-6">
            <div class="col-md-4">TOTAL PAYOUT<br>
                <p class="total">  
                    <?php
                    $totalweek = abs($pay);
                    echo "<span style='color: $color;'>" . Config::get('app.currency') . $totalweek . "</span>";
                    ?>
                </p>
            </div>

        </div>
    </div>
    <br>
    <div class="row col-md-12">
        <br>
        <div class="col-md-6">
            Period Ending: <?php echo date('F d,Y', strtotime(Input::get('weekend'))); ?>
        </div>
    </div>

    <div class="row col-md-12 heading" style="margin-left: 0;">
        <div class="col-md-6">

            DRIVER EARNINGS
        </div>
    </div>
    <table class="table">
        <tbody>
            <tr>
                <td><b>Drivers</b></td>
                <td><b>Fare</b></td>
                <td><b>{{Config::get('app.website_title')}} Fee</b></td>
                <td><b>Earnings</b></td>
            </tr>


            <?php
            $i = 0;
            $total_fare = 0;
            $rider_fee = 0;
            $total_earning = 0;
            foreach ($walkers as $walker) {
                ?><tr>
                    <td></td>
                    <td>
                        <a href="<?php echo $_SERVER['REQUEST_URI'] . '#' . $walker->id; ?>"><?php echo $walker->contact_name; ?></a>
                    </td>
                    <td><?php
                        $toal_payment = sprintf2(($walker->card_pay), 2);

                        echo formated_value($toal_payment);
                        ?>
                    </td>
                    <td><?php
                        $riderfee = sprintf2(($walker->refund_remaining), 2);
                        $riderfee = sprintf2(($walker->card_pay - $totalweek), 2);
                        echo formated_value($riderfee);
                        ?>
                    </td>
                    <td><?php
                        $payment_derive = sprintf2(($toal_payment - $riderfee), 2);
                        echo formated_value($payment_derive);
                        ?>
                    </td>
                    <td></td>
                </tr>

                <?php
                $total_fare = $total_fare + $toal_payment;
                $rider_fee = $rider_fee + $riderfee;
                $total_earning = $total_earning + $payment_derive;
            }
            ?>

            <tr>
                <td></td>
                <td> Total</td>
                <td><?php echo Config::get('app.currency') . formated_value($total_fare); ?></td>
                <td><?php echo Config::get('app.currency') . formated_value($rider_fee); ?> </td>
                <td><?php echo Config::get('app.currency') . formated_value($total_earning); ?> </td>
                <td></td>
            </tr>


        </tbody>

    </table>

    <div class="row col-md-12">
        <hr class="hr">
    </div>




    <div class="box box-info tbl-box">


        <?php
        $drivername = '';
        foreach ($walkers as $walker) {

            if ($walker->total_requests != 0) {
                ?>



                <?php
                $weektotal = 0;
                foreach ($walks as $walk) {
                    if ($walker->id == $walk->confirmed_walker) {
                        $weektotal = $weektotal + $walk->card_payment;
                    }
                }
                ?>

                <table class="table">
                    <tbody>
                        <?php
                        $date = 0;
                        $datewisetotal = 0;
                        $recorddis = 0;
                        $daypayout = 0;
                        foreach ($walks as $walk) {
                            if ($walker->id == $walk->confirmed_walker) {
                                if ($date == 0) {
                                    $recorddis = 1;
                                    $date = date('Y-M-d', strtotime($walk->date));
                                    ?>
                                    <?php
                                }
                                if ($date > date('Y-m-d', strtotime($walk->date))) {
                                    $date = date('Y-m-d', strtotime($walk->date));
                                    $datedis = date('F d,  Y', strtotime($walk->date));
                                    $daypatyout = patyoutday($walks, $date, $walker->id)
                                    ?>
                                    <tr style="background-color: #ffffff;">

                                        <?php
                                        if ($drivername != $walk->walker_contact_name) {
                                            ?>
                                            <td colspan="7" style="text-align:left ">



                                            </td>

                                            <?php
                                        } else {
                                            ?>
                                    <hr class='hr'>
                                    <?php
                                    echo '<td colspan="4"><span class="headother"><b>' . strtoupper($datedis) . ' | </b></span></td>';



                                    echo '<td colspan="3"><span class="align right_side"><b>' . Config::get('app.currency') . $daypatyout . '<b></span></td>';
                                    echo "<br>";
                                }
                                ?>




                                </tr>

                                <tr><td colspan="7" style="text-align: left">

                                        <?php
                                        if ($drivername != $walk->walker_contact_name) {
                                            ?>

                                            <?php echo "<b><h2> " . $walk->walker_contact_name . ": Payment Statement</h2></b>";
                                            ?>
                                            <br>
                                            <?php
                                            $weektotal = driverweek($walks, $walker->id);
                                            $payment_remaining_total = payment_remaining_total($walks);
                                            $refund_remaining_total = refund_remaining_total($walks);


                                            $ridercut = $payment_remaining_total - $refund_remaining_total;
                                            $pay1 = $totalpayout = formated_value($weektotal);
                                            if ($pay1 < 0) {
                                                $color1 = "#FF0000";
                                            } else {
                                                $color1 = "#2866A1";
                                            }
                                        }
                                        ?>
                                        TOTAL PAYOUT
                                        <br>
                                        <?php echo "<span style='color: $color;'>" . Config::get('app.currency') . abs($pay1) . "</span>"; ?>


                                    </td>
                                </tr>

                                <?php
                                if ($drivername != $walk->walker_contact_name) {
                                    ?>
                                    <tr>
                                        <th><b style="color: #000033;">Date/Time</b></th>
                                        <th><b style="color: #000033;">Trip ID</b></th>
                                        <th><b style="color: #000033;">Type</b></th>

                                        <th><b style="color: #000033;">Payment Type</b></th>
                                        <th><b style="color: #000033;">Fare</b></th>
                                        <th><b style="color: #000033;">{{Config::get('app.website_title')}} Fee</b></th>
                                        <th><b style="color: #000033;">Earnings</b></th>

                                    </tr>
                                    <?php
                                    $weektotal = driverweek($walks, $walker->id);
                                    $payment_remaining_total = payment_remaining_total($walks);
                                    $refund_remaining_total = refund_remaining_total($walks);
                                    ?>
                                    <tr><td><b>WEEK TOTALS</b></td>
                                        <td></td>
                                        <td></td><td></td>
                                        <td style="text-align: right"><?php echo "<b>" . Config::get('app.currency') . formated_value($weektotal) . "</b>"; ?></td>
                                        <td style="text-align: right"><?php echo "<b>(" . Config::get('app.currency') . formated_value($weektotal - $payment_remaining_total - $refund_remaining_total) . ")</b>" ?></td>
                                        <td style="text-align: right"><?php echo "<b>" . Config::get('app.currency') . formated_value($payment_remaining_total - $refund_remaining_total) . "</b>"; ?></td>

                                    </tr>
                                    <tr>
                                        <td colspan="7" style="background-color: #ffffff;">
                                            <hr class="hr">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="7" style="text-align: left;background-color: #ffffff;">
                                            <?php
                                            //

                                            echo '<span class="headother"><b>' . strtoupper($datedis) . '</b></span>';

                                            echo '<span class="align right_side"><b>' . Config::get('app.currency') . $daypatyout . '</b></span>';
                                            ?>
                                        </td>
                                    </tr>
                                    <?php
                                    $drivername = $walk->walker_contact_name;
                                }
                                ?>
                                <?php
                                $daypayout = 0;
                                $daypayout +=$walk->card_payment;
                            } else {
                                $daypayout +=$walk->card_payment;
                            }
                            ?>


                            <tr>
                                <td><?php echo date('h:i A', strtotime($walk->request_start_time)); ?> </td>
                                <td><?= $walk->id ?></td>
                                <td><?php
                                    $walker_type = DB::table('walker_type')->where('id', '=', $walk->type)->first();

                                    echo $walker_type->name
                                    ?></td>





                                <td><?php
                                    if ($walk->cash_or_card) {
                                        echo "Cash";
                                    } else {
                                        echo "Credit Card";
                                    }
                                    ?></td>
                                <td>
                                    <?php echo Config::get('app.currency') . sprintf2(($walk->card_payment), 2); ?>
                                </td>
                                <td>
                                    <?php echo '(' . Config::get('app.currency') . sprintf2(($walk->card_payment - $walk->payment_remaining - $walk->refund_remaining), 2) . ')'; ?>
                                </td>
                                <td>
                                    <?php echo Config::get('app.currency') . sprintf2(($walk->payment_remaining - $walk->refund_remaining), 2); ?>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                        <?php
                    }
                    ?>  </tbody>
                </table>
                <?php
            }
        }
        ?>
    </div>
</div>


@stop