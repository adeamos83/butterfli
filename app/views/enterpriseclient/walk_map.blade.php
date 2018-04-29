@extends('enterpriseclient.layout')
@section('content')
    <script src="<?php echo asset_url(); ?>/web/js/jstz.min.js"></script>
<script src="https://momentjs.com/downloads/moment.min.js"></script>
<script src="https://momentjs.com/downloads/moment-timezone-with-data.min.js"></script> 

<div class="box box-success">
    <div id="map" style="height:600px;width:100%;"></div>
</div>
<div class="box box-info tbl-box">
	<div class="row white_bg">
    <table class="table table-bordered">
        <tbody>
            <tr>
                <th>Request ID</th>
                <th>{{ trans('customize.Provider') }} Name</th>
                <th>{{ trans('customize.User') }} Name</th>
                <th>Date/Time</th>
                <th>Status</th>
               <th>Response</th>
            </tr>
            <?php $i = 0; ?>
                <tr>
                    <td><?= $walk_id ?></td>
                    <td><?php
                        if ($full_walk->driver_name) {
                            echo $full_walk->driver_name;
                        } else {
                            echo "Un Assigned";
                        }
                        ?></td>
                    <td><?= $owner_name ?></td>
                    <td id= 'time<?php echo $i; ?>' >
                        <script>
							var tz = jstz.determine();
							//alert(tz.name());
							var timevar = moment.utc("<?php echo $start_time; ?>");
							var format = 'MMMM Do YYYY, h:mm:ss a';
							var datetime = moment(timevar).tz(tz.name()).format(format);
							document.getElementById("time<?php echo $i; ?>").innerHTML = datetime;
							<?php $i++; ?>
                        </script>
					</td>
                    <td><?= $status1 ?></td>
                   
                    <td>
                        <?php
                        if ($full_walk->status == 0) {
                            echo "<span class='badge bg-yellow'>In Queue</span>";
                        } elseif ($full_walk->status == 1) {
                            echo "<span class='badge bg-green'>Accepted</span>";
                        } elseif ($full_walk->status == 3) {
                            echo "<span class='badge bg-red'>Rejected</span>";
                        } else {
                            echo "<span class='badge bg-red'>No Response</span>";
                        }
                        ?>
                    </td>
                </tr>
        </tbody>
    </table>
    </div>
</div>
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB6NA_AMOEp8CiAxEZPWf_PTyy4v6xKvdA&libraries=places"></script>
<script type="text/javascript">

        var map = null;
        var infowindow = new google.maps.InfoWindow();
        var bounds = new google.maps.LatLngBounds();
        var customIcons = {
			restaurant: {
			icon: 'https://labs.google.com/ridefinder/images/mm_20_blue.png',
				  shadow: 'https://labs.google.com/ridefinder/images/mm_20_shadow.png'
			},
			bar: {
			icon: 'https://labs.google.com/ridefinder/images/mm_20_red.png',
				  shadow: 'https://labs.google.com/ridefinder/images/mm_20_shadow.png'
			},
			client: {
			icon: '<?php echo asset_url(); ?>/image/start_pin_flag.png',
					shadow: 'https://labs.google.com/ridefinder/images/mm_20_shadow.png'
			},
			client_stop: {
			icon: '<?php echo asset_url(); ?>/image/end_pin_flag.png',
					shadow: 'https://labs.google.com/ridefinder/images/mm_20_shadow.png'
			},
			driver: {
			icon: '<?php echo asset_url(); ?>/image/driver-70.png',
					shadow: 'https://labs.google.com/ridefinder/images/mm_20_shadow.png'
			}
        };
        var markers1 = [
        {
			"lat": <?php echo $pickup_latitude; ?>,
            "lng": <?php echo $pickup_longitude;?>,
        },
        {
            "lat": <?php echo $dropoff_latitude; ?>,
            "lng": <?php echo $dropoff_longitude; ?>,
        }
        ];
        function load() {
			var mapOptions = {
			center: new google.maps.LatLng(
				parseFloat(markers1[0].lat),
				parseFloat(markers1[0].lng)),
				zoom: 15,
				mapTypeId: google.maps.MapTypeId.ROADMAP
			};
			var path = new google.maps.MVCArray();
			var service = new google.maps.DirectionsService();
			var infoWindow = new google.maps.InfoWindow();
			map = new google.maps.Map(document.getElementById("map"), mapOptions);
			var poly = new google.maps.Polyline({
			map: map,
					strokeColor: '#F3443C'
			});
			var lat_lng = new Array();
			/* path.push(new google.maps.LatLng(parseFloat(markers1[0].lat),
			 parseFloat(markers1[0].lng)));
			 */
			var start_icon = customIcons['client'] || {};
			var stop_icon = customIcons['client_stop'] || {};
			var marker = new google.maps.Marker({
				position: map.getCenter(),
					map: map,
					icon: start_icon.icon,
					shadow: start_icon.shadow,
					draggable: false
			});
			bounds.extend(marker.getPosition());
			google.maps.event.addListener(marker, "click", function () {
			infowindow.setContent("<p><b>User </b><br/>Walk ID : <?php echo $walk_id; ?><br/>Name :  <?php echo $owner_name; ?><br/>Phone :  <?php echo $owner_phone; ?><br/>Status :  <span style='color:red'><?php echo $status; ?></span></p>");
					infowindow.open(map, marker);
			});
            for (var i = 0; i < markers1.length; i++) {
			if ((i + 1) < markers1.length) {
				//alert(parseFloat(markers1[i].lat));
				//alert(parseFloat(markers1[i].lng));
				var src = new google.maps.LatLng(parseFloat(markers1[i].lat),
                parseFloat(markers1[i].lng));
                var smarker = new google.maps.Marker({position: src, draggable: false, icon: start_icon.icon, shadow: start_icon.shadow});
                bounds.extend(smarker.getPosition());
                google.maps.event.addListener(smarker, "click", function () {
                infowindow.setContent("<p><b>User </b><br/>Walk ID : <?php echo $walk_id; ?><br/>Name :  <?php echo $owner_name; ?><br/>Phone :  <?php echo $owner_phone; ?><br/>Status :  <span style='color:red'><?php echo $status; ?></span></p>");
                        infowindow.open(map, smarker);
                });
				//alert(parseFloat(markers1[i + 1].lat));
				//alert(parseFloat(markers1[i + 1].lng));
                var des = new google.maps.LatLng(parseFloat(markers1[i + 1].lat),
                        parseFloat(markers1[i + 1].lng));
                var dmarker = new google.maps.Marker({position: des, map: map, draggable: false, icon: stop_icon.icon, shadow: stop_icon.shadow});
                bounds.extend(dmarker.getPosition());
                google.maps.event.addListener(dmarker, "click", function () {
                infowindow.setContent("<p><b>Provider </b><br/>Walk ID :  <?php echo $walk_id; ?><br/>Name :  <?php echo $walker_name; ?><br/>Phone :  <?php echo $walker_phone; ?><br/>Status :  <span style='color:red'><?php echo $status; ?></span></p>");
                        infowindow.open(map, dmarker);
                });
                map.fitBounds(bounds);
                //  poly.setPath(path);
            }
        }
        var legendDiv = document.createElement('DIV');
                var legend = new Legend(legendDiv, map);
                legendDiv.index = 1;
                map.controls[google.maps.ControlPosition.RIGHT_TOP].push(legendDiv);
        }
function Legend(controlDiv, map) {
// Set CSS styles for the DIV containing the control
// Setting padding to 5 px will offset the control
// from the edge of the map
controlDiv.style.padding = '5px';
        // Set CSS for the control border
        var controlUI = document.createElement('DIV');
        controlUI.style.backgroundColor = 'white';
        controlUI.style.borderStyle = 'solid';
        controlUI.style.borderWidth = '1px';
        controlUI.title = 'Legend';
        controlDiv.appendChild(controlUI);
        // Set CSS for the control text
        var controlText = document.createElement('DIV');
        controlText.style.fontFamily = 'Arial,sans-serif';
        controlText.style.fontSize = '12px';
        controlText.style.paddingLeft = '4px';
        controlText.style.paddingRight = '4px';
        // Add the text
        controlText.innerHTML = '<b>Legends</b><br />' +
        '<img src="<?php echo asset_url(); ?>/image/start_pin_flag.png" style="height:25px;"/>Pickup Address<br />' +
        '<img src="<?php echo asset_url(); ?>/image/end_pin_flag.png" style="height:25px;"/>Dropoff Address<br />';
        controlUI.appendChild(controlText);
        }
google.maps.event.addDomListener(window, 'load', load);


</script>

@stop
