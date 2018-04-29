@extends('web.providerLayout')

@section('content')

<div class="col-md-12 mt" style="position:relative;top:25px;" >
    <div class="content-panel">
        <table class="table table-hover" id="trip-table">
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Trips</th>
                    <th>Total</th>
                    <th>Week Ending On</th>
                    <th>Status</th>
                    <th>Download</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $i = 0;
                $startno;
                $end;
                foreach ($walks as $walk) {

                    if ($i == 0) {
                        $startno = 0;
                        $end = $walk->id;
                        $startdate = 0;
                        $enddate = $walk->created_at;
                    }
                    if ($i != 0) {

                        $startno = $end - 1;
                        $end = $walk->id;

                        $startdate = strtotime($enddate);
                        $startdate = strtotime("+1 day", $startdate);
                        $startdate = date('Y-m-d H:i:s', $startdate);
                        $enddate = $walk->created_at;
                    }

                    if ($i == 1) {
                        $end = $walk->id;
                        $enddate = $walk->created_at;
                    }
                    ?>
                    <tr>

                        <td><?= $walk->id ?> </td>
                        <td><?= $walk->trips ?> </td>
                        <td><?= Config::get('app.currency') . sprintf2(($walk->total),2) ?></td>
                        <td><?php
                            $formate = 'Y-m-d H:i:s';
                            $displaydate = Config::get('app.appdate');
                            // echo  $walk->created_at;
                            //echo  date('N', strtotime($walk->created_at));
                            if (date('N', strtotime($walk->created_at)) == 1) {
                                $dateweek = strtotime(date($formate, strtotime($walk->created_at)) . " +6 days");
                                echo $weekend = date($displaydate, $dateweek);
//                            echo "<br>";
//                          echo   date('l', $dateweek);
                            }


                            if (date('N', strtotime($walk->created_at)) == 2) {

                                $dateweek = strtotime(date($formate, strtotime($walk->created_at)) . " +5 days");
                                echo $weekend = date($displaydate, $dateweek);
//                            echo "<br>";
//                          echo   date('l', $dateweek);
                            } else if (date('N', strtotime($walk->created_at)) == 3) {
                                $dateweek = strtotime(date($formate, strtotime($walk->created_at)) . " +4 days");
                                echo $weekend = date($displaydate, $dateweek);
//                             echo "<br>";
//                          echo   date('l', $dateweek);
                            } else if (date('N', strtotime($walk->created_at)) == 4) {
                                $dateweek = strtotime(date($formate, strtotime($walk->created_at)) . " +3 days");
                                echo $weekend = date($displaydate, $dateweek);
//                             echo "<br>";
//                          echo   date('l', $dateweek);
                            } else if (date('N', strtotime($walk->created_at)) == 5) {
                                $dateweek = strtotime(date($formate, strtotime($walk->created_at)) . " +2 days");
                                echo $weekend = date($displaydate, $dateweek);
//                             echo "<br>";
//                          echo   date('l', $dateweek);
                            } else if (date('N', strtotime($walk->created_at)) == 6) {
                                $dateweek = strtotime(date($formate, strtotime($walk->created_at)) . " +1 days");
                                echo $weekend = date($displaydate, $dateweek);
//                             echo "<br>";
//                          echo   date('l', $dateweek);
                            } else if (date('N', strtotime($walk->created_at)) == 7) {

                                $dateweek = strtotime(date($formate, strtotime($walk->created_at)) . " +0 days");
                                echo $weekend = date($displaydate, $dateweek);
//                             echo "<br>";
//                          echo   date('l', $dateweek);
                            }
                            ?> </td>
                        <td>
                            <?php
                            $start = date("Y-m-d 00:00:00", strtotime('monday this week'));
                            $end_week = date("Y-m-d 23:59:59", strtotime("sunday this week"));

                            $curentdate = date('Y-m-d 11:59:59', strtotime($weekend));






                            if ($curentdate >= $start && $curentdate <= $end_week) {

                                echo $end_week . " <span class='badge bg-green'>In Progress</span>";
                            } else {
                                echo $end_week . " <span class='badge bg-blue'>Procesed</span>";
                            }
                            ?>


                        </td>


                        <td>

                            <a href="<?php echo web_url(); ?>/provider/providers_payout?<?php
                            echo 'start=' . $end;
                            echo '&end=' . $startno . '&startdate=' . $enddate . '&enddate=' . $enddate . '&weekend=' . $weekend;
                            ?> " target="_blanck">Html</a> | <a href="<?php echo web_url(); ?>/provider/providers_payout?<?php
                            echo 'start=' . $end;
                            echo '&end=' . $startno . '&startdate=' . $enddate . '&enddate=' . $enddate . '&submit1=Download Report' . '&weekend=' . $weekend;
                            ?>">Pdf</a>
                        </td>
                    </tr>
                            <?php
                            $i++;
                        }
                        ?>

            </tbody>
        </table>
    </div>
</div>

<script type="text/javascript">

    $(function () {
        $(".trip-basic").click(function () {
            var $this = $(this);
            var id = $(this).data('id');
            $this.next().toggle();
            $.ajax({url: id,
                type: 'get',
                success:
                        function (msg) {
                            if (msg === 'false') {
                                alert('No Data Found');
                            }
                            else {
                                $this.next().html(msg);
                            }
                        }
            });

        });


    });

</script>

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
    var tour = new Tour(
            {
                name: "providerappTrips",
            });

    // Add your steps. Not too many, you don't really want to get your users sleepy
    tour.addSteps([
        {
            element: "#flow21",
            title: "Setting Availability",
            content: "Click on profile to change your availability and other {{trans('customize.Provider')}} details",
        },
    ]);

    // Initialize the tour
    tour.init();

    // Start the tour
    tour.start();
</script>


@stop 