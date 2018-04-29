@extends('driverlocation.layout')
@section('content')
    <script src="<?php echo asset_url(); ?>/web/js/jstz.min.js"></script>
	<script src="https://momentjs.com/downloads/moment.min.js"></script>
	<script src="https://momentjs.com/downloads/moment-timezone-with-data.min.js"></script>

	<div class="box box-success">
		<div id="map" style="height:600px;width:100%;"></div>
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
                icon: '<?php echo asset_url(); ?>/image/icon_van_bfli.png',
                shadow: 'https://labs.google.com/ridefinder/images/mm_20_shadow.png'
            }
        };
        var markers1 = [
            {
                "lat": <?php echo $pickup_latitude; ?>,
                "lng": <?php echo $pickup_longitude; ?>,
            },
            {
                "lat": <?php echo $dropoff_latitude; ?>,
                "lng": <?php echo $dropoff_longitude; ?>,
            }
        ];

        function getLocation(requestid,walkerid){

            var finalresult;
            if(requestid!='' && walkerid!=''){
                $.ajax({
                    type: "POST",
                    async:false,
                    url:'<?php echo URL::Route('getWalkerLocation') ?>',
                    data:{request_id:requestid,walker_id:walkerid},
                    success: function(data) {
                        console.log(data);
                        finalresult = data;
                        return finalresult;
                    }
                });
                //console.log(test);
                return finalresult;
            }
        }

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
                strokeColor: '#6a00bc'
            });
            var lat_lng = new Array();
            /* path.push(new google.maps.LatLng(parseFloat(markers1[0].lat),
             parseFloat(markers1[0].lng)));
             */
            var start_icon = customIcons['client'] || {};
            var stop_icon = customIcons['client_stop'] || {};
            var driver_icon = customIcons['driver'] || {};
            var marker = new google.maps.Marker({
                position: map.getCenter(),
                map: map,
                icon: start_icon.icon,
                shadow: start_icon.shadow,
                draggable: false
            });
            bounds.extend(marker.getPosition());
            google.maps.event.addListener(marker, "click", function () {
                infowindow.setContent("<p>Walk ID :  <?php echo $request_id; ?><br/>Pickup :  <?php echo $pickup_address; ?></p>");
                infowindow.open(map, marker);
            });
            for (var i = 0; i < markers1.length; i++) {
                if ((i + 1) < markers1.length) {
                    var src = new google.maps.LatLng(parseFloat(markers1[i].lat),parseFloat(markers1[i].lng));
                    var des = new google.maps.LatLng(parseFloat(markers1[i + 1].lat),parseFloat(markers1[i + 1].lng));
                    var dmarker = new google.maps.Marker({position: des, map: map, draggable: false, icon: stop_icon.icon, shadow: stop_icon.shadow});
                    bounds.extend(dmarker.getPosition());
                    google.maps.event.addListener(dmarker, "click", function () {
                        infowindow.setContent("<p>Walk ID :  <?php echo $request_id; ?><br/>Dropoff :  <?php echo $dropoff_address; ?></p>");
                        infowindow.open(map, dmarker);
                    });
                    map.fitBounds(bounds);
                    poly.setPath(path);

                    var flightPlanCoordinates=[];
                    var data = '';
                    var s='';
                    window.setInterval(function() {
                        data = getLocation('<?php echo $request_id ?>', '<?php echo $walker_id ?>');
                        if (data != '') {
                            console.log(data);
                            if(s !== data.latitude){
                                if(typeof ddmarker !== 'undefined'){
                                    ddmarker.setMap(null);
                                }
                                flightPlanCoordinates = [
//                                new google.maps.LatLng(parseFloat(data.latitude), parseFloat(data.longitude)),
                                    ddmarker = new google.maps.Marker({
                                        position: new google.maps.LatLng(parseFloat(data.latitude), parseFloat(data.longitude)),
                                        map: map,
                                        draggable: false,
                                        icon: driver_icon.icon,
                                        shadow: driver_icon.shadow
                                    }),
                                    bounds.extend(ddmarker.getPosition()),
                                    google.maps.event.addListener(ddmarker, "click", function () {
                                        infowindow.setContent("<p><b>Provider </b><br/>Walk ID :  " + data.request_id + "<br/>Name :  " + data.walker_name + "<br/>Phone :  " + data.walker_phone + "</p>");
                                        infowindow.open(map, ddmarker);
                                    }),

                                ];
                                ddmarker.setMap(map);
                                s = data.latitude;
                            }
                        }
                    },500);

                    var flightPath = new google.maps.Polyline({
                        path: flightPlanCoordinates,
                        geodesic: true,
                        strokeColor: '#FF0000',
                        strokeOpacity: 1.0,
                        strokeWeight: 2
                    });
                    flightPath.setMap(map);
                    service.route({
                        origin: src,
                        destination: des,
                        travelMode: google.maps.DirectionsTravelMode.DRIVING
                    }, function (result, status) {
                        if (status == google.maps.DirectionsStatus.OK) {
                            for (var i = 0, len = result.routes[0].overview_path.length; i < len; i++) {
                                path.push(result.routes[0].overview_path[i]);
                            }
                            poly.setPath(path);
                            map.fitBounds(bounds);
                        }
                    });
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
                '<img src="<?php echo asset_url(); ?>/image/start_pin_flag.png" style="height:25px;"/> Ride Start <br />' +
                '<img src="<?php echo asset_url(); ?>/image/end_pin_flag.png" style="height:25px;"/> Ride End <br />';
            controlUI.appendChild(controlText);
        }
        google.maps.event.addDomListener(window, 'load', load);


	</script>

@stop
