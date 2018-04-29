@extends('web.layout')
@section('content')
<div class="col-md-12 mt">
    @if(Session::has('message'))
    <!--<div class="alert alert-{{ Session::get('type') }}">
        <b>{{ Session::get('message') }}</b> 
    </div>-->
    @endif

    <div class="content-panel">
        <table class="table table-hover" id="trip-table">
            <thead>
                <tr>
                    <th>{{ trans('customize.Schedules'); }} Date</th>
                    <th>{{ trans('customize.Schedules'); }} Time</th>
                    <th>{{ trans('customize.User');}} Time-Zone</th>
                    <th>Source Address</th>
                    <th>Destination Address</th>
                    <th>Promotional Code</th>
                    <th>Payment Mode</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($requests as $request)
                <tr class="trip-basic" data-id="{{ route('/user/trip',$request->id)}}">
                    <td>{{ date("d M Y", strtotime($request->start_time)) }}</td>
                    <td>{{ date("g:iA", strtotime($request->start_time)) }}</td>
                    <td>{{ $request->time_zone }}</td>
                    <td>{{ $request->src_address }}</td>
                    <td>{{ $request->dest_address }}</td>
                    <td>
                        <?php
                        if ($request->promo_code == "" || $request->promo_code == NULL) {
                            echo "<span class='badge bg-red'>" . Config::get('app.blank_fiend_val') . "</span>";
                        } else {
                            echo $request->promo_code;
                        }
                        ?>
                    </td>
                    <td>
                        <?php
                        if ($request->payment_mode == 0) {
                            echo "<span class='badge bg-orange'>Stored Cards</span>";
                        } elseif ($request->payment_mode == 1) {
                            echo "<span class='badge bg-blue'>Pay by Cash</span>";
                        } elseif ($request->payment_mode == 2) {
                            echo "<span class='badge bg-purple'>Paypal</span>";
                        }
                        ?>
                    </td>
                    <td>
                        <a href="{{route('/user/deletescheduledtrips')."?id=". $request->id}}" class="btn btn-info">Delete</a>
                    </td>
                </tr>
                <tr class="trip-detail" style="display:none;">
                    <td colspan="4"><center>Loading...</center></td>
            </tr>
            @endforeach

            </tbody>
        </table>
    </div>
</div>

<!--script for this page-->
<script type="text/javascript">
    var tour = new Tour(
            {
                name: "userappHome",
            });

    // Add your steps. Not too many, you don't really want to get your users sleepy
    tour.addSteps([
        {
            element: "#flow1",
            title: "Requesting a {{trans('customize.Trip')}}",
            content: "Click here to request your first {{trans('customize.Trip')}}",
        }
    ]);

    // Initialize the tour
    tour.init();

    // Start the tour
    tour.start();
</script>

@stop 